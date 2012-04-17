<?php

namespace Application\Model\Metalib;

use Xerxes,
	Xerxes\Marc,
	Xerxes\Record\Bibliographic,
	Xerxes\Record\ContextObject,
	Xerxes\Record\Format,
	Xerxes\Utility\Parser,
	Xerxes\Utility\Registry;

/**
 * Metaib Record
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @version 
 * @package Xerxes
 */

class Record extends Bibliographic
{
	protected $metalib_id; // metalib id
	protected $result_set; // result set number
	protected $record_number; // record number
	
	protected $context_object; // xerxes context object
	
	/**
	 * Create Metalib Record
	 */
	
	public function __construct()
	{
		// metalib includes an openurl context object buried inside the marc-xml
		// we'll inspect it's values too, since it often contains useful data
		
		$this->context_object = new ContextObject();
		$this->context_object->loadXML($this->document);
		
		$this->utility[] = "context_object"; // it's just a utility
	}
	
	/**
	 * Map properties
	 * 
	 * Special handling here for metalib id's, openurl context object, and various metalib
	 * data-munging hacks
	 */
	
	public function map()
	{
		## source database
		
		$sid = $this->marc->datafield("SID");
		
		$this->metalib_id = (string) $sid->subfield("d");
		$this->record_number = (string) $sid->subfield("j");
		$this->result_set = (string) $sid->subfield("s");
		
		$this->database_name = (string) $sid->subfield("t");
		$this->source = (string) $sid->subfield("b");
		
		// eric doc number
		
		$this->eric_number = (string) $this->marc->datafield("ERI")->subfield("a");
		
		
		## metalib weirdness
		
		$leader = $this->marc->leader();
		
		// puts leader in control field
		
		$strLeaderMetalib = (string) $this->marc->controlfield("LDR");
		
		if ( $strLeaderMetalib != "" )
		{
			$leader->value = $strLeaderMetalib;
		}
		
		$demunge = array("1XX", "6XX");
		
		// character entity references de-munging code -- thanks a lot metalib!
		
		foreach ( $demunge as $field )
		{
			$got_one = true;
			
			do // this until all references are re-combined
			{
				$authors = $this->marc->datafield($field); // variable is called 'authors' but this also now covers subjects
				
				$got_one = false; // whether we found any in the list
				
				for ( $x = 0; $x < $authors->length(); $x++ )
				{
					$this_datafield = $authors->item($x);
					$this_value = (string) $this_datafield->subfield();
					
					// we found an un-terminated char entity ref
					
					$matches = array();
					
					if ( preg_match('/\&\#\d{3}$/', $this_value, $matches) )
					{
						$got_one = true;
						$new_value = "";
						
						// grab the value out of the next field
						
						$x++;
						
						// hopefully we aren't at the end?
						
						if ( $x < $authors->length() )
						{
							// nope, so grab the next field's value
							
							$next_datafield = $authors->item($x);
							$next_value = (string) $next_datafield->subfield();
							
							// add back in the terminating semi-colon
							$new_value = "$this_value;$next_value";
							
							// blank it so we don't re-process it
							$next_datafield->tag = "XXX";
						}
						else
						{
							// yup, just add a terminating char to the value
							
							$new_value = $this_value . ";";
						}
							
						// now create a new datafield composed of both old and new values
							
						$fixed_datafield = new Marc\DataField();
						$fixed_datafield->tag = $this_datafield->tag;
							
						// we'll just assume this is |a
							
						$fixed_subfield = new Marc\Subfield();
						$fixed_subfield->code = "a";
						$fixed_subfield->value = $new_value;
						$fixed_datafield->addSubField($fixed_subfield);
							
						// add it to the main record
							
						$this->marc->addDataField($fixed_datafield);
							
						// now blank the old ones
							
						$this_datafield->tag = "XXX";
					}
					else
					{
						// we need to shift this to the end to keep field order in tact 
						// (critical for authors) so the above code works right
						
						$new_field = clone($this_datafield);
						$this->marc->addDataField($new_field);
						$this_datafield->tag = "XXX";
					}
				}
				
				// if we found one, cycle back again to see if our now-combined
				// field(s) *also* have un-terminated references since there may have
				// been more than one broken char reference for a single author, e.g.
			}
			while ( $got_one == true);
		}
		
		// z3950/sutrs and some screen-scrapers have multiple authors in repeating 100 fields; 
		// invalid marc, so switch all but first to 700
		
		$authors = $this->marc->datafield("100");
		
		if ( $authors->length() > 1 )
		{
			for ( $x = 1; $x < $authors->length(); $x++ )
			{
				$author = $authors->item($x);
				$author->tag = "700";
			}
		}
		
		// there are often multiple 773's, just combine them into one so we don't
		// have to iterate over all of them in other code
		
		$obj773 = new Marc\DataField();
		$obj773->tag = "773";
		
		foreach ( $this->marc->datafield("773") as $linked_entry )
		{
			// add all of its subfields to the new one
			
			foreach ($linked_entry->subfield() as $linked_entry_subfield )
			{
				$obj773->addSubField($linked_entry_subfield);
			}
			
			// now blank this one to take it out of the mix
			$linked_entry->tag = "XXX";
		}
		
		// add our new one to the document
		
		$this->marc->addDataField($obj773);
		
		
		// psycinfo and related databases
		
		if ( strstr($this->source, "EBSCO_PDH") || strstr($this->source, "EBSCO_PSYH") || strstr($this->source, "EBSCO_LOH") )
		{
			// includes a 502 that is not a thesis note -- bonkers!
			// need to make this a basic note, otherwise xerxes will assume this is a thesis
				
			foreach ( $this->marc->datafield("502") as $thesis )
			{
				$thesis->tag = "500";
			}
		}		
		
		
		### context object
		
		$this->isbns = $this->context_object->getISBN();
		$this->issns = $this->context_object->getISSN();
		
		// now do a regular marc mapping, with exceptions below
		
		
		parent::map();
		
		
		## oclc dissertation abstracts
		
		// (HACK) 10/1/2007 this assumes that the diss abs record includes the 904, which means
		// there needs to be a local search config that performs an 'add new' action rather than
		// the  'remove' action that the parser uses by default
		
		if ( strstr ( $this->source, "OCLC_DABS" ) )
		{
			$this->degree = $this->marc->datafield("904")->subfield("j");
			$this->institution = $this->marc->datafield("904")->subfield("h");
			$this->journal_title = $this->marc->datafield("904")->subfield("c");
				
			$this->journal = $this->journal_title . " " . $this->journal;
				
			if ($this->journal_title == "MAI")
			{
				$format = "Thesis";
			}
			else
			{
				$format = "Dissertation";
			}
			
			$this->format = new Format();
			$this->format->setFormat($format);
		}
		
		
		## ebsco 77X weirdness
		
		if ( strstr($this->source, "EBSCO") )
		{
			// pages in $p (abbreviated title)
		
			$pages = (string) $this->datafield("773")->subfield('p');
		
			if ( $pages != "" )
			{
				$this->short_title = "";
			}
				
			// book chapter
		
			$btitle = (string) $this->datafield("771")->subfield('a');
		
			if ( $btitle != "" )
			{
				$this->book_title = $btitle;
				$this->format = "Book Chapter";
			}
		}
		
		
		## JSTOR book review correction 
		
		// title is meaningless, but subjects contain the title of the books, 
		// so we'll swap them to the title here
		
		if (strstr ( $this->source, 'JSTOR' ) && $this->title == "Review")
		{
			$this->title = "";
			$this->sub_title = "";
		
			foreach ( $this->subjects as $subject )
			{
				$this->title .= " " . $subject->value;
			}
		
			$this->title = trim( $this->title );
		
			$this->subjects = null;
			
			$this->format = new Format();
			$this->format->setFormat("Book Review"); // @todo: normalized?
		}
	}
	
	protected function parseJournal()
	{
		parent::parseJournal();
		
		
		// these values from context object are usually preferred
		
		$volume = $this->context_object->getVolume();
		$issue = $this->context_object->getIssue();
		$start_page = $this->context_object->getStartPage();
		$end_page = $this->context_object->getEndPage();
		$short_title = $this->context_object->getShortTitle();
		
		if ( $volume != "" )
		{
			$this->volume = $volume;
		}
		
		if ( $issue != "" )
		{
			$this->issue = $issue;
		}
		
		if ( $start_page != "" )
		{
			$this->start_page = $start_page;
		}
		
		if ( $end_page != "" )
		{
			$this->end_page = $end_page;
		}
		
		if ( $short_title != "" )
		{
			$this->short_title = $short_title;
		}
		
		
		### journal title
		
		
		// we'll take the journal title extracted from 773$t as the best option,
		
		if ( $this->journal_title == "" )
		{
			$format = $this->format()->getPublicFormat(); // @todo switch to normalized?
			
			// otherwise see if the context object has one
				
			if ( $this->context_object->getJournalTitle() != null )
			{
				$this->journal_title = $this->context_object->getJournalTitle();
			}
				
			// or see if a short title exists, but only for article/serial types
			// @todo switch to normalized?
			
			elseif ( $this->short_title != "" && ( $format == "Article" || $format == "Journal" || $format == "Newspaper" )) 
			{
				$this->journal_title = $this->short_title;
			}
		}		
		
		
		### year
		
		
		// metalib's own year
		
		$year = (string) $this->marc->datafield("YR ")->subfield("a"); // space at end of YR is not a typo
		
		if ( $year != "" )
		{
			$this->year = $year;
		}
		else // try the context object
		{
			$this->year = $this->context_object->getYear();
		}
		
		
		### issue, volume
		
		
		// metalib's own issue, volume fields

		if ( $this->issue == null )
		{
			$this->issue = (string) $this->marc->datafield("ISS")->subfield("a");
		}

		if ( $this->volume == null )
		{
			$this->volume = (string) $this->marc->datafield("VOL")->subfield("a");
		}
	}
	
	protected function parseAuthors()
	{
		parent::parseAuthors();
		
		// last-chance from context-object
		
		if ( count($this->authors) == 0 )
		{
			$this->authors = $this->context_object->getAuthors();
		}		
	}
	
	protected function parseTitle()
	{
		// Gale title clean-up, because for some reason unknown to man they put weird
		// notes and junk at the end of the title. so remove them here and add them to notes.
		
		if (strstr ( $this->source, 'GALE_' ))
		{
			$arrMatches = array ();
			$strGaleRegExp = '/\(([^)]*)\)/';
		
			$title = $this->marc->datafield("245");
			$title_main = $title->subfield("a");
			$title_sub = $title->subfield("b");
		
			$note_field = new Marc\DataField();
			$note_field->tag = "500";
		
			if ( $title_main != null )
			{
				if (preg_match_all ( $strGaleRegExp, $title_main->value, $arrMatches ) != 0)
				{
					$title_main->value = preg_replace ( $strGaleRegExp, "", $title_main->value );
				}
		
				foreach ( $arrMatches[1] as $strMatch )
				{
					$subfield = new Marc\Subfield();
					$subfield->code = "a";
					$subfield->value = "From title: " . $strMatch;
					$note_field->addSubField($subfield);
				}
			}
		
			// sub title is only these wacky notes
		
			if ( $title_sub != null )
			{
				$subfield = new Marc\Subfield();
				$subfield->code = "a";
				$subfield->value = "From title: " . $title_sub->value;
				$note_field->addSubField($subfield);
		
				$title_sub->value = "";
			}
		
			if ( $note_field->subfield("a")->length() > 0 )
			{
				$this->marc->addDataField($note_field);
			}
		}
		
		
		
		parent::parseTitle();
		
		
		
		// last chance, check the context object
		
		if ( $this->title == "" && $this->context_object->getTitle() != null )
		{
			$this->title = $this->context_object->getTitle();
		}
	}
	
	protected function parseSubjects()
	{
		parent::parseSubjects();
		
		// CSA subject term clean-up,
		// since they put an asterick in front of each term (2009-09-30)
		
		if (strstr ( $this->source, 'CSA_' ))
		{
			for ( $x = 0; $x < count($this->subjects); $x++ )
			{
				$subject_object = $this->subjects[$x];
				$subject_object->value = str_replace("*", "", $subject_object->value);
				$this->subjects[$x] = $subject_object;
			}
		}	
	}
	
	protected function parseLinks()
	{
		### create our own link
		
		
		// some databases have full-text but no 856
		// will capture these here and add to links array
		
		// factiva -- no indicator of full-text, just make original record
		
		if (stristr ( $this->source, "FACTIVA" ))
		{
			$link_array = array("035_a" => (string) $this->marc->datafield("035")->subfield("a") );
			$this->links[] = new Link($link_array, Link::ORIGINAL_RECORD);
		}
		
		// eric -- document is recent enough to likely contain full-text;
		// 340000 being a rough approximation of the document number after which they
		// started digitizing
		
		if (strstr ( $this->source, "ERIC" ) && strlen ( $this->eric_number ) > 3)
		{
			$strEricType = substr( $this->eric_number, 0, 2 );
			$strEricNumber = (int) substr ( $this->eric_number, 2 );
				
			if ($strEricType == "ED" && $strEricNumber >= 340000)
			{
				$strFullTextPdf = "http://www.eric.ed.gov/ERICWebPortal/contentdelivery/servlet/ERICServlet?accno=" . $this->eric_number;
				$this->links[] = new Link($strFullTextPdf, Link::PDF);
			}
		}
		
		// 7 Apr 09, jrochkind. Gale Biography Resource Center
		// No 856 is included at all, but a full text link can be
		// constructed from the 001 record id.
		
		if ( stristr($this->source,"GALE_ZBRC") )
		{
			$url = "http://ic.galegroup.com/ic/bic1/ReferenceDetailsPage/ReferenceDetailsWindow?" .
				"displayGroupName=K12-Reference&action=e&windowstate=normal&mode=view&documentId=GALE|" .
				(string) $this->marc->controlfield("001");
				
			$this->links[] = new Link($url, Link::HTML);
		}
		
		
		### remove links
		
		
		$notes = $this->marc->fieldArray("500", "a"); // needed for gale
		
		foreach ( $this->marc->datafield("856") as $link )
		{
			$strDisplay = (string) $link->subfield("z");
			$strUrl = (string) $link->subfield("u");
				
			// records that have 856s, but are not always for full-text
		
			if ( stristr ( $this->source, "METAPRESS_XML" ) ||  // does not distinguish between things in your subscription or not (9/16/08)
				( stristr ( $this->source, "WILSON_" ) && (string) $this->marc->datafield("901")->subfield("t") == "" ) || // 901|t shows an indication of full-text (9/16/10)
				stristr ( $this->source, "CABI" ) || // just point back to site (10/30/07)
				stristr ( $this->source, "AMAZON" ) ||  // just point back to site (3/20/08)
				stristr ( $this->source, "ABCCLIO" ) || // just point back to site (7/30/07)
				stristr ( $this->source, "EVII" ) || // has unreliable full-text links in a consortium environment (4/1/08)
				stristr ( $this->source, "WILEY_IS" ) || // wiley does not limit full-text links only to your subscription (4/29/08)
				(stristr ( $this->source, "OXFORD_JOU" ) && ! strstr ( $strUrl, "content/full/" )) || // only include the links that are free, otherwise just a link to abstract (5/7/08)
				(strstr ( $this->source, "GALE" ) && ! strstr( $this->source, "GALE_GVRL") && ! in_array ( "Text available", $notes )) || // only has full-text if 'text available' note in 500 field (9/7/07) BUT: Not true of Gale virtual reference library (GALE_GVRL). 10/14/08 jrochkind.
				stristr ( $this->source, "IEEE_XPLORE" ) || // does not distinguish between things in your subscription or not (2/13/09)
				stristr ($this->source, "ELSEVIER_SCOPUS") || // elsevier links are not based on subscription (6/2/10)
				stristr ($this->source, "ELSEVIER_SCIRUS") || // elsevier links are not based on subscription (6/2/10)
				stristr ( $this->source, "SCOPUS4" ) || // elsevier links are not based on subscription (6/2/10)
				( strstr($strUrl, "proquest.umi.com") && strstr($strUrl, "Fmt=2") ) || // these links are not full-text (thanks jerry @ uni) (5/26/10)
				( strstr($strUrl, "gateway.proquest.com") && strstr($strUrl, "xri:fiaf:article") ) // there doesn't appear to be a general rule to this, so only doing it for fiaf (5/26/10)
			)
			{
				$link->tag = "XXX"; // take it out so the parent class doesn't treat it as full-text
				$this->links[] = new Link($strUrl, Link::ORIGINAL_RECORD, $strDisplay);
			}
				
			// bad links
		
			elseif ( stristr ( $this->source, "EBSCO_RZH" ) || // not only is 856 bad, but link missing http://  bah! thanks greg at upacific! (9/10/08)
				( stristr ($this->source,"EBSCO") && $strUrl != "" && ! strstr ($strUrl, "epnet") ) || // harvard business: these links in business source premiere are not part of your subscription (5/26/10)
				stristr ( $this->source, "NEWPC" ) // primo central: no actual links here
			)
			{
				$link->tag = "XXX"; // take it out so the parent class doesn't treat it as full-text
				$this->links[] = new Link($strUrl, Link::INFORMATIONAL, $strDisplay);
			}
		}
		
		
		
		parent::parseLinks();
		
		
		
		## jstor links are all pdfs
		
		
		if (strstr ( $this->source, 'JSTOR' ))
		{
			for( $x = 0; $x < count($this->links); $x++ )
			{
				$link = $this->links[$x];
				$link->setType(LINK::PDF);
				$this->links[$x] = $link;
			}
		}
		
		
		## demote links based on config
		
		
		$config = Config::getInstance();
		$configIgnoreFullText = $config->getConfig( "FULLTEXT_IGNORE_SOURCES", false );
		
		$configIgnoreFullText = str_replace(" ", "", $configIgnoreFullText );
		$arrIgnore = explode(",", $configIgnoreFullText);
		
		for($x = 0; $x < count ( $this->links ); $x ++)
		{
			$link = $this->links[$x];
				
			if (in_array ( $this->source, $arrIgnore ) || in_array ( $this->metalib_id, $arrIgnore ))
			{
				$link->setType(Link::ORIGINAL_RECORD);
			}
			
			$this->links[$x] = $link;
		}		
	}
	
	protected function parseISSN()
	{
		parent::parseISSN();
		
		// gale puts issn in 773b
		
		if (strstr ( $this->source, 'GALE' ))
		{
			$strGaleIssn = (string) $this->marc->datafield("773")->subfield("b");
				
			if ($strGaleIssn != null)
			{
				array_push ( $this->issns, $strGaleIssn );
			}
		}
	}
	
	protected function parseFormat()
	{
		$format_object = new Format();
		
		
		// metalib usually maps format info from database to this field
		
		$format_array = $this->marc->fieldArray("513", "a");

		// isbd media and format notes
		
		$strTitleFormat =  $this->datafield("245")->subfield("hk")->__toString();
		
		if ( $strTitleFormat != null )
		{
			array_push( $format_array, $strTitleFormat );
		}
		
		// context object
		
		array_push($format_array, $this->context_object->format()->getPublicFormat()); // @todo switch to normalized?
		

		## ebsco format
		
		
		if (strstr ( $this->source, "EBSCO" ))
		{
			// leader appears to be hard-wired; useless
				
			$this->marc->leader()->value = "";
		
			// format
				
			array_push($format_array, (string) $this->marc->datafield("656")->subfield("a"));
			array_push($format_array, (string) $this->marc->datafield("514")->subfield("a"));
				
			$strEbscoType = (string) $this->marc->datafield("072")->subfield("a");
				
			if (strstr ( $this->source, "EBSCO_PSY" ) || strstr ( $this->source, "EBSCO_PDH" ))
			{
				$strEbscoType = "";
			}
				
			array_push($format_array, $strEbscoType);
		
			// ebsco book chapter
				
			$strEbscoBookTitle = (string) $this->marc->datafield("771")->subfield("a");
				
			if ($strEbscoBookTitle != "")
			{
				array_push ( $format_array, "Book Chapter" );
			}
		}
		
		
		// format made explicit from metalib
		
		$format = $format_object->extractFormat($format_array);
		
		// nothing good found, so get it from the basic marc parsing
		
		if ( $format == Format::Unknown )
		{
			$format = $this->convertToNormalizedFormat();
		}
		
		// explicit format related changes
		
		if ( (string) $this->marc->controlfield("002") == "DS" ) // czech libraries
		{
			$format = "Thesis";
		}
		elseif ( strstr ( $this->source, 'ERIC' ) && strstr ( $this->eric_number, 'ED' ) && ! stristr ( $this->title, "proceeding" ))
		{
			$format = "Report";
		}
		elseif (strstr ( $this->source, 'ERIC' ) && ! strstr ( $this->eric_number, 'ED' ) )
		{
			$format = "Article";
		}
		elseif (strstr ( $this->source, 'OCLC_PAPERS' ))
		{
			$format = "Conference Paper";
		}
		elseif (strstr ( $this->source, 'PCF1' ))
		{
			$format = "Conference Proceeding";
		}
		elseif ( stristr($this->source,"GOOGLE_B") )
		{
			$format = "Book";
		}
		elseif ( strstr($this->source, "EBSCO_LOH") )
		{
			$format = "Tests & Measures";
		}
		elseif ( strstr($this->source, "OXFORD_MUSIC_ONLINE") )
		{
			$format = "Article";
		}
		elseif ( strstr($this->source, "ESPACENET") )
		{
			$format = "Patent";
		}
		elseif ( strstr($this->source, "WIPO_PCT") )
		{
			$format = "Patent";
		}
		elseif ( strstr($this->source, "USPA") )
		{
			$format = "Patent";
		}
		elseif ( strstr($this->source, "DEPATIS") )
		{
			$format = "Patent";
		}
		elseif ( strstr($this->source, "DART") )
		{
			$format = "Thesis";
		}
		elseif ( strstr($this->source, "DDI") )
		{
			$format = "Thesis";
		}
		elseif ( strstr($this->source, "ETHOS") )
		{
			$format = "Thesis";
		}
		elseif ( strstr($this->source, "DIVA_EXTR") )
		{
			$format = "Thesis";
		}
		elseif ( strstr($this->source, "UNION_NDLTD") )
		{
			$format = "Thesis";
		}
		
		// set it 
		
		$this->format->setFormat($format);
	}

	
	### PROPERTIES ###
	

	public function getMetalibID()
	{
		return $this->metalib_id;
	}
	
	public function getResultSet()
	{
		return $this->result_set;
	}
	
	public function setResultSet($data)
	{
		$this->result_set = $data;
	}
	
	public function getRecordNumber()
	{
		return $this->record_number;
	}
	
	public function setRecordNumber($data)
	{
		$this->record_number = $data;
	}
	
	public function getDatabaseName()
	{
		return $this->database_name;
	}
}