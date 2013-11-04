<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Solr;

use Application\Model\Search;
use Xerxes\Utility\Factory;
use Xerxes\Mvc\Request;

/**
 * Solr Search Engine
 * 
 * @author David Walker <dwalker@calstate.edu>
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
		
		$results = parent::getRecord($id);
		
		$record = $results->getRecord(0);
		
		$record->fetchHoldings(); // item availability
		$record->addReviews(); // good read reviews
		
		return $results;
	}
	
	/**
	 * Do the actual fetch of an individual record
	 * 
	 * @param string  record identifier
	 * @return Results
	 */		
	
	protected function doGetRecord($id)
	{
		$id = str_replace(':', "\\:", $id);
		
		$query = new Query();
		$query->simple = "id:$id";
		
		$results = $this->doSearch($query, 1, 1);
		return $results;
	}
	
	/**
	 * Do the actual search
	 * 
	 * @param Search\Query $search  search object or string
	 * @param int $start            [optional] starting record number
	 * @param int $max              [optional] max records
	 * @param string $sort          [optional] sort order
	 * @param bool $facets  [optional] whether to include facets or not
	 * 
	 * @return ResultSet
	 */		
	
	protected function doSearch( Search\Query $search, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		// start
		
		if ( $start > 0)
		{
			$start--; // solr is 0-based
		}
		
		// parse the query
		
		$query = '';
		
		if ( $search->simple != "" )
		{
			$query = "&q=" . urlencode($search->simple);
		}
		else
		{
			$query = $search->toQuery();
		}
		
		// now the url
		
		$this->url = $this->server . $query;

		$this->url .= "&start=$start&rows=" . $max . "&sort=" . urlencode($sort);
		
		if ( $facets == true )
		{
			$this->url .= "&facet=true&facet.mincount=1";
			
			foreach ( $this->config->getFacets() as $facet => $attributes )
			{
				$sort = (string) $attributes["sort"];
				$max = (string) $attributes["max"];
				$type = (string) $attributes["type"];
				
				if ( $type == 'date' )
				{
					$sort = 'index';
				}
				
				$this->url .= "&facet.field=" . urlencode("{!ex=$facet}$facet");

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
		
		// echo $this->url;
		
		
		## get and parse the response
		
		// get the data
		
		$client = Factory::getHttpClient();
		$response = $client->getUrl($this->url);
		
		// header('Content-type: text/xml'); echo $response; echo '<!--' . $this->url . '-->'; exit;
		
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
		
		$facets = $this->extractFacets($xml);
		
		// associate facets excluded in the query with the 
		// multi-select facets returned in the response
		
		foreach ( $facets->getGroups() as $group )
		{
			foreach ( $search->getLimits(true) as $matching_limit )
			{
				if ( $matching_limit->boolean == 'NOT' && $matching_limit->field == $group->name )
				{
					$facet_values = $matching_limit->value;
					
					if ( ! is_array($facet_values) )
					{
						$facet_values = array($facet_values);
					}
					
					foreach ( $group->getFacets() as $facet )
					{
						foreach ( $facet_values as $facet_value )
						{
							if ( $facet->name == $facet_value)
							{
								$facet->is_excluded = true;
							}
						}
					}
				}
			}
		}
		
		$results->setFacets($facets);
		
		return $results;
	}
	
	/**
	 * Extract records from the Solr response
	 * 
	 * @param simplexml	$xml	solr response
	 * @return Record[]
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
				
				// put facets into an array
				
				$facet_array = array();
				
				foreach ( $facet_group->int as $int )
				{
					$facet_array[(string)$int["name"]] = (int) $int;
				}
				
				$is_date = $this->config->isDateType($group_internal_name);

				foreach ( $facet_array as $key => $value )
				{
					$facet = new Search\Facet();
					$facet->name = $key;
					$facet->count = $value;
					
					if ( $is_date == true )
					{
						$facet->is_date = true;
						$facet->timestamp = strtotime("1/1/$key") * 1000;
					}
					
					$group->addFacet($facet);
				}
				
				$facets->addGroup($group);
			}
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
	 * Return the Summon search query object
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
