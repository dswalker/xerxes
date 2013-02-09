<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Utility;

/**
 * One PDO connection to rule them all
 * @var \PDO
 */

global $xerxes_pdo;

/**
 * DatabaseConnection
 *
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class DatabaseConnection
{
	private $connection; // database connection info
	private $username; // username to connect with
	private $password; // password to connect with	
	
	/**
	 * @var Registry
	 */
	
	protected $registry;

	/**
	 * Create a Data Map
	 * 
	 * @param string $connection [optional] database connection info
	 * @param string $username   [optional] username to connect with
	 * @param string $password   [optional] password to connect with
	 */
	
	public function __construct($connection = null, $username = null, $password = null)
	{
		$this->registry = Registry::getInstance();
		
		// take conn and credentials from config, unless overriden in constructor
		
		if ( $connection == null) $connection = $this->registry->getConfig( "DATABASE_CONNECTION", true );
		if ( $username == null ) $username = $this->registry->getConfig( "DATABASE_USERNAME", true );
		if ( $password == null ) $password = $this->registry->getConfig( "DATABASE_PASSWORD", true );
		
		// assign it
		
		$this->connection = $connection;
		$this->username = $username;
		$this->password = $password;
	}	
	
	/**
	 * Lazy load initialization of the database object
	 *
	 * @return \PDO
	 */
	
	protected function pdo()
	{
		global $xerxes_pdo; // global so there is only one connection at a time, for efficiency
		
		if ( ! $xerxes_pdo instanceof \PDO )
		{
			// options to ensure utf-8
			
			$driver_options = array();
			$driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8'";
			
			// make it!
			
			$xerxes_pdo = new \PDO($this->connection, $this->username, $this->password, $driver_options);
			
			// will force PDO to throw exceptions on error
			
			$xerxes_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		
		return $xerxes_pdo;
	}
}
