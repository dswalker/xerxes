<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Primo;

use Xerxes;
use Xerxes\Record\Format as RecordFormat;
use Xerxes\Record\Link;
use Xerxes\Utility\Parser;

/**
 * Primo Record
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Record extends Xerxes\Record
{
	protected $source = "primo";
	protected $open_url; // pc-generated openurl
	
	/**
	 * Get an OpenURL 1.0 formatted URL
	 *
	 * @param string $strResolver	base url of the link resolver
	 * @param string $strReferer	referrer (unique identifier)
	 * @return string
	 */
	
	public function getOpenURL($resolver, $referer = null, $para_delimiter = '&')
	{
		$open_url = $resolver . '?' . $this->open_url; // use pc supplied openurl
	
		// special cooking for sfx
	
		if ( $this->format->getNormalizedFormat() == RecordFormat::Journal )
		{
			$open_url .= '&sfx.ignore_date_threshold=1';
		}
	
		return $open_url;
	}
	
	public function map()
	{
		// score
		
		$this->score = (string) $this->document->documentElement->getAttribute("RANK");
		
		// record data
		
		$record = $this->document->documentElement->getElementsByTagName("record")->item(0);
		
		// header('Content-type:text/xml'); echo $this->document->saveXML(); exit;
		
		$control = $this->getElement($record, "control");
		$display = $this->getElement($record, "display");
		$search = $this->getElement($record, "search");
		$sort = $this->getElement($record, "sort");
		$delivery = $this->getElement($record, "delivery");
		$addata = $this->getElement($record, "addata");
		$facets = $this->getElement($record, "facets");
		
		// document data
		
		$doc_links = $this->getElement($this->document->documentElement, "LINKS");
		
		$sourceid = "";
		
		if ( $control != null)
		{
			$sourceid = $this->getElementValue($control,"sourceid");
		}
		
		if ( $display != null)
		{
			// database name
			
			$this->database_name = $this->getElementValue($display,"source");
			$this->database_name = strip_tags($this->database_name);
			
			// journal
			
			$this->journal = $this->getElementValue($display,"ispartof");
			
			// snippet
			
			$this->snippet = $this->getElementValue($display,"snippet"); 
			$this->snippet = trim(strip_tags($this->snippet));
			
			// description
			
			$this->abstract = $this->getElementValue($display,"description");
			
			if ( substr($this->abstract, 0, 6) == '&nbsp;')
			{
				$this->abstract =  substr($this->abstract, 6); 
			}
			
			$this->abstract = trim(strip_tags($this->abstract));
			
			// language

			$language = $this->getElementValue($display,"language");
			$languages = array();
			
			foreach ( explode(';', $language) as $lang )
			{
				$languages[] = Language::getLanguageLabel(trim($lang));
			}
			
			$this->language = implode(', ', $languages);
			
			// peer reviewed
			
			$peer_reviewed = $this->getElementValue($display,'lds50');
			
			if ( $peer_reviewed == 'peer_reviewed' )
			{
				$this->refereed = true;
			}
		}
		
		if ( $search != null)
		{
			// record id
			
			$this->record_id = $this->getElementValue($search,"recordid");
			
			// year
			
			$this->year = $this->getElementValue($search,"creationdate");
			$this->year = substr($this->year, 0, 4);
			
			// issn
			
			$issn = $this->getElementValue($search,"issn");
			$issn = preg_replace('/\D/', "", $issn);
			array_push($this->issns, $issn);
			
			// authors
			
			$authors = $this->getElementValues($search,"creatorcontrib");
			
			foreach ( $authors as $author )
			{
				array_push($this->authors, new Xerxes\Record\Author($author, null, "personal"));
			}
			
			// format
			
			$format = $this->getElementValue($search,"rsrctype");
			$this->format()->setInternalFormat($format);
			
			// create a readable display
			
			$format_display = self::createReadableLabel($format);
			$this->format()->setPublicFormat($format_display);
		}		
		
		// article data
		
		if ( $addata != null)
		{
			$this->journal_title = $this->start_page = $this->getElementValue($addata,"jtitle");
			$this->volume = $this->getElementValue($addata,"volume");
			$this->issue = $this->getElementValue($addata,"issue");
			$this->start_page = $this->getElementValue($addata,"spage");
			$this->end_page = $this->getElementValue($addata,"epage");
			
			// primo's own ris type
			
			$ris_type = $this->getElementValue($addata,'ristype');
			
			if ( $ris_type != "" )
			{
				$this->format()->setNormalizedFormat($ris_type);
			}
			
			// abstract 
			
			$abstract = $this->getElementValue($addata,"abstract");
			
			if ( $this->abstract == "" ) // only take this one if none set above
			{
				$this->abstract = strip_tags($abstract);
			}
		}
		
		// subjects
		
		if ( $facets != null )
		{
			$topics = $this->getElementValues($facets,"topic");
			
			foreach ( $topics as $topic )
			{
				$subject_object = new Xerxes\Record\Subject();
				$subject_object->value = $topic;
				$subject_object->display = $topic;
				
				array_push(	$this->subjects, $subject_object);
			}
			
		}

		// title
		
		if ( $sort != null)
		{
			$this->title = $this->getElementValue($sort,"title");
		}
		
		// links
		
		if ($doc_links != null )
		{
			// openurl
			// @todo: figure out openurlfulltext
			
			$openurl = $this->getElementValue($doc_links,"openurl");
			
			if ( $openurl != null )
			{
				$this->open_url = Parser::removeLeft($openurl, '?');
			}
			
			// direct link
			
			$link_to_source = $this->getElementValue($doc_links,"linktorsrc");
			
			$delivery_type = "";
				
			if ( $delivery != null )
			{
				$delivery_type = $this->getElementValue($delivery,"fulltext");
			}
			
			$open_access = "";
			
			if ( $addata != null )
			{
				$open_access = $this->getElementValue($addata,"oa");
			}
			
			if ( $link_to_source != "" && $delivery_type == 'fulltext_linktorsrc' && $open_access == 'free_for_read')
			{
				$link = new Link($link_to_source);
				$link->setType(Link::ONLINE);
				$this->links[] = $link;
			}
		}
		
		// Gale title clean-up, because for some reason unknown to man they put weird 
		// notes and junk at the end of the title. so remove them here and add them to notes.		
		
		if ( stristr($sourceid, "gale") || stristr($sourceid, "muse"))
		{
			$gale_regex = '/\(([^)]*)\)/';
			$matches = array();
	
			if ( preg_match_all( $gale_regex, $this->title, $matches ) != 0)
			{
				$this->title = preg_replace ( $gale_regex, "", $this->title );
				
				foreach ( $matches[1] as $match )
				{
					array_push($this->notes, $match);
				}
			}
			
			if ( strpos($this->abstract, 'the full-text of this article') !== false )
			{
				$this->abstract = Parser::removeLeft($this->abstract, 'Abstract:');
			}
		}
		
		// abstract clean-up
		
		$this->abstract = str_replace('&nbsp;', ' ', $this->abstract);
		
		while ( ord(substr($this->abstract, 0, 1)) == 194 )
		{
			$this->abstract = substr($this->abstract, 2);
		}
		
		$this->abstract = trim($this->abstract);
	}
	
	public static function createReadableLabel($format)
	{
		$format_display = str_replace('audio_video', 'Audio/Video', $format);
			
		$format_array = explode('_', $format_display);
			
		for ( $x = 0; $x < count($format_array); $x++ )
		{
			$format_array[$x] = ucfirst($format_array[$x]);
		}
			
		$format_display = implode(' ', $format_array);
		
		return $format_display;
	}
	
	protected function getElement(\DOMNode $node, $name)
	{
		$elements = $node->getElementsByTagName($name);
		
		if ( count($elements) > 0 )
		{
			return $elements->item(0);
		}
		else
		{
			return null;
		}
	}
	
	protected function getElementValue(\DOMNode $node, $name)
	{
		$element = $this->getElement($node, $name);
		
		if ( $element != null )
		{
			return $element->nodeValue;
		}
		else
		{
			return null;
		}
	}
	
	protected function getElementValues(\DOMNode $node, $name)
	{
		$values = array();
		
		$elements = $node->getElementsByTagName($name);
		
		foreach ( $elements as $node )
		{
			array_push($values, $node->nodeValue);
		}
		
		return $values;
	}		
}
