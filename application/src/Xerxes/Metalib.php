<?php

namespace Xerxes;

use Xerxes\Utility\Parser,
	Zend\Http\Client;

/**
 * Metalib X-Server Client
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @version 
 * @package Xerxes
 */

class Metalib
{ 
	private $server = ""; // metalib server address
	private $username = "";	// this application's username
	private $password = "";	// this application's password	

	private $session = ""; // session id
	private $session_expires = 0; // expiry date for metalib session

	private $url = ""; // url request to server
	private $xml = null; // DOMDocument xml
	private $warning = null; // warning xml
	private $timeout = 15; // timeout	
	private $finished = null; // flag indicating metalib is done searching
	private $return_quick = false; // return quick

	private $client; // http client
	
	/**
	 * Create Metalib Client
	 * 
	 * @param string $server	the Metalib address url
	 * @param string $username	this application's username 
	 * @param string $password	this application's password
	 * @param Client $client	[optional] subclass of Zend\Client
	 */
	
	public function __construct( $server, $username, $password, Client $client = null )
	{						
		$this->setServer($server);
		$this->username = $username;
		$this->password = $password;
		
		if ( $client != null )
		{
			$this->client = $client;
		}
		else
		{
			$this->client = new Client();
		}
		
		$this->ensureSession();
	}
	
	public function __sleep()
	{
		return array("server", "username", "password", "session", "session_expires", "client");
	}
	
	public function __wakeup()
	{
		$this->ensureSession();
	}
	
	/**
	 * Create a new session if none exists or current one is expired
	 */
	
	protected function ensureSession()
	{
		// grab a new session if none exists or last one expired
		
		if ( $this->session == null || time() > $this->session_expires )
		{
			$this->session = $this->getSession();
			$this->session_expires = time() + 1200;
		}		
	}
	
	/**
	 * Acquire a new session id
	 * 
	 * @return string session id
	 */ 

	public function getSession() 
	{			
		$this->url = $this->server . "/X?op=login_request" .
			"&user_name=" . $this->username .
			"&user_password=" . $this->password;

		// get login_response from Metalib

		$this->xml = $this->getResponse($this->url, $this->timeout);
		
		// extract session ID
		
		$objSession = $this->xml->getElementsByTagName("session_id")->item(0);
		return $objSession->nodeValue;
	}
	
	/**
	 * Initiates metasearch request
	 *
	 * @param string $query			metalib formatted query 
	 * @param array|string $databases	selected databases, array if multiple
	 * @param bool $wait			[optional] whether to wait until results are availble (default false)
	 * @return mixed 			if wait = false, returns group number as string; else search progress as DOMDocument
	 */

	public function search( $query, $databases, $wait = false) 
	{
		$query = trim($query); // extra spaces will cause error
		
		$strWaitFlag = "N";			// wait flag
		$database_list = "";		// string list of databases
		
		if ( $wait == true )
		{
			$strWaitFlag = "Y";
		}
		
		// expects databases as an array, so catch here if only one supplied
					
		if ( ! is_array($databases) ) $databases = array($databases);

		foreach ( $databases as $database ) 
		{
			if ( $database != null )
			{
				$database_list .= "&find_base_001=" . trim($database);
			}
		}
		
		$this->url = $this->server . "/X?op=find_request" .
			"&find_request_command=" . urlencode($query) .
			$database_list . 
			"&session_id=" . $this->session .
			"&wait_flag=" . $strWaitFlag;
		
		// get find_response from Metalib
		
		$this->xml = $this->getResponse($this->url, $this->timeout);			
		
		if ( $wait == true)
		{
			// return search response
			return $this->xml;
		
		}
		else
		{
			// extract group id if this was the non-wait flag

			$objGroup = $this->xml->getElementsByTagName("group_number")->item(0);
			return $objGroup->nodeValue;
		}
	}
	
	/**
	 * Check status of initiated search
	 *
	 * @param string $group_number		group id
	 * @return DOMDocument 			status response
	 */

	public function getSearchStatus( $group_number ) 
	{
		$this->url = $this->server . "/X?op=find_group_info_request" .
			"&group_number=" . $group_number .
			"&session_id=" . $this->session;

		// find_group_info_response from Metalib
		
		$this->xml = $this->getResponse($this->url, $this->timeout);

		// set finished flag
		
		$this->finished = $this->checkFinished($this->xml->saveXML());
		
		return $this->xml; 
	}

	/**
	 * Creates a merged set of top results from individual result sets
	 * 
	 * @param string $group_number		group id
	 * @param string $sort_primary		primary sort criteria: rank, title, author, year, database
	 * @param string $sort_secondary	secondary sort criteria: rank, title, author, year, database
	 * @return DOMDocument 			merge response document
	 */
	
	public function merge( $group_number, $sort_primary = null, $sort_secondary = null )
	{	
		$this->url = $this->server . "/X?op=merge_sort_request" .
			"&group_number=" . $group_number .
			"&action=merge" .
			"&primary_sort_key=" . $sort_primary .
			"&secondary_sort_key=" . $sort_secondary .
			"&session_id=" . $this->session;

      
		// get merge_response from Metalib

		$this->xml = $this->getResponse($this->url, $this->timeout);
		
		return $this->xml;
	}
	
	/**
	 * Returns facets and clusters for the merged result set
	 *
	 * @param string $resultset_number		result set number
	 * @param string $type				valed values include:
	 *	-  all: both cluster and facet results: Topic Cluster, Facet Year, Facet Author, Facet Journal, Facet Database, Facet Subject
		- facet: all facet results: Facet Year, Facet Author, Facet Journal, Facet Database, Facet Subject
		- cluster: Cluster results
		- year: Facet Year results
		- author: Facet Author results
		- journal: Facet Journal results
		- database: Facet Database results
		- subject: Facet Subject results
	 * @param string $id				calling application id
	 * @return unknown
	 */
	
	public function getFacets($resultset_number, $type = "all", $id = "calstate.edu:xerxes")
	{
		$this->url = $this->server . "/X?op=retrieve_cluster_facet_request" .
		
			"&set_number=" . $resultset_number .
			"&type=" . $type .
			"&calling_application=" . $id .
			"&session_id=" . $this->session;

		// get merge_response from Metalib

		$this->xml = $this->getResponse($this->url, $this->timeout);
		
		return $this->xml;
	}

	/**
	* Sorts a merged result set
	* 
	* @param string $group_number		group id
	* @param string $sort_primary		[optional] primary sort criteria: rank, title, author, year, database
	* @param string $sort_secondary		[optional] secondary sort criteria: rank, title, author, year, database
	* @return DOMDocument 			sort response document
	*/

	public function sort( $group_number, $sort_primary, $sort_secondary = null )
	{	
		$this->url = $this->server . "/X?op=merge_sort_request" .
			"&group_number=" . $group_number .
			"&action=sort_only" .
			"&primary_sort_key=" . $sort_primary .
			"&secondary_sort_key=" . $sort_secondary .
			"&session_id=" . $this->session;

		// get merge_response from Metalib

		$this->xml = $this->getResponse($this->url, $this->timeout);

		return $this->xml;
	}

	/**
	* Retrieves results, either as a range or individually
	* 
	* @param string $recordset_id		record set id
	* @param int $start			first record in range, or individual record
	* @param int $max			maximum number of records to retrieve
	* @param int $total			[optional] total number of records in result set
	* @param string $view			[optional] fullness of response: brief, full, customize
	* @param array $fields			[optional] marc fields to return in customize response
	* @param array $docs			[optional] list of document id's from facet
	* @return DOMDocument 			marc-xml records
	*/

	public function retrieve( $recordset_id, $start, $max, $total = null, $view = null, array $fields = array(), array $docs = array() ) 
	{
		// type check
		
		if (!is_int($start)) throw new \InvalidArgumentException("param 2 needs to be of type int");
		if (!is_int($max)) throw new \InvalidArgumentException("param 3 needs to be of type int");
		if ($total != null && !is_int($total)) throw new \InvalidArgumentException("param 4 needs to be of type int");
		
		// if document id's supplied, use that as total
		
		if ( count($docs) > 0 )
		{
			$total = count($docs);
		}
		
		$strFields = ""; // specified fields for customize view
		$stop = null; // end of range
	
		// fields to retrieve
		
		if ( $view == "customize" )
		{
			foreach( $fields as $strField) 
			{
				$strFields .= "&field=" . urlencode($strField);
			}
		}
		
		// set end point
		
		$stop = $start + ( $max - 1 );
		
		// if end value of group of 10 exceeds total number of hits,
		// take total number of hits as end value 
		
		if ( $stop > $total ) 
		{
			$stop = $total;
		}
		
		if ( count($docs) == 0 )
		{
			// strings for converting integers to Metalib IDs which have 0000s
			
			$strRange = "";
			$strStart = "";
			$strStop = "";
			
			// convert integers to Metalib record IDs by padding with 0's
			
			$strStart = str_pad($start, 9, "0", STR_PAD_LEFT);
			$strStop = str_pad($stop, 9, "0", STR_PAD_LEFT);
			
			// if request is for individual record, otherwise for range
			
			if ( $max == 1 ) 
			{
				$strRange = $strStart;
			} 
			else
			{
				$strRange = $strStart . "-" . $strStop;
			}
			
			$this->url = $this->server . "/X?op=present_request" .
				"&set_number=" . $recordset_id . 
				"&set_entry=" . $strRange .
				$strFields .
				"&format=marc" .
				"&view=" . $view .
				"&session_id=" . $this->session;
		}
		else 
		{
			// get documents from the list up to the number specified
			
			$strDocs = "";
			
			for ( $x = $start - 1; $x < $stop && $x < $total; $x++ )
			{
				if ( $x == $start - 1 )
				{
					$strDocs = $docs[$x];
				}
				else
				{
					$strDocs .= urlencode("," . $docs[$x]);
				}
			}
			
			$this->url = $this->server . "/X?op=present_request" .
				"&set_number=1" . 
				"&doc_number=" . $strDocs .
				$strFields .
				"&format=marc" .
				"&view=" . $view .
				"&session_id=" . $this->session;
		}

		// get present_response from Metalib
    
		$this->xml = $this->getResponse($this->url, $this->timeout);
		
		return $this->xml;
	}

	/**
	 * Retrieves all categories and subcategories from the Metalib KnowledgeBase
	 *
	 * @return DOMDocument		metalib category xml document	
	 */
	
	public function getCategories( $institute = null, $portal = null, $language = null ) 
	{
		$this->url = $this->server . "/X?op=retrieve_categories_request";
		
		if ( $institute != null && $portal != null && $language != null )
		{
			$this->url .= "&institute=$institute&portal=$portal&language=$language";
		}
		else 
		{
			throw new \InvalidArgumentException("you must specify institute, portal, and language");
		}

		$this->url .= "&session_id=" . $this->session;
		
		
		// get retrieve_resource_categories_response from Metalib

		$this->xml = $this->getResponse($this->url);

		return $this->xml;
	}
	
	/**
	 * Retrieves all the databases in a Metalib subcategory
	 *
	 * @param string $category_id		category id number, taken from categories xml
	 * @param string $full			whether to incldue full record, false by default
	 * @return DOMDocument			metalib category xml with records in marc-xml
	 */
	
	public function getDatabasesSubCategory( $category_id, $full = false ) 
	{
		// set string flag for inclusion of full marc record
		$strFull = "N";
				
		if ( $full == true ) $strFull = "Y";

		$this->url = $this->server . "/X?op=retrieve_resources_by_category_request" .
			"&category_id=" . $category_id .
			"&source_full_info_flag=" . $strFull .
			"&session_id=" . $this->session;

		// get retrieve_resource_categories_response from Metalib

		$this->xml = $this->getResponse($this->url);
		
		return $this->xml; 
	}
	
	/**
	 * Retrieve Metalib types
	 *
	 * @param string $institute		Metalib institute code
	 * @return DOMDocument			Metalib type xml document
	 */
	
	public function getTypes( $institute ) 
	{
		$this->url = $this->server . "/X?op=retrieve_resource_types_request" .
			"&institute=" . $institute .
			"&session_id=" . $this->session;
		
		// get retrieve_resource_types_response from Metalib
		
		$this->xml = $this->getResponse($this->url);
		
		return $this->xml; 
	}
	
	/**
	 * Retrieve all databases from the Metalib KB
	 *
	 * @param string $institute		Metalib institute code
	 * @param bool $full			whether to include full record, true by default
	 * @param bool $chunk			whether we should chunk the response for a really large KB
	 * @return DOMDocument			marc-xml collection
	 */
	
	public function getAllDatabases( $institute, $full = true, $chunk = false )
	{
		// master xml document
		
		$final_xml = Parser::convertToDOMDocument("<collection />");
		$final_xml->documentElement->setAttribute("metalib_version", $this->getVersion());
		
		$institute = urlencode(trim($institute));
		
		// set fullness flag
		
		$strFull = "Y";
		
		if ($full == false) 
		{
			$strFull = "N";
		}
		
		if ( $chunk == true ) // get them in batches
		{
			$this->xml = Parser::convertToDOMDocument("<collection />");
			
			// get the list without the full record
			
			$this->url = $this->server . "/X?op=source_locate_request" .
				"&locate_command=WIN=($institute)" .
				"&source_full_info_flag=N" . 
				"&session_id=" . $this->session;
			
			$doc = $this->getResponse($this->url);
			
			// extract the database ids and fetch the full record for each individually
			
			foreach ( $doc->getElementsByTagName("source_001") as $database )
			{
				$this->url = $this->server . "/X?op=source_locate_request" .
					"&locate_command=IDN=" . $database->nodeValue .
					"&source_full_info_flag=" . $strFull . 
					"&session_id=" . $this->session;
				
				$database = $this->getResponse($this->url);
				
				$import = $this->xml->importNode($database->documentElement, true);
				$this->xml->documentElement->appendChild($import);
			}
			
			$this->xml->save("test.xml");
		}
		else // get them in one call
		{
			$this->url = $this->server . "/X?op=source_locate_request" .
				"&locate_command=WIN=($institute)" .
				"&source_full_info_flag=" . $strFull .
				"&session_id=" . $this->session;				
			
			$this->xml = $this->getResponse($this->url);
		}
		
		// extract marc records
		
		$xpath = new \DOMXPath($this->xml);
		$xpath->registerNamespace("marc", "http://www.loc.gov/MARC21/slim");
		$records = $xpath->query("//marc:record");
		
		// import and append marc records to master document
		
		foreach ($records as $record)
		{
			$import_node = $final_xml->importNode($record, true);
			$final_xml->documentElement->appendChild($import_node);
		}
		
		$this->xml = $final_xml;
		
		return $final_xml;
	}
	
	/**
	 * Status information of the Metalib server
	 *
	 * @return DOMDocument		status information
	 */
	
	public function getMetalibInfo()
	{
		$this->url = $this->server . "/X?op=retrieve_metalib_info_request" .
			"&view=full" .
			"&session_id=" . $this->session;
		
		// load into DOM
		
		$this->xml = $this->getResponse($this->url);
		
		return $this->xml; 
	}
	
	/**
	* Check if metalib is done searching
	*
	* Parses search status response from Metalib for existence
	* of terms that would indicate Metalib is still searching;
	* if not present, return status of DONE, so hits page stops auto-refreshing.
	* 
	* @param string $status 		xml status document
	* @return bool 				true if finished, false if not
	*/ 

	private function checkFinished( $status )
	{
		$final = ""; // response if found

		if ( strpos( $status,"START") !== false ) {
		} else if ( strpos( $status,"FIND") !== false ) {
		} else if ( strpos( $status,"FORK") !== false ) {
		} else if ( strpos( $status,"FETCH") !== false ) {
		} else if ( strpos( $status,"DONE1") !== false && $this->return_quick == false ) {
		} else if ( strpos( $status,"DONE2") !== false ) {
		} else if ( strpos( $status,"DONE3") !== false ) {
		} else {
			$final = "Done";
		}

		if ( $final == "" )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Fetch the data from Metalib and check for errors
	 *
	 * @param string $url		url of the request
	 */
	
	private function getResponse( $url, $timeout = null, $retry = 0)
	{
		// fetch the data
		
		$this->client->setUri($url);
		$this->client->setConfig(array('timeout' => $timeout));
		$response = $this->client->send();
		
		if ( $response->isClientError() || $response->getBody() == "")
		{
			throw new \Exception( "Cannot process search at this time." );
		}
		
		// load into xml
		
		$doc = Parser::convertToDOMDocument($response->getBody());
		
		// no response?
		
		if ( $doc->documentElement == null )
		{
			throw new \Exception("cannot connect to metalib server");
		}
		
		// error in response
		
		if ( $doc->getElementsByTagName("error_code") != null )
		{
			// for easier handling
			
			$xml = simplexml_import_dom($doc->documentElement);
			
			foreach ( $xml->xpath("//global_error|//local_error") as $error )
			{
				$error_code = (int) $error->error_code;
				$error_text = (string) $error->error_text;
				
				// now examine them
				
				// metalib session has timed out!
				
				if ( $error_code == 0151 ) 
				{
					// this particular metalib error message is confusing to end-users, so make it more clear
					
					$error_text = "The Metalib session has expired";
					
					// also try to re-up the session in the event metalib was restarted or something
					
					if ( $retry == 0 ) // but not more than once
					{
						$this->session = $this->getSession();
						return $this->getResponse( $url, $timeout, 1);
					}
				}
				
				// these are just warnings
				
				if ( $error_code == 2114 // x-server license will expire in 10 days
				  || $error_code == 6039 // primary and secondary sort keys are identical
				  || $error_code == 6033 // results have been retrieved only from "search & link" resources
				  || $error_code == 6034 // results have been retrieved only from "Link to" resources
				  || $error_code == 6023 // category has no resources
				  || $error_code == 134 // The merged set has more records than the merge limit
				  || $error_code == 6022 // all the resources are not authorized
				  ) 
				{
					trigger_error("Metalib warning ($error_code): $error_text", E_USER_WARNING);	
				}
				else
				{
					throw new \Exception("Metalib exception ($error_code): $error_text");
				}
			}
		}
		
		return $doc;
	}

	/**
	 * Get full URL sent to X-Server
	 *
	 * @return string
	 */
	
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Get string version of current XML response
	 *
	 * @return string
	 */
	 
	public function getXml()
	{
		return $this->xml->saveXML();
	}
	
	/**
	 * Set address of Metalib server
	 *
	 * @param string
	 */

	public function setServer($value)
	{
		$this->server = rtrim($value, '/');
	}
	
	/**
	 * Set Metalib username for this application
	 *
	 * @param string $strvalue username
	 */
	
	public function setUsername($value)
	{
		$this->username = $value;
	}
	
	/**
	 * Set Metalib password for this application
	 *
	 * @param string $strvalue	password
	 */
	
	public function setPassword($value)
	{
		$this->password = $value;
	}
	
	/**
	 * Get current Metalib session id
	 *
	 * @return string
	 */
	
	public function getSessionId()
	{
		return $this->session;
	}

	/**
	 * Metalib version
	 *
	 * @return string
	 */
	
	public function getVersion()
	{
		$version = "";
		$version_object = null;
		
		// see if some xml response has already been fetched
		// in which case it will have the version, otherwise just
		// do a quick bump on the xsever and get it from that 
		
		if ( $this->xml->documentElement != null )
		{
			$version_object = $this->xml->getElementsByTagName("x_server_response")->item(0);
		}
		else
		{
			$this->url = $this->server . "/X";
			$this->xml = $this->getResponse($this->url, $this->timeout);
		
			$version_object = $this->xml->getElementsByTagName("x_server_response")->item(0);
		}
		
		$version = $version_object->getAttribute("metalib_version");
		
		// extract session ID
		
		if ( $version != null)
		{
			return $version;
		}
		else
		{
			throw new \Exception("Could not extract version");
		}
	}
	
	/**
	 * Whether group is finished searching
	 * 
	 * @param string $group		group id
	 * @return bool				true if finished, false if not 
	 */
	
	public function isFinished($group)
	{
		if ( $this->finished == null ) // we haven't checked
		{
			$this->getSearchStatus($group);
		}
		
		return $this->finished;
	}
	
	/**
	 * Override current Metalib search status flag
	 *
	 * useful for prematurely ending search
	 *
	 * @param bool $value		true if finished, false if not
	 */
	
	public function setFinished($value)	
	{
		if ( is_bool($value) )
		{
			$this->finished = $value;
		}
		else
		{
			throw new \InvalidArgumentException("param 1 must be of type bool");
		}
	}
}