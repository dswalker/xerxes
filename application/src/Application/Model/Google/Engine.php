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
use Xerxes\Utility\Factory;

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
	
	protected function doSearch( Search\Query $search, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		$results = new Search\ResultSet($this->config);
		
		$query = $search->toQuery();
		
		$this->url = $this->server . "/search?q=" . urlencode($query);
		
		if ( $this->config->getConfig('client') )
		{
			$this->url .= '&client=' . urlencode($this->config->getConfig('client'));
		}
		
		if ( $this->config->getConfig('site') )
		{
			$this->url .= '&site=' . urlencode($this->config->getConfig('site'));
		}
		
		$client = Factory::getHttpClient();
		$google_results = $client->getUrl($this->url, 3);
		
		$xml = simplexml_load_string($google_results);
		
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
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	/**
	 * Google search query object
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
