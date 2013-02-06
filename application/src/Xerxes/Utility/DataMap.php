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
 * Data Map
 * 
 * Provides basic CRUD functions on the database; basically a convenience wrapper around PDO 
 * for operations that require speed and efficiency; otherwise use the ORM
 *
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class DataMap
{
	private $connection; // database connection info
	private $username; // username to connect with
	private $password; // password to connect with	
	protected $sql = null; // sql statement, here for debugging
	
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
		global $xerxes_pdo; // global so there is only one, for efficiency
		
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
	
	/**
	 * Begin the database transaction
	 */
		
	public function beginTransaction()
	{
		return $this->pdo()->beginTransaction();
	}
	
	/**
	 * Commit any outstanding database transactions
	 */

	public function commit()
	{
		return $this->pdo()->commit();
	}
	
	/**
	 * Fetch all records from a select query
	 *
	 * @param string $sql   SQL query
	 * @param array $values paramaterized values
	 * @return array        of results as supplied by PDO
	 */
	
	public function select($sql, array $values = null )
	{
		$this->logSql($sql);

		$statement = $this->pdo()->prepare($sql);
		
		if ( $values != null )
		{
			foreach ($values as $key => $value )
			{
				if ( is_object($value) )
				{
					throw new \Exception('Value cannot be an object');
				}
				
				$statement->bindValue( $key, $value);
			}
		}
		
		$statement->execute();
			
		return $statement->fetchAll();
	}
	
	/**
	 * Update rows in the database
	 *
	 * @param string $sql   SQL query
	 * @param array $values paramaterized values
	 * @return mixed        status of the request, as set by PDO
	 */
	
	public function update($sql, array $values = null )
	{
		$this->logSql($sql);
		
		$statement = $this->pdo()->prepare($this->sql);
		
		if ( $values != null )
		{
			foreach ($values as $key => $value )
			{
				$statement->bindValue( $key, $value);
			}
		}
		
		return $statement->execute();      
	}
	
	/**
	 * Insert rows in the database
	 *
	 * @param string $sql       SQL query
	 * @param array $values     paramaterized values
	 * @param bool $return_pk   return the inserted pk value?
	 * @return mixed            if $return_pk is false, status of the request (true or false), 
	 *                          as set by PDO. if $return_pk is true, either the last inserted pk, 
	 *                          or 'false' for a failed insert. 
	 */
	
	public function insert($sql, array $values = null, $return_pk = false)
	{
		$status = $this->update($sql, $values);      
		
		if ($status && $return_pk)
		{
			return $this->lastInsertId();
		} 
		else
		{
			return $status;
		}
	}
	
	/**
	 * Delete rows in the database
	 *
	 * @param string $sql   SQL query
	 * @param array $values paramaterized values
	 * @return mixed        status of the request, as set by PDO
	 */
	
	protected function delete($sql, $values = null)
	{
		return $this->update($sql, $values);
	}
	
	/**
	 * Get the last inserted ID
	 */
	
	protected function lastInsertId()
	{
		return $this->pdo()->lastInsertId();
	}

	/**
	 * A utility method for adding single-value data to a table
	 *
	 * @param string $table_name  table name
	 * @param mixed $value_object instance of DataValue
	 * @param bool $return_pk     default false, return the inserted pk value?
	 * @return bool               false if failure. on success, true or inserted pk based on $return_pk
	 */
	
	protected function doSimpleInsert($table_name, DataValue $value_object, $return_pk = false)
	{
		$properties = array();
		
		foreach ( $value_object->properties() as $key => $value )
		{
			if ( ! is_int($value) && $value == "" )
			{
				unset($value_object->$key);
			}
			else
			{
				$properties[":$key"] = $value;
			}
		}
		
		$fields = implode( ",", array_keys( $value_object->properties() ) );
		$values = implode( ",", array_keys( $properties ) );
		
		$sql = "INSERT INTO $table_name ( $fields ) VALUES ( $values )";
		
		return $this->insert( $sql, $properties, $return_pk );
	}

	/**
	 * For debugging
	 */
	
	private function logSql($sql)
	{
		$this->sql = $sql;
		
		// echo "<p>" . $sql . "</p>";
	}
}
