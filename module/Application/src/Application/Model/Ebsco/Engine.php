<?php

/**
 * Ebsco Search Engine
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Ebsco_Engine extends Xerxes_Model_Search_Engine 
{
	protected $username; // ebsco username
	protected $password; // ebsco password
	
	private $deincrementing = 0; // ebsco hack
	
	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		$this->username = $this->config->getConfig("EBSCO_USERNAME");
		$this->password = $this->config->getConfig("EBSCO_PASSWORD");	
	}
	
	/**
	 * Return the total number of hits for the search
	 * 
	 * @return int
	 */	
	
	public function getHits( Xerxes_Model_Search_Query $search )
	{
		// get the results
		
		$results = $this->doSearch($search, null, 1, 1 );

		// return total
		
		return $results->getTotal();		
	}

	/**
	 * Search and return results
	 * 
	 * @param Xerxes_Model_Search_Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Xerxes_Model_Search_Results
	 */	
	
	public function searchRetrieve( Xerxes_Model_Search_Query $search, $start = 1, $max = 10, $sort = "")
	{
		// get the results
		
		$results = $this->doSearch($search, null, $start, $max, $sort);
		
		// do some stuff
		
		$results->markRefereed();
		$results->markFullText();
		
		return $results;
	}	
	
	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return Xerxes_Model_Solr_Results
	 */
	
	public function getRecord( $id )
	{
		$results = $this->doGetRecord($id);
		
		// enhance
		
		$results->getRecord(0)->addRecommendations();
		$results->markFullText();
		$results->markRefereed();
		
		return $results;
	}

	/**
	 * Get record to save
	 * 
	 * @param string	record identifier
	 * @return int		internal saved id
	 */	
	
	public function getRecordForSave( $id )
	{
		return $this->doGetRecord($id);
	}
	
	/**
	 * Return the search engine config
	 * 
	 * @return Xerxes_Model_Ebsco_Config
	 */		
	
	public function getConfig()
	{
		return Xerxes_Model_Ebsco_Config::getInstance();
	}		
	
	/**
	 * Do the actual fetch of an individual record
	 * 
	 * @param string	record identifier
	 * @return Xerxes_Model_Solr_Results
	 */	
	
	protected function doGetRecord($id)
	{
		if ( ! strstr($id, "-") )
		{
			throw new Exception("could not find record");
		}
		
		// database and id come in on same value, so split 'em
		
		$database = Xerxes_Framework_Parser::removeRight($id,"-");
		$id = Xerxes_Framework_Parser::removeLeft($id,"-");
		
		// get results
		
		$results = $this->doSearch("AN $id", $database, 1, 1);
		
		return $results;
	}

	/**
	 * Do the actual search
	 * 
	 * @param mixed $search							search object or string
	 * @param string $database						[optional] database id
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Xerxes_Model_Search_Results
	 */		
	
	protected function doSearch( $search, $database, $start, $max, $sort = "relevance")
	{
		// default for sort
		
		if ( $sort == "" )
		{
			$sort = "relevance";
		}
		
		// prepare the query
		
		$query = "";
		
		if ( $search instanceof Xerxes_Model_Search_Query )
		{
			// just one term for now
			
			$terms = $search->getQueryTerms();
			$term = $terms[0];
			
			$term->toLower()
			     ->andAllTerms();
			
			$query = $term->field_internal . " " . $term->phrase;
		}
		else
		{
			$query = $search;
		}
		
		// databases
		
		$databases = array();
		
		// we asked for this database specifically
		
		if ( $database != "" )
		{
			$databases = array($database);
		}

		// no database specifically defined, so ...
		
		else
		{
			// see if any supplied as facet limit
			
			foreach ( $search->getLimits(true) as $limit )
			{
				array_push($databases, $limit->value);
			}
			
			// nope
			
			if ( count($databases) == 0)
			{
				// get 'em from config
				
				$databases_xml = $this->config->getConfig("EBSCO_DATABASES");
				
				if ( $databases_xml == "" )
				{
					throw new Exception("No databases defined");
				}
				
				foreach ( $databases_xml->database as $database )
				{
					array_push($databases, (string) $database["id"]);
				}
			}
		}
		
		// construct url
		
		$this->url = "http://eit.ebscohost.com/Services/SearchService.asmx/Search?" . 
			"prof=" . $this->username . 
			"&pwd=" . $this->password . 
			"&authType=&ipprof=" . // empty params are necessary because ebsco is stupid
			"&query=" . urlencode($query) .		
			"&startrec=$start&numrec=$max" . 
			"&sort=$sort" .
			"&format=detailed";
		
		// add in the databases
		
		foreach ( $databases as $database )
		{
			$this->url .= "&db=$database";
		}
				
		// get the xml from ebsco
		
		$response = Xerxes_Framework_Parser::request($this->url);
		
		// testing
		// echo "<pre>$this->url<hr>$response</pre>"; exit;
		
		if ( $response == null )
		{
			throw new Exception("Could not connect to Ebsco search server");
		}
		
		// load it in
		
		$xml = new DOMDocument();
		$xml->recover = true;
		$xml->loadXML($response);
		
		// result set
		
		$results = new Xerxes_Model_Search_ResultSet($this->config);
		
		// get total
		
		$total = 0;
		
		$hits = $xml->getElementsByTagName("Hits")->item(0);
		
		if ( $hits != null )
		{
			$total = (int) $hits->nodeValue;
		}
		
		
		
		### hacks until ebsco gives us proper hit counts, they are almost there
		
		$check = 0;
		
		foreach ( $xml->getElementsByTagName("rec") as $hits )
		{
			$check++;
		}
		
		// no hits, but we're above the first page, so the user has likely
		// skipped here, need to increment down until we find the true ceiling
		
		if ( $check == 0 && $start > $max )
		{
			// but let's not get crazy here
			
			if ( $this->deincrementing <= 8 )
			{
				$this->deincrementing++;
				$new_start = $start - $max;
				
				return $this->doSearch($query, $databases, $new_start, $max, $sort);
			}
		}
		
		// we've reached the end prematurely, so set this to the end
		
		$check_end = $start + $check;
		
		if ( $check < $max )
		{
			if ( $check_end	< $total )
			{
				$total = $check_end;
			}
		}
		
		## end hacks
		
		
		
		// set total
		
		$results->total = $total;
		
		// add records
		
		foreach ( $this->extractRecords($xml) as $record )
		{
			$results->addRecord($record);
		}
		
		// add clusters
		
		$facets = $this->extractFacets($xml);
		$results->setFacets($facets);
		
		return $results;
	}
	
	/**
	 * Parse records out of the response
	 *
	 * @param DOMDocument $xml
	 * @return array of Xerxes_Model_Ebsco_Record's
	 */
	
	protected function extractRecords(DOMDocument $xml)
	{
		$records = array();

		$xpath = new DOMXPath($xml);
		
		$records_object = $xpath->query("//rec");
		
		foreach ( $records_object as $record )
		{
			$xerxes_record = new Xerxes_Model_Ebsco_Record();
			$xerxes_record->loadXML($record);
			array_push($records, $xerxes_record);
		}
		
		return $records;
	}
	
	/**
	 * Parse facets out of the response
	 *
	 * @param DOMDocument $dom
	 * @return Xerxes_Model_Search_Facets
	 */
	
	protected function extractFacets(DOMDocument $dom)
	{
		$facets = new Xerxes_Model_Search_Facets();

		$xml = simplexml_import_dom($dom->documentElement);
		
		// for now just the database hit counts
		
		$databases = $xml->Statistics->Statistic;
		
		if ( count($databases) > 1 )
		{
			$databases_facet_name = $this->config->getConfig("DATABASES_FACET_NAME", false, "Databases");
				
			$group = new Xerxes_Model_Search_FacetGroup("databases");
			$group->name = "databases";
			$group->public = $databases_facet_name;
			
			$databases_array = array();
			
			foreach ( $databases as $database )
			{
				$database_id = (string) $database->Database;
				$database_hits = (int) $database->Hits;
				
				// nix the empty ones
				
				if ( $database_hits == 0 )
				{
					continue;
				}
				
				$databases_array[$database_id] = $database_hits;
			}
			
			// get 'em in reverse order
			
			arsort($databases_array);
			
			foreach ( $databases_array as $database_id => $database_hits)
			{
				$facet = new Xerxes_Model_Search_Facet();
				$facet->name = $this->config->getDatabaseName($database_id);
				$facet->count = Xerxes_Framework_Parser::number_format( $database_hits );
				$facet->key = $database_id;
					
				$group->addFacet($facet);
			}
			
			$facets->addGroup($group);
		}
		
		return $facets;
	}
}
