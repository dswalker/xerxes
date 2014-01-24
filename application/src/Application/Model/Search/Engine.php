<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

use Xerxes\Utility\Cache;
use Xerxes\Utility\Factory;
use Xerxes\Utility\Registry;
use Xerxes\Mvc\Request;

/**
 * Search Engine
 *
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class Engine
{
	/**
	 * identifier of this search engine
	 * 
	 * @var string
	 */
	
	public $id;
	
	/**
	 * url to the search service
	 * 
	 * @var string
	 */
	
	protected $url;
	
	/**
	 * @var Registry
	 */
	
	protected $registry;
	
	/**
	 * @var Config
	 */
	
	protected $config;
	
	/**
	 * @var Query
	 */
	
	protected $query;
	
	/**
	 * @var Cache
	 */
	
	protected $cache;
	
	/**
	 * Should cache results
	 * @var bool
	 */
	
	protected $should_cache_results = true;
	
	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		$this->cache = new Cache();
		
		// application config
		
		$this->registry = Registry::getInstance();
		
		// local config
		
		$this->config = $this->getConfig();
		
		// cache results based on local config or registry, default true
		
		$this->should_cache_results = $this->config->getConfig('CACHE_RESULTS', false, $this->registry->getConfig('CACHE_RESULTS', false, true));
	}
	
	/**
	 * Return the total number of hits for the search
	 * 
	 * @return int
	 */	
	
	public function getHits( Query $search )
	{
		// get the results
	
		$results = $this->doSearch( $search, 1, 1 );
	
		// return total
	
		return $results->getTotal();
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
	
	public function searchRetrieve( Query $search, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		// cache
	
		$results = $this->getCachedResults($search);
	
		if ( $results == null )
		{
			$results = $this->doSearch( $search, $start, $max, $sort, $facets);
			$this->setCachedResults($results, $search);
		}
	
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
	
	protected function doSearch( Query $query, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		$request = $query->getQueryUrl();
		
		// get the data
		
		$client = Factory::getHttpClient();
		$response = $client->getUrl($request->url, null, $request->headers);
		
		return $this->parseResponse($response);
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
	
		return $this->doGetRecord( $id );
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
	 * Do the actual fetch of an individual record
	 *
	 * @param string	record identifier
	 * @return ResultSet
	 */
	
	protected function doGetRecord($id)
	{
		$query = $this->getQuery();
		$request = $query->getRecordUrl($id);
		
		$client = Factory::getHttpClient();
		$response = $client->getUrl($request->url, null, $request->headers);
		
		return $this->parseResponse($response);
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
	 * Return the search engine config
	 * 
	 * @return Config
	 */
	
	abstract public function getConfig();
	
	/**
	 * Return the URL sent ot the web service
	 * 
	 * @return string
	 */
	
	public function getURL()
	{
		return $this->url;
	}
	
	/**
	 * Return a search query object
	 * 
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
	
	/**
	 * Check for previously cached results
	 * 
	 * @param string|Query $query  the search query
	 * @return null|ResultSet      null if no previously cached results
	 */
	
	public function getCachedResults($query)
	{
		// if cache is turned off, then don't bother looking up cache
		
		if ( $this->should_cache_results == false )
		{
			return null;
		}
		
		$id = $this->getResultsID($query);
		
		return $this->cache->get($id);
	}
	
	/**
	 * Cache search results
	 * 
	 * @param ResultSet $results
	 * @param string|Query $query
	 */
	
	public function setCachedResults(ResultSet $results, $query)
	{
		// if cache is turned off, then don't bother caching
		
		if ( $this->should_cache_results == false )
		{
			return null;
		}		
		
		$id = $this->getResultsID($query);
		
		$this->cache->set($id, $results);
	}
	
	/**
	 * calculate query identifier
	 * 
	 * @param string|Query $query
	 * @return string
	 */
	
	protected function getResultsID($query)
	{
		if ( $query == '' )
		{
			throw new \DomainException("Query ID cannot be empty");
		}
		
		$id = 'results';
		
		if ( $query instanceof Query)
		{
			$id .= $query->getUrlHash();
		}
		else
		{
			$id .= $query;
		}
		
		return $id;
	}
}
