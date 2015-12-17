<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes;

use Xerxes\Record\Author;
use Xerxes\Record\Chapter;
use Xerxes\Record\Subject;
use Xerxes\Record\Format;
use Xerxes\Record\Link;
use Xerxes\Utility\Languages;
use Xerxes\Utility\Parser;

/**
 * Properties for books, media, articles, and dissertations
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Record
{
	protected $source = "";	// source database id
	protected $database_name; // source database name
	protected $database_openurl; // source database identifier for openurl
	protected $score; // relevenace score

	protected $record_id; // canonical record id
	protected $control_number = ""; // the 001 basically, OCLC or otherwise
	protected $oclc_number = ""; // oclc number
	protected $govdoc_number = ""; // gov doc number
	protected $gpo_number = ""; // gov't printing office (gpo) number
	protected $eric_number = ""; // eric document number
	protected $pubmed_id = ""; // pubmed id
	protected $isbns = array(); // isbn
	protected $issns = array(); // issn
	protected $call_number = ""; // lc call number
	protected $doi = ""; // doi

	protected $authors = array(); // authors
	protected $editor = false; // whether primary author is an editor
	
	protected $non_sort = ""; // non-sort portion of title
	protected $title = ""; // main title
	protected $sub_title = ""; // subtitle	
	protected $series_title = ""; // series title
	protected $trans_title = false; // whether title is translated
	protected $uniform_title = ""; // uniform title
	protected $additional_titles = array(); // related titles
	protected $alternate_titles = array(); // alternate versions of the title
	
	protected $place = ""; // place of publication	
	protected $publisher = ""; // publisher	
	
	protected $year = ""; // year of publication
	protected $month = ''; // month of publication
	protected $day = ''; // day of publication
	protected $publication_date; // formatted publication date set by search engine

	protected $edition = ""; // edition
	protected $extent = ""; // total pages
	protected $price = ""; // price

	protected $book_title = ""; // book title (for book chapters)
	protected $book_host_information = ""; // host item data, for book chapters, etc.
	
	protected $journal_title = ""; // journal title
	protected $journal = ""; // journal source information
	protected $short_title = ""; // journal short title
	protected $journal_title_continued_by = array(); // journal continued by title
	protected $journal_title_continues = array(); // journal continues a title
	
	protected $volume = ""; // volume
	protected $issue = ""; // issue
	protected $start_page = ""; // start page
	protected $end_page = ""; // end page

	protected $series = array(); // series info
	
	protected $description = ""; // physical description
	protected $abstract = ""; // abstract
	protected $summary = ""; // summary
	protected $snippet = ""; // snippet
	protected $summary_type = ""; // the type of summary
	protected $language = ""; // primary language of the record
	protected $notes = array(); // notes that are not the abstract, language, or table of contents
	protected $toc = array(); // table of contents note

	protected $degree = ""; // thesis degree conferred
	protected $institution = ""; // thesis granting institution
	
	/**
	 * format
	 * @var Format
	 */
	protected $format = "";
	
	protected $technology = ""; // technology/system format
	
	protected $subjects = array(); // subjects
	
	protected $links = array(); // all supplied links in the record both full text and non
		
	protected $refereed = false; // whether the item is peer-reviewed
	protected $subscription = false; // whether the item is available in library subscription
	protected $physical_holdings = true; // whether record has physical holdings
	
	// utility objects
	
	protected $utility = array(); // register utiltiy objects
	protected $document; // original xml
	protected $serialized; // for serializing the object
	
	/**
	 * Create a Xerxes Record
	 */
	
	public function __construct()
	{
		$this->document = new \DOMDocument();
		$this->format = new Format();
		
		$this->utility[] = "document";
		$this->utility[] = "serialized";
	}
	
	/**
	 * Serialize
	 */
	
	public function __sleep()
	{
		// save only the xml
		
		$this->serialized = $this->document->saveXML();
		return array("serialized");
	}
	
	/**
	 * De-serialize
	 */
	
	public function __wakeup()
	{
		$this->__construct();
		
		// and then we recreate the object (with any new changes we've made)
		// by just loading the saved xml back into the object
		
		$this->loadXML($this->serialized);
	}
	
	/**
	 * Load, map, and clean-up record data from XML
	 * 
	 * @param mixed $xml		XML as DOM, SimpleXML or string
	 */
	
	public function loadXML($xml)
	{
		$this->document = Parser::convertToDOMDocument($xml);
		
		$this->map();
		$this->cleanup();
	}
	
	/**
	 * Map the source data to record properties
	 * 
	 * By default here it maps from the internal xml produced by toXML()
	 */
	
	protected function map()
	{
		$xml = simplexml_load_string($this->document->saveXML());
		
		foreach ( $xml->children() as $child )
		{
			$name = $child->getName();
				
			if ( $name == 'standard_numbers' )
			{
				foreach ( $child->children() as $number )
				{
					$this->addPropertyFromXML($number);
				}
			}
			elseif ( $name == 'authors' )
			{
				foreach ( $child->children() as $author )
				{
					$author_object = new Author();
					$author_object->fromXML($author);
					$this->authors[] = $author_object;
				}
			}
			elseif ( $name == 'links' )
			{
				foreach ( $child->children() as $link )
				{
					$link_object = new Link();
					$link_object->fromXML($link);
					$this->links[] = $link_object;
				}
			}
			elseif ( $name == 'format' )
			{
				$this->format->fromXML($child);
			}
			elseif ( $name == 'subjects' )
			{
				foreach ( $child->children() as $subject )
				{
					$subject_object = new Subject();
					$subject_object->display = (string) $subject->display;
					$subject_object->value = (string) $subject->value;
						
					$this->subjects[] = $subject_object;
				}
			}
			elseif ( $name == 'toc' )
			{
				foreach ( $child->children() as $chapter )
				{
					$chapter_object = new Chapter();
					$chapter_object->title = (string) $chapter->display;
					$chapter_object->author = (string) $chapter->value;
					$chapter_object->statement = (string) $chapter->value;
						
					$this->toc[] = $chapter_object;
				}
			}
			else
			{
				$this->addPropertyFromXML($child);
			}
		}
	}
	
	/**
	 * Property clean-up
	 */
	
	protected function cleanup()
	{
		### title

		$this->non_sort = strip_tags( $this->non_sort );
		$this->title = strip_tags( $this->title );
		$this->sub_title = strip_tags( $this->sub_title );
		
		// make sure subtitle is properly parsed out

		$iColon = strpos( $this->title, ":" );
		
		if ( $this->sub_title == "" && $iColon !== false )
		{
			$this->sub_title = trim( substr( $this->title, $iColon + 1 ) );
			$this->title = trim( substr( $this->title, 0, $iColon ) );
		}
		
		// make sure nonSort portion of the title is extracted

		// punctuation; we'll also *add* the definite/indefinite article below should 
		// the quote be followed by one of those -- this is all in english, yo!

		if ( strlen( $this->title ) > 0 )
		{
			if ( substr( $this->title, 0, 1 ) == "\"" || substr( $this->title, 0, 1 ) == "'" )
			{
				$this->non_sort = substr( $this->title, 0, 1 );
				$this->title = substr( $this->title, 1 );
			}
		}
		
		// common definite and indefinite articles

		if ( strlen( $this->title ) > 4 )
		{
			if ( Parser::strtolower( substr( $this->title, 0, 4 ) ) == "the " )
			{
				$this->non_sort .= substr( $this->title, 0, 4 );
				$this->title = substr( $this->title, 4 );
			} 
			elseif ( Parser::strtolower( substr( $this->title, 0, 2 ) ) == "a " )
			{
				$this->non_sort .= substr( $this->title, 0, 2 );
				$this->title = substr( $this->title, 2 );
			} 
			elseif ( Parser::strtolower( substr( $this->title, 0, 3 ) ) == "an " )
			{
				$this->non_sort .= substr( $this->title, 0, 3 );
				$this->title = substr( $this->title, 3 );
			}
		}

		### isbn
		
		// get just the isbn minus format notes

		for ( $x = 0 ; $x < count( $this->isbns ) ; $x ++ )
		{
			$arrIsbnExtract = array();
			
			$this->isbns[$x] = str_replace( "-", "", $this->isbns[$x] );
			
			if ( preg_match( "/[0-9]{12,13}X{0,1}/", $this->isbns[$x], $arrIsbnExtract ) != 0 )
			{
				$this->isbns[$x] = $arrIsbnExtract[0];
			} 
			elseif ( preg_match( "/[0-9]{9,10}X{0,1}/", $this->isbns[$x], $arrIsbnExtract ) != 0 )
			{
				$this->isbns[$x] = $arrIsbnExtract[0];
			}
		}

		## summary
		
		if ( $this->abstract != "" )
		{
			$this->summary = $this->abstract;
			$this->summary_type = "abstract";
		}
		elseif ( $this->snippet != "" )
		{
			$this->summary = $this->snippet;
			$this->summary_type = "snippet";
		} 
		elseif ( count($this->toc) > 0 )
		{
			$this->summary = implode(' ', $this->toc);
			$this->summary_type = "toc";
		} 
		elseif ( count( $this->subjects ) > 0 )
		{
			$this->summary_type = "subjects";
			
			for ( $x = 0 ; $x < count( $this->subjects ) ; $x ++ )
			{
				$subject_object = $this->subjects[$x];
				$this->summary .= $subject_object->value;
				
				if ( $x < count( $this->subjects ) - 1 )
				{
					$this->summary .= "; ";
				}
			}
		}		
		
		## pages
		
		// no end page specified, but there is an extent 
		
		if ( $this->end_page == "" && $this->extent != "" && $this->start_page != "" )
		{
			// there is an extent note, indicating the number of pages,
			// calculate end page based on that

			$arrExtent = array();
				
			if ( preg_match( '/([0-9]{1})\/([0-9]{1})/', $this->extent, $arrExtent ) != 0 )
			{
				// if extent expressed as a fraction of a page, just take
				// the start page as the end page
				
				$this->end_page = $this->start_page;
			} 
			elseif ( preg_match( "/[0-9]{1,}/", $this->extent, $arrExtent ) != 0 )
			{
				// otherwise take whole number
				$start = ( int ) $this->start_page;
				$end = ( int ) $arrExtent[0];
				
				$this->end_page = $start + ($end - 1);
			}
		}		
		
		// page normalization
		
		if ( $this->end_page != "" && $this->start_page != "" )
		{
			// pages were input as 197-8 or 197-82, or similar, so convert
			// the last number to the actual page number
			
			if ( strlen( $this->end_page ) < strlen( $this->start_page ) )
			{
				$strMissing = substr( $this->start_page, 0, strlen( $this->start_page ) - strlen( $this->end_page ) );
				$this->end_page = $strMissing . $this->end_page;
			}
		}

		## journal
		
		// construct a readable journal field if none supplied
		
		if ( $this->journal == "" )
		{
			if ( $this->journal_title != "" )
			{
				$this->journal = $this->toTitleCase($this->journal_title);
				
				
				if ( $this->volume != "" || $this->issue != "" )
				{
					$this->journal .= ',';
				}				

				if ( $this->volume != "" ) 
				{
					$this->journal .= " volume " . $this->volume;
				}
				
				if ( $this->issue != "" )
				{
					$this->journal .= " issue " . $this->issue;
				}
				
				$date = $this->getPublicationDate();
				
				if ( $this->publication_date != "")
				{
					$date = $this->publication_date;
				}
				
				if ( $date != null )
				{
					$this->journal .= " ($date)";
				}
				
				if ( $this->start_page != "" )
				{
					$this->journal .= ', ';
					
					if ( $this->end_page != "" )
					{
						$this->journal .= "pages " . $this->start_page  . '-' . $this->end_page;
					}
					else
					{
						$this->journal .= " page " . $this->start_page;
					}
				}				
			}
		}		
		
		### language
		
		// normalize and translate language names
		
		$langConverter = Languages::getInstance();
		
		if ( strlen( $this->language ) == 2 )
		{
			$this->language = $langConverter->getNameFromCode( 'iso_639_1_code', $this->language );
		} 
		elseif ( strlen( $this->language ) == 3 )
		{
			$this->language = $langConverter->getNameFromCode( 'iso_639_2B_code', $this->language );
		} 
		else
		{
			$language = $langConverter->getNameFromCode( 'name', $this->language );
			
			if ( $language != "" )
			{
				$this->language = $language;
			}
		}
		
		## de-duping
		
		// make sure no dupes in author array
		
		$author_original = $this->authors;
		$author_other = $this->authors;
		
		for ( $x = 0; $x < count($author_original); $x++ )
		{
			$objXerxesAuthor = $author_original[$x];
			
			if ( $objXerxesAuthor instanceof Author  ) // skip those set to null (i.e., was a dupe)
			{
				$this_author = $objXerxesAuthor->getAllFields();
				
				if ( $objXerxesAuthor->display != '' )
				{
					$this_author = $objXerxesAuthor->display . $objXerxesAuthor->title;
				}
				
				for ( $a = 0; $a < count($author_other); $a++ )
				{
					if ( $a != $x ) // compare all other authors in the array
					{
						$objThatAuthor = $author_other[$a];
						
						if ( $objThatAuthor instanceof Author ) // just in case
						{
							$that_author = $objThatAuthor->getAllFields();

							if ( $objThatAuthor->display != '' )
							{
								$that_author = $objThatAuthor->display . $objThatAuthor->title;
							}							
							
							if ( $this_author == $that_author)
							{
								// remove the dupe
								$author_original[$a] = null;
							}
						}
					}
				}
			}
		}
		
		$this->authors = array(); // reset author array
		
		foreach ( $author_original as $author )
		{
			if ( $author instanceof Author )
			{
				array_push($this->authors, $author);
			}
		}
		
		// make sure no dupes and no blanks in standard numbers
		
		$arrISSN = $this->issns;
		$arrISBN = $this->isbns;
		
		$this->issns = array();
		$this->isbns = array();
		
		foreach ( $arrISSN as $strISSN )
		{
			$strISSN = trim($strISSN);
			
			if ( $strISSN != "" )
			{
				$strISSN = str_replace( "-", "", $strISSN);
				
				//extract the issn number leaving behind extra chars and comments
				
				$match = array();
				
				if ( preg_match("/[0-9]{8,8}/", $strISSN, $match) )
				{
					$strISSN = $match[0];
				}
				
				array_push($this->issns, $strISSN);
			}
		}

		foreach ( $arrISBN as $strISBN )
		{
			$strISBN = trim($strISBN);
			
			if ( $strISBN != "" )
			{
				$strISBN = str_replace( "-", "", $strISBN);
				array_push($this->isbns, $strISBN);
			}
		}		
		
		
		$this->issns = array_unique( $this->issns ); 
		$this->isbns = array_unique( $this->isbns );
		
		
		### punctuation clean-up

		$this->book_title = rtrim( $this->book_title, "./;,:" );
		$this->title = rtrim( $this->title, "./;,:" );
		$this->sub_title = rtrim( $this->sub_title, "./;,:" );
		$this->short_title = rtrim( $this->short_title, "./;,:" );
		$this->journal_title = rtrim( $this->journal_title, "./;,:" );
		$this->series_title = rtrim( $this->series_title, "./;,:" );
		$this->technology = rtrim( $this->technology, "./;,:" );
		
		$this->place = rtrim( $this->place, "./;,:" );
		$this->publisher = rtrim( $this->publisher, "./;,:" );
		$this->edition = rtrim( $this->edition, "./;,:" );
		
		for ( $x = 0 ; $x < count( $this->authors ) ; $x ++ )
		{
			foreach ( $this->authors[$x] as $key => $value )
			{
				$objXerxesAuthor = $this->authors[$x];
				
				foreach ( $objXerxesAuthor as $key => $value )
				{
					$objXerxesAuthor->$key = rtrim( $value, "./;,:" );
				}
				
				$this->authors[$x] = $objXerxesAuthor;
			}
		}
		
		for ( $s = 0 ; $s < count( $this->subjects ) ; $s ++ )
		{
			$subject_object = $this->subjects[$s];
			$subject_object->value = rtrim( $subject_object->value, "./;,:" );
			$this->subjects[$s] = $subject_object;
		}
	}
	
	/**
	 * Get an OpenURL 1.0 formatted URL
	 *
	 * @param string $strResolver	base url of the link resolver
	 * @param string $strReferer	referrer (unique identifier)
	 * @return string
	 */
	
	public function getOpenURL($strResolver, $strReferer = null, $param_delimiter = "&")
	{
		$arrReferant = array(); // referrant values, minus author
		$strBaseUrl = ""; // base url of openurl request
		$strKev = ""; // key encoded values

		// set base url and referrer with database name

		$strKev = "url_ver=Z39.88-2004";
		
		if ( $strResolver != "" )
		{
			$strBaseUrl = $strResolver . "?";
		}
		if ( $strReferer != "" )
		{
			$strKev .= $param_delimiter . "rfr_id=info:sid/" . urlencode( $strReferer );
		}
		
		// search engine and database
		
		$source = '';
		
		if ( $this->source != "" )
		{
			$source = $this->source;
			
			if ( $this->database_openurl != "" )
			{
				$source .= ":";
			}
		}
		
		if ( $this->database_openurl != "" )
		{
			$source .= $this->database_openurl;
		}
		
		$source = trim($source);
		
		if ( $source != '' )
		{
			$strKev .= urlencode( "($source)" );
		}
		
		// add rft_id's
		
		$arrReferentId = $this->referentIdentifierArray();
		
		foreach ($arrReferentId as $id) 
		{
			$strKev .= $param_delimiter . "rft_id=" . urlencode($id); 
		}
			
		// add simple referrant values
		
		$arrReferant = $this->referantArray();
		
		foreach ( $arrReferant as $key => $value )
		{
			if ( $value != "" )
			{
				$strKev .= $param_delimiter . $key . "=" . urlencode( $value );
			}
		}
		
		// add primary author

		if ( count( $this->authors ) > 0 )
		{
			$objXerxesAuthor = $this->authors[0];
			
			if ( $objXerxesAuthor->type == "personal" )
			{
				if ( $objXerxesAuthor->last_name != "" )
				{
					$strKev .= $param_delimiter . "rft.aulast=" . urlencode( $objXerxesAuthor->last_name );
					
					if ( $this->editor == true )
					{
						$strKev .= urlencode( ", ed." );
					}
				}
				if ( $objXerxesAuthor->first_name != "" )
				{
					$strKev .= $param_delimiter. "rft.aufirst=" . urlencode( $objXerxesAuthor->first_name );
				}
				if ( $objXerxesAuthor->init != "" )
				{
					$strKev .= $param_delimiter . "rft.auinit=" . urlencode( $objXerxesAuthor->init );
				}
			} 
			else
			{
				$strKev .= $param_delimiter . "rft.aucorp=" . urlencode( $objXerxesAuthor->name );
			}
		}
		
		return $strBaseUrl . $strKev;
	}
	
	/**
	 * Convert record to OpenURL 1.0 formatted XML Context Object
	 *
	 * @return DOMDocument
	 */
	
	public function getContextObject()
	{
		$ns_context = "info:ofi/fmt:xml:xsd:ctx";

		$ns_referrant = "";
		
		$arrReferant = $this->referantArray();
		$arrReferantIds = $this->referentIdentifierArray();
		
		$objXml = new \DOMDocument( );
		$objXml->loadXML( "<context-objects />" );
		
		$objContextObject = $objXml->createElementNS($ns_context, "context-object" );
		$objContextObject->setAttribute( "version", "Z39.88-2004" );
		$objContextObject->setAttribute( "timestamp", date( "c" ) );
		
		$objReferrent = $objXml->createElementNS($ns_context, "referent" );
		$objMetadataByVal = $objXml->createElementNS($ns_context, "metadata-by-val" );
		$objMetadata = $objXml->createElementNS($ns_context,"metadata" );
		
		// set data container

		if ( $arrReferant["rft.genre"] == "book" || 
			$arrReferant["rft.genre"] == "bookitem" || 
			$arrReferant["rft.genre"] == "report" )
		{
			$ns_referrant = "info:ofi/fmt:xml:xsd:book";
			$objItem = $objXml->createElementNS($ns_referrant, "book" );
		} 
		elseif ( $arrReferant["rft.genre"] == "dissertation" )
		{
			$ns_referrant = "info:ofi/fmt:xml:xsd:dissertation";
			$objItem = $objXml->createElementNS($ns_referrant, "dissertation" );
		} 
		else
		{
			$ns_referrant = "info:ofi/fmt:xml:xsd:journal";
			$objItem = $objXml->createElementNS($ns_referrant, "journal" );
		}
		
		$objAuthors = $objXml->createElementNS($ns_referrant, "authors" );
		
		// add authors

		$x = 1;
		
		foreach ( $this->authors as $objXerxesAuthor )
		{
			$objAuthor = $objXml->createElementNS($ns_referrant, "author" );
			
			if ( $objXerxesAuthor->last_name != "" )
			{
				$objAuthorLast = $objXml->createElementNS($ns_referrant, "aulast", Parser::escapeXml( $objXerxesAuthor->last_name ) );
				$objAuthor->appendChild( $objAuthorLast );
			}
			
			if ( $objXerxesAuthor->first_name != "" )
			{
				$objAuthorFirst = $objXml->createElementNS($ns_referrant, "aufirst", Parser::escapeXml( $objXerxesAuthor->first_name ) );
				$objAuthor->appendChild( $objAuthorFirst );
			}
			
			if ( $objXerxesAuthor->init != "" )
			{
				$objAuthorInit = $objXml->createElementNS($ns_referrant, "auinit", Parser::escapeXml( $objXerxesAuthor->init ) );
				$objAuthor->appendChild( $objAuthorInit );
			}
			
			if ( $objXerxesAuthor->name != "" )
			{
				$objAuthorCorp = $objXml->createElementNS($ns_referrant, "aucorp", Parser::escapeXml( $objXerxesAuthor->name ) );
				$objAuthor->appendChild( $objAuthorCorp );
			}
			
			$objAuthor->setAttribute( "rank", $x );
			
			if ( $x == 1 && $this->editor == true )
			{
				$objAuthor->setAttribute( "editor", "true" );
			}
			
			$objAuthors->appendChild( $objAuthor );
			
			$x ++;
		
		}
		
		$objItem->appendChild( $objAuthors );
			
		// add rft_id's. 
		
		foreach ( $arrReferantIds as $id )
		{
			// rft_id goes in the <referent> element directly, as a <ctx:identifier>
			
			$objNode = $objXml->createElementNS($ns_context, "identifier", Parser::escapeXml ( $id ) );
			$objReferrent->appendChild ( $objNode );
		}
		
		// add simple referrant values

		foreach ( $arrReferant as $key => $value )
		{
			if ( is_array( $value ) )
			{
				if ( count( $value ) > 0 )
				{
					foreach ( $value as $element )
					{
						$objNode = $objXml->createElementNS($ns_referrant, $key, Parser::escapeXml( $element ) );
						$objItem->appendChild( $objNode );
					}
				}
			} 
			elseif ( $value != "" )
			{
				$objNode = $objXml->createElementNS($ns_referrant, $key, Parser::escapeXml( $value ) );
				$objItem->appendChild( $objNode );
			}
		}
		
		$objMetadata->appendChild( $objItem );
		$objMetadataByVal->appendChild( $objMetadata );
		$objReferrent->appendChild( $objMetadataByVal );
		$objContextObject->appendChild( $objReferrent );
		$objXml->documentElement->appendChild( $objContextObject );
		
		return $objXml;
	}
	
	/**
	 * Serialize to XML
	 * 
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
		$xml = new \DOMDocument( );
		$xml->loadXML( "<xerxes_record />" );
		
		$properties = $this->getProperties();
		
		#### special handling
		
		// normalized title
		
		$title_normalized = $this->getTitle(true);
		
		if ( $title_normalized != "" )
		{
			$properties['title_normalized'] = $title_normalized;
		}
		
		// journal title
		
		$journal_title = $this->getJournalTitle(true);
		
		if ( $journal_title != "" )
		{
			$properties['journal_title'] = $journal_title;
		}		
		
		// primary author
		
		$primary_author = $this->getPrimaryAuthor(true);
		
		if ( $primary_author != "")
		{
			$properties['primary_author'] = $primary_author;
		}
		
		// full-text indicator
		
		if ($this->hasFullText())
		{
			$properties['full_text_bool'] = 1;
		}
		
		// authors
			
		if ( count($this->authors) > 0 )
		{
			$authors_xml = $xml->createElement("authors");
			$x = 1;
			
			foreach ( $this->authors as $author )
			{
				$author_xml =  $xml->createElement("author");
				$author_xml->setAttribute("type", $author->type);
				
				if ( $author->additional == true )
				{
					$author_xml->setAttribute("additional", "true");
				}

				$author_xml->setAttribute("rank", $x);
				
				if ( $x == 1 && $this->editor == true )
				{
					$author_xml->setAttribute("editor", "true");
				}
				
				foreach ( $author->toArray() as $key => $value )
				{
					$objNew = $xml->createElement($key, Parser::escapeXml( $value ) );
					$author_xml->appendChild($objNew);
				}
				
				$authors_xml->appendChild($author_xml);
				
				$x++;
			}
			
			$xml->documentElement->appendChild($authors_xml);
		}		
	
		// standard numbers
			
		if ( count($this->issns) > 0 || count($this->isbns) > 0 || $this->govdoc_number != "" || $this->gpo_number != "" || $this->oclc_number != "")
		{
			$objStandard = $xml->createElement("standard_numbers");
			
			if ( count($this->issns) > 0 )
			{
				foreach ( $this->issns as $strIssn )
				{
					$objIssn = $xml->createElement("issn", Parser::escapeXml($strIssn));
					$objStandard->appendChild($objIssn);
				}
			}
			
			if ( count($this->isbns) > 0 )
			{
				foreach ( $this->isbns as $strIsbn )
				{
					$objIssn = $xml->createElement("isbn", Parser::escapeXml($strIsbn));
					$objStandard->appendChild($objIssn);
				}
			}
			
			if ( $this->govdoc_number != "" )
			{
				$objGovDoc = $xml->createElement("govdoc", Parser::escapeXml($this->govdoc_number));
				$objStandard->appendChild($objGovDoc);
			}
			
			if ( $this->gpo_number != "" )
			{
				$objGPO = $xml->createElement("gpo", Parser::escapeXml($this->gpo_number));
				$objStandard->appendChild($objGPO);
			}
				
			if ( $this->oclc_number != "" )
			{
				$objOCLC = $xml->createElement("oclc", Parser::escapeXml($this->oclc_number));
				$objStandard->appendChild($objOCLC);					
			}
				
			$xml->documentElement->appendChild($objStandard);
		}		
		
		## basic elements
		
		foreach ( $properties as $key => $value )
		{
			// these are utility variables
			
			if ( $key == "utility" || in_array($key, $this->utility) )
			{
				continue;
			}
			
			// we handled these above
			
			if ($key == "authors" || 
				$key == "isbns" ||
				$key == "issns" ||
				$key == "govdoc_number" ||
				$key == "gpo_number" ||
				$key == "oclc_number" )
			{
				continue;
			}
			
			// otherwise, create a new node
			
			Parser::addToXML($xml, $key, $value);
		}
		
		return $xml;
	}
	
	/**
	 * Return record in CSL array
	 * 
	 * @return array
	 */
	
	public function toCSL()
	{
		$citation = array();
		
		// title

		$citation["title"] = $this->getTitle(true);
		
		// format
		
		if ( (string) $this->format == "Book" )
		{
			$citation["type"] = "book";
			$citation["publisher"] = $this->getPublisher(); 
			$citation["publisher-place"] = $this->getPlace();
		}
		else
		{
			// journal info
			
			$citation["type"] = "article-journal";
			$citation["container-title"] = $this->getJournalTitle(true);
			$citation["volume"] = $this->getVolume(); 
			$citation["issue"] = $this->getIssue(); 
			$citation["page"] = $this->getPages(); 
		}
			
		// authors
		
		if ( count($this->authors) > 0 )
		{
			$citation["author"] = array();
			
			foreach ( $this->authors as $author )
			{
				$author_array = array(
					"family" => $author->last_name, 
					"given" => $author->first_name, 
				);
				
				array_push($citation["author"], $author_array);
			}
		}
		
		 // year
		
		if ( $this->getYear() != "" )
		{
			$citation["issued"]["date-parts"] = array(array($this->getYear()));
		}
		
		return $citation;
	}
	
	/**
	 * html decode
	 *
	 * @param string|array $item
	 * @return string|array
	 */
	
	public static function decode($item)
	{
		if (  is_string($item) )
		{
			return html_entity_decode($item, null, 'UTF-8');
		}
		elseif ( is_array($item) )
		{
			foreach ( $item as $key => $value )
			{
				$item[$key] = html_entity_decode($item, null, 'UTF-8');
			}
				
			return $item;
		}
	}
	
	/**
	 * Add the value of the XML to the property
	 *
	 * @param \SimpleXMLElement $child
	 */
	
	private function addPropertyFromXML(\SimpleXMLElement $xml)
	{
		$name = $xml->getName();
		
		if ( ! property_exists($this, $name))
		{
			$name .= 's'; // try the plural
		}
	
		if ( property_exists($this, $name))
		{
			if ( is_array($this->$name) )
			{
				if ( $xml->count() > 0 )
				{
					foreach ( $xml->children() as $child )
					{
						array_push($this->$name, (string) $child);
					}
				}
				else
				{
					array_push($this->$name, (string) $xml);
				}
			}
			else
			{
				$this->$name = (string) $xml;
			}
		}
	}	

	/**
	 * Returns the object's properties that correspond to the OpenURL standard
	 * as an easy to use associative array
	 *
	 * @return array
	 */
	
	private function referantArray()
	{
		$arrReferant = array();
		$strTitle = "";
		
		### simple values

		$arrReferant["rft.genre"] = $this->format->toOpenURLGenre();
		
		switch($arrReferant["rft.genre"])
		{
			case "dissertation":
				
				$arrReferant["rft_val_fmt"] = "info:ofi/fmt:kev:mtx:dissertation";
				break;				
			
			case "book":
			case "bookitem":
			case "conference":
			case "proceeding":
			case "report":
			case "document":
				
				$arrReferant["rft_val_fmt"] = "info:ofi/fmt:kev:mtx:book";
				break;

			case "journal":
			case "issue":
			case "article":
			case "proceeding":
			case "conference":
			case "preprint":
			case "unknown":
				$arrReferant["rft_val_fmt"] = "info:ofi/fmt:kev:mtx:journal";
				break;					
		}
		
		
		if ( count( $this->isbns ) > 0 )
		{
			$arrReferant["rft.isbn"] = $this->isbns[0];
		}
		
		if ( count( $this->issns ) > 0 )
		{
			$arrReferant["rft.issn"] = $this->issns[0];
		}
			
		// rft.ed_number not an actual openurl 1.0 standard element, 
		// but sfx recognizes it. But only add if the eric type
		// is ED, adding an EJ or other as an ED just confuses SFX. 

		if ( $this->eric_number)
		{
			$strEricType = substr( $this->eric_number, 0, 2 );
			
			if ( $strEricType == "ED" )
			{
				$arrReferant["rft.ed_number"] = $this->eric_number;
			}
		}
		
		$arrReferant["rft.series"] = $this->series_title;
		$arrReferant["rft.place"] = $this->place;
		$arrReferant["rft.pub"] = $this->publisher;
		$arrReferant["rft.edition"] = $this->edition;
		$arrReferant["rft.tpages"] = $this->extent;
		$arrReferant["rft.jtitle"] = $this->journal_title;
		$arrReferant["rft.stitle"] = $this->short_title;
		$arrReferant["rft.volume"] = $this->volume;
		$arrReferant["rft.issue"] = $this->issue;
		$arrReferant["rft.spage"] = $this->start_page;
		$arrReferant["rft.epage"] = $this->end_page;
		$arrReferant["rft.degree"] = $this->degree;
		$arrReferant["rft.inst"] = $this->institution;
		
		// date
		
		$arrReferant["rft.date"] = $this->year; // $this->getPublicationDate('Y-m-d');
		
		
		### title

		if ( $this->non_sort != "" )
		{
			$strTitle = $this->non_sort . " ";
		}
		if ( $this->title != "" )
		{
			$strTitle .= $this->title . " ";
		}
		if ( $this->sub_title != "" )
		{
			$strTitle .= ": " . $this->sub_title . " ";
		}
			
		// map title to appropriate element based on genre
		
		if ( $arrReferant["rft.genre"] == "book" || 
			$arrReferant["rft.genre"] == "conference" || 
			$arrReferant["rft.genre"] == "proceeding" || 
			$arrReferant["rft.genre"] == "report" )
		{
			$arrReferant["rft.btitle"] = $strTitle;
		} 
		elseif ( $arrReferant["rft.genre"] == "bookitem" )
		{
			$arrReferant["rft.atitle"] = $strTitle;
			$arrReferant["rft.btitle"] = $this->book_title;
		} 
		elseif ( $arrReferant["rft.genre"] == "dissertation" )
		{
			$arrReferant["rft.title"] = $strTitle;
			
			// since this is sometimes divined from diss abs, we'll drop all
			// the journal stuff that is still in the openurl but messes up sfx

			$arrReferant["rft.jtitle"] = null;
			$arrReferant["rft.issn"] = null;
			$arrReferant["rft.volume"] = null;
			$arrReferant["rft.issue"] = null;
			$arrReferant["rft.spage"] = null;
			$arrReferant["rft.epage"] = null;
		} 
		elseif ( $arrReferant["rft.genre"] == "journal" )
		{
			$arrReferant["rft.title"] = $strTitle;
			
			// remove these elements from a journal, since they produce
			// some erroneous info, especially date!

			$arrReferant["rft.date"] = null;
			$arrReferant["rft.pub"] = null;
			$arrReferant["rft.place"] = null;
		} 
		else
		{
			$arrReferant["rft.atitle"] = $strTitle;
		}
		
		return $arrReferant;
	}
	
	/**
	 * Returns the object's properties that correspond to OpenURL standard
	 * rft_id URIs as a simple list array. 
	 *
	 * @return array
	 */
	
	private function referentIdentifierArray()
	{
		$results = array ();
		
		if ($this->oclc_number != "")
		{
			array_push ( $results, "info:oclcnum/" . $this->oclc_number );
		}
	
		// doi
		
		if ($this->doi != "")
		{
			array_push ( $results, "info:doi/" . $this->doi );
		}
			
		// sudoc, using rsinger's convention, http://dilettantes.code4lib.org/2009/03/a-uri-scheme-for-sudocs/
		
		if ($this->govdoc_number != "")
		{
			array_push ( $results, "http://purl.org/NET/sudoc/" . urlencode ( $this->govdoc_number ) );
		}
		
		return $results;
	}	
	
	/**
	 * Extract four digit year from string
	 * 
	 * @param string $strYear
	 */
	
	protected function extractYear($strYear)
	{
		$arrYear = array();
		
		if ( preg_match( "/[0-9]{4}/", $strYear, $arrYear ) != 0 )
		{
			return $arrYear[0];
		} 
		else
		{
			return null;
		}
	}
	
	/**
	 * Convert string to title case
	 * 
	 * @param string $strInput
	 */
	
	protected function toTitleCase($strInput)
	{
		$arrMatches = ""; // matches from regular expression
		$arrSmallWords = ""; // words that shouldn't be capitalized if they aren't the first word.
		$arrWords = ""; // individual words in input
		$strFinal = ""; // final string to return
		$strLetter = ""; // first letter of subtitle, if any

		// if there are no lowercase letters (and its sufficiently long a title to 
		// not just be an aconym or something) then this is likely a title stupdily
		// entered into a database in ALL CAPS, so drop it entirely to 
		// lower-case first

		$iMatch = preg_match( "/[a-z]/", $strInput );
		
		if ( $iMatch == 0 && strlen( $strInput ) > 10 )
		{
			$strInput = Parser::strtolower( $strInput );
		}
		
		// array of small words
		
		$arrSmallWords = array ('of', 'a', 'the', 'and', 'an', 'or', 'nor', 'but', 'is', 'if', 'then', 
			'else', 'when', 'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to', 'into', 'with', 'as' );
		
		// split the string into separate words

		$arrWords = explode( ' ', $strInput );
		
		foreach ( $arrWords as $key => $word )
		{
			// if this word is the first, or it's not one of our small words, capitalise it 
			
			if ( $key == 0 || ! in_array( Parser::strtolower( $word ), $arrSmallWords ) )
			{
				// make sure first character is not a quote or something
				
				if ( preg_match("/^[^a-zA-Z0-9]/", $word ) )
				{
					$first = substr($word,0,1);
					$rest = substr($word,1);
					
					$arrWords[$key] = $first . ucwords( $rest );
				}
				else
				{
					$arrWords[$key] = ucwords( $word );
				}
			} 
			elseif ( in_array( Parser::strtolower( $word ), $arrSmallWords ) )
			{
				$arrWords[$key] = Parser::strtolower( $word );
			}
		}
		
		// join the words back into a string

		$strFinal = implode( ' ', $arrWords );
		
		// catch all subtitles

		$strFinal = preg_replace_callback(
			'/: ([a-z])/',
			function ($matches) {
				return ucwords($matches[0]);
			},
			$strFinal
		);
		
		// catch words that start with double quotes

		if ( preg_match( "/\"([a-z])/", $strFinal, $arrMatches ) )
		{
			$strLetter = ucwords( $arrMatches[1] );
			$strFinal = preg_replace( "/\"[a-z]/", "\"" . $strLetter, $strFinal );
		}
		
		// catch words that start with a single quote
		// need to be a little more cautious here and make sure there is a space before the quote when
		// inside the title to ensure this isn't a quote for a contraction or for possisive; separate
		// case to handle when the quote is the first word

		if ( preg_match( "/ '([a-z])/", $strFinal, $arrMatches ) )
		{
			$strLetter = ucwords( $arrMatches[1] );
			$strFinal = preg_replace( "/ '[a-z]/", " '" . $strLetter, $strFinal );
		}
		
		if ( preg_match( "/^'([a-z])/", $strFinal, $arrMatches ) )
		{
			$strLetter = ucwords( $arrMatches[1] );
			$strFinal = preg_replace( "/^'[a-z]/", "'" . $strLetter, $strFinal );
		}
		
		return $strFinal;
	}
	
	### PROPERTIES ###
	
	/**
	 * Whether this item has full-text
	 *
	 * @return bool
	 */	
	
	public function hasFullText()
	{
		foreach ( $this->links as $link )
		{
			if ( $link->isFullText() == true )
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Links associated with this item
	 *
	 * @param bool $bolFullText		true = resrtict to full-text links only
	 * @return Link[]
	 */	
	
	public function getLinks($bolFullText = false)
	{
		// limit to only full-text links

		if ( $bolFullText == true )
		{
			$arrFinal = array();
			
			foreach ( $this->links as $link )
			{
				if ( $link->isFullText() == true )
				{
					array_push( $arrFinal, $link );
				}
			}
			
			return $arrFinal;
		} 
		else
		{
			// all the links

			return $this->links;
		}
	}
	
	/**
	 * Add a link
	 * 
	 * @param Link $link
	 */
	
	public function addLink(Link $link)
	{
		$this->links[] = $link;
	}
	
	/**
	 * Primary Author
	 *
	 * @param bool $bolReverse	whether author should be return as last,first
	 * @return string
	 */	
	
	public function getPrimaryAuthor($bolReverse = false)
	{
		$arrPrimaryAuthor = $this->getAuthors( true, true, $bolReverse );
		
		if ( count( $arrPrimaryAuthor ) > 0 )
		{
			return $arrPrimaryAuthor[0];
		} 
		else
		{
			return null;
		}
	}
	
	/**
	 * Authors
	 * 
	 * authors will return as array, with each author name optionally formatted
	 * as a string ('first last' or 'last, first') or objects
	 *
	 * @param bool $bolPrimary		[optional] return just the primary author, default false
	 * @param bool $bolFormat		[optional] return the author names as strings (otherwise as objects), default false
	 * @param bool $bolReverse		[optional] return author names as strings, last name first
	 * @return array
	 */
	
	public function getAuthors($bolPrimary = false, $bolFormat = false, $bolReverse = false)
	{
		$arrFinal = array();
		
		foreach ( $this->authors as $author )
		{
			// author as string
			
			if ( $bolFormat == true )
			{
				array_push( $arrFinal, $author->getName($bolReverse) );
			} 
			else // author objects
			{
				array_push( $arrFinal, $author );
			}
			
			// we're only asking for the primary author
			
			if ( $bolPrimary == true )
			{
				// sorry, only additional authors (7XX), so return empty
				
				if ( $author->additional == true )
				{
					return array();
				}
				else
				{
					break; // exit loop, we've got the author we need
				}
			}
		}
		
		return $arrFinal;
	}
	
	/**
	 * Title
	 *
	 * @param bool $bolTitleCase	whether title should be in title case
	 * @return string
	 */	
	
	public function getTitle($bolTitleCase = false)
	{
		$strTitle = "";
		
		if ( $this->non_sort != "" )
		{
			$strTitle = $this->non_sort;
		}
		
		$strTitle .= $this->title;
		
		if ( $this->sub_title != "" )
		{
			$strTitle .= ": " . $this->sub_title;
		}
		
		if ( $bolTitleCase == true )
		{
			$strTitle = $this->toTitleCase( $strTitle );
		}
		
		return $strTitle;
	}
	
	/**
	 * Book Title, if this is a book chapter
	 *
	 * @param bool $bolTitleCase	whether title should be in title case
	 * @return string
	 */	
	
	public function getBookTitle($bolTitleCase = false)
	{
		if ( $bolTitleCase == true )
		{
			return $this->toTitleCase( $this->book_title );
		} 
		else
		{
			return $this->book_title;
		}
	}
	
	/**
	 * Journal title
	 * 
	 * @param bool $bolTitleCase	whether title should be in title case
	 * @return string
	 */	
	
	public function getJournalTitle($bolTitleCase = false)
	{
		if ( $bolTitleCase == true )
		{
			return $this->toTitleCase( $this->journal_title );
		} 
		else
		{
			return $this->journal_title;
		}
	}
	
	/**
	 * ISSN
	 *
	 * @return array
	 */	
	
	public function getISSN()
	{
		if ( count( $this->issns ) > 0 )
		{
			return $this->issns[0];
		} 
		else
		{
			return null;
		}
	}
	
	/**
	 * ISBN
	 *
	 * @return array
	 */	
	
	public function getISBN()
	{
		if ( count( $this->isbns ) > 0 )
		{
			return $this->isbns[0];
		} 
		else
		{
			return null;
		}
	}

	/**
	 * All ISSN's for this item
	 *
	 * @return array
	 */	
	
	public function getAllISSN()
	{
		return $this->issns;
	}
	
	/**
	 * All ISBN's for this item
	 *
	 * @return array
	 */	
	
	public function getAllISBN()
	{
		return $this->isbns;
	}
	
	/**
	 * Main title
	 *
	 * @return string
	 */	
	
	public function getMainTitle()
	{
		return $this->title;
	}
	
	/**
	 * Edition of the item
	 *
	 * @return string
	 */	
	
	public function getEdition()
	{
		return $this->edition;
	}
	
	/**
	 * Control number
	 *
	 * @return string
	 */	
	
	public function getControlNumber()
	{
		return $this->control_number;
	}
	
	/**
	 * Whether item has an editor
	 *
	 * @return bool
	 */	
	
	public function isEditor()
	{
		return $this->editor;
	}
	
	/**
	 * Format
	 *
	 * @return Format
	 */	
	
	public function format()
	{
		return $this->format;
	}
	
	/**
	 * System/format of item
	 *
	 * @return string
	 */	
	
	public function getTechnology()
	{
		return $this->technology;
	}
	
	/**
	 * Non-sorting portion of title (e.g., 'a' 'the')
	 *
	 * @return string
	 */	
	
	public function getNonSort()
	{
		return $this->non_sort;
	}
	
	/**
	 * Sub-title
	 *
	 * @return string
	 */	
	
	public function getSubTitle()
	{
		return $this->sub_title;
	}
	
	/**
	 * Title of series this item is a part of
	 *
	 * @return string
	 */	
	
	public function getSeriesTitle()
	{
		return $this->series_title;
	}
	
	/**
	 * Short title
	 *
	 * @return string
	 */	
	
	public function getShortTitle()
	{
		return $this->short_title;
	}
	
	/**
	 * Abstract
	 *
	 * @return string
	 */	
	
	public function getAbstract()
	{
		return $this->abstract;
	}
	
	/**
	 * Summary of the item
	 *
	 * @return string
	 */	
	
	public function getSummary()
	{
		return $this->summary;
	}
	
	/**
	 * Description of item
	 *
	 * @return string
	 */	
	
	public function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * Language of item
	 *
	 * @return string
	 */	
	
	public function getLanguage()
	{
		return $this->language;
	}
	
	/**
	 * Table of contents
	 *
	 * @return array
	 */	
	
	public function getTOC()
	{
		return $this->toc;
	}
	
	/**
	 * Place of publication
	 *
	 * @return string
	 */	
	
	public function getPlace()
	{
		return $this->place;
	}
	
	/**
	 * Publisher
	 *
	 * @return string
	 */	
	
	public function getPublisher()
	{
		return $this->publisher;
	}
	
	/**
	 * Year of publication
	 *
	 * @return string
	 */	
	
	public function getYear()
	{
		return $this->year;
	}
	
	/**
	 * Full (if known) publication date for item
	 * 
	 * @param string $format	date format
	 */
	
	public function getPublicationDate($format = 'j F Y')
	{
		// no date, no mas
		
		if ( $this->month == "" && $this->day == "" && $this->year == "")
		{
			return null;
		}
		
		// full date, full response!
		
		if ( is_int($this->month) && is_int($this->day) && is_int($this->year) )
		{
			$date = new \DateTime();
			$date->setDate($this->year, $this->month, $this->day);
			return $date->format($format);
		}
		
		// just return the year
		
		return $this->year;		
	}
	
	/**
	 * Full journal information
	 *
	 * @return string
	 */	
	
	public function getJournal()
	{
		return $this->journal;
	}
	
	/**
	 * The volume the article is published in
	 *
	 * @return string
	 */	
	
	public function getVolume()
	{
		return $this->volume;
	}
	
	/**
	 * The issue article is published in
	 *
	 * @return string
	 */	
	
	public function getIssue()
	{
		return $this->issue;
	}
	
	/**
	 * Start page of article
	 *
	 * @return string
	 */	
	
	public function getStartPage()
	{
		return $this->start_page;
	}
	
	/**
	 * End page of article
	 *
	 * @return string
	 */	
	
	public function getEndPage()
	{
		return $this->end_page;
	}
	
	/**
	 * Page range of article
	 *
	 * @return string
	 */	
	
	public function getPages()
	{
		$pages = $this->start_page;
		
		if ( $this->getEndPage() != "" )
		{
			$pages .= "-" . $this->getEndPage();
		}
		
		return $pages;
	}
	
	/**
	 * Extent (pages, etc.) of the item
	 *
	 * @return string
	 */	
	
	public function getExtent()
	{
		return $this->extent;
	}
	
	/**
	 * Price of the item
	 *
	 * @return string
	 */	
	
	public function getPrice()
	{
		return $this->price;
	}
	
	/**
	 * Notes
	 *
	 * @return array
	 */	
		
	public function getNotes()
	{
		return $this->notes;
	}
	
	/**
	 * Get Digital Object Identifier this item
	 *
	 * @return string
	 */	
	
	public function getSubjects() 
	{
		return $this->subjects;
	}
	
	/**
	 * Granting institution for thesis
	 *
	 * @return string
	 */	
	
	public function getInstitution()
	{
		return $this->institution;
	}
	
	/**
	 * Degree type for thesis
	 *
	 * @return string
	 */
		
	public function getDegree()
	{
		return $this->degree;
	}
	
	/**
	 * (Main) Call Number this item
	 *
	 * @return string
	 */	
	
	public function getCallNumber()
	{
		return $this->call_number;
	}
	
	/**
	 * OCLC Number for this item
	 *
	 * @return string
	 */	
		
	public function getOCLCNumber()
	{
		return $this->oclc_number;
	}
	
	/**
	 * Digital Object Identifier this item
	 *
	 * @return string
	 */	
	
	public function getDOI()
	{
		return $this->doi;
	}

	/**
	 * Source (search engine) for this item
	 *
	 * @return string
	 */	
	
	public function getSource()
	{
		return $this->source;
	}
	
	/**
	 * Set source (search engine) for this item
	 *
	 * @param string $source
	 */	

	public function setSource($source)
	{
		$this->source = $source;
	}
	
	/**
	 * Set whether article is peer reviewed
	 *
	 * @param bool $bool
	 */	
	
	public function setRefereed($bool)
	{
		$this->refereed = (bool) $bool;
	}
	
	/**
	 * Whether article is peer reviewed
	 * 
	 * @return bool
	 */	
	
	public function getRefereed()
	{
		return $this->refereed;
	}
	
	/**
	 * Set whether library subscribe to this item
	 * 
	 * @param bool $bool
	 */
	
	public function setSubscription($bool)
	{
		$this->subscription = (bool) $bool;
	}
	
	/**
	 * Whether library subscribe to this item
	 *
	 * @return bool
	 */
	
	public function getSubscription()
	{
		return $this->subscription;
	}
	
	/**
	 * Record ID
	 * 
	 * @return string
	 */
	
	public function getRecordID()
	{
		return $this->record_id;
	}
	
	/**
	 * Set the record ID
	 * 
	 * @param string $id
	 */
	
	public function setRecordID($id)
	{
		return $this->record_id = $id;
	}
	
	/**
	 * Whether the item has physical holdings
	 * 
	 * @return bool
	 */
	
	public function hasPhysicalHoldings()
	{
		return $this->physical_holdings;
	}
	
	/**
	 * Get score
	 *
	 * @param string $score
	 */
	
	public function getScore()
	{
		return $this->score;
	}	
	
	/**
	 * Set the records score in a result set
	 * 
	 * @param string $score
	 */
	
	public function setScore($score)
	{
		$this->score = $score;
	}
	
	/**
	 * (Periodical) titles this record continues
	 * 
	 * @return LinkedItem[]
	 */
	
	public function getPrecedingTitles()
	{
		return $this->journal_title_continues;
	}

	/**
	 * (Periodical) titles this record is continued by
	 *
	 * @return LinkedItem[]
	 */	
	
	public function getSucceedingTitles()
	{
		return $this->journal_title_continued_by;
	}
	
	/**
	 * Get the original XML
	 * 
	 * @param bool $bolString	true = return as string
	 * @return string|\DOMDocument
	 */
	
	public function getOriginalXML($bolString = false)
	{
		if ( $bolString == true )
		{
			return $this->document->saveXML();
		}
		else
		{
			return $this->document;
		}
	}	
	
	/**
	 * Get all properties as array
	 * 
	 * @return array
	 */
	
	public function getProperties()
	{
		$properties = array();
		
		foreach ( $this as $key => $value )
		{
			$properties[$key] = $value;
		}
		
		return $properties;
	}
	
	/**
	 * Set properties from the given array
	 * 
	 * @param array $arguments	key must match property name
	 */
	
	public function setProperties(array $arguments)
	{
		foreach ( $arguments as $key => $value )
		{
			if ( property_exists($this, $key) )
			{
				if ( is_array($this->$key) && ! is_array($value)  )
				{
					throw new \OutOfBoundsException('Property is array but supplied value was not an array');
				}

				$this->$key = $value;
			}
		}
	}
}
