<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Summon;

use Application\Model\Search;
use Xerxes\Summon;
use Xerxes\Mvc\Request;
use Xerxes\Utility\Factory;
use Xerxes\Utility\Json;
use Xerxes\Utility\Parser;

/**
 * Summon Search Engine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Search\Engine 
{
	/**
	 * formats configured to exclude
	 * @var array
	 */
	
	protected $formats_exclude = array();

	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		// formats to exclude
		
		$this->formats_exclude = explode(',', $this->config->getConfig("EXCLUDE_FORMATS") );
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
		// get results
		
		$results = parent::searchRetrieve($search, $start, $max, $sort, $facets);
		
		// enhance
		
		if ( $this->config->getConfig('mark_fulltext_using_export', false, false ) )
		{
			$results->markFullText(); // sfx data
		}
		
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
		
		$results = parent::getRecord( $id );
		
		// enhance
		
		$results->getRecord(0)->addRecommendations(); // bx
		
		if ( $this->config->getConfig('mark_fulltext_using_export', false, false ) )
		{
			$results->markFullText(); // sfx data
		}
		
		return $results;
	}

	/**
	 * Parse the summon response
	 *
	 * @param string $response
	 * @return ResultSet
	 */
	
	public function parseResponse($response)
	{
		$summon_results = json_decode($response, true);
		
		// testing
		// header("Content-type: text/plain"); print_r($summon_results); exit;	

		// nada
		
		if ( ! is_array($summon_results) )
		{
			throw new \Exception("Cannot connect to Summon server");
		}		
		
		// just an error, so throw it
		
		if ( ! array_key_exists('recordCount', $summon_results) && array_key_exists('errors', $summon_results) )
		{
			$message = $summon_results['errors'][0]['message'];
			
			throw new \Exception($message);
		}
		
		// results
		
		$result_set = new ResultSet($this->config);
		
		
		// recommendations
		
		$databases = $this->extractRecommendations($summon_results);
		
		foreach ( $databases as $database )
		{
			$result_set->addRecommendation($database);
		}
		
		// query expansion
		
		$terms = $this->extractQueryExpansion($summon_results);
		$result_set->addQueryExpansion($terms);

		// total
		
		$total = $summon_results['recordCount'];
		$result_set->total = $total;
		
		// extract records
		
		foreach ( $this->extractRecords($summon_results) as $xerxes_record )
		{
			$result_set->addRecord($xerxes_record);
		}
		
		// extract facets
		
		$facets = $this->extractFacets($summon_results);
		$result_set->setFacets($facets);
		
		return $result_set;
	}

	/**
	 * Parse out database recommendations
	 * 
	 * @param array $summon_results
	 * @return Database[]|Resource[]
	 */
	
	protected function extractRecommendations(array $summon_results)
	{
		$recommend = array();
		
		$recommendations = $summon_results['recommendationLists'];
		
		if ( array_key_exists('database', $recommendations) )
		{
			foreach ( $recommendations['database'] as $database_array )
			{
				if ( array_key_exists('score', $database_array) )
				{
					if ( (int) $database_array['score'] > 75 )
					{
						$recommend[] = new Database($database_array);
					}
				}
			}
		}
		
		if ( array_key_exists('bestBet', $recommendations) )
		{
			foreach ( $recommendations['bestBet'] as $database_array )
			{
				$recommend[] = new Resource($database_array);
			}
		}		
		
		return $recommend;
	}
	
	/**
	 * Parse out query expansion
	 *
	 * @param array $summon_results
	 * @return array  of terms, if expanded
	 */
	
	protected function extractQueryExpansion(array $summon_results)
	{
		$json = new Json($summon_results);
		
		$terms = $json->extractData('queryExpansion/searchTerms');
		$terms = str_replace('"', '', $terms);
		
		return $terms;
	}	
	
	/**
	 * Parse records out of the response
	 *
	 * @param array $summon_results
	 * @return Record[]
	 */
	
	protected function extractRecords(array $summon_results)
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
	
	protected function extractFacets(array $summon_results)
	{
		$facets = new Search\Facets();
		
		$facet_fields = array();
		
		if ( array_key_exists('facetFields', $summon_results) )
		{
			$facet_fields = $summon_results['facetFields'];
		}
		
		if ( array_key_exists('rangeFacetFields', $summon_results) )
		{
			foreach ( $summon_results['rangeFacetFields'] as $range_facet )
			{
				$facet_fields[] = $range_facet;
			}
		}
		
		if ( count($facet_fields) > 0 )
		{		
			// @todo: figure out how to factor out some of this to parent class
			
			// take them in the order defined in config
				
			foreach ( $this->config->getFacets() as $group_internal_name => $config )
			{
				foreach ( $facet_fields as $facetFields )
				{
					if ( $facetFields["displayName"] == $group_internal_name)
					{
						$group = new Search\FacetGroup();
						$group->name = $facetFields["displayName"];
						$group->type = (string) $config["type"];
						
						if ( $config['display'] == 'false' )
						{
							$group->display = 'false';
						}
							
						$facets->addGroup($group);
						
						// date type
						
						if ( (string) $config["type"] == "date")
						{
							foreach ( $facetFields["counts"] as $counts )
							{
								$start_date = $counts['range']['minValue'];
								$end_date = $counts['range']['maxValue'];
								$count = $counts["count"];
								
								$facet = new Search\Facet();
								$facet->name = "$start_date-$end_date";
								$facet->count = $counts["count"];
								$facet->key = "$start_date:$end_date";
								$facet->is_date = true;
								
								$facet->timestamp = strtotime("1/1/$start_date") * 1000;
									
								$date_facets[] = $facet;
							}
							
							// since summon returns date ranges whether they get 0 hits or not
							// we need to trim the ones at the front and end that are 0 count
							
							for ( $i = 0; $i < count($date_facets); $i++ )
							{
								$date_facet = $date_facets[$i];
								
								if ( (int) $date_facet->count == 0 )
								{
									$date_facets[$i] = null;
								}
								else
								{
									break;
								}
							}
							
							$date_facets = array_values(array_filter($date_facets));
							
							for ( $i = count($date_facets) - 1; $i >= 0; $i-- )
							{
								$date_facet = $date_facets[$i];

								if ( $date_facet->count == 0 )
								{
									unset($date_facets[$i]);
								}
								else
								{
									break;
								}								
							}
							
							foreach ( $date_facets as $date_facet)
							{
								$group->addFacet($date_facet);
							}
						
						}
						else // regular
						{
							foreach ( $facetFields["counts"] as $counts )
							{
								// skip excluded facets
								
								if ( $group->name == 'ContentType' && in_array($counts["value"], $this->query->getExcludedFormats()) )
								{
									continue;
								}
								
								$facet = new Search\Facet();
								$facet->name = $counts["value"];
								$facet->count = $counts["count"];
								
								if ( $counts['isNegated'] == '1')
								{
									$facet->is_excluded = 1;
								}
								
								$group->addFacet($facet);
							}
						}
					}
				}
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
	
	public function getQuery(Request $request = null)
	{
		if ( ! $this->query instanceof Query )
		{
			$this->query = new Query($request, $this->getConfig());
		}
		
		return $this->query;
	}	
}
