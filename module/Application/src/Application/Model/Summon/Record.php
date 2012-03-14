<?php

namespace Application\Model\Summon;

use Xerxes,
	Xerxes\Record\Format,
	Xerxes\Utility\Parser;

/**
 * Extract properties for books, articles, and dissertations from Summon
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Record extends Xerxes\Record
{
	private $original_array;

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
	
	public function load($document)
	{
		$this->original_array = $document;
		$this->map($document);
		$this->cleanup();
	}	
	
	protected function map($document)
	{
		$this->source = "Summon";
		$this->database_name = $this->extractValue($document, "Source/0");;
		
		$this->record_id = $this->extractValue($document, "ID/0");
		$this->score = $this->extractValue($document, "Score/0");
		
		// title
		
		$this->title = $this->extractValue($document, "Title/0");
		$this->sub_title = $this->extractValue($document, "Subtitle/0");
		
		// basic info
		
		$this->language = $this->extractValue($document, "Language/0");
		$this->year = $this->extractValue($document, "PublicationDate_xml/0/year");
		$this->extent = $this->extractValue($document, "PageCount/0");
		
		// format
		
		$format = $this->extractValue($document, "ContentType/0");
		
		
		
		// @todo: proper format mapping
		
		if ( $format == "Journal Article") $format = "Article";
		
		$this->format->setFormat($format);
		
		$this->format->setInternalFormat(Format::Article);
		if ( $format == "Conference Proceeding") $this->format->setInternalFormat(Format::ConferenceProceeding);
		if ( $format == "Dissertation") $this->format->setInternalFormat(Format::Thesis);
		
		
		
		
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
		
		$openurl = $this->extractValue($document, "openUrl");
		$direct_link = $this->extractValue($document, "url/0");
		$uri = $this->extractValue($document, "URI/0");
		
		/*
		echo " <a href='$direct_link'>direct</a>: " . strlen($direct_link) .
			" openurl: " . strlen($openurl) .
			" id: " . strlen($this->record_id) . 
			" uri: " . strlen($uri) . 
			"<br />";
		*/
		
		// the length of the fields gives an indication if the direct link field
		// goes directly to an external link, or simply the link resolver
		
		if ( 100 + strlen($openurl) - strlen($direct_link) > 0 )
		{
			$this->links[] = new Xerxes\Record\Link($direct_link, Xerxes\Record\Link::ONLINE);
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
	 * Conventience function for extracting data from Summon json
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
	
	protected function convertFormatToInternal()
	{
		/*
		 	Journal Article
			Book Review
			Dissertation
			Patent
			Newsletter
			Trade Publication Article
			Book Chapter
			Conference Proceeding
			Standard
			Publication
			Government Document
			Image
			Report
			Audio Recording
			Data Set
			Photograph
			Archival Material
			Technical Report
			Journal / eJournal
			Music Recording
			Electronic Resource
			Manuscript
			Paper
			Map
			Sheet Music
			Music Score
			Video Recording
			Special Collection
			Play
			Personal Narrative
			Microform
			Newspaper
			Student Thesis
			Market Research
			Pamphlet
			Presentation
			Poem
			Art
			Artifact
			Architectural Drawing
			Realia
			Magazine
			Exam
			Poster
			Magazine Article
			Transcript
			Archival Material/Manuscripts
			Computer File
			Compact Disc
			Publication Article
			Postcard
			Library Holding
			Sound Recording
			Spoken Word Recording
			Slide
			Print
			Drawing
			Painting
			Course Reading
			Library Research Guide
			Film Script
			Blueprints
			Kit
			Finding Aid
			Case
			Ceremonial Object
			Mixed
			Catalog
			Houseware
			Text
			Film
			Equipment
			Performance
			Learning Object
			Album
			Model
			Furnishing
			Personal Article
			Tool
			Atlas
			Musical Instrument
			Clothing
			Article
			Database
			Graphic Arts
			Implements
			Microfilm
			Newspaper Article
			Book / eBook
			Reference
			Web Resource
			Research Guide		
		 */
	}
	
	
}
	
