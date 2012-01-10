<?php

namespace Application\Model\Metalib;

use Application\Model\DataMap\Databases,
 	Application\Model\Search,
	Xerxes\Metalib,
	Xerxes\Utility\Factory,
	Xerxes\Utility\Registry,
	Xerxes\Utility\Request,
	Xerxes\Utility\User,
	Zend\Http\Client;

/**
 * Metalib Search Engine
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Engine extends Search\Engine
{
	protected $client; // metalib client
	protected $datamap; // xerxes datamap
	
	/**
	 * Create Metalib Search Engine
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		// metalib client
		
		$address = $this->config->getConfig("METALIB_ADDRESS", true);
		$username = $this->config->getConfig("METALIB_USERNAME", true);
		$password = $this->config->getConfig("METALIB_PASSWORD", true);
		
		// create the client
		
		$this->client = new Metalib($address, $username, $password, Factory::getHttpClient());
		
		// datamap
		
		$this->datamap = new Databases(); // @todo: use KB model instead?
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
	 * Initiate the search
	 * 
	 * @param Search\Query $search
	 */
	
	public function search(Query $search)
	{
		// add KB information to the request
		
		$search->fillDatabaseInfo();
		
		// initiate search
		
		$group_id = $this->client->search($search->toQuery(), $search->getSearchableDatabases() );
		
		$group = new Group();
		
		$group->id = $group_id;
		$group->date = $this->getSearchDate();
		$group->query = $search;
		
		print_r($group); exit;
	}
	
	public function checkStatus(Group $group)
	{
		$status_xml = $this->client->searchStatus($group->id);
	}
	
	/**
	 * Return the total number of hits for the search
	 *
	 * @return int
	 */
	
	public function getHits( Search\Query $search ) {}	// @todo: had to switch to Search\Query here php complained, why?
	
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
	
	public function searchRetrieve( Search\Query $search, $start = 1, $max = 10, $sort = "" ) {} // @todo: had to switch to Search\Query here php complained, why?
	
	/**
	 * Return an individual record
	 *
	 * @param string	record identifier
	 * @return Results
	 */
	
	public function getRecord( $id ) {}
	
	/**
	 * Get record to save
	 *
	 * @param string	record identifier
	 * @return int		internal saved id
	 */
	
	 public function getRecordForSave( $id ) {}
	
	
	 /**
	  * Calculate search date based on Metalib search flush
	  */
	 
	 protected function getSearchDate()
	 {
	 	$time = time();
	 	$hour = (int) date("G", $time);
	 	
	 	$flush_hour = $this->config->getConfig("METALIB_RESTART_HOUR", false, 4);
	 		
	 	if ( $hour < $flush_hour )
	 	{
	 		// use yesterday's date
	 		// by setting a time at least one hour greater than the flush hour,
	 		// so for example 5 hours ago if flush hour is 4:00 AM
	 			
	 		$time = $time - ( ($flush_hour + 1) * (60 * 60) );
	 	}
	 
	 	return date("Y-m-d", $time);
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
}
