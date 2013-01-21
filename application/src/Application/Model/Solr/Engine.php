<?php

namespace Application\Model\Solr;

use Application\Model\Search,
	Xerxes\Utility\Factory,
	Xerxes\Utility\Request;

/**
 * Solr Search Engine
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Solr
 */

class Engine extends Search\Engine 
{
	protected $server; // solr server address
	protected $url; // track the url

	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		parent::__construct();

		// server address
		
		$this->server = $this->config->getConfig('SOLR', true);
		
		$this->server = rtrim($this->server, '/');
		
		$this->server .= "/select/?version=2.2";
	}
	
	/**
	 * Return the total number of hits for the search
	 * 
	 * @return int
	 */	
	
	public function getHits( Search\Query $search )
	{
		// get the results, just the hit count, no facets
		
		$results = $this->doSearch($search, 0, 0, null, false);
		
		// return total
		
		return $results->getTotal();
	}

	/**
	 * Search and return results
	 * 
	 * @param Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Results
	 */	
	
	public function searchRetrieve( Search\Query $search, $start = 1, $max = 10, $sort = "")
	{
		// get the results
		
		$results = $this->doSearch($search, $start, $max, $sort, true);
		
		// find any holding we have cached
		
		$results->injectHoldings();
		
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
		// get the record
		
		$results = $this->doGetRecord($id);
		$record = $results->getRecord(0);
		
		$record->fetchHoldings(); // item availability
		$record->addReviews(); // good read reviews
		
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
		$results = $this->doGetRecord($id);
		$record = $results->getRecord(0);
		
		$record->fetchHoldings(); // item availability
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
		$id = str_replace(':', "\\:", $id);
		$results = $this->doSearch("id:$id", 1, 1);
		return $results;
	}
	
	/**
	 * Do the actual search
	 * 
	 * @param string|Query $search					search object or string
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * @param bool $include_facets					[optional] whether to include facets or not
	 * 
	 * @return ResultSet
	 */		
	
	protected function doSearch( $search, $start, $max = 10, $sort = null, $include_facets = true)
	{
		// already cached
		
		$results = $this->getCachedResults($search);
		
		if ( $results instanceof Search\ResultSet )
		{
			return $results;
		}
		
		// start
		
		if ( $start > 0)
		{
			$start--; // solr is 0-based
		}
		
		### parse the query
		
		$query = "";
		
		// passed in a query object, so handle this
		
		if ( $search instanceof Search\Query )
		{
			$query = $search->toQuery();
		}
		
		// was just a string, so just take it straight-up
		
		else
		{
			$query = "&q=" . urlencode($search);
		}
		
		
		### now the url
		
		$this->url = $this->server . $query;

		$this->url .= "&start=$start&rows=" . $max . "&sort=" . urlencode($sort);
		
		if ( $include_facets == true )
		{
			$this->url .= "&facet=true&facet.mincount=1";
			
			foreach ( $this->config->getFacets() as $facet => $attributes )
			{
				$sort = (string) $attributes["sort"];
				$max = (string) $attributes["max"];
				
				$this->url .= "&facet.field=$facet";

				if ( $sort != "" )
				{
					$this->url .= "&f.$facet.facet.sort=$sort";
				}				
				
				if ( $max != "" )
				{
					$this->url .= "&f.$facet.facet.limit=$max";
				}					
			}
		}
		
		// make sure we get the score
		
		$this->url .= "&fl=*+score";
		
		
		## get and parse the response

		// get the data
		
		$client = Factory::getHttpClient();
		$client->setUri($this->url);
		$response = $client->send()->getBody();
		
		$xml = simplexml_load_string($response);
		
		if ( $response == null || $xml === false )
		{
			throw new \Exception("Could not connect to search engine.");
		}
		
		// parse the results
		
		$results = new Search\ResultSet($this->config);
		
		// extract total
		
		$results->total = (int) $xml->result["numFound"]; 
		
		// extract records
		
		foreach ( $this->extractRecords($xml) as $record )
		{
			$results->addRecord($record);
		}
		
		// extract facets
		
		$results->setFacets($this->extractFacets($xml));
		
		// cache it for later
		
		$this->setCachedResults($results, $search);
		
		return $results;
	}
	
	/**
	 * Extract records from the Solr response
	 * 
	 * @param simplexml	$xml	solr response
	 * @return array of Record's
	 */	
	
	protected function extractRecords($xml)
	{
		$records = array();
		$docs = $xml->xpath("//doc");
		
		if ( $docs !== false && count($docs) > 0 )
		{
			foreach ( $docs as $doc )
			{
				$record = new Record();
				$record->loadXML($doc);
				array_push($records, $record);
			}
		}
		
		return $records;
	}
	
	/**
	 * Extract facets from the Solr response
	 * 
	 * @param simplexml	$xml	solr response
	 * @return Facets, null if none
	 */
	
	protected function extractFacets($xml)
	{
		$facets = new Search\Facets();
		
		$groups = $xml->xpath("//lst[@name='facet_fields']/lst");
		
		if ( $groups !== false && count($groups) > 0 )
		{
			foreach ( $groups as $facet_group )
			{
				// if only one entry, then all the results have this same facet,
				// so no sense even showing this as a limit
				
				$count = count($facet_group->int);
				
				if ( $count <= 1 )
				{
					continue;
				}
				
				$group_internal_name = (string) $facet_group["name"];
				
				$group = new Search\FacetGroup();
				$group->name = $group_internal_name;
				$group->public = $this->config->getFacetPublicName($group_internal_name);
				
				// put facets into an array
				
				$facet_array = array();
				
				foreach ( $facet_group->int as $int )
				{
					$facet_array[(string)$int["name"]] = (int) $int;
				}

				// date

				$decade_display = array();
				
				$is_date = $this->config->isDateType($group_internal_name);
				
				if ( $is_date == true )
				{
					$date_arrays = $group->luceneDateToDecade($facet_array);
					$decade_display = $date_arrays["display"];
					$facet_array = $date_arrays["decades"];		
				}
				
				foreach ( $facet_array as $key => $value )
				{
					$facet = new Search\Facet();
					$facet->name = $key;
					$facet->count = $value;
					
					// dates are different
					
					if ( $is_date == true )  
					{
						$facet->name = $decade_display[$key];
						$facet->is_date = true;
						$facet->key = $key;
					}

					$group->addFacet($facet);
				}
				
				$facets->addGroup($group);
			}
		}
		
		return $facets;
	}
	
	/**
	 * Return the search engine config
	 *
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	/**
	 * Return the Summon search query object
	 *
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
