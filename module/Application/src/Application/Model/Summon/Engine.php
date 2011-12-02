<?php

namespace Application\Model\Summon;

use Application\Model\Search,
	Xerxes\Summon,
	Xerxes\Utility\Factory;

/**
 * Summon Search Engine
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Solr
 */

class Engine extends Search\Engine 
{
	protected $summon_client; // summon client

	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		$id = $this->config->getConfig("SUMMON_ID", true);
		$key = $this->config->getConfig("SUMMON_KEY", true);		
				
		$this->summon_client = new Summon($id, $key, Factory::getHttpClient());
	}
	
	/**
	 * Return the total number of hits for the search
	 * 
	 * @return int
	 */	
	
	public function getHits( Search\Query $search )
	{
		// get the results
		
		$results = $this->doSearch( $search, 1, 0 );

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
		$results = $this->doSearch( $search, $start, $max, $sort);
		
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
		// get result
		
		$results = $this->doGetRecord( $id );
		
		$results->getRecord(0)->addRecommendations(); // bx
		$results->markFullText(); // sfx data
		$results->markRefereed(); // refereed
		
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
	 * @return Config
	 */		
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	/**
	 * Do the actual fetch of an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */	
	
	protected function doGetRecord( $id )
	{
		// get result
		
		$summon_results = $this->summon_client->getRecord($id);
		$results = $this->parseResponse($summon_results);
		
		return $results;
	}		
	
	/**
	 * Do the actual search
	 * 
	 * @param Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Results
	 */		
	
	protected function doSearch( Search\Query $search, $start = 1, $max = 10, $sort = "")
	{ 	
		// prepare the query
		
		$terms = $search->getQueryTerms();
		$term = $terms[0];
		
		// limits
		
		$facets = array();
		
		foreach ( $search->getLimits(true) as $limit )
		{
			array_push($facets, $limit->field . "," . str_replace(',', '\,', $limit->value) . ",false");
		}

		
		############## HACK
		
		// filter out formats
		
		array_push($facets, 'ContentType,Newspaper Article,true'); // newspaper
		array_push($facets, 'ContentType,Book / eBook,true'); // catalog
		array_push($facets, 'ContentType,Reference,true'); // catalog
		array_push($facets, 'ContentType,Web Resource,true'); // catalog
		array_push($facets, 'ContentType,Research Guide,true'); // just random gudies
		
		// summon deals in pages, not start record number
		
		if ( $max > 0 )
		{
			$page = ceil ($start / $max);
		}
		else
		{
			$page = 1;
		}
		
		// get the results
		
		$summon_results = $this->summon_client->query($term->phrase, $facets, $page, $max, $sort);
		
		return $this->parseResponse($summon_results);
	}
	
	/**
	 * Parse the summon response
	 *
	 * @param array $summon_results		summon results array from client
	 * @return ResultSet
	 */
	
	protected function parseResponse($summon_results)
	{
		// testing
		// header("Content-type: text/plain"); print_r($summon_results); exit;	

		// nada
		
		if ( ! is_array($summon_results) )
		{
			throw new \Exception("Cannot connect to Summon server");
		}		
		
		$result_set = new Search\ResultSet($this->config);

		// total
		
		$total = $summon_results['recordCount'];
		$result_set->total = $total;
		
		// extract records
		
		foreach ( $this->extractRecords($summon_results) as $xerxes_record )
		{
			$result_set->addRecord($xerxes_record);
		}
		
		// extract facets
		
		$facets = $this->extractFacets($summon_results, $total);	 ############## HACK	
		$result_set->setFacets($facets);
		
		return $result_set;
	}
	
	/**
	 * Parse records out of the response
	 *
	 * @param array $summon_results
	 * @return array of Record's
	 */
	
	protected function extractRecords($summon_results)
	{
		$records = array();
		
		if ( array_key_exists("documents", $summon_results) )
		{
			foreach ( $summon_results["documents"] as $document )
			{
				$xerxes_record = new Record();
				$xerxes_record->load($document);
				array_push($records, $xerxes_record);
			}
		}
		
		return $records;
	}
	
	/**
	 * Parse facets out of the response
	 *
	 * @param array $summon_results
	 * @return Facets
	 */	
	
	protected function extractFacets($summon_results, $total)
	{
		$facets = new Search\Facets();
		
		if ( array_key_exists("facetFields", $summon_results) )
		{		
			// take them in the order defined in config
				
			foreach ( array_keys($this->config->getFacets()) as $group_internal_name )
			{
				foreach ( $summon_results["facetFields"] as $facetFields )
				{
					if ( $facetFields["displayName"] == $group_internal_name)
					{
						$group = new Search\FacetGroup();
						$group->name = $facetFields["displayName"];
						$group->public = $this->config->getFacetPublicName($facetFields["displayName"]);
							
						$facets->addGroup($group);
							
						foreach ( $facetFields["counts"] as $counts )
						{
							############## HACK
							
							if ( $counts["count"] == $total)
							{
								continue;
							}
							
							$facet = new Search\Facet();
							$facet->name = $counts["value"];
							$facet->count = $counts["count"];
								
							$group->addFacet($facet);
						}
					}
				}
			}
		}
		
		return $facets;
	}
}
