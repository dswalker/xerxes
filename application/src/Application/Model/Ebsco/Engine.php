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

use Application\Model\Ebsco\Exception\DatabaseException;
use Application\Model\Search;
use Xerxes\Utility\Parser;
use Xerxes\Mvc\Request;

/**
 * Ebsco Search Engine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Search\Engine 
{
	/**
	 * Ebsco hack
	 * @var int
	 */
	
	private $deincrementing = 0;
	
	/**
	 * database refresh
	 * @var int
	 */
	
	private $database_refresh = 0; 
	
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
		
		try
		{
			$results = parent::searchRetrieve( $search, $start, $max, $sort, $facets);
		}
		catch ( DatabaseException $e ) // database access error
		{
			if ( $this->database_refresh == 0 )
			{
				$this->getQuery()->getDatabases(true); // force a refresh
				$this->database_refresh = 1; // don't repeat this twice, please
				$this->searchRetrieve( $search, $start, $max, $sort, $facets ); // let's try that again
			}
			else // already tried this, so error-out
			{
				throw $e;
			}
		}
		
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
	 * Parse the ebsco response
	 *
	 * @param string $response
	 * @return ResultSet
	 */	
	
	public function parseResponse($response)
	{
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
				
				// need to get a fresh set of databases
				
				if ( strstr($message->nodeValue, 'User does not have access rights to database') )
				{
					throw new DatabaseException($message->nodeValue);
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
		
		if ( $check == 0 && $this->query->start > $this->query->max )
		{
			// but let's not get crazy here
			
			if ( $this->deincrementing <= 8 )
			{
				$this->deincrementing++;
				$new_start = $this->query->start - $this->query->max;
				
				$this->query->start = $new_start;
				
				return $this->doSearch($this->query);
			}
		}
		
		// we've reached the end prematurely, so set this to the end
		
		$check_end = $this->query->start + $check;
		
		if ( $check < $this->query->max )
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
				$facet->name = $this->getQuery()->getDatabaseName($database_id);
				$facet->count = Parser::number_format( $database_hits );
				$facet->key = $database_id;
					
				$group->addFacet($facet);
			}
			
			$facets->addGroup($group);
		}
		
		return $facets;
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
	
	public function getQuery(Request $request = null)
	{
		if ( ! $this->query instanceof Query )
		{
			$this->query = new Query($request, $this->getConfig());
		}
		
		return $this->query;
	}	
	
}
