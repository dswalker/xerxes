<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Ebsco;

use Application\Model\Search;
use Xerxes\Utility\Factory;
use Xerxes\Utility\Parser;
use Xerxes\Mvc\Request;

/**
 * Ebsco Search Engine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Search\Engine 
{
	protected $username; // ebsco username
	protected $password; // ebsco password
	
	private $deincrementing = 0; // ebsco hack
	
	/**
	 * New Ebsco Search Engine
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		$this->username = $this->config->getConfig("EBSCO_USERNAME");
		$this->password = $this->config->getConfig("EBSCO_PASSWORD");	
	}
	
	/**
	 * Search and return results
	 * 
	 * @param Query $search  search object
	 * @param int $start     [optional] starting record number
	 * @param int $max       [optional] max records
	 * @param string $sort   [optional] sort order
	 * @param bool $facets   [optional] whether to include facets
	 * 
	 * @return Results
	 */	
	
	public function searchRetrieve( Search\Query $search, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		// get the results
		
		$results = parent::searchRetrieve( $search, $start, $max, $sort, $facets);
		
		// enhance
		
		$results->markRefereed();
		$results->markFullText();
		
		return $results;
	}	
	
	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */
	
	public function getRecord( $id )
	{
		$results = parent::getRecord($id);
		
		// enhance
		
		$results->getRecord(0)->addRecommendations();
		$results->markFullText();
		$results->markRefereed();
		
		return $results;
	}

	/**
	 * Do the actual fetch of an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */	
	
	protected function doGetRecord($id)
	{
		if ( ! strstr($id, "-") )
		{
			throw new \Exception("could not find record");
		}
		
		// database and id come in on same value, so split 'em
		
		$database = Parser::removeRight($id,"-");
		$id = Parser::removeLeft($id,"-");
		
		// get results
		
		$query = new Query();
		$query->simple = "AN $id";
		$query->addLimit(null, 'database', null, $database);
		
		$results = $this->doSearch($query, 1, 1);
		
		return $results;
	}

	/**
	 * Do the actual search and return results
	 *
	 * @param Query $search  search object
	 * @param int $start     [optional] starting record number
	 * @param int $max       [optional] max records
	 * @param string $sort   [optional] sort order
	 * @param bool $facets   [optional] whether to include facets
	 *
	 * @return Results
	 */	
	
	protected function doSearch( Search\Query $search, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		// default for sort
		
		if ( $sort == "" )
		{
			$sort = "relevance";
		}
		
		// prepare the query
		
		$query = "";
		
		if ( $search->simple != "")
		{
			$query = $search->simple;
		}
		else
		{
			$query = $search->toQuery();
		}
		
		// databases
		
		$databases = array();
		
		// see if any supplied as facet limit
		
		foreach ( $search->getLimits(true) as $limit )
		{
			if ( $limit->field == "database")
			{
				array_push($databases, $limit->value);
			}
		}
			
		// nope
			
		if ( count($databases) == 0)
		{
			// get 'em from config
				
			$databases_xml = $this->config->getConfig("EBSCO_DATABASES");
			
			if ( $databases_xml == "" )
			{
				throw new \Exception("No databases defined");
			}
			
			foreach ( $databases_xml->database as $database )
			{
				array_push($databases, (string) $database["id"]);
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
		
		$client = Factory::getHttpClient();
		$response = $client->getUrl($this->url, 10);

		// testing
		// echo "<pre>$this->url<hr>$response</pre>"; exit;
		
		if ( $response == null )
		{
			throw new \Exception("Could not connect to Ebsco search server");
		}
		
		// load it in
		
		$xml = Parser::convertToDOMDocument($response);
		
		// catch a non-xml response
		
		if ( $xml->documentElement == null )
		{
			throw new \Exception("Could not connect to Ebsco search server");
		}
		
		// check for fatal error
		
		if ( $xml->documentElement->nodeName == 'Fault')
		{
			$message = $xml->getElementsByTagName('Message')->item(0);
			
			if ( $message != null )
			{
                                if ( $message->nodeValue == "The following parameter(s) have incorrect values: Field query: Greater than 0" )
                                {
                                        throw new \Exception('Ebsco search error: your search query cannot be empty');
                                }
				
				throw new \Exception('Ebsco server error: ' . $message->nodeValue);
			}
		}
		
		// result set
		
		$results = new Search\ResultSet($this->config);
		
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
				
				return $this->doSearch($search, $new_start, $max, $sort);
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
		
		/*
		
		$facets_id = 'facets' . $query->getHash();
		
		// cached clusters
		
		$cached_facets = $this->cache->get($facets_id);
		
		if ( $cached_facets instanceof Facets )
		{
			$results->setFacets($cached_facets);
		}
		else
		{
			$facets = $this->extractFacets($xml);
			$this->cache->set($facets_id, $facets);
			$results->setFacets($facets);
		}
		*/
		
		return $results;
	}
	
	/**
	 * Parse records out of the response
	 *
	 * @param DOMDocument $xml
	 * @return Record[]
	 */
	
	protected function extractRecords(\DOMDocument $xml)
	{
		$records = array();

		$xpath = new \DOMXPath($xml);
		
		$records_object = $xpath->query("//rec");
		
		foreach ( $records_object as $record )
		{
			$xerxes_record = new Record();
			$xerxes_record->loadXML($record);
			array_push($records, $xerxes_record);
		}
		
		return $records;
	}
	
	/**
	 * Parse facets out of the response
	 *
	 * @param DOMDocument $dom
	 * @return Facets
	 */
	
	protected function extractFacets(\DOMDocument $dom)
	{
		$facets = new Search\Facets();

		$xml = simplexml_import_dom($dom->documentElement);
		
		// for now just the database hit counts
		
		$databases = $xml->Statistics->Statistic;
		
		if ( count($databases) > 1 )
		{
			$databases_facet_name = $this->config->getConfig("DATABASES_FACET_NAME", false, "Databases");
				
			$group = new Search\FacetGroup();
			$group->name = "database";
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
				$facet = new Search\Facet();
				$facet->name = $this->config->getDatabaseName($database_id);
				$facet->count = Parser::number_format( $database_hits );
				$facet->key = $database_id;
					
				$group->addFacet($facet);
			}
			
			$facets->addGroup($group);
		}
		
		return $facets;
	}
	
	public function getDatabases()
	{
		$url = 'http://eit.ebscohost.com/Services/SearchService.asmx/Info?prof=' . 
			$this->username  . '&pwd=' . $this->password;
	}
	
	/**
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	/**
	 * Return the Ebsco search query object
	 *
	 * @param Request $request
	 * @return Query
	 */
	
	public function getQuery(Request $request )
	{
		if ( $this->query instanceof Query )
		{
			return $this->query;
		}
		else
		{
			return new Query($request, $this->getConfig());
		}
	}	
}
