<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Google;

use Application\Model\Search;
use Xerxes\Mvc\Request;

/**
 * Search and retrieve records Google search appliance
 *
 * @author David Walker <dwalker@calstate.edu> 
 */

class Engine extends Search\Engine
{
	protected $server;
	
	/**
	 * Create new Google Appliance Search Engine
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		$this->server = $this->config->getConfig('google_address', true);
		$this->server = rtrim($this->server, '/');
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
		// cache
	
		$results = $this->getCachedResults($search);
	
		if ( $results == null )
		{
			$results = $this->doSearch( $search, $start, $max, $sort);
			$this->setCachedResults($results, $search);
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
	 * No individual records in Google
	 *
	 * @param string	record identifier
	 * @return ResultSet
	 */
	
	protected function doGetRecord( $id )
	{
		return new Search\ResultSet();
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
	
	protected function doSearch( Query $search, $start = 1, $max = 10, $sort = "")
	{
		$results = new Search\ResultSet($this->config);
		
		$query = $search->toQuery();
		
		$this->url = $this->server . "/search?q=" . urlencode($query);
		
		$xml = simplexml_load_file($this->url);
		
		// header("Content-type: text/xml"); echo $xml->saveXML(); exit;
		
		$x = 0;
		
		$results_array = $xml->xpath("//RES");
		
		if ( count($results_array) > 0 && $results_array !== false)
		{
			$results_xml = $results_array[0];
				
			$results->total = (int) $results_xml->M;
				
			foreach ( $results_xml->R as $result_xml )
			{
				if ( $x >= $max )
				{
					break;
				}
		
				$record = new Record();
				$record->loadXML($result_xml);
				
				$results->addRecord($record);
		
				$x++;
			}
		}
		
		return $results;		
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
