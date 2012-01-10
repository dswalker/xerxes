<?php

namespace Application\Model\Metalib;

use Application\Model\KnowledgeBase\KnowledgeBase,
	Xerxes\Metalib,
	Xerxes\Utility\Factory;

/**
 * Metalib Search Group
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Group
{
	public $date; // date search was initialized
	public $id; // id for the group
	public $query; // metalib search query
	
	public $merged_set; // merged result set
	public $databases = array(); // databases together w/ resultset objects?
	public $facets; // facet object
	
	protected $config; // metalib config
	protected $client; // metalib client
	protected $knowledgebase; // metalib kb

	
	/**
	 * Create Metalib Search Group
	 * 
	 * @param Query $query
	 */
	
	public function __construct(Query $query)
	{
		$this->config = Config::getInstance(); // metalib config
		$this->client = $this->getMetalibClient(); // metalib client
		$this->knowledgebase = new KnowledgeBase(); // metalib kb
		
		$this->query = $query; // search query
		
		// flesh out database information from the kb
		
		$this->fillDatabaseInfo($query);
	}
	
	/**
	 * Initiate the search with Metalib for this Group
	 */
	
	public function initiateSearch()
	{
		$group_id = $this->client->search($this->query->toQuery(), $this->getSearchableDatabases() );
		
		$group->id = $group_id;
		$group->date = $this->getSearchDate();
	}
	
	/**
	 * Lazyload Metalib Client
	 */
	
	public function getMetalibClient()
	{
		if ( ! $this->client instanceof Metalib )
		{
			$address = $this->config->getConfig("METALIB_ADDRESS", true);
			$username = $this->config->getConfig("METALIB_USERNAME", true);
			$password = $this->config->getConfig("METALIB_PASSWORD", true);
				
			$this->client = new Metalib($address, $username, $password, Factory::getHttpClient());
		}
	
		return $this->client;
	}	
	
	/**
	 * Flesh out the request with database information from KB
	 *
	 * @throws \Exception
	 */
	
	protected function fillDatabaseInfo(Query $query)
	{
		// make sure we got some terms!
	
		if ( count($query->getQueryTerms()) == 0 )
		{
			throw new \Exception("No search terms supplied");
		}
	
		// databases or subject chosen
	
		$databases = $query->getDatabases();
		$subject = $query->getSubject();
		
	
		### populate the database information from KB
	
		// databases specifically supplied
	
		if ( count($databases) >= 0 )
		{
			$this->databases = $this->knowledgebase->getDatabases($databases);
		}
	
		// just a subject supplied, so get databases from that subject, yo!
	
		elseif ( count($databases) == 0 && $subject != null )
		{
			$search_limit = $this->config->getConfig( "SEARCH_LIMIT", true );
	
			$subject_object = $this->knowledgebase->getSubject($subject);
	
			// did we find a subject that has subcategories?
	
			if ( $subject_object != null && $subject_object->subcategories != null && count( $subject_object->subcategories ) > 0 )
			{
				$subs = $subject_object->subcategories;
				$subcategory = $subs[0];
				$index = 0;
					
				// get databases up to search limit from first subcategory
					
				foreach ( $subcategory->databases as $database_object )
				{
					if ( $database_object->searchable == 1 )
					{
						$this->databases[] = $database_object;
						$index++;
					}
	
					if ( $index >= $search_limit )
					{
						break;
					}
				}
			}
		}
	
		// make sure we have a scope, either databases or subject
	
		if ( count($databases) == 0 && $subject == null )
		{
			throw new \Exception("No databases or subject supplied");
		}
	}
	
	/**
	 * Get searchable database IDs
	 *
	 * @return array
	 */
	
	protected function getSearchableDatabases()
	{
		$databases_to_search = array();
		
		$user = $this->query->getUser();
	
		foreach ( $this->databases as $database_object )
		{
			// only include databases searched by user
			
			if ( $database_object->isSearchableByUser($user) )
			{
				$databases_to_search[] = $database_object->metalib_id; // @todo: switch to database_id
			}
		}
	
		return $databases_to_search;
	}
	
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
}
