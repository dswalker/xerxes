<?php

namespace Application\Model\Summon;

use Xerxes,
	Xerxes\Record\Format,
	Xerxes\Record\Link,
	Xerxes\Utility\Parser;

/**
 * Extract properties for books, articles, and dissertations from Summon
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @version
 * @package Xerxes
 */

class Record extends Xerxes\Record
{
	protected $source = "Summon";
	
	private $original_array; // main data from summon
	private $config; // summon config
	
	protected $direct_link; // summon direct link

	public function __sleep()
	{
		$this->serialized = $this->original_array;
		return array("serialized");
	}
	
	public function __wakeup()
	{
		parent::__construct();
		$this->load($this->serialized);
	}	
	
	/**
	 * Load, map, and clean-up record data from Summon
	 *
	 * @param array $document
	 */	
	
	public function load($document)
	{
		$this->original_array = $document;
		$this->map($document);
		$this->cleanup();
	}
	
	/**
	 * Lazy load config object
	 * 
	 * @return Config
	 */
	
	public function config()
	{
		if ( ! $this->config instanceof Config )
		{
			$this->config = Config::getInstance();
		}
		
		return $this->config;
	}
	
	/**
	 * Get an OpenURL 1.0 formatted URL
	 *
	 * @param string $strResolver	base url of the link resolver
	 * @param string $strReferer	referrer (unique identifier)
	 * @return string
	 */	
	
	public function getOpenURL($resolver, $referer = null, $para_delimiter = '&')
	{
		if ( $this->config()->getConfig('direct_linking', false, false ) )
		{
			return $this->direct_link;
		}
		else
		{
			return parent::getOpenURL($resolver, $referer, $para_delimiter);
		}
	}
	
	/**
	 * Map the source data to record properties
	 */	
	
	protected function map($document)
	{
		$this->database_name = $this->extractValue($document, "Source/0");
		
		$this->record_id = $this->extractValue($document, "ID/0");
		$this->score = $this->extractValue($document, "Score/0");
		
		// title
		
		$this->title = $this->extractValue($document, "Title/0");
		$this->sub_title = $this->extractValue($document, "Subtitle/0");
		
		// basic info
		
		$this->language = $this->extractValue($document, "Language/0");
		$this->extent = $this->extractValue($document, "PageCount/0");
		
		// date
		
		$this->year = (int) $this->extractValue($document, "PublicationDate_xml/0/year");
		$this->month = (int) $this->extractValue($document, "PublicationDate_xml/0/month");
		$this->day = (int) $this->extractValue($document, "PublicationDate_xml/0/day");
		
		// only use this if it has text in it
		
		$publication_date = $this->extractValue($document, "PublicationDate_xml/0/text");
		
		if ( preg_match('/[a-zA-Z]{1}/', $publication_date) )
		{
			$this->publication_date = $publication_date;
		}
		
		// format
		
		$format = $this->extractValue($document, "ContentType/0");
		
		$this->format->setPublicFormat($format);
		$this->format->setInternalFormat($format);
		$this->format->setNormalizedFormat($this->normalizeFormat($format));
		
		// summary
		
		$this->snippet = $this->extractValue($document, "Snippet/0");
		$this->abstract = $this->extractValue($document, "Abstract/0");
		
		// books
		
		$this->edition = $this->extractValue($document, "Edition/0");
		$this->publisher = $this->toTitleCase($this->extractValue($document, "Publisher/0"));
		$this->place = $this->extractValue($document, "PublicationPlace_xml/0/name");
		
		// article
		
		$this->journal_title = $this->toTitleCase($this->extractValue($document, "PublicationTitle/0"));
		$this->issue = $this->extractValue($document, "Issue/0");
		$this->volume = $this->extractValue($document, "Volume/0");
		$this->start_page = $this->extractValue($document, "StartPage/0");
		$this->end_page = $this->extractValue($document, "EndPage/0");
		$this->doi = $this->extractValue($document, "DOI/0");
		
		// direct link
		
		$this->direct_link = $this->extractValue($document, "link");
		
		// original record link
		
		$uri = $this->extractValue($document, "URI/0");
		
		if ( $uri != '' )
		{
			$this->links[] = new Link($uri, Link::ORIGINAL_RECORD);
		}
		
		// subscription
		
		$has_full_text = (int) $this->extractValue($document, 'hasFullText');
		$in_holdings = (int) $this->extractValue($document, 'inHoldings');
			
		if ($has_full_text == 1 && $in_holdings == 1)
		{
			$this->setSubscription(true);
		}		
		
		// peer reviewed
		
		if ( $this->extractValue($document, "IsPeerReviewed/0") == "true" )
		{
			$this->refereed = true;
		}
		
		// subjects
		
		if ( array_key_exists('SubjectTerms', $document) )
		{
			foreach ( $document['SubjectTerms'] as $subject)
			{
				$subject = Parser::toSentenceCase($subject);
				
				$subject_object = new Xerxes\Record\Subject();
				$subject_object->display = $subject;
				$subject_object->value = $subject;
				array_push($this->subjects, $subject_object);
			}
		}

		// isbn
		
		if ( array_key_exists('ISBN', $document) )
		{
			$this->isbns = $document['ISBN'];
		}

		// issn
		
		if ( array_key_exists('ISSN', $document) )
		{
			$this->issns = $document['ISSN'];
		}
		elseif ( array_key_exists('EISSN', $document) )
		{
			$this->issns = $document['EISSN'];
		}
		
		// notes

		if ( array_key_exists('Notes', $document) )
		{
			$this->notes = $document['Notes'];
		}
		
		if ( array_key_exists('Genre', $document) )
		{
			$this->notes = $document['Genre'];
		}		
		
		// authors
		
		if ( array_key_exists('Author_xml', $document) )
		{
			foreach ( $document['Author_xml'] as $author )
			{
				$author_object = new Xerxes\Record\Author();
				
				if ( array_key_exists('givenname', $author) )
				{
					$author_object->type = "personal";
					$author_object->last_name = $author['surname'];
					$author_object->first_name = $author['givenname'];
				}
				elseif ( array_key_exists('fullname', $author) )
				{
					
					$author_object = new Xerxes\Record\Author($author['fullname'], null, 'personal');
				}
				
				array_push($this->authors, $author_object);
			}
		}
	}
	
	/**
	 * Convenience function for extracting data from Summon json
	 * 
	 * @param array	$document
	 * @param string $path		path to the value
	 * 
	 * @return mixed			strign if found data, null otherwise
	 */
	
	private function extractValue($document, $path )
	{
		$path = explode('/', $path);
		$pointer = $document;
		
		foreach ( $path as $part )
		{
			if ( array_key_exists($part, $pointer) )
			{
				$pointer = $pointer[$part];
			}
		}
		
		if ( is_array($pointer) )
		{
			return ""; // we didn't actually get our value
		}
		else
		{
			return strip_tags($pointer);
		}
	}
	
	/**
	 * Convert to Xerxes normalized format
	 * 
	 * @param string $format Summon format designation
	 */
	
	protected function normalizeFormat($format)
	{
		$map = array(
			'Album' => 'SoundRecording',
			'Architectural Drawing' => 'Artwork',
			'Archival Material' => 'ArchivalMaterial',
			'Archival Material/Manuscripts' => 'ArchivalMaterial',
			'Art' => 'Artwork',
			'Article' => 'ArticleJournal',
			'Artifact' => 'PhysicalObject',
			'Atlas' => 'Map',
			'Audio Recording' => 'SoundRecording',
			'Blueprints' => 'Artwork',
			'Book' => 'Book',
			'eBook' => 'Book',
			'Book Chapter' => 'BookSection',
			'Book Review' => 'BookReview',
			'Case' => 'LegalRule',
			'Catalog' => 'Manuscript',
			'Ceremonial Object' => 'PhysicalObject',
			'Citation' => 'Unknown',
			'Clothing' => 'PhysicalObject',
			'Compact Disc' => 'ComputerProgram',
			'Computer File' => 'ComputerProgram',
			'Conference Proceeding' => 'ConferenceProceeding',
			'Course Reading' => 'Manuscript',
			'Data Set' => 'Dataset',
			'Database' => 'OnlineDatabase',
			'Dissertation' => 'Thesis',
			'Drawing' => 'Artwork',
			'Electronic Resource' => 'WebPage',
			'Equipment' => 'PhysicalObject',
			'Exam' => 'Manuscript',
			'Film' => 'VideoRecording',
			'Film Script' => 'Manuscript',
			'Finding Aid' => 'ArchivalMaterial',
			'Furnishing' => 'PhysicalObject',
			'Government Document' => 'GovernmentDocument',
			'Graphic Arts' => 'Artwork',
			'Houseware' => 'PhysicalObject',
			'Image' => 'Image',
			'Implements' => 'PhysicalObject',
			'Interactive Media' => 'OnlineMultimedia',
			'Journal' => 'Journal',
			'eJournal' => 'Journal',
			'Journal Article' => 'ArticleJournal',
			'Learning Object' => 'OnlineMultimedia',
			'Library Holding' => 'Unknown',
			'Magazine' => 'Periodical',
			'Magazine Article' => 'ArticleMagazine',
			'Manuscript' => 'Manuscript',
			'Map' => 'Map',
			'Market Research' => 'Report',
			'Microfilm' => 'Unknown',
			'Microform' => 'Unknown',
			'Mixed' => 'MixedMaterial',
			'Model' => 'Artwork',
			'Music Recording' => 'SoundRecording',
			'Music Score' => 'MusicalScore',
			'Musical Instrument' => 'PhysicalObject',
			'Newsletter' => 'ArticleMagazine',
			'Newspaper' => 'Periodical',
			'Newspaper Article' => 'ArticleNewspaper',
			'Painting' => 'Artwork',
			'Pamphlet' => 'Pamphlet',
			'Paper' => 'Manuscript',
			'Patent' => 'Patent',
			'Performance' => 'Unknown',
			'Personal Article' => 'PersonalCommunication',
			'Personal Narrative' => 'PersonalCommunication',
			'Photograph' => 'Image',
			'Play' => 'Manuscript',
			'Poem' => 'Manuscript',
			'Postcard' => 'Image',
			'Poster' => 'Artwork',
			'Presentation' => 'OnlineMultimedia',
			'Print' => 'Manuscript',
			'Publication' => 'Manuscript',
			'Publication Article' => 'ArticleJournal',
			'Realia' => 'PhysicalObject',
			'Reference' => 'EncyclopediaArticle',
			'Report' => 'Report',
			'Research Guide' => 'WebPage',
			'Sheet Music' => 'MusicalScore',
			'Slide' => 'Image',
			'Sound Recording' => 'SoundRecording',
			'Special Collection' => 'ArchivalMaterial',
			'Spoken Word Recording' => 'SoundRecording',
			'Standard' => 'Standard',
			'Streaming Audio' => 'OnlineMultimedia',
			'Streaming Video' => 'OnlineMultimedia',
			'Student Thesis' => 'Thesis',
			'Technical Report' => 'Report',
			'Text' => 'Manuscript',
			'Tool' => 'PhysicalObject',
			'Trade Publication Article' => 'ArticleJournal',
			'Transcript' => 'Manuscript',
			'Video Recording' => 'VideoRecording',
			'Web Resource' => 'WebPage'
		);
		
		if ( array_key_exists($format, $map) )
		{
			$const = constant('\Xerxes\Record\Format::' . $map[$format]);
			
			if ( $const != null )
			{
				return $const;
			}
			
		}
			
		return Format::Unknown;
	}
	
	public function getOriginalXML($bolString = false)
	{
		// convert original (JSON-based) array to xml
		
		$this->document = Parser::convertToDOMDocument('<original />');
		Parser::addToXML($this->document, 'record', $this->original_array);
		
		return parent::getOriginalXML($bolString);
	}
}