<?php

namespace Application\Model\Search;

use Xerxes\Utility\Cache,
	Xerxes\Utility\Registry,
	Xerxes\Utility\Request,
	Zend\Http\Client;

/**
 * Search Engine
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

abstract class Engine
{
	public $id; // identifier of this search engine
	
	protected $url; // url to the search service
	protected $registry; // xerxes application config
	protected $config; // local search engine config
	protected $query; // search query
	
	private $cache; // cache object
	
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
	
	abstract public function getHits( Query $search );
	
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
	
	abstract public function searchRetrieve( Query $search, $start = 1, $max = 10, $sort = "" );
	
	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */
	
	abstract public function getRecord( $id );

	/**
	 * Get record to save
	 * 
	 * @param string	record identifier
	 * @return int		internal saved id
	 */	
	
	abstract public function getRecordForSave( $id );
	
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
	
	/**
	 * Check for previously cached results
	 * 
	 * @param string|Query $query
	 * @return null|ResultSet     null if no previously cached results
	 */
	
	public function getCachedResults($query)
	{
		// if cache is turned off, then don't bother looking up cache
		
		if ( $this->config->getConfig('CACHE_RESULTS', false, false) == false )
		{
			return null;
		}
		
		$id = $this->getCacheID($query);
		
		$results = $this->cache->get($id);
		
		return unserialize($results);
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
		
		if ( $this->config->getConfig('CACHE_RESULTS', false, false) == false )
		{
			return null;
		}		
		
		$id = $this->getCacheID($query);
		
		$this->cache->set($id, serialize($results));
	}
	
	/**
	 * calculate query identifier
	 * 
	 * @param string|Query $query
	 */
	
	protected function getCacheID($query)
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
