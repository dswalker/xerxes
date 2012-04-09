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
	
	protected function normalizeFormat($public)
	{
		switch($public)
		{
			// articles
			
			case 'Article':
			case 'Journal Article':
			case 'Publication Article':
			case 'Trade Publication Article':
				
				return Format::ArticleJournal;
				break;
				
			case 'Newspaper Article':
				
				return Format::ArticleNewspaper;
				break;				
				
			case 'Magazine Article':
			case 'Newsletter':
				
				return Format::ArticleMagazine;
				break;
			
			case 'Book Review':
				
				return Format::BookReview;
				break;

			// periodicals
				
			case 'Journal / eJournal':
				
				return Format::Journal;
				break;
				
			case 'Magazine':
			case 'Newspaper':
				
				return Format::Periodical;
				break;
				
			// books
				
			case 'Book / eBook':
				
				return Format::Book;
				break;				

			case 'Book Chapter':
			
				return Format::BookSection;
				break;
				
			case 'Reference':
				
				return Format::EncyclopediaArticle;
				break;
				
			// dissertation/thesis
				
			case 'Dissertation':
			case 'Student Thesis':

				return Format::Thesis;
				break;
				
			// reports & other documents
				
			case 'Report':
			case 'Market Research':
			case 'Technical Report':
				
				return Format::Report;
				break;				
				
			case 'Patent':
				
				return Format::Patent;
				break;				
				
			case 'Standard':
			
				return Format::Standard;
				break;
			
			case 'Conference Proceeding':

				return Format::ConferenceProceeding;
				break;
				
			case 'Government Document':
				
				return Format::GovernmentDocument;
				break;
				
			case 'Case':
				
				return Format::LegalRule;
				break;

			case 'Pamphlet':
				
				return Format::Pamphlet;
				break;
				
			// maps	
				
			case 'Atlas':
			case 'Map':
				
				return Format::Map;
				break;				
				
			// audio / visual
				
			case 'Album':
			case 'Audio Recording':
			case 'Music Recording':
			case 'Sound Recording':
			case 'Spoken Word Recording':
				
				return Format::SoundRecording;
				break;
			
			case 'Image':
			case 'Photograph':
			case 'Postcard':
			case 'Slide':
				
				return Format::Image;
				break;
				
			case 'Sheet Music':
			case 'Music Score':
				
				return Format::MusicalScore;
				break;
			
			case 'Film':
			case 'Video Recording':
				
				return Format::VideoRecording;
				break;
				
			case 'Architectural Drawing':
			case 'Art':
			case 'Blueprints':
			case 'Drawing':
			case 'Graphic Arts':
			case 'Painting':
			case 'Poster':

				return Format::Artwork;
				break;
				
			// computer & web resources
				
			case 'Computer File':
			case 'Compact Disc':
				
				return Format::ComputerProgram;
				break;
				
			case 'Database':
				
				return Format::OnlineDatabase;
				break;
				
			case 'Data Set':
				
				return Format::Dataset;
				break;

			case 'Learning Object':
				
				return Format::OnlineMultimedia;
				break;
							
			case 'Electronic Resource':
			case 'Library Research Guide':
			case 'Research Guide':
			case 'Web Resource':
									
				return Format::WebPage;
				break;
				
			// physical objects				
				
			case 'Artifact':
			case 'Ceremonial Object':
			case 'Clothing':
			case 'Equipment':
			case 'Furnishing':
			case 'Houseware':
			case 'Model':
			case 'Musical Instrument':
			case 'Implements':
			case 'Realia':
			case 'Tool':
				
				return Format::PhysicalObject;
				break;
			
			case 'Kit':
			
				return Format::Kit;
				break;
			
			case 'Mixed':
			
				return Format::MixedMaterial;
				break;
				
			// archival & unpublished works

			case 'Archival Material':
			case 'Archival Material/Manuscripts':
			case 'Finding Aid':
			case 'Special Collection':
				
				return Format::ArchivalMaterial;
				break;
				
			case 'Manuscript':
				
				return Format::Manuscript;
				break;				
				
			case 'Personal Article':
			case 'Personal Narrative':
				
				return Format::PersonalCommunication;
				break;				
				
			case 'Course Reading':				
			case 'Exam':
			case 'Paper':
			case 'Print':
			case 'Text':
			case 'Transcript':
			case 'Film Script':
			case 'Catalog':
			case 'Poem':
			case 'Publication':

				return Format::Manuscript;
				break;
				
			// unknown stuff	
				
			case 'Performance':
			case 'Play':
			case 'Presentation':
			
			case 'Library Holding':
			case 'Microform':
			case 'Microfilm':				
				
			default:
				
				return Format::Unknown;
				break;
		
		}
	}
}
	
