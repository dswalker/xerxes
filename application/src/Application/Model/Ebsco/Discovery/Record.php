<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Ebsco\Discovery;

use Xerxes\Utility\Json;

use Xerxes;
use Xerxes\Record\Format;
use Xerxes\Record\Link;
use Xerxes\Utility\Parser;

/**
 * Extract properties for books, articles, and dissertations from EDS
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Record extends Xerxes\Record
{
	protected $source = "EDS";
	
	private $original_array; // main data from ebsco
	private $config; // summon config

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
	 * Map the source data to record properties
	 */	
	
	protected function map(array $document)
	{
		$json = new Json($document);
		
		$this->database_name = $json->extractValue('Header/DbLabel');
		
		$this->record_id = $json->extractValue('Header/DbId') . '-' . $json->extractValue('Header/An');
		$this->score = $json->extractValue('Header/RelevancyScore');
		
		// title
		
		$this->title = $json->extractValue('RecordInfo/BibRecord/BibEntity/Titles/0/TitleFull');
		$this->sub_title;
		
		// basic info
		
		$this->language = $json->extractValue("RecordInfo/BibRecord/BibEntity/Languages/0/Text");
		$this->extent = $json->extractValue("RecordInfo/BibRecord/BibEntity/PhysicalDescription/Pagination/PageCount");
		
		// date
		
		$this->year = (int) $json->extractValue('RecordInfo/BibRecord/BibRelationships/IsPartOfRelationships/0/BibEntity/Dates/0/Y');
		$this->month = (int) $json->extractValue('RecordInfo/BibRecord/BibRelationships/IsPartOfRelationships/0/BibEntity/Dates/0/M');
		$this->day = (int) $json->extractValue('RecordInfo/BibRecord/BibRelationships/IsPartOfRelationships/0/BibEntity/Dates/0/D');
		
		// format
		
		$format = $json->extractValue("Header/PubType");
		
		$this->format->setPublicFormat($format);
		$this->format->setInternalFormat($format);
		$this->format->setNormalizedFormat($this->normalizeFormat($format));
		
		// Item data
		
		foreach ( $json->extractData('Items') as $item )
		{
			// title
				
			if ( $item['Name'] == 'Title')
			{
				$this->title = $item['Data'];
			}
			
			// abstract
			
			if ( $item['Name'] == 'Abstract')
			{
				$this->abstract = $item['Data'];
			}
			
			// publication source
			
			if ( $item['Name'] == 'TitleSource' && $this->journal == "") // only grab the first one
			{
				$this->journal = strip_tags(html_entity_decode($item['Data']));
			}

			// pmid
				
			if ( $item['Label'] == 'PMID')
			{
				$this->pubmed_id = $item['Data'];
			}	
			
			// notes
			
			if ( $item['Label'] == 'Notes')
			{
				$this->notes[] = $item['Data'];
			}			
		}
		
		// books
		
		$this->edition = $json->extractValue("Edition/0");
		$this->publisher = $this->toTitleCase($json->extractValue("Publisher/0"));
		$this->place = $json->extractValue("PublicationPlace_xml/0/name");
		
		// article
		
		$this->journal_title = $json->extractValue("RecordInfo/BibRecord/BibRelationships/IsPartOfRelationships/0/BibEntity/Titles/0/TitleFull");
		$this->start_page = $json->extractValue("RecordInfo/BibRecord/BibEntity/PhysicalDescription/Pagination/StartPage");
		
		if ( $this->start_page != "" && $this->extent != "" )
		{
			$this->end_page = $this->start_page + $this->extent - 1;
		}
		
		foreach ( $json->extractData('RecordInfo/BibRecord/BibRelationships/IsPartOfRelationships/0/BibEntity/Numbering') as $number )
		{
			if ( $number['Type'] == 'volume' )
			{
				$this->volume = $number['Value'];
			}	
			elseif ( $number['Type'] == 'issue' )
			{
				$this->issue = $number['Value'];
			}
		}
		
		// links
		
		foreach ( $json->extractData('FullText/Links') as $link )
		{
			$type = $link['Type'];
			
			if ( strstr($type, 'pdf') )
			{
				$type = Xerxes\Record\Link::PDF;
			}
			
			if ( array_key_exists('Url', $link) )
			{
				$url = $link['Url'];
				$this->links[] = new Xerxes\Record\Link( $url, $type );
			}
		}
		
		// subjects
		
		foreach ( $json->extractData('RecordInfo/BibRecord/BibEntity/Subjects') as $subject)
		{
			$subject = Parser::toSentenceCase($subject['SubjectFull']);
			
			$subject_object = new Xerxes\Record\Subject();
			$subject_object->display = $subject;
			$subject_object->value = $subject;
			array_push($this->subjects, $subject_object);
		}
		
		// identifiers
		
		foreach ( $json->extractData('RecordInfo/BibRecord/BibEntity/Identifiers') as $identifier )
		{
			// doi
			
			if ( $identifier['Type'] == 'doi')
			{
				$this->doi = $identifier['Value'];
			}
		}
		
		foreach ( $json->extractData('RecordInfo/BibRecord/BibRelationships/IsPartOfRelationships/0/BibEntity/Identifiers') as $identifier )
		{
			// isbn
				
			if ( strstr( $identifier['Type'], 'isbn') )
			{
				$this->isbns[] =  $identifier['Value'];
			}
			
			// issn
				
			if ( strstr( $identifier['Type'], 'issn') )
			{
				$this->issns[] = $identifier['Value'];
			}
		}
		
		// authors
		
		foreach ( $json->extractData('RecordInfo/BibRecord/BibRelationships/HasContributorRelationships') as $author )
		{
			$json_author = new Json($author);
			
			$author_object = new Xerxes\Record\Author($json_author->extractValue('PersonEntity/Name/NameFull'), null, 'personal');
			
			array_push($this->authors, $author_object);
		}
		
		if ( array_key_exists('debugrec', $_GET) )
		{
			header('Content-type: text/plain'); print_r($this); exit;
		}
	}
	
	/**
	 * Convert to Xerxes normalized format
	 * 
	 * @param string $format Summon format designation
	 */
	
	protected function normalizeFormat($format)
	{
		return $format;
		
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