<?php

class Xerxes_Record_Document extends Xerxes_Marc_Document 
{
	protected $record_type = "Xerxes_Record";
}

/**
 * Extract properties for books, articles, and dissertations from MARC-XML
 * 
 * @author David Walker
 * @copyright 2009 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Record.php 1938 2011-07-26 22:46:52Z dwalker.calstate@gmail.com $
 * @todo ->__toString() madness below due to php 5.1 object-string casting problem
 * @package Xerxes
 */

class Xerxes_Record extends Xerxes_Marc_Record
{
	protected $source = "";	// source database id
	protected $database_name; // source database name
	protected $record_id; // canonical record id

	protected $format = ""; // format
	protected $format_array = array(); // possible formats
	protected $technology = ""; // technology/system format

	protected $control_number = ""; // the 001 basically, OCLC or otherwise
	protected $oclc_number = ""; // oclc number
	protected $govdoc_number = ""; // gov doc number
	protected $gpo_number = ""; // gov't printing office (gpo) number
	protected $eric_number = ""; // eric document number
	protected $isbns = array ( ); // isbn
	protected $issns = array ( ); // issn
	protected $call_number = ""; // lc call number
	protected $doi = ""; // doi

	protected $authors = array ( ); // authors
	protected $author_from_title = ""; // author from title statement
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
	protected $year = ""; // date of publication

	protected $edition = ""; // edition
	protected $extent = ""; // total pages
	protected $price = ""; // price

	protected $book_title = ""; // book title (for book chapters)
	protected $journal_title = ""; // journal title
	protected $journal = ""; // journal source information
	protected $short_title = ""; // journal short title
	protected $journal_title_continued_by = ""; // journal continued by title
	protected $journal_title_continues = ""; // journal continues a title	
	
	protected $volume = ""; // volume
	protected $issue = ""; // issue
	protected $start_page = ""; // start page
	protected $end_page = ""; // end page

	protected $degree = ""; // thesis degree conferred
	protected $institution = ""; // thesis granting institution

	protected $description = ""; // physical description
	protected $abstract = ""; // abstract
	protected $summary = ""; // summary
	protected $summary_type = ""; // the type of summary
	protected $language = ""; // primary language of the record
	protected $notes = array (); // notes that are not the abstract, language, or table of contents
	protected $subjects = array (); // subjects
	protected $toc = ""; // table of contents note
	protected $series = array();
	
	protected $refereed = false; // whether the item is peer-reviewed
	protected $subscription = false; // whether the item is available in library subscription
	
	protected $links = array (); // all supplied links in the record both full text and non
	protected $embedded_text = array (); // full text embedded in document
	
	protected $alt_scripts = array (); // alternate character-scripts like cjk or hebrew, taken from 880s
	protected $alt_script_name = ""; // the name of the alternate character-script; we'll just assume one for now, I guess
	
	protected $items = array(); // item records attached
	protected $no_items = false; // wheter the item has no items
	
	protected $serialized_xml; // for serializing the object
	
	
	### PUBLIC FUNCTIONS ###

	
	public function __sleep()
	{
		// save only the xml
		
		$this->serialized_xml = $this->document->saveXML();
		return array("serialized_xml");
	}
	
	public function __wakeup()
	{
		// and then we recreate the object (with any new changes we've made)
		// by just loading the saved xml back into the object
		
		$this->loadXML($this->serialized_xml);
	}		
	
	/**
	 * Maps the marc data to the object's properties
	 */
	
	protected function map()
	{
		// item data in the XML?
		
		foreach ( $this->document->getElementsByTagName("item") as $item_record )
		{
			$item = new Xerxes_Record_Item();
			$item->loadXML($item_record);
			$this->addItem($item);
		}
		
		## openurl
		
		// the source can contain an openurl context object buried in it as well as marc-xml
		
		// test to see what profile the context object is using; set namespace accordingly

		if ($this->document->getElementsByTagNameNS ( "info:ofi/fmt:xml:xsd:book", "book" )->item ( 0 ) != null)
		{
			$this->xpath->registerNamespace ( "rft", "info:ofi/fmt:xml:xsd:book" );
		} 
		elseif ($this->document->getElementsByTagNameNS ( "info:ofi/fmt:xml:xsd:dissertation", "dissertation" )->item ( 0 ) != null)
		{
			$this->xpath->registerNamespace ( "rft", "info:ofi/fmt:xml:xsd:dissertation" );
		} 		
		elseif ($this->document->getElementsByTagNameNS ( "info:ofi/fmt:xml:xsd", "journal" )->item ( 0 ) != null)
		{
			$this->xpath->registerNamespace ( "rft", "info:ofi/fmt:xml:xsd" );
		}
		else
		{
			$this->xpath->registerNamespace ( "rft", "info:ofi/fmt:xml:xsd:journal" );
		}
		
		// context object: 

		// these just in case
		
		$objATitle = $this->xpath->query( "//rft:atitle" )->item ( 0 );
		$objBTitle = $this->xpath->query( "//rft:btitle" )->item ( 0 );
		$objAuthors = $this->xpath->query( "//rft:author[rft:aulast != '' or rft:aucorp != '']" );
		$objGenre = $this->xpath->query( "//rft:genre" )->item ( 0 );
		$objDate = $this->xpath->query( "//rft:date" )->item ( 0 );
		
		// journal title, volume, issue, pages from context object
		
		$objTitle = $this->xpath->query( "//rft:title" )->item ( 0 );
		$objSTitle = $this->xpath->query( "//rft:stitle" )->item ( 0 );
		$objJTitle = $this->xpath->query( "//rft:jtitle" )->item ( 0 );
		$objVolume = $this->xpath->query( "//rft:volume" )->item ( 0 );
		$objIssue = $this->xpath->query( "//rft:issue" )->item ( 0 );
		$objStartPage = $this->xpath->query( "//rft:spage" )->item ( 0 );
		$objEndPage = $this->xpath->query( "//rft:epage" )->item ( 0 );
		$objISSN = $this->xpath->query( "//rft:issn" )->item ( 0 );
		$objISBN = $this->xpath->query( "//rft:isbn" )->item ( 0 );
		
		if ($objSTitle != null) $this->short_title = $objSTitle->nodeValue;
		if ($objVolume != null)	$this->volume = $objVolume->nodeValue;
		if ($objIssue != null) $this->issue = $objIssue->nodeValue;
		if ($objStartPage != null) $this->start_page = $objStartPage->nodeValue;
		if ($objEndPage != null) $this->end_page = $objEndPage->nodeValue;
		if ($objISBN != null) array_push($this->isbns, $objISBN->nodeValue);
		if ($objISSN != null) array_push($this->issns, $objISSN->nodeValue);
		if ($objGenre != null) array_push($this->format_array, $objGenre->nodeValue);
		
		// control and standard numbers
		
		$this->control_number =  $this->controlfield("001")->__toString();
		$this->record_id = $this->control_number;
		
		$arrIssn = $this->fieldArray("022", "a" );
		$arrIsbn = $this->fieldArray("020", "az" );

		$this->govdoc_number =  $this->datafield("086")->subfield("a")->__toString();		
		$this->gpo_number =  $this->datafield("074")->subfield("a")->__toString();
		
		// doi
				
		// this is kind of iffy since the 024 is not _really_ a DOI field; but this
		// is the most likely marc field; however need to see if the number follows the very loose
		// pattern of the DOI of 'prefix/suffix', where prefix and suffix can be nearly anything
		
		$field_024 = $this->fieldArray("024", "a");
		
		foreach ( $field_024 as $doi )
		{
			// strip any doi: prefix
			
			$doi = str_ireplace( "doi:", "", $doi );
			$doi = str_ireplace( "doi", "", $doi );
			
			// got it!
			
			if ( preg_match('/.*\/.*/', $doi) )
			{
				$this->doi = $doi;
				break;
			}
		}
		
		$strJournalIssn =  $this->datafield("773")->subfield("x")->__toString();
		
		if ( $strJournalIssn != null )
		{
			array_push( $arrIssn, $strJournalIssn );
		}
			
		// call number

		$strCallNumber =  $this->datafield("050")->__toString();
		$strCallNumberLocal =  $this->datafield("090")->__toString();
		
		if ( $strCallNumber != null )
		{
			$this->call_number = $strCallNumber;
		} 
		elseif ( $strCallNumberLocal != null )
		{
			$this->call_number = $strCallNumberLocal;
		}
		
		// format
		
		$this->technology =  $this->datafield("538")->subfield("a")->__toString();
		
		$arrFormat = $this->fieldArray("513", "a");

		foreach ( $arrFormat as $format )
		{
			array_push($this->format_array,  $format);
		}
		
		$strTitleFormat =  $this->datafield("245")->subfield("hk")->__toString();
		
		if ( $strTitleFormat != null )
		{
			array_push( $this->format_array, $strTitleFormat );
		}
			
		// thesis degree, institution, date awarded
		
		$strThesis =  $this->datafield("502")->subfield("a")->__toString();
		
		### title
		
		$this->title =  $this->datafield("245")->subfield("anp")->__toString();
		$this->sub_title =  $this->datafield("245")->subfield("b")->__toString();
		$this->series_title = $this->datafield("440")->subfield("a" )->__toString();
		$this->uniform_title = $this->datafield("130|240")->__toString();
		
		// sometimes the title appears in a 242 or even a 246 if it is translated from another
		// language, although the latter is probably bad practice.  We will only take these
		// if the title in the 245 is blank, and take a 242 over the 246

		$strTransTitle =  $this->datafield("242")->subfield("a")->__toString();
		$strTransSubTitle =  $this->datafield("242")->subfield("b")->__toString();
		
		$strVaryingTitle =  $this->datafield("246")->subfield("a" )->__toString();
		$strVaryingSubTitle =  $this->datafield("246")->subfield("b")->__toString();
		
		if ( $this->title == "" && $strTransTitle != "" )
		{
			$this->title = $strTransTitle;
			$this->trans_title = true;
		} 
		elseif ( $this->title == "" && $strVaryingTitle != "" )
		{
			$this->title = $strVaryingTitle;
			$this->trans_title = true;
		}
		
		if ( $this->sub_title == "" && $strTransSubTitle != "" )
		{
			$this->sub_title = $strTransTitle;
			$this->trans_title = true;
		} 
		elseif ( $this->sub_title == "" && $strVaryingSubTitle != "" )
		{
			$this->sub_title = $strVaryingSubTitle;
			$this->trans_title = true;
		}
		
		// alternate titles
		
		foreach ( $this->datafield("246") as $varying_title )
		{
			array_push($this->alternate_titles, $varying_title->__toString());
		}		
		
		
		// last chance, check the context object
		
		if ( $this->title == "" && $objATitle != null )
		{
			$this->title = $objATitle->nodeValue;
		}
		elseif ( $this->title == "" && $objBTitle != null )
		{
			$this->title = $objBTitle->nodeValue;
		}

		// additional titles for display
		
		foreach ( $this->datafield('730|740') as $additional_titles )
		{
			$subfields =  $additional_titles->subfield()->__toString();
			array_push($this->additional_titles, $subfields);
		}
		
		
		
		
		
		
		
		### exception: 245|c is remainder of title, not statement of responsibility
		
		$statement_of_responsiblity = (string) $this->datafield("245")->subfield("c");
		
		$title_parts = preg_split('/\W/', $statement_of_responsiblity);
		
		$found = false;
		
		
		
		foreach ( $this->datafield("100|111|700|710|711") as $author )
		{
			$author_parts = preg_split('/\W/', (string) $author);
		
			foreach ( $author_parts as $author_part )
			{
				if ( in_array($author_part, $title_parts) )
				{
					$found = true;
				}
			}
		}
		
		// if the 245|c doesn't include *any* terms from any of the author fields, then this is likely
		// the continuation of the title, rather than the statement of responsibility, and
		// so we need to include it in the title proper
		
		if ( $found == false && (string) $this->datafield("245") != "")
		{
			$this->title = (string) $this->datafield("245")->subfield("acnp"); // added 'c'
		}

		
		
		
		
		
		// edition, extent, description

		$this->edition =  $this->datafield("250")->subfield("a" )->__toString();
		$this->extent =  $this->datafield("300")->subfield("a" )->__toString();
		$this->description =  $this->datafield("300")->__toString();
		$this->price =  $this->datafield("365")->__toString();
		
		// publisher
		
		$this->place =  $this->datafield("260")->subfield("a")->__toString();
		$this->publisher =  $this->datafield("260")->subfield("b")->__toString();
		
		// date

		$strDate =  $this->datafield("260")->subfield("c")->__toString();
		
		// notes
		
		$arrToc = $this->fieldArray("505", "agrt");

		foreach ( $arrToc as $toc )
		{
			$this->toc .=  $toc;
		}
		
		$arrAbstract = $this->fieldArray("520", "a");
		$strLanguageNote =  $this->datafield("546")->subfield("a")->__toString();
		
		// other notes
		
		$objNotes = $this->xpath("//marc:datafield[@tag >= 500 and @tag < 600 and @tag != 505 and @tag != 520 and @tag != 546]" );
		
		foreach ( $objNotes as $objNote )
		{
			array_push($this->notes, $objNote->nodeValue);
		}
		
		// subjects

		// we'll exclude the numeric subfields since they contain information about the
		// source of the subject terms, which are probably not needed for display?

		foreach ( $this->datafield("6XX") as $subject )
		{
			$subfields = $subject->subfield("abcdefghijklmnopqrstuvwxyz");
			$subfields_array = array();
			
			foreach ( $subfields as $subfield )
			{
				array_push($subfields_array, $subfield->__toString());
			}
			
			$subject_object = new Xerxes_Record_Subject();
			
			$subject_object->display = implode(" -- ", $subfields_array );
			$subject_object->value = $subfields->__toString();
			
			array_push($this->subjects, $subject_object);
		}

		// series information

		foreach ( $this->datafield('4XX|800|810|811|830') as $subject )
		{
			array_push($this->series, $subject->__toString());
		}		
		
		// journal
		
		// specify the order of the subfields in 773 for journal as $a $t $g and then everything else
		//  in case they are out of order 
		
		$this->journal =  $this->datafield("773")->subfield("atgbcdefhijklmnopqrsuvwxyz1234567890", true)->__toString();
		$strJournal =  $this->datafield("773")->subfield("agpqt")->__toString();
		$this->journal_title =  $this->datafield("773")->subfield("t")->__toString();
		$this->short_title =  $this->datafield("773")->subfield("p")->__toString();
		$strExtentHost =  $this->datafield("773")->subfield("h")->__toString();
		
		// continues and continued by
		
		$this->journal_title_continues = (string) $this->datafield("780")->subfield('at');
		$this->journal_title_continued_by = (string) $this->datafield("785")->subfield('at');		
		
		// alternate character-scripts
		
		// the 880 represents an alternative character-script, like Hebrew or CJK;
		// for simplicity's sake, we just dump them all here in an array, with the 
		// intent of displaying them in paragraphs together in the interface or something?
		
		// we get every field except for the $6 which is a linking field

		$this->alt_scripts = $this->fieldArray("880", "abcdefghijklmnopqrstuvwxyz12345789" );
		
		// now use the $6 to figure out which character-script this is
		// assume just one for now

		$strAltScript =  $this->datafield("880")->subfield("6")->__toString();
		
		if ( $strAltScript != null )
		{
			$arrMatchCodes = array ( );
			
			$arrScriptCodes = array ("(3" => "Arabic", "(B" => "Latin", '$1' => "CJK", "(N" => "Cyrillic", "(S" => "Greek", "(2" => "Hebrew" );
			
			if ( preg_match( '/[0-9]{3}-[0-9]{2}\/([^\/]*)/', $strAltScript, $arrMatchCodes ) )
			{
				if ( array_key_exists( $arrMatchCodes[1], $arrScriptCodes ) )
				{
					$this->alt_script_name = $arrScriptCodes[$arrMatchCodes[1]];
				}
			}
		}
		
		### volume, issue, pagination
		
		// a best guess extraction of volume, issue, pages from 773

		$arrRegExJournal = $this->parseJournalData( $strJournal );
		
		// some sources include ^ as a filler character in issn/isbn, these people should be shot!

		foreach ( $arrIssn as $strIssn )
		{
			if ( strpos( $strIssn, "^" ) === false )
			{
				array_push( $this->issns, $strIssn);
			}
		}
		
		foreach ( $arrIsbn as $strIsbn )
		{
			if ( strpos( $strIsbn, "^" ) === false )
			{
				array_push( $this->isbns, $strIsbn );
			}
		}
		
		### language
		
		$langConverter = Xerxes_Framework_Languages::getInstance();
		
		// take an explicit language note over 008 if available

		if ( $strLanguageNote != null )
		{
			$strLanguageNote = $this->stripEndPunctuation( $strLanguageNote, "." );
			
			if ( strlen( $strLanguageNote ) == 2 )
			{
				$this->language = $langConverter->getNameFromCode( 'iso_639_1_code', $strLanguageNote );
			} 
			elseif ( strlen( $strLanguageNote ) == 3 )
			{
				$this->language = $langConverter->getNameFromCode( 'iso_639_2B_code', $strLanguageNote );
			} 
			elseif ( ! stristr( $strLanguageNote, "Undetermined" ) )
			{
				$this->language = str_ireplace( "In ", "", $strLanguageNote );
				$language = $langConverter->getNameFromCode( 'name', ucfirst( $this->language ) );
				if ($language != null) {
					$this->language = $language;
				}
			}
		} 
		else
		{
			// get the language code from the 008
			
			$objLang = $this->controlfield("008")->__toString();
			
			if ( $objLang instanceof Xerxes_Marc_ControlField )
			{
				$strLangCode = $objLang->position("35-37");

				if ( $strLangCode != "")
				{
					$this->language = $langConverter->getNameFromCode( 'iso_639_2B_code', $strLanguageNote );
				}			
			}
		}
		
		### format

		$this->format = $this->parseFormat( $this->format_array );
		

		### full-text
		
		// examine the 856s present in the record to see if they are in
		// fact to full-text, and not to a table of contents or something
		// stupid like that

		foreach ( $this->datafield("856") as $link )
		{
			$resource_type = $link->ind2;
			$part = $link->subfield("3")->__toString();
			
			$strUrl = $link->subfield("u")->__toString();
			$strHostName = $link->subfield("a")->__toString();
			$strDisplay = $link->subfield("z")->__toString();
			$strLinkFormatType = $link->subfield("q")->__toString();
			$strLinkText = $link->subfield("y")->__toString();			
			
			if ( $strDisplay == "" )
			{
				if ( $strLinkText != "" )
				{
					$strDisplay = $strLinkText;
				}
				elseif ( $strHostName != "")
				{
					$strDisplay = $strHostName;
				}
			}

			if ( $part != "" )
			{
				$strDisplay = $part . " " . $strDisplay;
			}
			
			// no link supplied
			
			if (  $link->subfield("u")->__toString() == "" )
			{
				continue;
			}
			
			// link includes loc url (bad catalogers!)
			
			if ( stristr($strUrl, "catdir") || $resource_type == 2 )
			{
				array_push( $this->links, array (null, $link->subfield("u")->__toString(), "none" ) );
			}
			else
			{
				$strLinkFormat = "online";
					
				if ( stristr( $strDisplay, "PDF" ) || 
					stristr( $strUrl, "PDF" ) || 
					stristr($strLinkFormatType, "PDF" ) || 
					stristr($strLinkText, "PDF" ) )
				{
					$strLinkFormat = "pdf";
				} 
				elseif ( stristr( $strDisplay, "HTML" ) || 
					stristr($strLinkFormatType, "HTML" ) ||  
					stristr($strLinkText, "HTML" ) )
				{
					$strLinkFormat = "html";
				}
				
				array_push( $this->links, array ($strDisplay, $strUrl, $strLinkFormat ) );
			}
		}
		
		### oclc number
		
		// oclc number can be either in the 001 or in the 035$a
		// make sure 003 says 001 is oclc number or 001 includes an oclc prefix, 
		
		$str001 =  $this->controlfield("001")->__toString();
		$str003 =  $this->controlfield("003")->__toString();
		$str035 =  $this->datafield("035")->subfield("a")->__toString();

		if ( $str001 != "" && (( $str003 == "" && preg_match('/^\(?([Oo][Cc])/', $str001) ) || 
			$str003 == "OCoLC" ))
		{
			$this->oclc_number = $str001;
		} 
		elseif ( strpos( $str035, "OCoLC" ) !== false )
		{
			$this->oclc_number = $str035;
		}
		
		// get just the number
		
		$arrOclc = array ( );
		
		if ( preg_match( "/[0-9]{1,}/", $this->oclc_number, $arrOclc ) != 0 )
		{
			$strJustOclcNumber = $arrOclc[0];
			
			// strip out leading 0s

			$strJustOclcNumber = preg_replace( "/^0{1,8}/", "", $strJustOclcNumber );
			
			$this->oclc_number = $strJustOclcNumber;
		}
		
		### summary
		
		// abstract
		
		foreach ( $arrAbstract as $strAbstract )
		{
			$this->abstract .= " " . $strAbstract;
		}
		
		$this->abstract = trim( strip_tags( $this->abstract ) );
		
		// summary
		
		if ( $this->abstract != "" )
		{
			$this->summary = $this->abstract;
			$this->summary_type = "abstract";
		} 
		elseif ( $this->toc != "" )
		{
			$this->summary = $this->toc;
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
		
		### journal title

		// we'll take the journal title form the 773$t as the best option,

		if ( $this->journal_title == "" )
		{
			// otherwise see if context object has one
					
			if ( $objJTitle != null )
			{
				$this->journal_title = $objJTitle->nodeValue;
			}
			elseif ( $objTitle != null )
			{
				$this->journal_title = $objTitle->nodeValue;
			}
			
			// or see if a short title exists
			
			elseif ( $this->short_title != "" && 
				($this->format == "Article" || $this->format == "Journal" || $this->format == "Newspaper")  )
			{
				$this->journal_title = $this->short_title;
			}
		}

		### volume

		if ( $this->volume == "" )
		{
			if ( array_key_exists( "volume", $arrRegExJournal ) )
			{
				$this->volume = $arrRegExJournal["volume"];
			}
		}
		
		### issue
		
		if ( $this->issue == "" )
		{
			if ( array_key_exists( "issue", $arrRegExJournal ) )
			{
				$this->issue = $arrRegExJournal["issue"];
			}
		}
		
		### pages

		// start page

		if ( $this->start_page == "" )
		{
			if ( array_key_exists( "spage", $arrRegExJournal ) )
			{
				$this->start_page = $arrRegExJournal["spage"];
			}
		}
		
		// end page
		
		if ( $this->end_page == "" )
		{
			if ( array_key_exists( "epage", $arrRegExJournal ) )
			{
				// found an end page from our generic regular expression parser

				$this->end_page = $arrRegExJournal["epage"];
			} 
			elseif ( $strExtentHost != "" && $this->start_page != "" )
			{
				// there is an extent note, indicating the number of pages,
				// calculate end page based on that

				$arrExtent = array ( );
				
				if ( preg_match( '/([0-9]{1})\/([0-9]{1})/', $strExtentHost, $arrExtent ) != 0 )
				{
					// if extent expressed as a fraction of a page, just take
					// the start page as the end page
					
					$this->end_page = $this->start_page;
				} 
				elseif ( preg_match( "/[0-9]{1,}/", $strExtentHost, $arrExtent ) != 0 )
				{
					// otherwise take whole number

					$iStart = ( int ) $this->start_page;
					$iEnd = ( int ) $arrExtent[0];
					
					$this->end_page = $iStart + ($iEnd - 1);
				}
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
		
		### isbn
		
		// get just the isbn minus format notes

		for ( $x = 0 ; $x < count( $this->isbns ) ; $x ++ )
		{
			$arrIsbnExtract = array ( );
			
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
		
		### thesis

		// most 502 fields follow the following pattern, which we will use to
		// match and extract individual elements:
		// Thesis (M.F.A.)--University of California, San Diego, 2005
		// Thesis (Ph. D.)--Queen's University, Kingston, Ont., 1977.

		if ( $strThesis != "" )
		{
			// extract degree conferred

			$arrDegree = array ( );
			
			if ( preg_match( '/\(([^\(]*)\)/', $strThesis, $arrDegree ) != 0 )
			{
				$this->degree = $arrDegree[1];
			}
			
			// extract institution

			$iInstPos = strpos( $strThesis, "--" );
			
			if ( $iInstPos !== false )
			{
				$strInstitution = "";
				
				// get everything after the --
				$strInstitution = substr( $strThesis, $iInstPos + 2, strlen( $strThesis ) - 1 );
				
				// find last comma in remaining text
				$iEndPosition = strrpos( $strInstitution, "," );
				
				if ( $iEndPosition !== false )
				{
					$strInstitution = substr( $strInstitution, 0, $iEndPosition );
				}
				
				$this->institution = $strInstitution;
			
			}
			
			// extract year conferred

			$this->year = $this->extractYear( $strThesis );
		}
		
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
			if ( Xerxes_Framework_Parser::strtolower( substr( $this->title, 0, 4 ) ) == "the " )
			{
				$this->non_sort .= substr( $this->title, 0, 4 );
				$this->title = substr( $this->title, 4 );
			} 
			elseif ( Xerxes_Framework_Parser::strtolower( substr( $this->title, 0, 2 ) ) == "a " )
			{
				$this->non_sort .= substr( $this->title, 0, 2 );
				$this->title = substr( $this->title, 2 );
			} 
			elseif ( Xerxes_Framework_Parser::strtolower( substr( $this->title, 0, 3 ) ) == "an " )
			{
				$this->non_sort .= substr( $this->title, 0, 3 );
				$this->title = substr( $this->title, 3 );
			}
		}
		
		### year

		if ( $strDate != "" )
		{
			$this->year = $this->extractYear( $strDate );
		} 
		elseif ( $this->extractYear( $this->publisher ) )
		{
			// off chance that the date is hanging out in the publisher field;
			// might as well strip it out here as well

			$this->year = $this->extractYear( $this->publisher );
			$this->publisher = str_replace( $this->year, "", $this->publisher );
		} 
		elseif ( $this->extractYear( $this->journal ) )
		{
			// perhaps somewhere in the 773$g

			$this->year = $this->extractYear( $this->journal );
		}
		
		// last chance grab from context object
		
		if ( $this->year == "" && $objDate != null )
		{
			$this->year = $this->extractYear($objDate->nodeValue);
		}
		
		
		#### authors

		// authors

		$this->author_from_title =  (string) $this->datafield("245")->subfield("c" )->__toString();
		
		$objConfName =  $this->datafield("111"); // "anc"
		$objAddAuthor = $this->datafield("700"); // "a"
		$objAddCorp = $this->datafield("710"); //, "ab"
		$objAddConf = $this->datafield("711"); // "acn"
		
		// conference and corporate names from title ?

		$objConferenceTitle = $this->datafield("811"); // all
		
		if ( $objAddConf->length() == 0 && $objConferenceTitle->length() > 0 )
		{
			$objAddConf = $objConferenceTitle;
		}
		
		$objCorporateTitle = $this->datafield("810"); // all
		
		if ( $objAddCorp->length() == 0 && $objCorporateTitle->length() > 0 )
		{
			$objAddCorp = $objCorporateTitle;
		}
		
		if ( $objConfName->length() > 0 || $objAddConf->length() > 0 )
		{
			array_push( $this->format_array, "conference paper" );
		}		
		
		// personal primary author
		
		if ( $this->datafield("100")->length() > 0 )
		{
			$objXerxesAuthor = $this->splitAuthor( $this->datafield("100"), "a", "personal" );
			array_push( $this->authors, $objXerxesAuthor );
		} 
		elseif ( $objAddAuthor->length() > 0 )
		{
			// editor

			$objXerxesAuthor = $this->splitAuthor( $objAddAuthor->item(0), "a", "personal", true);
			array_push( $this->authors, $objXerxesAuthor );
			$this->editor = true;
		}
		
		// additional personal authors

		if ( $objAddAuthor->length() > 0  )
		{
			// if there is an editor it has already been included in the array
			// so we need to skip the first author in the list
			
			if ( $this->editor == true )
			{
				$objAddAuthor->next();
			}
			
			foreach ( $objAddAuthor as $obj700 )
			{
				$objXerxesAuthor = $this->splitAuthor( $obj700, "a", "personal", true );
				array_push( $this->authors, $objXerxesAuthor );
			}
		}
		
		// corporate author
		
		if ( $this->datafield("110")->subfield("ab")->__toString() != "" )
		{
			$objXerxesAuthor = $this->splitAuthor( $this->datafield("110"), "ab", "corporate" );
			array_push( $this->authors, $objXerxesAuthor );
		}
		
		// additional corporate authors

		if ( $objAddCorp->length() > 0 )
		{
			foreach ( $objAddCorp as $objCorp )
			{
				$objXerxesAuthor = $this->splitAuthor( $objCorp, "ab", "corporate", true );
				array_push( $this->authors, $objXerxesAuthor );
			}
		}
		
		// conference name

		if ( $objConfName->length() > 0)
		{
			$objXerxesAuthor = $this->splitAuthor( $objConfName, "anc", "conference" );
			array_push( $this->authors, $objXerxesAuthor );
		}
		
		// additional conference names

		if ( $objAddConf->length() > 0 )
		{
			foreach ( $objAddConf as $objConf )
			{
				$objXerxesAuthor = $this->splitAuthor( $objConf, "acn", "conference", true );
				array_push( $this->authors, $objXerxesAuthor );
			}
		}
		
		// last-chance from context-object
		
		if ( count($this->authors) == 0 && $objAuthors != null )
		{
			foreach ( $objAuthors as $objAuthor )
			{
				$objXerxesAuthor = new Xerxes_Record_Author();
				
				foreach ( $objAuthor->childNodes as $objAuthAttr )
				{					
					switch ( $objAuthAttr->localName )
					{
						case "aulast":
							$objXerxesAuthor->last_name = $objAuthAttr->nodeValue;
							$objXerxesAuthor->type = "personal";
							break;

						case "aufirst":
							$objXerxesAuthor->first_name = $objAuthAttr->nodeValue;
							break;
							
						case "auinit":
							$objXerxesAuthor->init = $objAuthAttr->nodeValue;
							break;
							
						case "aucorp":
							$objXerxesAuthor->name = $objAuthAttr->nodeValue;
							$objXerxesAuthor->type = "corporate";
							break;							
					}
				}
				
				array_push($this->authors, $objXerxesAuthor);
			}
		}
		
		// construct a readable journal field if none supplied
		
		if ( $this->journal == "" )
		{
			if ( $this->journal_title != "" )
			{
				$this->journal = $this->toTitleCase($this->journal_title);

				if ( $this->volume != "" ) 
				{
					$this->journal .= " vol. " . $this->volume;
				}
				
				if ( $this->issue != "" )
				{
					$this->journal .= " iss. " . $this->issue;
				}
				
				if ( $this->year != "" )
				{
					$this->journal .= " (" . $this->year . ")";
				}
			}
		}
		
		
		## de-duping
		
		// make sure no dupes in author array
		
		$author_original = $this->authors;
		$author_other = $this->authors;
		
		for ( $x = 0; $x < count($author_original); $x++ )
		{
			$objXerxesAuthor = $author_original[$x];
			
			if ( $objXerxesAuthor instanceof Xerxes_Record_Author  ) // skip those set to null (i.e., was a dupe)
			{
				$this_author = $objXerxesAuthor->allFields();
				
				for ( $a = 0; $a < count($author_other); $a++ )
				{
					if ( $a != $x ) // compare all other authors in the array
					{
						$objThatAuthor = $author_other[$a];
						
						if ( $objThatAuthor instanceof Xerxes_Record_Author ) // just in case
						{
							$that_author = $objThatAuthor->allFields();
							
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
			if ( $author instanceof Xerxes_Record_Author )
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

		$this->book_title = $this->stripEndPunctuation( $this->book_title, "./;,:" );
		$this->title = $this->stripEndPunctuation( $this->title, "./;,:" );
		$this->sub_title = $this->stripEndPunctuation( $this->sub_title, "./;,:" );
		$this->short_title = $this->stripEndPunctuation( $this->short_title, "./;,:" );
		$this->journal_title = $this->stripEndPunctuation( $this->journal_title, "./;,:" );
		$this->series_title = $this->stripEndPunctuation( $this->series_title, "./;,:" );
		$this->technology = $this->stripEndPunctuation( $this->technology, "./;,:" );
		
		$this->place = $this->stripEndPunctuation( $this->place, "./;,:" );
		$this->publisher = $this->stripEndPunctuation( $this->publisher, "./;,:" );
		$this->edition = $this->stripEndPunctuation( $this->edition, "./;,:" );
		
		for ( $x = 0 ; $x < count( $this->authors ) ; $x ++ )
		{
			foreach ( $this->authors[$x] as $key => $value )
			{
				$objXerxesAuthor = $this->authors[$x];
				
				foreach ( $objXerxesAuthor as $key => $value )
				{
					$objXerxesAuthor->$key = $this->stripEndPunctuation( $value, "./;,:" );
				}
				
				$this->authors[$x] = $objXerxesAuthor;
			}
		}
		
		for ( $s = 0 ; $s < count( $this->subjects ) ; $s ++ )
		{
			$subject_object = $this->subjects[$s];
			$subject_object->value = $this->stripEndPunctuation( $subject_object->value, "./;,:" );
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
		$arrReferant = array ( ); // referrant values, minus author
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
		if ( $this->database_name != "" )
		{
			$strKev .= urlencode( " ( " . $this->database_name . ")" );
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
		
		$objXml = new DOMDocument( );
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
				$objAuthorLast = $objXml->createElementNS($ns_referrant, "aulast", $this->escapeXml( $objXerxesAuthor->last_name ) );
				$objAuthor->appendChild( $objAuthorLast );
			}
			
			if ( $objXerxesAuthor->first_name != "" )
			{
				$objAuthorFirst = $objXml->createElementNS($ns_referrant, "aufirst", $this->escapeXml( $objXerxesAuthor->first_name ) );
				$objAuthor->appendChild( $objAuthorFirst );
			}
			
			if ( $objXerxesAuthor->init != "" )
			{
				$objAuthorInit = $objXml->createElementNS($ns_referrant, "auinit", $this->escapeXml( $objXerxesAuthor->init ) );
				$objAuthor->appendChild( $objAuthorInit );
			}
			
			if ( $objXerxesAuthor->name != "" )
			{
				$objAuthorCorp = $objXml->createElementNS($ns_referrant, "aucorp", $this->escapeXml( $objXerxesAuthor->name ) );
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
			
			$objNode = $objXml->createElementNS($ns_context, "identifier", $this->escapeXml ( $id ) );
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
						$objNode = $objXml->createElementNS($ns_referrant, $key, $this->escapeXml( $element ) );
						$objItem->appendChild( $objNode );
					}
				}
			} 
			elseif ( $value != "" )
			{
				$objNode = $objXml->createElementNS($ns_referrant, $key, $this->escapeXml( $value ) );
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
	 * Convert record to Xerxes_Record XML object
	 *
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
		$objXml = new DOMDocument( );
		$objXml->loadXML( "<xerxes_record />" );

		
		#### special handling
		
		// normalized title
		
		$strTitle = $this->getTitle(true);
		
		if ( $strTitle != "" )
		{
			$objTitle = $objXml->createElement("title_normalized",  $this->escapeXML($strTitle));
			$objXml->documentElement->appendChild($objTitle);
		}
		
		// journal title
		
		$strJournalTitle = $this->getJournalTitle(true);
		
		if ( $strJournalTitle != "" )
		{
			$objJTitle = $objXml->createElement("journal_title",  $this->escapeXML($strJournalTitle));
			$objXml->documentElement->appendChild($objJTitle);
		}		
		
		// primary author
		
		$strPrimaryAuthor = $this->getPrimaryAuthor(true);
		
		if ( $strPrimaryAuthor != "")
		{
			$objPrimaryAuthor= $objXml->createElement("primary_author", $this->escapeXML($strPrimaryAuthor));
			$objXml->documentElement->appendChild($objPrimaryAuthor);
		}
		
		// full-text indicator
		
		if ($this->hasFullText())
		{
			$objFull= $objXml->createElement("full_text_bool", 1);
			$objXml->documentElement->appendChild($objFull);
		}
		
		// authors
			
		if ( count($this->authors) > 0 )
		{
			$objAuthors = $objXml->createElement("authors");
			$x = 1;
			
			foreach ( $this->authors as $objXerxesAuthor )
			{
				$objAuthor =  $objXml->createElement("author");
				$objAuthor->setAttribute("type", $objXerxesAuthor->type);
				
				if ( $objXerxesAuthor->additional == true )
				{
					$objAuthor->setAttribute("additional", "true");
				}

				if ( $objXerxesAuthor->last_name != "" )
				{					
					$objAuthorLast =  $objXml->createElement("aulast", $this->escapeXml( $objXerxesAuthor->last_name ) );
					$objAuthor->appendChild($objAuthorLast);
				}
				
				if ( $objXerxesAuthor->first_name != "" )
				{
					$objAuthorFirst =  $objXml->createElement("aufirst", $this->escapeXml( $objXerxesAuthor->first_name ) );
					$objAuthor->appendChild($objAuthorFirst);
				}
				
				if ( $objXerxesAuthor->init != "" )
				{
					$objAuthorInit =  $objXml->createElement("auinit", $this->escapeXml( $objXerxesAuthor->init) );
					$objAuthor->appendChild($objAuthorInit);
				}

				if ( $objXerxesAuthor->name != "" )
				{
					$objAuthorCorp =  $objXml->createElement("aucorp", $this->escapeXml( $objXerxesAuthor->name) );
					$objAuthor->appendChild($objAuthorCorp);
				}

				if ( $objXerxesAuthor->display != "" )
				{
					$objAuthorDisplay = $objXml->createElement("display", $this->escapeXml( $objXerxesAuthor->display) );
					$objAuthor->appendChild($objAuthorDisplay);
				}				
				
				$objAuthor->setAttribute("rank", $x);
				
				if ( $x == 1 && $this->editor == true )
				{
					$objAuthor->setAttribute("editor", "true");
				}
				
				$objAuthors->appendChild($objAuthor);
				
				$x++;
			}
			
			$objXml->documentElement->appendChild($objAuthors);
		}		
	
		// standard numbers
			
		if ( count($this->issns) > 0 || count($this->isbns) > 0 || $this->govdoc_number != "" || $this->gpo_number != "" || $this->oclc_number != "")
		{
			$objStandard = $objXml->createElement("standard_numbers");
			
			if ( count($this->issns) > 0 )
			{
				foreach ( $this->issns as $strIssn )
				{
					$objIssn = $objXml->createElement("issn", $this->escapeXml($strIssn));
					$objStandard->appendChild($objIssn);
				}
			}
			
			if ( count($this->isbns) > 0 )
			{
				foreach ( $this->isbns as $strIsbn )
				{
					$objIssn = $objXml->createElement("isbn", $this->escapeXml($strIsbn));
					$objStandard->appendChild($objIssn);
				}
			}
			
			if ( $this->govdoc_number != "" )
			{
				$objGovDoc = $objXml->createElement("gpo", $this->escapeXml($this->govdoc_number));
				$objStandard->appendChild($objGovDoc);
			}
			
			if ( $this->gpo_number != "" )
			{
				$objGPO = $objXml->createElement("govdoc", $this->escapeXml($this->gpo_number));
				$objStandard->appendChild($objGPO);
			}
				
			if ( $this->oclc_number != "" )
			{
				$objOCLC = $objXml->createElement("oclc", $this->escapeXml($this->oclc_number));
				$objStandard->appendChild($objOCLC);					
			}
				
			$objXml->documentElement->appendChild($objStandard);
		}		
		
		// table of contents
		
		if ($this->toc != null )
		{
			$objTOC = $objXml->createElement("toc");
				
			$arrChapterTitles = explode("--",$this->toc);
				
			foreach ( $arrChapterTitles as $strTitleStatement )
			{
				$objChapter = $objXml->createElement("chapter");
				
				if ( strpos($strTitleStatement, "/") !== false )
				{
					$arrChapterTitleAuth = explode("/", $strTitleStatement);
					
					$objChapterTitle = $objXml->createElement("title",  $this->escapeXml(trim($arrChapterTitleAuth[0])));
					$objChapterAuthor = $objXml->createElement("author",  $this->escapeXml(trim($arrChapterTitleAuth[1])));
					
					$objChapter->appendChild($objChapterTitle);
					$objChapter->appendChild($objChapterAuthor);
				}
				else 
				{
					$objStatement = $objXml->createElement("statement", $this->escapeXml(trim($strTitleStatement)));
					$objChapter->appendChild($objStatement);
				}
				
				$objTOC->appendChild($objChapter);
			}
			
			$objXml->documentElement->appendChild($objTOC);
		}

		// links
			
		if ( $this->links != null )
		{
			$objLinks = $objXml->createElement("links");
		
			foreach ( $this->links as $arrLink )
			{
				$objLink = $objXml->createElement("link");
				$objLink->setAttribute("type", $arrLink[2]);
				
				$objDisplay = $objXml->createElement("display", $this->escapeXml($arrLink[0]));
				$objLink->appendChild($objDisplay);
				
				// if this is a "construct" link, then the second element is an associative 
				// array of marc fields and their values for constructing a link based on
				// the metalib IRD record linking syntax
				
				if ( is_array($arrLink[1]) )
				{
					foreach ( $arrLink[1] as $strField => $strValue )
					{
						$objParam = $objXml->createElement("param", $this->escapeXml($strValue));
						$objParam->setAttribute("field", $strField);
						$objLink->appendChild($objParam);
					}
				}
				else
				{
					$objURL = $objXml->createElement("url", $this->escapeXml($arrLink[1]));
					$objLink->appendChild($objURL);
				}
				
				$objLinks->appendChild($objLink);
			}
			
			$objXml->documentElement->appendChild($objLinks);
		}
		
		// items
		
		if ( count($this->items) > 0 )
		{
			$objItems = $objXml->createElement("items");
			$objXml->documentElement->appendChild($objItems);
			
			foreach ( $this->items as $item )
			{
				$import = $objXml->importNode($item->toXML()->documentElement, true);
				$objItems->appendChild($import);
			}
		}
		
		// subjects
		
		if ( count($this->subjects) > 0 )
		{
			$objSubjects = $objXml->createElement("subjects");
			$objXml->documentElement->appendChild($objSubjects);
		
			foreach ( $this->subjects as $subject_object )
			{
				$objSubject = $objXml->createElement("subject", $this->escapeXml($subject_object->display));
				$objSubject->setAttribute("value", $subject_object->value);
				$objSubjects->appendChild($objSubject);
			}
		}
		
		## basic elements
		
		foreach ( $this as $key => $value )
		{
			// these we handled above
			
			if ($key == "authors" || 
				$key == "isbns" ||
				$key == "issns" ||
				$key == "govdoc_number" ||
				$key == "gpo_number" ||
				$key == "oclc_number" ||
				$key == "toc" ||
				$key == "links" || 
				$key == "journal_title" ||
				$key == "items" ||
				$key == "subjects" ||
				
				// these are utility variables, not to be output
				
				$key == "document" ||
				$key == "xpath" || 
				$key == "node" ||
				$key == "format_array" ||
				$key == "serialized_xml")
			{
				continue;
			}
			
			if ( is_array($value) )
			{
				if ( count($value) == 0 )
				{
					continue;
				}
			}
			
			if ( $value == "" )
			{
				continue;	
			}
			
			$this->createNode($key, $value, $objXml, $objXml->documentElement);
		}
		
		return $objXml;
	}
	
	### PRIVATE FUNCTIONS ###	

	private function createNode($key, $value, $objDocument, $objParent)
	{
		if ( is_array($value) )
		{
			$objNode = $objDocument->createElement($key);
			$objParent->appendChild($objNode);
			
			foreach ( $value as $child_key => $child )
			{
				// assumes key is plural form with 's', so individual is minus 's'
				
				$name = substr($key, 0, -1);
				
				// unless it has a specific name
				
				if ( ! is_int($child_key) )
				{
					$name = $child_key;
				}
				
				// recursive
				
				$this->createNode($name, $child, $objDocument, $objNode);
			}
		}
		else
		{
			$objNode = $objDocument->createElement($key, $this->escapeXML($value));
			$objParent->appendChild($objNode);
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
		$arrReferant = array ( );
		$strTitle = "";
		
		### simple values

		$arrReferant["rft.genre"] = $this->convertGenreOpenURL( $this->format );
		
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
		$arrReferant["rft.date"] = $this->year;
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
	 * Crosswalk the internal identified genre to one available in OpenURL 1.0
	 *
	 * @param string $strFormat		original internal genre/format
	 * @return string				OpenURL genre value
	 */
	
	private function convertGenreOpenURL($strFormat)
	{
		switch ( $strFormat )
		{
			case "Journal" :
			case "Newspaper" :
				
				return "journal";
				break;
			
			case "Issue" :
				
				return "issue";
				break;
			
			case "Tests & Measures":
			case "Book Review" :
			case "Film Review" :
			case "Review" :
			case "Article" :
				
				return "article";
				break;
			
			case "Conference Proceeding" :
				
				// take this over 'conference' ?
				return "proceeding";
				break;
			
			case "Preprint" :
				
				return "preprint";
				break;
			
			case "Book" :
			case "Pamphlet":

                                //take this over 'Pamphlet'?
				return "book";
				break;

			case "Book Chapter" :
			case "Essay" :

				//take this over 'Essay'?
				return "bookitem";
				break;
			
			case "Report" :
				
				return "report";
				break;
			
			case "Dissertation" :
			case "Thesis" :
				
				// not an actual openurl genre
				return "dissertation";
				break;
			
			default :
				
				// take this over 'document'?
				return "unknown";
		}
	}
	
	/**
	 * Determines the format/genre of the item, broken out here for clarity
	 *
	 * @param string $arrFormat			format fields		
	 * @return string					internal xerxes format designation
	 */
	
	protected function parseFormat($arrFormat)
	{
		$chrLeader6 = "";
		$chrLeader7 = "";
		$chrLeader8 = "";
		
		// we'll combine all of the datafields that explicitly declare the
		// format of the record into a single string

		$strDataFields = "";
		
		foreach ( $arrFormat as $strFormat )
		{
			$strDataFields .= " " . Xerxes_Framework_Parser::strtolower( $strFormat );
		}
		
		if ( strlen( $this->leader()->__toString() ) >= 8 )
		{
			$chrLeader6 = substr( $this->leader()->__toString(), 6, 1 );
			$chrLeader7 = substr( $this->leader()->__toString(), 7, 1 );
			$chrLeader8 = substr( $this->leader()->__toString(), 8, 1 );
		}
		
		// grab the 008 & 006 for handling
		
		$obj008 = $this->controlfield("008");
		
		// newspaper
		
		if ( $obj008 instanceof Xerxes_Marc_ControlField )
		{
			if ( $chrLeader7 == 's' && $obj008->position("21") == 'n' )
			{
				 return "Newspaper";
			}
		}
		
		// format made explicit

		if ( strstr( $strDataFields, 'dissertation' ) ) return  "Dissertation"; 
		if (  $this->datafield("502")->__toString() != "" ) return  "Thesis"; 
		if (  $this->controlfield("002")->__toString() == "DS" ) return  "Thesis";
		if ( strstr( $strDataFields, 'proceeding' ) ) return  "Conference Proceeding"; 
		if ( strstr( $strDataFields, 'conference' ) ) return  "Conference Paper"; 
		if ( strstr( $strDataFields, 'hearing' ) ) return  "Hearing"; 
		if ( strstr( $strDataFields, 'working' ) ) return  "Working Paper"; 
		if ( strstr( $strDataFields, 'book review' ) || strstr( $strDataFields, 'review-book' ) ) return  "Book Review"; 
		if ( strstr( $strDataFields, 'film review' ) || strstr( $strDataFields, 'film-book' ) ) return  "Film Review";
		if ( strstr( "$strDataFields ", 'review ' ) ) return  "Review";
		if ( strstr( $strDataFields, 'book art' ) || strstr( $strDataFields, 'book ch' ) || strstr( $strDataFields, 'chapter' ) ) return  "Book Chapter"; 
		if ( strstr( $strDataFields, 'journal' ) ) return  "Article"; 
		if ( strstr( $strDataFields, 'periodical' ) || strstr( $strDataFields, 'serial' ) ) return  "Article"; 
		if ( strstr( $strDataFields, 'book' ) ) return  "Book";
        if ( strstr( $strDataFields, 'pamphlet' ) ) return  "Pamphlet";  
        if ( strstr( $strDataFields, 'essay' ) ) return  "Essay";
		if ( strstr( $strDataFields, 'article' ) ) return  "Article";

		// format from other sources

		if ( $this->journal != "" ) return  "Article"; 
		if ( $chrLeader6 == 'a' && $chrLeader7 == 'a' ) return  "Book Chapter"; 
		if ( $chrLeader6 == 'a' && $chrLeader7 == 'm' )
		{
			$strReturn = "Book"; 
			
			if ( $obj008 instanceof Xerxes_Marc_ControlField  )
			{
				switch( $obj008->position("23") )
				{
					case "a": $strReturn = "Microfilm"; break;
					case "b": $strReturn = "Microfiche"; break;
					case "c": $strReturn = "Microopaque"; break;
					case "d": $strReturn = "Book--Large print"; break;
					case "e": $strReturn = "Book--Braille"; break;
					case "s": $strReturn = "eBook"; break;
				}
			}
			
			return $strReturn;
		}
		
		if ( $chrLeader8 == 'a' ) return "Archive"; 
		if ( $chrLeader6 == 'e' || $chrLeader6 == 'f' ) return "Map"; 
		if ( $chrLeader6 == 'c' || $chrLeader6 == 'd' ) return "Printed Music"; 
		if ( $chrLeader6 == 'i' ) return "Audio Book"; 
		if ( $chrLeader6 == 'j' ) return "Sound Recording"; 
		if ( $chrLeader6 == 'k' ) return "Photograph or Slide"; 
		if ( $chrLeader6 == 'g' ) return "Video"; 
		if ( $chrLeader6 == 'm' && $chrLeader7 == 'i' ) return "Website"; 
		if ( $chrLeader6 == 'm' ) return "Electronic Resource"; 
		if ( $chrLeader6 == 'a' && $chrLeader7 == 'b' ) return "Article"; 
		if ( $chrLeader6 == 'a' && $chrLeader7 == 's' ) return "Journal"; 
		if ( $chrLeader6 == 'a' && $chrLeader7 == 'i' ) return "Website"; 

		if ( count( $this->isbns ) > 0 ) return "Book"; 
		if ( count( $this->issns ) > 0 ) return "Article";
		
		// if we got this far, just return unknown
		
		return "Unknown";
	}
	
	protected function formatFromDataFields($arrFormat)
	{
		// we'll combine all of the datafields that explicitly declare the
		// format of the record into a single string

		$strDataFields = "";
		
		foreach ( $arrFormat as $strFormat )
		{
			$strDataFields .= " " . Xerxes_Framework_Parser::strtolower( $strFormat );
		}
		
		// format made explicit

		if ( strstr( $strDataFields, 'dissertation' ) ) return  "Dissertation"; 
		if (  $this->datafield("502")->__toString() != "" ) return  "Thesis"; 
		if (  $this->controlfield("002")->__toString() == "DS" ) return  "Thesis";
		if ( strstr( $strDataFields, 'proceeding' ) ) return  "ConferenceProceeding"; 
		if ( strstr( $strDataFields, 'conference' ) ) return  "ConferencePaper"; 
		if ( strstr( $strDataFields, 'hearing' ) ) return  "Hearing"; 
		if ( strstr( $strDataFields, 'working' ) ) return  "WorkingPaper"; 
		if ( strstr( $strDataFields, 'book review' ) || strstr( $strDataFields, 'review-book' ) ) return  "BookReview"; 
		if ( strstr( $strDataFields, 'film review' ) || strstr( $strDataFields, 'film-book' ) ) return  "FilmReview";
		if ( strstr( "$strDataFields ", 'review ' ) ) return  "Review";
		if ( strstr( $strDataFields, 'book art' ) || strstr( $strDataFields, 'book ch' ) || strstr( $strDataFields, 'chapter' ) ) return  "BookChapter"; 
		if ( strstr( $strDataFields, 'journal' ) ) return  "Article"; 
		if ( strstr( $strDataFields, 'periodical' ) || strstr( $strDataFields, 'serial' ) ) return  "Article"; 
		if ( strstr( $strDataFields, 'book' ) ) return  "Book";
        if ( strstr( $strDataFields, 'pamphlet' ) ) return  "Pamphlet";  
        // if ( strstr( $strDataFields, 'essay' ) ) return  "Essay";
		if ( strstr( $strDataFields, 'article' ) ) return  "Article";

		// format from other sources

		if ( $this->journal != "" ) return  "Article"; 
		if ( count( $this->isbns ) > 0 ) return "Book"; 
		if ( count( $this->issns ) > 0 ) return "Article";
		
		// if we got this far, just return unknown
		
		return "Unknown";
	}
	
	/**
	 * Best-guess regular expression for extracting volume, issue, pagination,
	 * broken out here for clarity 
	 *
	 * @param string $strJournalInfo		any journal info, usually from 773
	 * @return array
	 */
	
	private function parseJournalData($strJournalInfo)
	{
		$arrFinal = array ( );
		$arrCapture = array ( );
		
		// we'll drop the whole thing to lower case and padd it
		// with spaces to make parsing easier
		
		$strJournalInfo = " " . Xerxes_Framework_Parser::strtolower( $strJournalInfo ) . " ";
		
		// volume

		if ( preg_match( '/ v[a-z]{0,5}[\.]{0,1}[ ]{0,3}([0-9]{1,})/', $strJournalInfo, $arrCapture ) != 0 )
		{
			$arrFinal["volume"] = $arrCapture[1];
			$strJournalInfo = str_replace( $arrCapture[0], "", $strJournalInfo );
		}
		
		// issue

		if ( preg_match( '/ i[a-z]{0,4}[\.]{0,1}[ ]{0,3}([0-9]{1,})/', $strJournalInfo, $arrCapture ) != 0 )
		{
			$arrFinal["issue"] = $arrCapture[1];
			$strJournalInfo = str_replace( $arrCapture[0], "", $strJournalInfo );
		} 
		elseif ( preg_match( '/ n[a-z]{0,5}[\.]{0,1}[ ]{0,3}([0-9]{1,})/', $strJournalInfo, $arrCapture ) != 0 )
		{
			$arrFinal["issue"] = $arrCapture[1];
			$strJournalInfo = str_replace( $arrCapture[0], "", $strJournalInfo );
		}
		
		// pages

		if ( preg_match( "/([0-9]{1,})-([0-9]{1,})/", $strJournalInfo, $arrCapture ) != 0 )
		{
			$arrFinal["spage"] = $arrCapture[1];
			$arrFinal["epage"] = $arrCapture[2];
			
			$strJournalInfo = str_replace( $arrCapture[0], "", $strJournalInfo );
		} 
		elseif ( preg_match( '/ p[a-z]{0,3}[\.]{0,1}[ ]{0,3}([0-9]{1,})/', $strJournalInfo, $arrCapture ) != 0 )
		{
			$arrFinal["spage"] = $arrCapture[1];
			$strJournalInfo = str_replace( $arrCapture[0], "", $strJournalInfo );
		}
		
		return $arrFinal;
	}
	
	protected function splitAuthor($author, $subfields, $strType, $bolAdditional = false)
	{
		$objAuthor = new Xerxes_Record_Author();
		
		$objAuthor->type = $strType;
		$objAuthor->additional = $bolAdditional;
		
		$strAuthor = "";
		$strAuthorDisplay = "";
		
		// author can be string or data field
		
		if ($author instanceof Xerxes_Marc_DataField || $author instanceof Xerxes_Marc_DataFieldList)
		{
			$strAuthor = $author->subfield($subfields)->__toString();
			$strAuthorDisplay = $author->__toString();
		}
		else
		{
			$strAuthor = $author;
		}
		
		$iComma = strpos( $strAuthor, "," );
		$iLastSpace = strripos( $strAuthor, " " );
		
		// for personal authors:

		// if there is a comma, we will assume the names are in 'last, first' order
		// otherwise in 'first last' order -- the second one here obviously being
		// something of a guess, assuming the person has a single word for last name
		// rather than 'van der Kamp', but better than the alternative?

		if ( $strType == "personal" )
		{
			$arrMatch = array ( );
			$strLast = "";
			$strFirst = "";
			$strInit = "";
			
			if ( $iComma !== false )
			{
				$strLast = trim( substr( $strAuthor, 0, $iComma ) );
				$strFirst = trim( substr( $strAuthor, $iComma + 1 ) );
			} 

			// some databases like CINAHL put names as 'last first' but first 
			// is just initials 'Walker DS' so we can catch this scenario?
			
			elseif ( preg_match( "/ ([A-Z]{1,3})$/", $strAuthor, $arrMatch ) != 0 )
			{
				$strFirst = $arrMatch[1];
				$strLast = str_replace( $arrMatch[0], "", $strAuthor );
			} 
			else
			{
				$strLast = trim( substr( $strAuthor, $iLastSpace ) );
				$strFirst = trim( substr( $strAuthor, 0, $iLastSpace ) );
			}
			
			if ( preg_match( '/ ([a-zA-Z]{1})\.$/', $strFirst, $arrMatch ) != 0 )
			{
				$strInit = $arrMatch[1];
				$strFirst = str_replace( $arrMatch[0], "", $strFirst );
			}
			
			$objAuthor->last_name = $strLast;
			$objAuthor->first_name = $strFirst;
			$objAuthor->init = $strInit;
		
		} 
		else
		{
			$objAuthor->name = trim( $strAuthor );
		}
		
		// all marc subfields, for display
		
		$objAuthor->display = $strAuthorDisplay;
		
		return $objAuthor;
	}
	
	protected function stripEndPunctuation($strInput, $strPunct)
	{
		$bolDone = false;
		$arrPunct = str_split( $strPunct );
		
		if ( strlen( $strInput ) == 0 )
		{
			return $strInput;
		}
		
		// check if the input ends in a character entity
		// reference, in which case, leave it alone, yo!
		
		if ( preg_match('/\&\#[0-9a-zA-Z]{1,5}\;$/', $strInput) )
		{
			return $strInput;
		}
		
		while ( $bolDone == false )
		{
			$iEnd = strlen( $strInput ) - 1;
			
			foreach ( $arrPunct as $strPunct )
			{
				if ( substr( $strInput, $iEnd ) == $strPunct )
				{
					$strInput = substr( $strInput, 0, $iEnd );
					$strInput = trim( $strInput );
				}
			}
			
			$bolDone = true;
			
			foreach ( $arrPunct as $strPunct )
			{
				if ( substr( $strInput, $iEnd ) == $strPunct )
				{
					$bolDone = false;
				}
			}
		}
		
		return $strInput;
	}
	
	protected function extractYear($strYear)
	{
		$arrYear = array ( );
		
		if ( preg_match( "/[0-9]{4}/", $strYear, $arrYear ) != 0 )
		{
			return $arrYear[0];
		} 
		else
		{
			return null;
		}
	}
	
	protected function escapeXml($string)
	{
		// NOTE: if you make a change to this function, make a corresponding change 
		// in the Xerxes_Framework_Parser class, since this one here is a duplicate function 
		// allowing Xerxes_Record it be as a stand-alone class 
		
		$string = str_replace( '&', '&amp;', $string );
		$string = str_replace( '<', '&lt;', $string );
		$string = str_replace( '>', '&gt;', $string );
		$string = str_replace( '\'', '&#39;', $string );
		$string = str_replace( '"', '&quot;', $string );
		
		$string = str_replace( "&amp;#", "&#", $string );
		$string = str_replace( "&amp;amp;", "&amp;", $string );
		
		// trying to catch unterminated entity references
		
		$string = preg_replace('/(&#[a-hA-H0-9]{2,5})\s/', "$1; ", $string);
		
		return $string;
	}
	
	protected function toTitleCase($strInput)
	{
		// NOTE: if you make a change to this function, make a corresponding change 
		// in the Xerxes_Framework_Parser class, since this one here is a duplicate function 
		// allowing Xerxes_Record to be a stand-alone class
		
		
		

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
			$strInput = Xerxes_Framework_Parser::strtolower( $strInput );
		}
		
		// array of small words
		
		$arrSmallWords = array ('of', 'a', 'the', 'and', 'an', 'or', 'nor', 'but', 'is', 'if', 'then', 
		'else', 'when', 'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to', 'into', 'with', 'as' );
		
		// split the string into separate words

		$arrWords = explode( ' ', $strInput );
		
		foreach ( $arrWords as $key => $word )
		{
			// if this word is the first, or it's not one of our small words, capitalise it 
			
			if ( $key == 0 || ! in_array( Xerxes_Framework_Parser::strtolower( $word ), $arrSmallWords ) )
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
			elseif ( in_array( Xerxes_Framework_Parser::strtolower( $word ), $arrSmallWords ) )
			{
				$arrWords[$key] = Xerxes_Framework_Parser::strtolower( $word );
			}
		}
		
		// join the words back into a string

		$strFinal = implode( ' ', $arrWords );
		
		// catch subtitles

		if ( preg_match( "/: ([a-z])/", $strFinal, $arrMatches ) )
		{
			$strLetter = ucwords( $arrMatches[1] );
			$strFinal = preg_replace( "/: ([a-z])/", ": " . $strLetter, $strFinal );
		}
		
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
	
	protected function ordinal($value)
	{
		if ( is_numeric( $value ) )
		{
			if ( substr( $value, - 2, 2 ) == 11 || substr( $value, - 2, 2 ) == 12 || substr( $value, - 2, 2 ) == 13 )
			{
				$suffix = "th";
			} 
			elseif ( substr( $value, - 1, 1 ) == 1 )
			{
				$suffix = "st";
			} 
			elseif ( substr( $value, - 1, 1 ) == 2 )
			{
				$suffix = "nd";
			} 
			elseif ( substr( $value, - 1, 1 ) == 3 )
			{
				$suffix = "rd";
			} 
			else
			{
				$suffix = "th";
			}
			
			return $value . $suffix;
		} 
		else
		{
			return $value;
		}
	}
	
	protected function isFullText($arrLink)
	{
		if ( $arrLink[2] == "pdf" || $arrLink[2] == "html" || $arrLink[2] == "online" )
		{
			return true; 
		}
		else
		{
			return false;
		}
	}
	
	### PROPERTIES ###
	
	// non-standard properties

	public function hasFullText()
	{
		$bolFullText = false;
		
		foreach ( $this->links as $arrLink )
		{
			if ( $this->isFullText($arrLink) == true )
			{
				$bolFullText = true;
			}
		}
		
		return $bolFullText;
	}
	
	public function getFullText($bolFullText = false)
	{
		// limit to only full-text links

		if ( $bolFullText == true )
		{
			$arrFinal = array ( );
			
			foreach ( $this->links as $arrLink )
			{
				if ( $this->isFullText($arrLink) == true )
				{
					array_push( $arrFinal, $arrLink );
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
	
	public function getPrimaryAuthor($bolReverse = false)
	{
		$arrPrimaryAuthor = $this->getAuthors( true, true, $bolReverse );
		
		if ( count( $arrPrimaryAuthor ) > 0 )
		{
			return $arrPrimaryAuthor[0];
		} 
		elseif ( $this->author_from_title != "" )
		{
			return trim( $this->author_from_title );
		} 
		else
		{
			return null;
		}
	}
	
	/**
	 * Return authors.  Authors will return as array, with each author name optionally formatted
	 * as a string ('first last' or 'last, first') or as an associative array in parts, based on
	 * paramaters listed below.
	 *
	 * @param bool $bolPrimary		[optional] return just the primary author, default false
	 * @param bool $bolFormat		[optional] return the author names as strings (otherwise as objects), default false
	 * @param bool $bolReverse		[optional] return author names as strings, last name first
	 * @return array
	 */
	
	public function getAuthors($bolPrimary = false, $bolFormat = false, $bolReverse = false)
	{
		$arrFinal = array ( );
		
		foreach ( $this->authors as $objXerxesAuthor )
		{
			// author as string
			
			if ( $bolFormat == true )
			{
				$strAuthor = ""; // author name formatted

				$strFirst = $objXerxesAuthor->first_name;
				$strLast = $objXerxesAuthor->last_name;
				$strInit = $objXerxesAuthor->init;
				$strName = $objXerxesAuthor->name;
				
				if ( $strName != "" )
				{
					$strAuthor = $strName;
				} 
				else
				{
					if ( $bolReverse == false )
					{
						$strAuthor = $strFirst . " ";
						
						if ( $strInit != "" )
						{
							$strAuthor .= $strInit . " ";
						}
						
						$strAuthor .= $strLast;
					} 
					else
					{
						$strAuthor = $strLast . ", " . $strFirst . " " . $strInit;
					}
				}
				
				array_push( $arrFinal, $strAuthor );
			} 
			else
			{
				// author objects
				
				array_push( $arrFinal, $objXerxesAuthor );
			}
			
			// we're only asking for the primary author
			
			if ( $bolPrimary == true )
			{
				// sorry, only additional authors (7XX), so return empty
				
				if ( $objXerxesAuthor->additional == true )
				{
					return array();
				}
				else
				{
					// exit loop, we've got the author we need
					break;
				}
			}
		}
		
		return $arrFinal;
	}
	
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
	
	public function getAllISSN()
	{
		return $this->issns;
	}
	
	public function getAllISBN()
	{
		return $this->isbns;
	}
	
	public function getMainTitle()
	{
		return $this->title;
	}
	
	public function getEdition()
	{
		return $this->edition;
	}
	
	public function getControlNumber()
	{
		return $this->control_number;
	}
	
	public function isEditor()
	{
		return $this->editor;
	}
	
	public function getFormat()
	{
		return $this->format;
	}
	
	public function setFormat($format)
	{
		$this->format = $format;
	}
	
	public function getTechnology()
	{
		return $this->technology;
	}
	
	public function getNonSort()
	{
		return $this->non_sort;
	}
	
	public function getSubTitle()
	{
		return $this->sub_title;
	}
	
	public function getSeriesTitle()
	{
		return $this->series_title;
	}
	
	public function getAbstract()
	{
		return $this->abstract;
	}
	
	public function getSummary()
	{
		return $this->summary;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function getEmbeddedText()
	{
		return $this->embedded_text;
	}
	
	public function getLanguage()
	{
		return $this->language;
	}
	
	public function getTOC()
	{
		return $this->toc;
	}
	
	public function getPlace()
	{
		return $this->place;
	}
	
	public function getPublisher()
	{
		return $this->publisher;
	}
	
	public function getYear()
	{
		return $this->year;
	}
	
	public function getJournal()
	{
		return $this->journal;
	}
	
	public function getVolume()
	{
		return $this->volume;
	}
	
	public function getIssue()
	{
		return $this->issue;
	}
	
	public function getStartPage()
	{
		return $this->start_page;
	}
	
	public function getEndPage()
	{
		return $this->end_page;
	}
	
	public function getExtent()
	{
		return $this->extent;
	}
	
	public function getPrice()
	{
		return $this->price;
	}
		
	public function getNotes()
	{
		return $this->notes;
	}
		
	public function getSubjects() 
	{
		return $this->subjects;
	}
		
	public function getInstitution()
	{
		return $this->institution;
	}
		
	public function getDegree()
	{
		return $this->degree;
	}
		
	public function getCallNumber()
	{
		return $this->call_number;
	}
		
	public function getOCLCNumber()
	{
		return $this->oclc_number;
	}
		
	public function getDOI()
	{
		return $this->doi;
	}
	
	public function getSource()
	{
		return $this->source;
	}
	
	public function setRefereed($bool)
	{
		$this->refereed = (bool) $bool;
	}
	
	public function getRefereed()
	{
		return $this->refereed;
	}
	
	public function setSubscription($bool)
	{
		$this->subscription = (bool) $bool;
	}
	
	public function getSubscription()
	{
		return $this->subscription;
	}
	
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
	
	public function getRecordID()
	{
		return $this->record_id;
	}
	
	public function setRecordID($id)
	{
		return $this->record_id = $id;
	}	
	
	public function setNoItems($bool)
	{
		$this->no_items = $bool;
	}
	
	public function addItems(Xerxes_Record_Items $items )
	{
		if ( $items->length() == 0 )
		{
			$this->setNoItems(true);
		}
		else
		{
			// add item
			
			foreach ( $items->getItems() as $item )
			{
				$this->addItem($item);
			}
			
			// include it in the XML as well
			
			if ( $this->document instanceof DOMDocument )
			{
				$record = $this->document->getElementsByTagName("record")->item(0);
				
				if ( $record != null )
				{
					foreach ( $items->getItems() as $item )
					{
						$import = $this->document->importNode($item->toXML()->documentElement,true);
						$record->appendChild($import);		
					}
				}
			}
		}
	}
	
	public function addItem($item )
	{
		array_push($this->items, $item);
	}
	
	public function getItems()
	{
		return $this->items;
	}
}

class Xerxes_Record_Subject
{
	public $value;
	public $display;
}

class Xerxes_Record_Author
{
	public $first_name;
	public $last_name;
	public $init;
	public $name;
	public $type;
	public $additional;
	public $display;
	
	public function allFields()
	{
		$values = "";
		
		foreach ( $this as $key => $value )
		{
			if ( $key == "additional" || $key == "display")
			{
				continue;
			}
			
			$values .= $value . " ";
		}
		
		return trim($values);
	}
}

class Xerxes_Record_Items
{
	private $items = array();
	
	public function addItem($item)
	{
		array_push($this->items, $item);
	}
	
	public function getItems()
	{
		return $this->items;
	}
	
	public function length()
	{
		return count($this->items);
	}
}

class Xerxes_Record_Item
{
	protected $id; 		// the bibliographic record ID
    protected $availability; // boolean: is this item available for checkout?
    protected $status; 	// string describing the status of the item
    protected $location; // string describing the physical location of the item
    protected $reserve; // string indicating “on reserve” status – legal values: 'Y' or 'N'
    protected $callnumber; // the call number of this item
    protected $duedate; // string showing due date of checked out item (null if not checked out)
    protected $number; 	// the copy number for this item (note: although called “number”, 
    					//this may actually be a string if individual items are named rather than numbered)
    protected $barcode; // the barcode number for this item
    
    public function loadXML(DOMNode $xml)
    {
    	foreach ($xml->childNodes as $child)
    	{
    		$name = $child->nodeName;
    		
    		if ( property_exists('Xerxes_Record_Item', $name) )
    		{
    			$this->$name = $child->nodeValue;
    		}
    	}
    }
	
	public function setProperty($name, $value)
	{
		if ( property_exists($this, $name) )
		{
			$this->$name = $value;
		}
	}
	
	public function toXML()
	{
		$xml = new DOMDocument();
		$xml->loadXML("<item />");
		
		foreach ( $this as $key => $value )
		{
			if ( $value == "")
			{
				continue;
			}
			
			$key = preg_replace('/\W|\s/', '', $key);
			
			$element = $xml->createElement($key, Xerxes_Framework_Parser::escapeXml($value));
			$xml->documentElement->appendChild($element);
		}
		
		return $xml;
	}
}

class Xerxes_Record_Holding
{
	private $data = array();
	
	public function setProperty($name, $value)
	{
		if ( $name != "holding" && $name != "id" )
		{
			$this->data[$name] = $value;
		}
	}
	
	public function toXML()
	{
		$xml = new DOMDocument();
		$xml->loadXML("<holding />");
		
		foreach ( $this->data as $key => $value )
		{
			$element = $xml->createElement("data");
			$element->setAttribute("key", $key);
			$element->setAttribute("value", $value);
			$xml->documentElement->appendChild($element);
		}
		
		return $xml;
	}	
	
}


?>
