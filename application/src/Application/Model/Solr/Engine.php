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

use Application\Model\Search\QueryTerm;

use Solarium\QueryType\Suggester\Result\Term;

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
	 * @param Search\Query $query  search object or string
	 * @param int $start           [optional] starting record number
	 * @param int $max             [optional] max records
	 * @param string $sort         [optional] sort order
	 * @param bool $facets         [optional] whether to include facets or not
	 * 
	 * @return ResultSet
	 */		
	
	protected function doSearch( Search\Query $query, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		$url = $query->getQueryUrl();
		
		// get the data
		
		$client = Factory::getHttpClient();
		$response = $client->getUrl($url);
		
		return $this->parseResponse($response);
	}
	
	/**
	 * Parse the solr response
	 *
	 * @param string $response
	 * @return ResultSet
	 */	
	
	public function parseResponse($response)
	{
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
			foreach ( $this->query->getLimits(true) as $matching_limit )
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
	 * @return Facets           null if none
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
	 * Get facets from an 'all records' search
	 * 
	 * @return Facets
	 */
	
	public function getAllFacets()
	{
		$this->getQuery()->addTerm(1, null, '*', null, '*');
		
		$results = $this->doSearch($this->query);
		
		$facets = $results->getFacets();
		
		foreach ( $facets->groups as $group )
		{
			$group->sortByName('asc');
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
	
	public function getQuery(Request $request = null)
	{
		if ( ! $this->query instanceof Query )
		{
			$this->query = new Query($request, $this->getConfig());
		}
		
		return $this->query;
	}	
}
