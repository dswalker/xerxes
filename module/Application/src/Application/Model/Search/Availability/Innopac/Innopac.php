<?php

namespace Application\Model\Search\Availability\Innopac;

use Application\Model\Search\Availability\AvailabilityInterface,
	Application\Model\Search,
	Xerxes\Utility\Parser,
	Zend\Http\Client;

/**
 * Retrieve item, holdings, and electonic resource information from an Innovative Millennium system
 * 
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @version
 * @license
 */

class Innopac implements AvailabilityInterface
{
	protected $url = ''; // final url
	protected $server = ''; // server address
	protected $innreach = false; // whether this is an innreach system
	protected $convert_to_utf8 = false; // needed?
	protected $config; // config object
	protected $client; // http client
	protected $availability_status = array(); // statuses that show available
	protected $locations_to_ignore = array(); // ignore these mofo's
	
	private $marc_ns = 'http://www.loc.gov/MARC21/slim'; // marc namespace

	/**
	 * Create new Innopac availability lookup object
	 *
	 * @param string $server		server address
	 */
	
	public function __construct( Client $client = null )
	{
		$this->config = Config::getInstance(); 
		
		$this->server = $this->config->getConfig('server', true);
		$this->server = rtrim($this->server, '/');
		
		$this->innreach = $this->config->getConfig('innreach', false, false);
		
		$this->convert_to_utf8 = $this->config->getConfig('convert_to_utf8', false, false);
		
		$availability_status = explode(';', $this->config->getConfig('available_statuses', true));
		
		foreach ( $availability_status as $status )
		{
			$this->availability_status[] = trim($status);
		}

		$ignore_locations = explode(';', $this->config->getConfig('ignore_locations', true));
		
		foreach ( $ignore_locations as $location )
		{
			$this->ignore_locations[] = trim($location);
		}		
		
		if ( $client != null )
		{
			$this->client = $client;
		}
		else
		{
			$this->client = new Client();
		}
	}
	
	/**
	 * Fetch record information
	 *
	 * @param string $bib_id	bibliographic id
	 */
	
	public function getHoldings( $bib_id )
	{
		// echo strlen($bib_id);

		$record = new Search\Holdings();
		
		// fetch info from server
		
		$id = substr( $bib_id, 1 );
		
		$query = "/search/.$bib_id/.$bib_id/1,1,1,B/detlmarc~$id&FF=&1,0,";
		
		$this->url = $this->server . $query;
		
		$response = $this->fetch( $this->url );
		
		// didn't find a record
		
		if ( ! stristr($response, "<pre>") )
		{
			return $record;
		}
		
		// parse record
		
		$record->id = $this->extractID( $response );
		
		// marc record
		
		$record->setBibliographicRecord( $this->extractMarc($response) );

		// items
		
		foreach ( $this->extractItemRecords( $response ) as $item )
		{
			// this isn't the location you are looking for
			
			if ( in_array($item->location, $this->locations_to_ignore) )
			{
				continue;
			}
			
			// this status shows the item is available
			
			if ( in_array($item->status, $this->availability_status) )
			{
				$item->availability = true;
			}
			
			$record->addItem($item);
		}		
		
		// periodical holdings
		
		foreach ( $this->extractHoldingsRecords( $response ) as $holdings )
		{
			$record->addHolding($holdings);
		}
		
		// erm records
		
		foreach ( $this->extractERMRecords( $response ) as $electronic )
		{
			$record->addElectronicResource($electronic);
		}
		
		return $record;
	}
	
	/**
	 * Fetch URL
	 * 
	 * @param string $url
	 * @return string 
	 */
	
	protected function fetch( $url )
	{
		$this->client->setUri($url);
		$this->client->setOptions(array('timeout' => 4));

		return $this->client->send()->getBody();
	}
	
	/**
	 * See if we can find the bib id on the page
	 *
	 * @param string $html		the HTML response from the server
	 * @return string or null		if we found the id, we return it
	 */
	
	protected function extractID($html)
	{
		$matches = array();
		
		if ( preg_match("/RECNUM B([0-9]{8,10})/", $html, $matches) )
		{
			return "b" . $matches[1];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Extracts data from the item records table in the HTML response
	 *
	 * @param string $html		html response from catalog
	 * @param bool $bolRecursive	[optional] whether this is a recursive call from inside the function
	 * @return array 				array of Item objects
	 */
	
	protected function extractItemRecords($html, $bolRecursive = false)
	{
		$bolOrderNote = false; // whether holdings table shows an ordered noted
		$bolFieldNotes = false; // whether holdings table includes comments indicating item record marc fields

		$holdings_array = array (); // master array we'll used to hold the holdings		
		
		// see if there is more than one page of item records, in which case get the
		// expanded (holdings) page instead; performance hit, but gotta do it
		
		if ( stristr($html, "additional copies") )
		{
			if ( $bolRecursive == true )
			{
				throw new \Exception("recursion error getting additional copies");
			}
			
			$holdings_url = ""; // uri to full holdings list
			$match_array = array(); // matches from the regex below
			
			// get all the post form actions, one of them will be for the 
			// function that gets the additional items (holdings) page
			
			if ( preg_match_all("/<form method=\"post\" action=\"([^\"]*)\"/", $html, $match_array, PREG_PATTERN_ORDER))
			{
				foreach($match_array[1] as $strPostUrl)
				{
					if ( stristr($strPostUrl, "/holdings") )
					{
						$holdings_url = $this->server . $strPostUrl;
						break;
					}
				}
			}
			
			// get the full response page now and redo the function call
			
			$response = $this->fetch($holdings_url);
			
			return $this->extractItemRecords($response, true);
		}
		
		$table = ""; // characters that mark the start of the holding table
		
		// look to see which template this is and adjust the 
		// parser to accomadate the html structure

		if ( strpos( $html, "class=\"bibItems\">" ) !== false )
		{
			// most library innopac systems
			$table = "class=\"bibItems\">";
		} 
		elseif ( strpos( $html, "BGCOLOR=#DDEEFF>" ) !== false )
		{
			// old innreach system
			$table = "BGCOLOR=#DDEEFF>";
		} 
		elseif ( strpos( $html, "centralHolding" ) !== false )
		{
			// newer innreach system
			$table = "centralHolding";
		}
		elseif ( strpos( $html, "centralDetailHoldings" ) !== false )
		{
			// newer innreach system
			$table = "centralDetailHoldings";			
		}
		elseif ( strpos( $html, "class=\"bibOrder" ) !== false )
		{
			// this is just a note saying the item has been ordered
			
			$table = "class=\"bibOrder";
			$bolOrderNote = true;
		} 
		elseif ( strpos( $html, "class=\"bibDetail" ) !== false )
		{
			$table = "class=\"bibDetail";
		} 
		else
		{
			return $holdings_array;
		}
		
		// narrow the page initially to the holdings table

		$html = Parser::removeLeft( $html, $table );
		$html = Parser::removeRight( $html, "</table>" );
		
		// we'll use the table row as the delimiter of each holding

		while ( strstr( $html, "<tr" ) )
		{
			$item_array = array();
			$item_data = "";
			
			// remove everything left of the table row, dump the row content into a
			// local variable, while removing it from the master variable so
			// our loop above continues to cycle thru the results

			$html = Parser::removeLeft( $html, "<tr" );
			$item_data = "<tr" . Parser::removeRight( $html, "</tr>" );
			$html = Parser::removeLeft( $html, "</tr>" );			
			
			// make sure this isn't the header row

			if ( strpos( $item_data, "<th" ) === false )
			{
				// extract any url in item, especially for InnReach

				$strUrl = null;
				$arrUrl = array();
				
				if ( preg_match( "/<a href=\"([^\"]{1,})\">/", $item_data, $arrUrl ) )
				{
					$strUrl = $arrUrl[1];
				}
				
				// replace the item record marc field comments with place holders
				// so we can grab them latter after removing the html tags
				
				$item_data = preg_replace('/<\!-- field (.{1}) -->/', "+~\$1~", $item_data);
				
				// strip out tags and non-breaking spaces 

				$item_data = preg_replace('/<[^>]*>/', '', $item_data );
				$item_data = str_replace( "&nbsp;", "", $item_data );
				
				// normalize spaces
				
				while ( strstr( $item_data, "  " ) )
				{
					$item_data = str_replace( "  ", " ", $item_data );
				}
				
				$item_data = trim( $item_data );
				
				// now split the remaining data out into an array
				
				$item_array = array();
				
				// the display included the item record field comments, in which
				// case we will use these over a general column-based approach
				// since it is more precise; this should be the case on all local systems
									
				if ( strstr($item_data, "+~"))
				{
					$bolFieldNotes = true;
					$item_arrayTemp = explode( "+~", $item_data );
					
					foreach ($item_arrayTemp as $item_dataTemp)
					{
						if ( strstr($item_dataTemp, "~") )
						{
							$item_arrayField = explode("~", $item_dataTemp);
							$strFieldKey = trim($item_arrayField[0]);
							$strFieldValue = trim($item_arrayField[1]);
							$item_array[$strFieldKey] = $strFieldValue;
						}
					}					
				}
				else
				{
					$item_array = explode( "\n", $item_data );

					// add url back into the array
				
					if ( $strUrl != null )
					{
						array_push( $item_array, $strUrl );
					}				
				}
				
				// final clean-up, assignment
				
				$item = new Search\Item();
				
				if ( $bolFieldNotes == true )
				{
					foreach ( $item_array as $key => $data )
					{
						switch ( $key )
						{
							case "1" :
								$item->location = $data;
								break;
							
							case "C" :
								$item->callnumber = $data;
								break;
								
							case "#":
							case "v" :
								$item->volume .= $data;
								break;
																
							case "%" :
								$item->status = $data;
								break;
						}
					}
				}
				else
				{
					for ( $x = 0 ; $x < count( $item_array ) ; $x ++ )
					{
						$data = trim( $item_array[$x] );
						
						if ( $bolOrderNote == true )
						{
							$item->onOrder = true;
							$item->note = $data;
						}
						elseif ( $this->innreach == true )
						{
							switch ( $x )
							{
								case 0 :
									$item->institution = $data;
									break;
								
								case 1 :
									$item->location = $data;
									break;
								
								case 2 :
									
									// note for accessing item online
									
									$item->note = $data;
									break;
								
								case 3 :
									
									// this is a link if the second position had an
									// online access note
									
									if ( $item->note != "" )
									{
										$item->link = $data;
									}
									else
									{
										$item->callnumber = $data;
									}
									
									break;
								
								case 4 :
									$item->status = $data;
									break;
							}
						}
						else
						{
							switch ( $x )
							{
								case 0 :
									$item->location = $data;
									break;
								
								case 2 :
									$item->callnumber = $data;
									break;
								
								case 3 :
									$item->status = $data;
									break;
							}
						}
					}
				}
				
				$match_array = array(); // for regex matching
				
				// check status for due date
				
				if ( preg_match("/([0-9]{2})-([0-9]{2})-([0-9]{2})/", $item->status, $match_array) )
				{
					$objDate = new \DateTime($match_array[1] . "/" . $match_array[2] . "/" . $match_array[3]);
					$item->dateAvailable = $objDate;
				}
				
				// check status for holds, should we make this configurable?
				
				if ( preg_match("/([0-9]{0,}) HOLD/", $item->status, $match_array) )
				{
					$item->holdQueueLength = $match_array[1];
				}				
				
				// add it to the list
				
				array_push( $holdings_array, $item );
			}
		}
		
		return $holdings_array;
	}

	/**
	 * Extracts data from the summary holdings holdings table
	 *
	 * @param string $html		html response from catalog
	 * @return array 				array of Holdings objects
	 */	
	
	protected function extractHoldingsRecords($html)
	{
		$final_array = array();
		
		// check to see if there are summary (journal) holdings as well as item records
		// if not, just return out
		
		if ( strpos( $html, "class=\"bibHoldings\">" ) === false )
		{
			return $final_array;
		}	
		
		$html = Parser::removeLeft( $html, "class=\"bibHoldings" );
		$html = Parser::removeRight( $html, "</table>" );
		
		$holdings_blocks = explode("holdingsDivider", $html);
		
		// first and last element are unused
		
		array_pop($holdings_blocks);
		array_shift($holdings_blocks);
		
		foreach ( $holdings_blocks as $block )
		{
			$holding = new Search\Holding();
			
			while ( strstr( $block, "<tr" ) )
			{
				// get just this row
				
				$block = Parser::removeLeft( $block, "<tr" );
				$item_data = "<tr" . Parser::removeRight( $block, "</tr>" );
				$block = Parser::removeLeft( $block, "</tr>" );
				
				// knock out line breaks and spaces
				
				$item_data = str_replace("\n", " ", $item_data);
				$item_data = str_replace("&nbsp;", " ", $item_data);
				
				// put a colon and period between the label and display
				
				$item_data = str_replace("class=\"bibHoldingsEntry\">", ">|| ", $item_data);
				$item_data = strip_tags($item_data);
				
				$item_data = trim($item_data);
				
				// last one is blank
				
				if ( $item_data == "" )
				{
					continue;
				}
				
				$pair = explode("||", $item_data);
								
				$id = $pair[0];
				$value = $pair[1];
				
				$id = str_replace(":", "", $id);
				$id = trim($id);
				
				// empty descriptor
				
				if ( $id == "" && strstr($value, ":") )
				{
					$parts = explode(":", $value);
					$id = array_shift($parts);
					$value = implode(":", $parts);
				}
				
				$holding->setProperty($id, $value);
			}
			
			array_push($final_array, $holding);
			
		}
		
		return $final_array;
	}

	/**
	 * Extracts data from the ERM table
	 *
	 * @param string $html		html response from catalog
	 * @return array 				array of Electronic Resource objects
	 */		
	
	protected function extractERMRecords($html)
	{
		$final_array = array();
		
		// check to see if there are ERM records
		
		if ( strpos( $html, "class=\"bibResource\">" ) === false )
		{
			return $final_array;
		}	
		
		// narrow to the erm table
		
		$html = Parser::removeLeft( $html, "class=\"bibResource\"" );
		$html = Parser::removeRight( $html, "</table>" );
				
		// we'll use the table row as the delimiter of each holding
		
		while ( strstr( $html, "<tr" ) )
		{
			$record = new Search\ElectronicResource();

			// remove everything left of the table row, dump the row content into a
			// local variable, while removing it from the master variable so
			// our loop above continues to cycle thru the results

			$html = Parser::removeLeft( $html, "<tr" );
			$strERM = "<tr" . Parser::removeRight( $html, "</tr>" );
			$html = Parser::removeLeft( $html, "</tr>" );
			
			$x = 0;
			
			while ( strstr( $strERM, "<td  class=\"bibResourceEntry\">" ) )
			{
				$strERM = Parser::removeLeft( $strERM, "<td  class=\"bibResourceEntry\">" );
				$data = Parser::removeRight( $strERM, "</td>" );
				$strERM = Parser::removeLeft( $strERM, "</td>" );
				
				// look for the link
				
				$matches = array();
				$url = "";
				
				if ( preg_match('/<a href=\"([^"]*)">(.*)<\/a>/', $data, $matches) )
				{
					$data = $matches[2];
					$url = $matches[1];
				}

				// clean-up
				
				$data = str_replace('&nbsp;', '', $data);
				$data = preg_replace('/<[^>]*>/', '', $data);
				$data = trim($data);
				
				if ( $x == 0 )
				{
					$record->database = $data;
					$record->link = $url;
				}
				elseif ( $x == 1 )
				{
					$record->coverage = $data;
				}
				elseif ( $x == 2 && $url != "")
				{
					$record->package = $this->server . $url;
				}
				
				$x++;
			}
			
			array_push($final_array,$record);
		}
		
		return $final_array;
	}	
	
	/**
	 * Extracts the MARC data from the HTML response and converts it to MARC-XML
	 *
	 * @param string $marc	marc data as string
	 * @return DOMDocument		marc-xml document
	 */
	
	protected function extractMarc($response)
	{
		$xml = Parser::convertToDOMDocument("<record xmlns=\"http://www.loc.gov/MARC21/slim\" />");
		
		$marc = ""; // marc data as text
		$arrTags = array(); // array to hold each MARC tag

		if ( ! stristr($response, "<pre>") )
		{
			// didn't find a record
			
			return $xml;
		}	
		
		// parse out MARC data

		$marc = Parser::removeLeft( $response, "<pre>" );
		$marc = Parser::removeRight( $marc, "</pre>" );
		
		// remove break-tabs for easier parsing

		$marc = str_replace( " \n       ", " ", $marc );
		$marc = str_replace( "\n       ", " ", $marc );
		
		$marc = trim( $marc );
		
		// assign the marc values to the array based on Unix LF as delimiter

		$arrTags = explode( "\n", $marc );
		
		foreach ( $arrTags as $strTag )
		{
			// assign tag # and identifiers

			$strTagNumber = substr( $strTag, 0, 3 );
			$strId1 = substr( $strTag, 4, 1 );
			$strId2 = substr( $strTag, 5, 1 );
			
			// assign data and clean it up

			$data = substr( $strTag, 7 );
			
			// only convert all data to utf8 if told to do so, 
			// but always do it to the leader, since it has mangled chars
			
			if ( $this->convert_to_utf8 == true || $strTagNumber == "LEA")
			{
				if ( function_exists("mb_convert_encoding") )
				{
					$data = mb_convert_encoding( $data, "UTF-8" );
				}
				else
				{
					$data = utf8_encode( $data );
				}
			}
				
			$data = Parser::escapeXml( $data );
			$data = trim( $data );
			
			if ( $strTagNumber == "LEA" )
			{
				// leader
				
				$objLeader = $xml->createElementNS( $this->marc_ns, "leader", $data );
				$xml->documentElement->appendChild( $objLeader );
			} 
			elseif ( $strTagNumber == "REC" )
			{
				// Pseudo-MARC "REC" data field to store the INNOPAC
				// bibliographic record number in subfield a.

				$objRecNum = $xml->createElementNS( $this->marc_ns, "datafield" );
				$objRecNum->setAttribute( "tag", "REC" );
				$objRecNum->setAttribute( "ind1", ' ' );
				$objRecNum->setAttribute( "ind2", ' ' );
				
				$objRecNumSub = $xml->createElementNS( $this->marc_ns, "subfield", strtolower( $data ) );
				$objRecNumSub->setAttribute( "code", 'a' );
				$objRecNum->appendChild( $objRecNumSub );
				$xml->documentElement->appendChild( $objRecNum );
			}
			elseif ( ( int ) $strTagNumber <= 8 )
			{
				// control fields

				$objControlField = $xml->createElementNS( $this->marc_ns, "controlfield", $data );
				$objControlField->setAttribute( "tag", $strTagNumber );
				$xml->documentElement->appendChild( $objControlField );
			} 
			else
			{
				// data fields

				$objDataField = $xml->createElementNS( $this->marc_ns, "datafield" );
				$objDataField->setAttribute( "tag", $strTagNumber );
				$objDataField->setAttribute( "ind1", $strId1 );
				$objDataField->setAttribute( "ind2", $strId2 );
				
				// if first character is not a pipe symbol, then this is the default |a subfield
				// so make that explicit for the array

				if ( substr( $data, 0, 1 ) != "|" )
				{
					$data = "|a " . $data;
				}
				
				// split the subfield data on the pipe and add them in using the first
				// character after the delimiter as the subfield code
				
				$arrSubFields = explode( "|", $data );
				
				foreach ( $arrSubFields as $strSubField )
				{
					if ( $strSubField != "" )
					{
						$code = substr( $strSubField, 0, 1 );
						$data = trim( substr( $strSubField, 1 ) );
						
						// check for a url, in which case we need to ensure there are no spaces;
						// which can happen on the wrap of the data in the marc display
						
						if ( strlen($data) > 4 )
						{
							if ( substr($data,0,4) == "http" )
							{
								$data = str_replace(" ", "", $data);
							}
						}
							
						$objSubField = $xml->createElementNS( $this->marc_ns, "subfield", $data );
						$objSubField->setAttribute( "code", $code );
						$objDataField->appendChild( $objSubField );
					}
				}
				
				$xml->documentElement->appendChild( $objDataField );
			}
		}
		
		return $xml;
	}
	
	
	### PROPERTIES ###
	
	
	public function getURL()
	{
		return $this->url;
	}
}
