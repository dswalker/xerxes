<?php

/**
 * Primo Record
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Primo_Record extends Xerxes_Record
{
	protected $source = "primo";
	
	public function map()
	{
		// score
		
		$this->score = (string) $this->document->documentElement->getAttribute("RANK");
		
		// record data
		
		$record = $this->document->documentElement->getElementsByTagName("record")->item(0);
		
		// print $this->document->saveXML();
		
		$display = $this->getElement($record, "display");
		$search = $this->getElement($record, "search");
		$sort = $this->getElement($record, "sort");
		$addata = $this->getElement($record, "addata");
		$facets = $this->getElement($record, "facets");
		
		$format = array(); // format yo
		
		if ( $display != null)
		{
			// database name
			
			$this->database_name = $this->getElementValue($display,"source");
			$this->database_name = strip_tags($this->database_name);
			
			// journal
			
			$this->journal = $this->getElementValue($display,"ispartof");
			
			// snippet
			
			$this->snippet = $this->getElementValue($display,"snippet"); 
			$this->snippet = strip_tags($this->snippet);
			
			// description
			
			$this->abstract = $this->getElementValue($display,"description");
			$this->abstract = strip_tags($this->abstract);
			
			// language

			$this->language = $this->getElementValue($display,"language");
		}
		
		if ( $search != null)
		{
			// record id
			
			$this->record_id = $this->getElementValue($search,"recordid");
			
			// year
			
			$this->year = $this->getElementValue($search,"creationdate");
			
			// issn
			
			$issn = $this->getElementValue($search,"issn");
			$issn = preg_replace('/\D/', "", $issn);
			array_push($this->issns, $issn);
			
			// authors
			
			$authors = $this->getElementValues($search,"creatorcontrib");
			
			foreach ( $authors as $author )
			{
				array_push($this->authors, new Xerxes_Record_Author($author, null, "personal"));
			}
		}		
		
		// article data
		
		if ( $addata != null)
		{
			$this->journal_title = $this->start_page = $this->getElementValue($addata,"jtitle");
			$this->volume = $this->getElementValue($addata,"volume");
			$this->issue = $this->getElementValue($addata,"issue");
			$this->start_page = $this->getElementValue($addata,"spage");
			$this->end_page = $this->getElementValue($addata,"epage");
			
			// genre
			
			array_push($format, $this->getElementValue($addata,"genre"));
			
			// abstract 
			
			$abstract = $this->getElementValue($addata,"abstract");
			
			if ( $this->abstract == "" )
			{
				$this->abstract = strip_tags($abstract);
			}

			// gale madness
			
			if ( stristr($this->database_name, "gale") )
			{
				if ( strpos($this->abstract, 'the full-text of this article') !== false )
				{
					$this->abstract = Xerxes_Framework_Parser::removeLeft($this->abstract, 'Abstract:');
				}
			}
		}
		
		// subjects
		
		if ( $facets != null )
		{
			$topics = $this->getElementValues($facets,"topic");
			
			foreach ( $topics as $topic )
			{
				$subject_object = new Xerxes_Record_Subject();
				$subject_object->value = $topic;
				$subject_object->display = $topic;
				
				array_push(	$this->subjects, $subject_object);
				
				if ( stripos($topic, 'book review') !== false )
				{
					array_push($format, 'book review');
				}
			}
			
		}

		// title
		
		if ( $sort != null)
		{
			$this->title = $this->getElementValue($sort,"title");
		}
		
		// Gale title clean-up, because for some reason unknown to man they put weird 
		// notes and junk at the end of the title. so remove them here and add them to notes.		
		
		// @todo factor this out somehow? since this is very similar to Gale MetalibRecord code
		
		if ( stristr($this->database_name, "gale") || stristr($this->database_name, "muse"))
		{
			$strGaleRegExp = '/\(([^)]*)\)/';
			$arrMatches = array();
	
			if (preg_match_all ( $strGaleRegExp, $this->title, $arrMatches ) != 0)
			{
				$this->title = preg_replace ( $strGaleRegExp, "", $this->title );
				
				foreach ( $arrMatches[1] as $strMatch )
				{
					array_push($this->notes, $strMatch);
					array_push($format, $strMatch);
				}
			}
		}
		
		// format

		$this->format->determineFormat($format);	
	}
	
	protected function getElement($node, $name)
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
	
	protected function getElementValue($node, $name)
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
	
	protected function getElementValues($node, $name)
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

?>