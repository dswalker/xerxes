<?php

namespace Application\Model\Metalib;

use Application\Model\KnowledgeBase\Database,
	Application\Model\KnowledgeBase\KnowledgeBase,
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
	protected $date; // date search was initialized
	protected $id; // id for the group
	protected $query; // metalib search query
	
	protected $merged_set; // merged result set
	protected $result_sets = array(); // individual database result sets
	protected $excluded_databases = array(); // non-searchable databases
	
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
		$this->client = Engine::getMetalibClient(); // metalib client
		$this->knowledgebase = new KnowledgeBase($query->getLanguage()); // metalib kb
		
		$this->query = $query; // search query
		
		// flesh out database information from the kb
		
		$this->fillDatabaseInfo($query);
	}
	
	/**
	 * Initiate the search with Metalib for this Group
	 */
	
	public function initiateSearch()
	{
		$this->id = $this->client->search( $this->query->toQuery(), $this->getSearchableDatabases() );
		$this->date = $this->getSearchDate();
	}
	
	/*
	 * Check the status of the search
	 * 
	 * Updates resultsets with status information from Metalib
	 * 
	 * @return Status
	*/
	
	public function getSearchStatus()
	{
		$status = new Status();
		
		// get latest status from metalib
	
		$status_xml = $this->client->getSearchStatus($this->id);
		
		// parse response		
	
		$x_server_response = simplexml_import_dom($status_xml->documentElement);
	
		// cycle over the databases in the response
	
		foreach ( $x_server_response->find_group_info_response->base_info as $base_info )
		{
			// metalib id
				
			$database_id = (string) $base_info->base_001;
				
			// not here?
				
			if ( ! array_key_exists($database_id, $this->result_sets) )
			{
				throw new \Exception("Metalib group contained resultset '$database_id' not in local resultset");
			}
			
			## update resultset objects
				
			$result_set = $this->result_sets[$database_id];
			$result_set->set_number = (string) $base_info->set_number;
			$result_set->find_status = (string)  $base_info->find_status;
			$result_set->total = (int)  $base_info->no_of_documents; // @todo: see x1 for usual 'there were hits' madness
				
			// set this again explicitly
				
			$this->result_sets[$database_id] = $result_set;
			
			## add to status
			
			$status->addResultSet($result_set);
		}
		
		// see if search is finished
		
		$status->setFinished($this->client->isFinished());
		
		return $status;
	}
	
	/**
	 * Flesh out the request with database information from KB
	 *
	 * @throws \Exception
	 */
	
	protected function fillDatabaseInfo(Query $query)
	{
		// databases or subject chosen
	
		$databases = $query->getDatabases();
		$subject = $query->getSubject();
		
		// make sure we have a scope, either databases or subject
		
		if ( count($databases) == 0 && $subject == null )
		{
			throw new \Exception("No databases or subject supplied");
		}		
		
	
		### populate the database information from KB
	
		// databases specifically supplied
	
		if ( count($databases) > 0 )
		{
			$databases = $this->knowledgebase->getDatabases($databases);
			
			foreach ( $databases as $database_object )
			{
				$this->addDatabase($database_object);
			}
		}
	
		// just a subject supplied, so get databases from that subject, yo!
	
		elseif ( $subject != null )
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
						$this->addDatabase($database_object);
						$index++;
					}
	
					if ( $index >= $search_limit )
					{
						break;
					}
				}
			}
		}
	}
	
	/**
	 * Add database to group
	 * 
	 * Assigns non-searchable databases to excluded list
	 * 
	 * @param Database $database_object
	 */
	
	public function addDatabase(Database $database_object)
	{
		$id = $database_object->metalib_id; // @todo: switch to database_id
		
		$user = $this->query->getUser();
		
		// see if this database is searchable
		
		if ( $database_object->isSearchableByUser($user) )
		{
			$this->result_sets[$id] = new ResultSet($this->config, $database_object);
		}
		else // dump it into the excluded pile
		{
			$this->excluded_databases[$id] = $database_object;
		}
	}
	
	/**
	 * Get searchable database IDs
	 *
	 * @return array
	 */
	
	protected function getSearchableDatabases()
	{
		return array_keys($this->result_sets);
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
	
	public function getResultSets()
	{
		return $this->result_sets;
	}
	
	public function getId()
	{
		return $this->id;
	}
}
