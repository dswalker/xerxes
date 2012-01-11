<?php

namespace Application\Model\Metalib;

use Application\Model\KnowledgeBase\KnowledgeBase,
 	Application\Model\Search,
 	Xerxes\Metalib,
	Xerxes\Utility\Factory,
	Xerxes\Utility\Request;

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
	private static $client; // metalib client
	protected $knowledgebase; // metalib kb
	
	/**
	 * Create Metalib Search Engine
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		// metalib kb
		
		$this->knowledgebase = new KnowledgeBase();
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
	 * Metalib Client
	 * 
	 * Static here so we maintain the session id
	 */
	
	public static function getMetalibClient()
	{
		if ( ! self::$client instanceof Metalib )
		{
			$config = Config::getInstance();
			
			$address = $config->getConfig("METALIB_ADDRESS", true);
			$username = $config->getConfig("METALIB_USERNAME", true);
			$password = $config->getConfig("METALIB_PASSWORD", true);
	
			self::$client = new Metalib($address, $username, $password, Factory::getHttpClient());
		}
	
		return self::$client;
	}	
	
	/**
	 * Initiate the search
	 * 
	 * @param Search\Query $query
	 * @return Group
	 */
	
	public function search(Query $query)
	{
		// initiate search
				
		$group = new Group();
		$group->initiateSearch($query);
		
		return $group;
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
