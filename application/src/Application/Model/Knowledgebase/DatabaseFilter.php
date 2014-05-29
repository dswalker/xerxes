<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Knowledgebase;

/**
 * Wrapper around database filter logic
 * 
 * All here so we can keep it in one place
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class DatabaseFilter
{
	/**
	 * @var Config
	 */
	protected $config;
	
	/**
	 * @var array
	 */
	protected $types_excluded = array();
	
	/**
	 * @var bool
	 */
	protected $should_filter_results = false;
	
	/**
	 * Create new Database Filter
	 */
	
	public function __construct()
	{
		$this->config = Config::getInstance();
		
		$this->types_excluded = explode(",", $this->config->getConfig("DATABASES_TYPE_EXCLUDE_AZ", false, array()) );
	}
	
	/**
	 * DQL query limiting databases to active and not expired
	 * 
	 * @param string $table  table name
	 * @return string
	 */
	
	public function getDqlQuery($table = 'd')
	{
		return "$table.active = 1 AND $table.date_trial_expiry IS NULL OR $table.date_trial_expiry > CURRENT_TIMESTAMP()";
	}
	
	/**
	 * SQL query limiting databases to active and not expired, also limits by type
	 * 
	 * @return string
	 */
	
	public function getSqlQuery()
	{
		$query  = '';
		
		if ( $this->should_filter_results == true )
		{
			$query = 'WHERE ( type IS NULL OR (';
				
			$x = 0;
				
			foreach ($this->types_excluded as $excluded )
			{
				if ( $x > 0 )
				{
					$query .= ' AND ';
				}
		
				$query .= " type <> '" . trim($excluded) . "'";
				$x++;
			}
				
			$query .= ')) AND active = 1 AND (date_trial_expiry IS NULL OR date_trial_expiry > NOW())';
		}
		
		return $query;
	}
			
	/**
	 * Filter array of Databases, limiting to active and not expired, also limits by type
	 * 
	 * @param array $databases Database[]
	 * @return array Database[]
	 */
	
	public function filterResults(array $databases)
	{
		if ( $this->should_filter_results == false )
		{
			return $databases;
		}
		
		$final = array();
		
		foreach ( $databases as $database )
		{
			// excluded by type
			
			$type = $database->getType();
			
			if ( in_array($type, $this->types_excluded) )
			{
				continue; 
			}
			
			// marked as in-active
			
			if ( $database->getActive() == false )
			{
				continue; 
			}
			
			// had trial expiry that has elapsed
			
			$expired = $database->getDateTrialExpiry();
			$now = new \DateTime("now");
			
			if ( $expired != null )
			{
				if ( $expired <= $now )
				{
					continue; 
				}
			}
			
			// none of the above true, so keep it
			
			$final[] = $database;
		}
		
		return $final;
	}
	
	/**
	 * Set to filter results
	 * 
	 * @param bool $bool
	 */
	
	public function setToFilter($bool)
	{
		$this->should_filter_results = $bool;
	}
}
