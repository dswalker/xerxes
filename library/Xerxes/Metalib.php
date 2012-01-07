<?php

namespace Xerxes;

use Zend\Http\Client;

/**
 * Search and retrieve results from Metalib X-Server
 * Accepts queries in Metalib search format and returns results in MARC-XML
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

	private $server = "";		// metalib server address
	private $url = "";			// url request to server
	private $xml = null;		// DOMDocument xml
	private $warning = null;	// warning xml
	private $timeout = 15;		// timeout

	private $username = "";		// this application's username
	private $password = "";		// this application's password	
	private $session = "";		// session id
	private $finished = false;	// flag indicating metalib is done searching
	private $return_quick = false; // return quick

	private $client; // http client
	
	/**
	 * Create a new Metalib access object
	 * 
	 * @param string $strServer		the Metalib address url
	 * @param string $strUsername	this application's username 
	 * @param string $strPassword	this application's password
	 * @param string $strSession	[optional] current metalib session id
	 */
	
	public function __construct( $strServer, $strUsername, $strPassword, $strSession = null, Client $client = null )
	{						
		$this->setServer($strServer);
		$this->username = $strUsername;
		$this->password = $strPassword;
		
		if ( $client != null )
		{
			$this->client = $client;
		}
		else
		{
			$this->client = new Client();
		}		

		if ( $strSession != null )
		{
			$this->session = $strSession;
		}
		else
		{
			$this->session = $this->session();
		}
	}
	
	/**
	 * Acquire a new session id
	 * 
	 * @return string session id
	 */ 

	public function session() 
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
	 * @param string $strQuery		metalib formatted query 
	 * @param mixed $arrDatabases	[array if multiple or string for single] selected databases
	 * @param bool $bolWait		    [optional] whether to wait until results are availble (default false)
	 * @return mixed 				if wait = false, returns group number as string; else search progress as DOMDocument
	 */

	public function search( $strQuery, $arrDatabases, $bolWait = false) 
	{
		$strQuery = trim($strQuery); // extra spaces will cause error
		
		$strWaitFlag = "N";			// wait flag
		$strDatabaseList = "";		// string list of databases
		
		if ( $bolWait == true )
		{
			$strWaitFlag = "Y";
		}
		
		// expects databases as an array, so catch here if only one supplied
					
		if ( ! is_array($arrDatabases) ) $arrDatabases = array($arrDatabases);

		foreach($arrDatabases as $strDatabase) 
		{
			if ( $strDatabase != null )
			{
				$strDatabaseList .= "&find_base_001=" . trim($strDatabase);
			}
		}
		
		$this->url = $this->server . "/X?op=find_request" .
			"&find_request_command=" . urlencode($strQuery) .
			$strDatabaseList . 
			"&session_id=" . $this->session .
			"&wait_flag=" . $strWaitFlag;

		// get find_response from Metalib

		$this->xml = $this->getResponse($this->url, $this->timeout);			
		
		if ( $bolWait == true)
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
	 * @param string $strGroupNumber	group id
	 * @return DOMDocument 				status response
	 */

	public function searchStatus( $strGroupNumber ) 
	{
		$this->url = $this->server . "/X?op=find_group_info_request" .
			"&group_number=" . $strGroupNumber .
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
	 * @param string $strGroupNumber		group id
	 * @param string $strPrimarySort		primary sort criteria: rank, title, author, year, database
	 * @param string $strSecondarySort		secondary sort criteria: rank, title, author, year, database
	 * @return DOMDocument 					merge response document
	 */
	
	public function merge( $strGroupNumber, $strPrimarySort = null, $strSecondarySort = null )
	{	
		$this->url = $this->server . "/X?op=merge_sort_request" .
			"&group_number=" . $strGroupNumber .
			"&action=merge" .
			"&primary_sort_key=" . $strPrimarySort .
			"&secondary_sort_key=" . $strSecondarySort .
			"&session_id=" . $this->session;

      
		// get merge_response from Metalib

		$this->xml = $this->getResponse($this->url, $this->timeout);
		
		return $this->xml;
	}
	
	/**
	 * Returns facets and clusters for the merged result set
	 *
	 * @param string $strResultSet			result set number
	 * @param string $strType				valed values include:
	 *     	-  all: both cluster and facet results: Topic Cluster, Facet Year, Facet Author, Facet Journal, Facet Database, Facet Subject
		    - facet: all facet results: Facet Year, Facet Author, Facet Journal, Facet Database, Facet Subject
		    - cluster: Cluster results
		    - year: Facet Year results
		    - author: Facet Author results
		    - journal: Facet Journal results
		    - database: Facet Database results
		    - subject: Facet Subject results
	 * @param string $strID					calling application id
	 * @return unknown
	 */
	
	public function facets($strResultSet, $strType = "all", $strID)
	{
		$this->url = $this->server . "/X?op=retrieve_cluster_facet_request" .
		
			"&set_number=" . $strResultSet .
			"&type=" . $strType .
			"&calling_application=" . $strID .
			"&session_id=" . $this->session;

		// get merge_response from Metalib

		$this->xml = $this->getResponse($this->url, $this->timeout);
		
		return $this->xml;
	}

	/**
	* Sorts a merged result set
	* 
	* @param string $strGroupNumber		group id
	* @param string $strPrimarySort		[optional] primary sort criteria: rank, title, author, year, database
	* @param string $strSecondarySort	[optional] secondary sort criteria: rank, title, author, year, database
	* @return DOMDocument sort response document
	*/

	public function sort( $strGroupNumber, $strPrimarySort, $strSecondarySort = null )
	{	
		$this->url = $this->server . "/X?op=merge_sort_request" .
			"&group_number=" . $strGroupNumber .
			"&action=sort_only" .
			"&primary_sort_key=" . $strPrimarySort .
			"&secondary_sort_key=" . $strSecondarySort .
			"&session_id=" . $this->session;

		// get merge_response from Metalib

		$this->xml = $this->getResponse($this->url, $this->timeout);

		return $this->xml;
	}

	/**
	* Retrieves results, either as a range or individually
	* 
	* @param string $strRecSet	record set id
	* @param int $iStart		first record in range, or individual record
	* @param int $iMaximum		maximum number of records to retrieve
	* @param int $iTotal		[optional] total number of records in result set
	* @param string $strView	[optional] fullness of response: brief, full, customize
	* @param array $arrFields	[optional] marc fields to return in customize response
	* @param array $arrDocs		[optional] list of document id's from facet
	* @return DOMDocument marc-xml records
	*/

	public function retrieve( $strRecSet, $iStart, $iMaximum, $iTotal = null, $strView = null, $arrFields = null, $arrDocs = null ) 
	{
		// type check
		
		if (!is_int($iStart)) throw new \InvalidArgumentException("param 2 needs to be of type int");
		if (!is_int($iMaximum)) throw new \InvalidArgumentException("param 3 needs to be of type int");
		if ($iTotal != null && !is_int($iTotal)) throw new \InvalidArgumentException("param 4 needs to be of type int");			
		if ($arrFields != null && !is_array($arrFields)) throw new \InvalidArgumentException("param 6 needs to be of type array");
		if ($arrDocs != null && !is_array($arrDocs)) throw new \InvalidArgumentException("param 7 needs to be of type array");
		
		if ( $arrDocs != null )
		{
			$iTotal = count($arrDocs);
		}
		
		$strFields = "";			// specified fields for customize view
		$iStop = null;				// end of range		
	
		// fields to retrieve
		
		if ( $strView == "customize" )
		{
			foreach( $arrFields as $strField) 
			{
				$strFields .= "&field=" . urlencode($strField);
			}
		}
		
		// set end point
		
		$iStop = $iStart + ( $iMaximum - 1 );
	
		// if end value of group of 10 exceeds total number of hits,
		// take total number of hits as end value 

		if ( $iStop > $iTotal ) 
		{
			$iStop = $iTotal;
		}
		
		if ( $arrDocs == null )
		{
			// strings for converting integers to Metalib IDs which have 0000s
			
			$strRange = "";
			$strStart = "";
			$strStop = "";

			// convert integers to Metalib record IDs by padding with 0's
			
			$strStart = str_pad($iStart, 9, "0", STR_PAD_LEFT);
			$strStop = str_pad($iStop, 9, "0", STR_PAD_LEFT);
		
			// if request is for individual record, otherwise for range
			
			if ( $iMaximum == 1 ) 
			{
				$strRange = $strStart;
			} 
			else
			{
				$strRange = $strStart . "-" . $strStop;
			}

			$this->url = $this->server . "/X?op=present_request" .
				"&set_number=" . $strRecSet . 
				"&set_entry=" . $strRange .
				$strFields .
				"&format=marc" .
				"&view=" . $strView .
				"&session_id=" . $this->session;
		}
		else 
		{
			// get documents from the list up to the number specified
			
			$strDocs = "";
			
			for ( $x = $iStart - 1; $x < $iStop && $x < $iTotal; $x++ )
			{
				if ( $x == $iStart - 1 )
				{
					$strDocs = $arrDocs[$x];
				}
				else
				{
					$strDocs .= urlencode("," . $arrDocs[$x]);
				}
			}
			
			$this->url = $this->server . "/X?op=present_request" .
				"&set_number=1" . 
				"&doc_number=" . $strDocs .
				$strFields .
				"&format=marc" .
				"&view=" . $strView .
				"&session_id=" . $this->session;
		}

		// get present_response from Metalib
    
		$this->xml = $this->getResponse($this->url, $this->timeout);
		
		return $this->xml;
	}

	/**
	 * Retrieves all categories and subcategories from the Metalib KnowledgeBase
	 *
	 * @param string $strIpAddress		IP address associated with a Metalib portal
	 * @return DOMDocument				Metalib category xml document	
	 */
	
	public function categories( $institute = null, $portal = null, $language = null ) 
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
	 * @param string $strCategoryId		category id number, taken from categories xml
	 * @param string $bolFull			whether to incldue full record, false by default
	 * @return DOMDocument				Metalib category xml with records in marc-xml
	 */
	
	public function databasesSubCategory( $strCategoryId, $bolFull = false ) 
	{
		// set string flag for inclusion of full marc record
		$strFull = "N";
				
		if ( $bolFull == true ) $strFull = "Y";

		$this->url = $this->server . "/X?op=retrieve_resources_by_category_request" .
			"&category_id=" . $strCategoryId .
			"&source_full_info_flag=" . $strFull .
			"&session_id=" . $this->session;

		// get retrieve_resource_categories_response from Metalib

		$this->xml = $this->getResponse($this->url);
		
		return $this->xml; 
	}
	
	/**
	 * Retrieve Metalib types
	 *
	 * @param string $strInstitute		Metalib institute code
	 * @return DOMDocument				Metalib type xml document
	 */
	
	public function types( $strInstitute ) 
	{
		$this->url = $this->server . "/X?op=retrieve_resource_types_request" .
			"&institute=" . $strInstitute .
			"&session_id=" . $this->session;

		// get retrieve_resource_types_response from Metalib

		$this->xml = $this->getResponse($this->url);
		
		return $this->xml; 
	}
	
	/**
	 * Retrieve all databases from the Metalib database
	 *
	 * @param string $strInstitute		Metalib institute code
	 * @param bool $bolFull				whether to include full record, true by default
	 * @param bool $bolChunk			whether we should chunk the response for a really large KB
	 * @return DOMDocument				marc-xml collection
	 */
	
	public function allDatabases( $strInstitute, $bolFull = true, $bolChunk = false )
	{
		// master xml document
		
		$objFinalXml = new \DOMDocument();
		$objFinalXml->loadXML("<collection />");
		$objFinalXml->documentElement->setAttribute("metalib_version", $this->getVersion());
		
		$strInstitute = urlencode(trim($strInstitute));
		
		// set fullness flag

		$strFull = "Y";
		
		if ($bolFull == false) 
		{
			$strFull = "N";
		}
		
		if ( $bolChunk == true )
		{
			$this->xml = new \DOMDocument();
			$this->xml->loadXML("<collection />");
			
			// get the list without the full record
			
			$this->url = $this->server . "/X?op=source_locate_request" .
				"&locate_command=WIN=($strInstitute)" .
				"&source_full_info_flag=N" . 
				"&session_id=" . $this->session;

			$objXml = $this->getResponse($this->url);
			
			// extract the database ids and fetch the full record for each individually
			
			foreach ( $objXml->getElementsByTagName("source_001") as $database )
			{
				$this->url = $this->server . "/X?op=source_locate_request" .
					"&locate_command=IDN=" . $database->nodeValue .
					"&source_full_info_flag=" . $strFull . 
					"&session_id=" . $this->session;
				
				$objDatabase = $this->getResponse($this->url);
				
				$objImport = $this->xml->importNode($objDatabase->documentElement, true);
				$this->xml->documentElement->appendChild($objImport);
			}
			
			$this->xml->save("test.xml");
		}
		else 
		{
			// load into DOM

			$this->url = $this->server . "/X?op=source_locate_request" .
				"&locate_command=WIN=($strInstitute)" .
				"&source_full_info_flag=" . $strFull .
				"&session_id=" . $this->session;				

			$this->xml = $this->getResponse($this->url);
		}
    
		// extract marc records
		
		$objXPathRecord = new \DOMXPath($this->xml);
		$objXPathRecord->registerNamespace("marc", "http://www.loc.gov/MARC21/slim");
		$objRecords = $objXPathRecord->query("//marc:record");

		// import and append marc records to master document
		
		foreach ($objRecords as $objRecord)
		{
			$objImportNode = $objFinalXml->importNode($objRecord, true);
			$objFinalXml->documentElement->appendChild($objImportNode);					
		}
		
		$this->xml = $objFinalXml;
		
		return $objFinalXml;
	}
	
	/**
	 * Status information of the Metalib server
	 *
	 * @return DOMDocument status information
	 */
	
	public function metalibInfo()
	{
		$this->url = $this->server . "/X?op=retrieve_metalib_info_request" .
			"&view=full" .
			"&session_id=" . $this->session;
		
		// load into DOM
		
		$this->xml = $this->getResponse($this->url);
		
		return $this->xml; 
	}
	
	/**
	* Checks if metalib is done searching
	* parses search status response from Metalib for existence
	* of terms that would indicate Metalib is still searching;
	* if not present, return status of DONE, so hits page stops auto-refreshing.
	* 
	* @param string $strStatus xml status document
	* @return bool true if finished, false if not
	*/ 

	private function checkFinished( $strStatus )
	{
		$strFinal = "";		// response if found

		if ( strpos( $strStatus,"START") !== false ) {
		} else if ( strpos( $strStatus,"FIND") !== false ) {
		} else if ( strpos( $strStatus,"FORK") !== false ) {
		} else if ( strpos( $strStatus,"FETCH") !== false ) {
		} else if ( strpos( $strStatus,"DONE1") !== false && $this->return_quick == false ) {
		} else if ( strpos( $strStatus,"DONE2") !== false ) {
		} else if ( strpos( $strStatus,"DONE3") !== false ) {
		} else {
			$strFinal = "Done";
		}

		if ( $strFinal == "" )
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
		// metalib takes little care to ensure propper encoding of its xml, so we will set 
		// recover to true here in order to allow libxml to recover from errors and continue 
		// processing the document
		
		$objXml = new \DOMDocument();
		$objXml->recover = true;
		
		// fetch the data
		
		$this->client->setUri($url);
		$this->client->setConfig(array('timeout' => $timeout));
		$response = $this->client->send();
		
		if ( $response->isClientError() || $response->getBody() == "")
		{
			throw new \Exception( "Cannot process search at this time." );
		}
		
		// load into xml
		
		$objXml->loadXML($response->getBody());
		
		// no response?
		
		if ( $objXml->documentElement == null )
		{
			throw new \Exception("cannot connect to metalib server");
		}
		
		// error in response
		
		if ( $objXml->getElementsByTagName("error_code") != null )
		{
			// for easier handling
			
			$xml = simplexml_import_dom($objXml->documentElement);
			
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
						$this->session = $this->session();
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
		
		return $objXml;
	}

	/**
	 * Get full URL sent to X-Server, for debugging
	 *
	 * @return string
	 */
	
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Get string version of current XML response, for debugging
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
	 * Assign current Metalib session id
	 *
	 * @param string $strvalue 	session id
	 */
	
	public function setSession($value)   
	{
		$this->session = $value;
	}
	
	/**
	 * Get current Metalib session id
	 *
	 * @return string
	 */
	
	public function getSession()   
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
		$strVersion = "";
		$objVersion = null;
		
		// see if some xml response has already been fetched
		// in which case it will have the version, otherwise just
		// do a quick bump on the xsever and get it from that 
		
		if ( $this->xml->documentElement != null )
		{
			$objVersion = $this->xml->getElementsByTagName("x_server_response")->item(0);
		}
		else
		{
			$this->url = $this->server . "/X";
			$this->xml = $this->getResponse($this->url, $this->timeout);
		
			$objVersion = $this->xml->getElementsByTagName("x_server_response")->item(0);
		}
		
		$strVersion = $objVersion->getAttribute("metalib_version");

		// extract session ID
		
		if ( $strVersion != null)
		{
			return $strVersion;
		}
		else
		{
			throw new \Exception("Could not extract version");
		}
	}
	
	/**
	 * Whether metalib is finished searching; true if finished, false if not 
	 * 
	 * @return bool
	 */
	
	public function getFinished()
	{
		return $this->finished;
	}
	
	/**
	 * Override current Metalib search status flag,
	 * useful for prematurely ending search
	 *
	 * @param bool $value	true for finished, false if not
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