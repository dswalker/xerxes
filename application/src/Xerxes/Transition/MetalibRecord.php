<?php

/**
 * Extract multiple Marc records from Metalib X-Server response
 * 
 * @author David Walker
 * @copyright 2009 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: MetalibRecord.php 2054 2012-05-01 21:34:24Z dwalker.calstate@gmail.com $
 * @todo ->__toString() madness below due to php 5.1 object-string casting problem
 * @package Xerxes
 */

class Xerxes_MetalibRecord_Document extends Xerxes_Marc_Document 
{
	protected $record_type = "Xerxes_MetalibRecord";
}

/**
 * Extract properties for books, articles, and dissertations from MARC-XML record 
 * with special handling for Metalib X-Server response
 * 
 * @author David Walker
 * @copyright 2009 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: MetalibRecord.php 2054 2012-05-01 21:34:24Z dwalker.calstate@gmail.com $
 * @todo ->__toString() madness below due to php 5.1 object-string casting problem, remove 
 *       when redhat provides php 5.2 package, since that is keeping people from upgrading
 *  * @package Xerxes
 */

class Xerxes_MetalibRecord extends Xerxes_Record
{
	protected $metalib_id;
	protected $result_set;
	protected $record_number;
	
	public function map()
	{
		$leader = $this->leader();

		## source database
		
		$sid = $this->datafield ( "SID" );
		
		$this->metalib_id = $sid->subfield( "d" )->__toString();
		$this->record_number = $sid->subfield( "j" )->__toString();
		$this->result_set = $sid->subfield( "s" )->__toString();
		$this->database_name = $sid->subfield( "t" )->__toString();
		$this->source = $sid->subfield( "b" )->__toString();
		
		## metalib weirdness
		
		// puts leader in control field
		
		$strLeaderMetalib = $this->controlfield( "LDR" )->__toString();
		
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
				$authors = $this->datafield($field);
				$got_one = false; // whether we found any in the list
				
				for ( $x = 0; $x < $authors->length(); $x++ )
				{
					$this_datafield = $authors->item($x);
					$this_value = $this_datafield->subfield()->__toString();
					
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
							$next_value = $next_datafield->subfield()->__toString();
							
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
							
						$fixed_datafield = new Xerxes_Marc_DataField();
						$fixed_datafield->tag = $this_datafield->tag;
							
						// we'll just assume this is |a
							
						$fixed_subfield = new Xerxes_Marc_Subfield();
						$fixed_subfield->code = "a";
						$fixed_subfield->value = $new_value;
						$fixed_datafield->addSubField($fixed_subfield);
							
						// add it to the main record
							
						$this->addDataField($fixed_datafield);
							
						// now blank the old ones
							
						$this_datafield->tag = "XXX";
					}
					else
					{
						// we need to shift this to the end to keep field order in tact 
						// (critical for authors) so the above code works right
						
						$new_field = clone($this_datafield);
						$this->addDataField($new_field);
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
		
		$authors = $this->datafield("100");
		
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
		
		$obj773 = new Xerxes_Marc_DataField();
		$obj773->tag = "773";
		
		foreach ( $this->datafield("773") as $linked_entry )
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
		
		$this->addDataField($obj773);
		
		
		## ebsco format
		
		if (strstr ( $this->source, "EBSCO" ))
		{
			// leader appears to be hard-wired; useless
			
			$leader->value = "";

			// format
			
			array_push($this->format_array, $this->datafield( "656" )->subfield( "a" )->__toString());
			array_push($this->format_array, $this->datafield( "514" )->subfield( "a" )->__toString());
			
			$strEbscoType =  $this->datafield( "072" )->subfield( "a" )->__toString();
			
			if (strstr ( $this->source, "EBSCO_PSY" ) || strstr ( $this->source, "EBSCO_PDH" ))
			{
				$strEbscoType = "";
			}
			
			array_push($this->format_array, $strEbscoType);

			// ebsco book chapter
			
			$strEbscoBookTitle = $this->datafield( "771" )->subfield( "a" )->__toString();
			
			if ($strEbscoBookTitle != "")
			{
				array_push ( $this->format_array, "Book Chapter" );
			}
		}
		
		// gale puts issn in 773b

		if (strstr ( $this->source, 'GALE' ))
		{
			$strGaleIssn = $this->datafield("773")->subfield("b")->__toString();
			
			if ($strGaleIssn != null)
			{
				array_push ( $this->issns, $strGaleIssn );
			}
		}
		
		// eric doc number

		$this->eric_number = $this->datafield( "ERI" )->subfield( "a" )->__toString();

		
		## full-text
			
		// some databases have full-text but no 856
		// will capture these here and add to links array
		
		// pychcritiques -- no indicator of full-text either, assume all to be (9/5/07)
		// no unique metalib config either, using psycinfo, so make determination based on name. yikes!
		
		if (stristr ( $this->database_name, "psycCRITIQUES" ))
		{
			array_push ( $this->links, array ("Full-Text in HTML", array ("001" => $this->controlfield("001")->__toString() ), "html" ) );
		}
		
		// factiva -- no indicator of full-text either, assume all to be (9/5/07)

		if (stristr ( $this->source, "FACTIVA" ))
		{
			array_push ( $this->links, array("Full-Text Available", array("035_a" => $this->datafield("035")->subfield("a")->__toString() ), "online" ) );
		}
		
		// eric -- document is recent enough to likely contain full-text;
		// 340000 being a rough approximation of the document number after which they 
		// started digitizing
		// EBSCO provides an indication of Full text available in 037 (jdwyn 01/12/2012)

		if (strstr ( $this->source, "ERIC" ) && strlen ( $this->eric_number ) > 3)
		{
			$strEricType = substr ( $this->eric_number, 0, 2 );
			$strEricNumber = ( int ) substr ( $this->eric_number, 2 );
			$strEricAvailabilityNote = $this->datafield("037")->subfield("a")->__toString();
			if ($strEricType == "ED")
			{
				if ($strEricNumber >= 340000 || (stristr ( $this->source, "EBSCO_ERIC" ) && $strEricAvailabilityNote != "Not available from ERIC" && $strEricAvailabilityNote != "Available on microfiche only")) 
				{
					$strFullTextPdf = "http://www.eric.ed.gov/ERICWebPortal/contentdelivery/servlet/ERICServlet?accno=" . $this->eric_number;
				 	array_push ( $this->links, array ("Full-text at ERIC.gov", $strFullTextPdf, "pdf" ) );
				}
			}
		}
		
		// 7 Apr 09, jrochkind. Gale Biography Resource Center
		// No 856 is included at all, but a full text link can be
		// constructed from the 001 record id.

		if ( stristr($this->source,"GALE_ZBRC") )
		{
			$url = "http://ic.galegroup.com/ic/bic1/ReferenceDetailsPage/ReferenceDetailsWindow?displayGroupName=K12-Reference&action=e&windowstate=normal&mode=view&documentId=GALE|" . $this->controlfield("001")->__toString();
			array_push ( $this->links, array ("Full-Text in HTML", $url, "html" ) );
		}
		
		// special handling of 856
		
		$notes = $this->fieldArray("500", "a"); // needed for gale

		foreach ( $this->datafield( "856" ) as $link )
		{
			$strDisplay = $link->subfield("z")->__toString();
			$strUrl = $link->subfield( "u" )->__toString();
			
			// bad links
			
			// records that have 856s, but are not always for full-text; in that case, specify them
			// here as original records, and remove 856 so parent code doesn't process them as full-text links
			//
			// springer (metapress): does not distinguish between things in your subscription or not (9/16/08) 
			// cinahl (bzh): not only is 856 bad, but link missing http://  bah! thanks greg at upacific! (9/10/08)
			// wilson: 901|t shows an indication of full-text (9/16/10) 
			// cabi: just point back to site (10/30/07)
			// google scholar: just point back to site (3/26/07) 
			// amazon: just point back to site (3/20/08)
			// abc-clio: just point back to site (7/30/07)
			// engineering village (evii): has unreliable full-text links in a consortium environment (4/1/08)
			// wiley interscience: wiley does not limit full-text links only to your subscription (4/29/08)
			// oxford: only include the links that are free, otherwise just a link to abstract (5/7/08)
			// gale: only has full-text if 'text available' note in 500 field (9/7/07) BUT: Not true of Gale virtual reference library (GALE_GVRL). 10/14/08 jrochkind. 
			// ieee xplore: does not distinguish between things in your subscription or not (2/13/09)
			// elsevier links are not based on subscription (6/2/10)
			// harvard business: these links in business source premiere are not part of your subscription (5/26/10)
			// proquest (umi): these links are not full-text (thanks jerry @ uni) (5/26/10)
			// proquest (gateway): there doesn't appear to be a general rule to this, so only doing it for fiaf (5/26/10)
			// primo central: no actual links here

			if ( stristr ( $this->source, "METAPRESS_XML" ) || 
				stristr ( $this->source, "EBSCO_RZH" ) || 
				( stristr ( $this->source, "WILSON_" ) && $this->datafield("901")->subfield("t")->__toString() == "" ) ||
				stristr ( $this->source, "CABI" ) || 
				stristr ( $this->source, "SCOPUS4" ) ||
				stristr ( $this->source, "GOOGLE_SCH" ) || 
				stristr ( $this->source, "AMAZON" ) || 
				stristr ( $this->source, "ABCCLIO" ) || 
				stristr ( $this->source, "EVII" ) || 
				stristr ( $this->source, "WILEY_IS" ) || 
				(stristr ( $this->source, "OXFORD_JOU" ) && ! strstr ( $strUrl, "content/full/" )) || 
				(strstr ( $this->source, "GALE" ) && ! strstr( $this->source, "GALE_GVRL") && ! in_array ( "Text available", $notes )) || 
				stristr ( $this->source, "IEEE_XPLORE" ) || 
				stristr ($this->source, "ELSEVIER_SCOPUS") ||
				stristr ($this->source, "ELSEVIER_SCIRUS") ||
				( stristr ($this->source,"EBSCO") && $strUrl != "" && ! strstr ($strUrl, "epnet") ) ||
				( strstr($strUrl, "proquest.umi.com") && strstr($strUrl, "Fmt=2") ) || 
				( strstr($strUrl, "gateway.proquest.com") && strstr($strUrl, "xri:fiaf:article") ) ||
				stristr ( $this->source, "NEWPC" )
				)
			{
				// take it out so the parent class doesn't treat it as full-text
				
				$link->tag = "XXX";
				array_push ( $this->links, array ($strDisplay, $strUrl, "original_record" ) );
			}
			
			// ebsco 

			elseif ( stristr ( $this->source, "EBSCO" ) )
			{
				$strEbscoFullText = $link->subfield( "i" )->__toString();
				$ebsco_fulltext_type = "";
				
				// html
				
				// there is (a) an indicator from ebsco that the record has full-text, or 
				// (b) an abberant 856 link that doesn't work, but the construct link will work, 
				// so we take that as something of a full-text indicator
							
				if ( strstr($strEbscoFullText, "T") || strstr($strDisplay, "View Full Text" ) )
				{
					$ebsco_fulltext_type = "html";
				}
				elseif ( strstr($link->subfield( "az" )->__toString(), "PDF") )
				{
					$ebsco_fulltext_type = "pdf";
				}
				
				if ( $ebsco_fulltext_type != "" )
				{
					$str001 = $this->controlfield("001")->__toString();
					$str016 = $this->datafield("016")->subfield("a")->__toString();
					
					// see if the id number is 'dirty'
					
					$bolAlpha001 = preg_match('/^\W/', $str001);
					
					// if so, and there is a 016, use that instead, if not go ahead and use 
					// the 001; if neither do nothing
					
					if ( $bolAlpha001 == true && $str016 != "" )
					{
						array_push ( $this->links, array ($strDisplay, array ("016" => $str016 ), $ebsco_fulltext_type ) );
					}
					elseif ( $bolAlpha001 == false )
					{
						array_push ( $this->links, array ($strDisplay, array ("001" => $str001 ), $ebsco_fulltext_type ) );
					}
	
					$link->tag = "XXX";
					array_push ( $this->links, array ($strDisplay, $strUrl, "original_record" ) );
				}
			} 
		}

		// Gale title clean-up, because for some reason unknown to man they put weird 
		// notes and junk at the end of the title. so remove them here and add them to notes.

		if (strstr ( $this->source, 'GALE_' ))
		{
			$arrMatches = array ();
			$strGaleRegExp = '/\(([^)]*)\)/';
			
			$title = $this->datafield("245");
			$title_main = $title->subfield("a");
			$title_sub = $title->subfield("b");
			
			$note_field = new Xerxes_Marc_DataField();
			$note_field->tag = "500";
			
			if ( $title_main != null )
			{
				if (preg_match_all ( $strGaleRegExp, $title_main->value, $arrMatches ) != 0)
				{
					$title_main->value = preg_replace ( $strGaleRegExp, "", $title_main->value );
				}
				
				foreach ( $arrMatches[1] as $strMatch )
				{				
					$subfield = new Xerxes_Marc_Subfield();
					$subfield->code = "a";
					$subfield->value = "From title: " . $strMatch;
					$note_field->addSubField($subfield);
				}
			}			
			
			// sub title is only these wacky notes

			if ($title_sub != null)
			{
				$subfield = new Xerxes_Marc_Subfield();
				$subfield->code = "a";
				$subfield->value = "From title: " . $title_sub->value;	
				$note_field->addSubField($subfield);			
				
				$title_sub->value = "";
			}
			
			if ( $note_field->subfield("a")->length() > 0 )
			{
				$this->addDataField($note_field);
			}
		}
		
		// psycinfo and related databases 
		
		if ( strstr($this->source, "EBSCO_PDH") || strstr($this->source, "EBSCO_PSYH") || strstr($this->source, "EBSCO_LOH") )
		{
			// includes a 502 that is not a thesis note -- bonkers!
			// need to make this a basic note, otherwise xerxes will assume this is a thesis
			
			foreach ( $this->datafield("502") as $thesis )
			{
				$thesis->tag = "500";
			}
		}		
		
		
		
		
		######## PARENT MAPPING ###########
		
		parent::map();	
		
		###################################
		
		
		
		
		// metalib's own year, issue, volume fields
		
		$year = $this->datafield("YR ")->subfield("a")->__toString();
		
		if ( $year != "" )
		{
			$this->year = $year;
		}

		if ( $this->issue == null )
		{
			$this->issue = $this->datafield("ISS")->subfield("a")->__toString();
		}

		if ( $this->volume == null )
		{
			$this->volume = $this->datafield("VOL")->subfield("a")->__toString();
		}
		
		
		// book chapters
		
		if ( $this->journal_title != "" && count($this->isbns) > 0 && count($this->issns) == 0 )
		{
			$this->book_title = $this->journal_title;
			$this->journal_title = "";
			$this->format = "Book Chapter";
		}
		
		
		## ebsco 77X weirdness
		
		if ( strstr($this->source, "EBSCO") )
		{
			// pages in $p (abbreviated title)
		
			$pages = $this->datafield("773")->subfield('p')->__toString();

			if ( $pages != "" )
			{
				$this->short_title = "";
			}			
			
			// book chapter
		
			$btitle = $this->datafield("771")->subfield('a')->__toString();
		
			if ( $btitle != "" )
			{
				$this->book_title = $btitle;
				$this->format = "Book Chapter";
			}
		}		
		
		
		## oclc dissertation abstracts

		// (HACK) 10/1/2007 this assumes that the diss abs record includes the 904, which means
		// there needs to be a local search config that performs an 'add new' action rather than
		// the  'remove' action that the parser uses by default

		if (strstr ( $this->source, "OCLC_DABS" ))
		{
			$this->degree = $this->datafield( "904" )->subfield( "j" )->__toString();
			$this->institution = $this->datafield( "904" )->subfield( "h" )->__toString();
			$this->journal_title = $this->datafield( "904" )->subfield( "c" )->__toString();
			
			$this->journal = $this->journal_title . " " . $this->journal;
			
			if ($this->journal_title == "MAI")
			{
				$this->format =  "Thesis";
			} 
			else
			{
				$this->format =  "Dissertation";
			}
		}		
		
		// random format related changes
		
		if ( strstr ( $this->source, 'ERIC' ) && strstr ( $this->eric_number, 'ED' ) && ! stristr ( $this->title, "proceeding" ))
		{
			$this->format = "Report";
		}
		elseif (strstr ( $this->source, 'ERIC' ) && ! strstr ( $this->eric_number, 'ED' ) )
		{
			$this->format = "Article";
		}
		elseif (strstr ( $this->source, 'OCLC_PAPERS' ))
		{
			$this->format = "Conference Paper";
		}
		elseif (strstr ( $this->source, 'PCF1' ))
		{
			$this->format = "Conference Proceeding";
		}
		elseif ( stristr($this->source,"GOOGLE_B") )
		{
			$this->format = "Book";
		}
		elseif ( strstr($this->source, "EBSCO_LOH") )
		{
			$this->format = "Tests & Measures";
		}
			elseif ( strstr($this->source, "OXFORD_MUSIC_ONLINE") )
		{
			$this->format = "Article";
		}
		elseif ( strstr($this->source, "ESPACENET") )
		{
			$this->format = "Patent";
		}
		elseif ( strstr($this->source, "WIPO_PCT") )
		{
			$this->format = "Patent";
		}
		elseif ( strstr($this->source, "USPA") )
		{
			$this->format = "Patent";
		}
		elseif ( strstr($this->source, "DEPATIS") )
		{
			$this->format = "Patent";
		}
		elseif ( strstr($this->source, "DART") )
		{
			$this->format = "Thesis";
		}
		elseif ( strstr($this->source, "DDI") )
		{
			$this->format = "Thesis";
		}
		elseif ( strstr($this->source, "ETHOS") )
		{
			$this->format = "Thesis";
		}
		elseif ( strstr($this->source, "DIVA_EXTR") )
		{
			$this->format = "Thesis";
		}
		elseif ( strstr($this->source, "UNION_NDLTD") )
		{
			$this->format = "Thesis";
		}
		
		// JSTOR book review correction: title is meaningless, but subjects
		// contain the title of the books, so we'll swap them to the title here

		if (strstr ( $this->source, 'JSTOR' ) && $this->title == "Review")
		{
			$this->title = "";
			$this->sub_title = "";
			
			foreach ( $this->subjects as $subject )
			{
				$this->title .= " " . $subject->value;
			}
			
			$this->title = trim ( $this->title );
			
			$this->subjects = null;
			
			$this->format = "Book Review";
		}

		// jstor links are all pdfs
		
		if (strstr ( $this->source, 'JSTOR' ))
		{
			for( $x = 0; $x < count($this->links); $x++ )
			{
				$link = $this->links[$x];
				$link[2] = "pdf";
				$this->links[$x] = $link;
			}
		}		
		
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
			
		// demote links based on config
		

		$objConfig = Xerxes_Framework_Registry::getInstance ();
		$configIgnoreFullText = $objConfig->getConfig ( "FULLTEXT_IGNORE_SOURCES", false );
		$configIgnoreFullText = str_replace ( " ", "", $configIgnoreFullText );
		$arrIgnore = explode ( ",", $configIgnoreFullText );
		
		for($x = 0; $x < count ( $this->links ); $x ++)
		{
			$link = $this->links [$x];
			
			if (in_array ( $this->source, $arrIgnore ) || in_array ( $this->metalib_id, $arrIgnore ))
			{
				$link [2] = "original_record";
			}
			
			$this->links [$x] = $link;
		}
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
		
	### until we move this elsewhere
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	static $TemplateEmptyValue = "Xerxes_Record_lookupTemplateValue_placeholder_missing";

	/**
	 * Take a Metalib-style template for a URL, including $100_a style
	 * placeholders, and replace placeholders with actual values
	 * taken from $this->marcXML
	 *
	 * @param string $template
	 * @return string url
	 */
	protected function resolveUrlTemplate($template)
	{
		# For some reason Metalib uses $0100 placeholder to correspond
		# to an SID field. If I understand how this works, this is nothing
		# but a synonym for $SID_c, so we'll use that. Absolutely no idea
		# why Metalib uses $0100 as syntactic sugar instead.
		
		$template = str_replace ( '$0100', '$SID_c', $template );
		
		$filled_out = preg_replace_callback ( '/\$([a-zA-Z0-9]{2,3})(_(.))?/', array ($this, 'lookupTemplateValue' ), $template );
		
		// Make sure it doesn't have our special value indicating a placeholder
		// could not be resolved. 
		if (strpos ( $filled_out, self::$TemplateEmptyValue ))
		{
			// Consistent with Metalib behavior, if a placeholder can't be resolved,
			// there is no link generated. 
			return null;
		}
		
		return $filled_out;
	
	}
	
	/* This function is just used as a callback in resolveUrlTemplate. 
       Takes a $matches array returned  by PHP regexp function that
       has a MARC field in $matches[1] and a subfield in $matches[3]. 
       Returns the value from $this->marcXML */
	protected function lookupTemplateValue($matches)
	{
		$field = $matches [1];
		
		$subfield = (count ( $matches ) >= 4) ? $matches [3] : null;
		
		$value = null;
		
		if ($subfield)
		{
			$value = $this->datafield($field)->subfield( $subfield )->__toString();
		} 
		else
		{
			//assume it's a control field, those are the only ones without subfields
			$value = $this->controlfield($field )->__toString();
		}
		if (empty ( $value ) && true)
		{
			// Couldn't resolve the placeholder, that means we should NOT
			// generate a URL, in this mode. Sadly we can't just throw
			// an exception, PHP eats it before we get it. I hate PHP. 
			// Put a special token in there. 
			return self::$TemplateEmptyValue;
		}
	
		return $value;
	}
	/* Fills out an array of Xerxes_Record to include links that are created
       by Metalib link templates (type 'holdings', 'original_record'). 
       
      @param $records, an array of Xerxes_Record 
      @param &$database_links_dom a DOMDocument containing a <database_links> section with Xerxes db information. Note that this is an optional parameter, if not given it will be calculated internally. If a variable with a null value is passed in, the variable will actually be SET to a valid DOMDocument on the way out (magic of pass by reference), so you can
      use this method to calculate a <database_links> section for you. */
	public static function completeUrlTemplates($records, $objRequest, $objRegistry, &$database_links_dom = null)
	{
		// If we weren't passed in a cached DOMDocument with a database_links
		// section, create one. Note that the var was passed by reference,
		// so this is available to the caller.   
		

		if ($database_links_dom == null)
		{
			$metalib_ids = array ();
			
			foreach ( $records as $r )
			{
				
				array_push ( $metalib_ids, $r->getMetalibID () );
			}
			
			$objData = new Xerxes_DataMap ( );
			$databases = $objData->getDatabases ( $metalib_ids );
			
			$database_links_dom = new DOMDocument ( );
			$database_links_dom->loadXML ( "<database_links/>" );
			
			foreach ( $databases as $db )
			{
				$objNodeDatabase = Xerxes_Helper::databaseToLinksNodeset ( $db, $objRequest, $objRegistry );
				
				$objNodeDatabase = $database_links_dom->importNode ( $objNodeDatabase, true );
				$database_links_dom->documentElement->appendChild ( $objNodeDatabase );
			}
		}
		
		// Pick out the templates into a convenient structure
		$linkTemplates = self::getLinkTemplates ( $database_links_dom );
		
		### Add link to native record and to external holdings URL too, if
		# available from metalib template. 
		foreach ( $records as $r )
		{
			if ($r->getMetalibID () && array_key_exists ( $r->getMetalibID (), $linkTemplates ))
			{
				
				$arrTemplates = $linkTemplates [$r->getMetalibID ()];
				
				foreach ( $arrTemplates as $type => $template )
				{
					$filled_in_link = $r->resolveUrlTemplate ( $template );
					if (! empty ( $filled_in_link ))
					{
						array_push ( $r->links, array (null, $filled_in_link, $type ) );
					}
				}
			}
		}
	}
	
	/* Creates a hash data structure of metalib-style URL templates for a given
     set of databases. Extracts this from Xerxes XML including a
     <database_links> section. Extracts into a hash for more convenient
     and quicker use.  Structure of hash is:
     { metalib_id1 => { "xerxes_link_type_a" => template,
                        "xerxes_link_type_b" => template }
       metalib_id2 => [...]
       
       Input is an XML DOMDocument containing a Xerxes <database_links>
       structure. 
  */
	protected function getLinkTemplates($xml)
	{
		$link_templates = array ();
		$dbXPath = new DOMXPath ( $xml );
		$objDbXml = $dbXPath->evaluate ( '//database_links/database' );
		
		for($i = 0; $i < $objDbXml->length; $i ++)
		{
			$dbXml = $objDbXml->item ( $i );
			$metalib_id = $dbXml->getAttribute ( "metalib_id" );
			$link_templates [$metalib_id] = array ();
			
			for($j = 0; $j < $dbXml->childNodes->length; $j ++)
			{
				$node = $dbXml->childNodes->item( $j );
				
				if ( $node instanceof DOMComment )
				{
					continue;
				}
				
				if ($node->tagName == 'link_native_record')
				{
					$link_templates [$metalib_id] ["original_record"] = $node->textContent;
				}
				if ($node->tagName == 'link_native_holdings')
				{
					$link_templates [$metalib_id] ["holdings"] = $node->textContent;
				}
			}
		}
		
		return $link_templates;
	}

	
	
	
}

class UrlTemplatePlaceholderMissing extends Exception {}

?>
