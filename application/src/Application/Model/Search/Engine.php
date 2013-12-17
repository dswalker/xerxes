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
	 * Constructor
	 */
	
	public function __construct()
	{
		$this->cache = new Cache();
		
		// application config
		
		$this->registry = Registry::getInstance();
		
		// local config
		
		$this->config = $this->getConfig();
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
	
	abstract protected function doSearch( Query $search, $start = 1, $max = 10, $sort = "", $facets = true);
	
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
	
	abstract protected function doGetRecord( $id );
	
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
		
		if ( $this->config->getConfig('CACHE_RESULTS', false, $this->registry->getConfig('CACHE_RESULTS', false, false)) == false )
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
		
		if ( $this->config->getConfig('CACHE_RESULTS', false, $this->registry->getConfig('CACHE_RESULTS', false, false)) == false )
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
