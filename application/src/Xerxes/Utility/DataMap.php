<?php

namespace Xerxes\Utility;

/**
 * One PDO connection to rule them all
 */

global $xerxes_pdo;

/**
 * Basic functions for selecting, instering, updating, and deleting data from a 
 * database, including transactions; basically a convenience wrapper around PDO
 *
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 */

abstract class DataMap
{
	private $connection; // database connection info
	private $username; // username to connect with
	private $password; // password to connect with	
	private $sql = null;	// sql statement, here for debugging
	
	/**
	 *
	 * @var \PDO
	 */
	
	private $pdo;	

	/**
	 * @var Registry
	 */
	
	protected $registry;

	/**
	 * Create a Data Map
	 * 
	 * @param string $connection	[optional] database connection info
	 * @param string $username		[optional] username to connect with
	 * @param string $password		[optional] password to connect with
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
	 * @param string $connection		pdo connection string
	 * @param string $username			database username
	 * @param string $password			database password
	 */
	
	protected function init()
	{
		global $xerxes_pdo;
		
		if ( ! $xerxes_pdo instanceof \PDO )
		{
			// options to ensure utf-8
			
			$arrDriverOptions = array();
			$arrDriverOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8'";
			
			// data access object
			
			$xerxes_pdo = new \PDO($this->connection, $this->username, $this->password, $arrDriverOptions);
			
			// will force PDO to throw exceptions on error
			
			$xerxes_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			
			$this->pdo = $xerxes_pdo;
		}
		
		if ( ! $this->pdo instanceof \PDO )
		{
			$this->pdo = $xerxes_pdo;
		}
	}
	
	/**
	 * Return the pdo object for specific handling
	 *
	 * @return PDO 
	 */
	
	protected function getDatabaseObject()
	{
		$this->init();
		return $this->pdo;
	}
	
	/**
	 * Begin the database transaction
	 *
	 */
		
	public function beginTransaction()
	{
		$this->init();
		$this->pdo->beginTransaction();
	}
	
	/**
	 * Commit any outstanding database transactions
	 *
	 */

	public function commit()
	{
		$this->init();
		$this->pdo->commit();
	}
	
	/**
	 * Fetch all records from a select query
	 *
	 * @param string $sql		SQL query
	 * @param array $arrValues		paramaterized values
	 * @return array				array of results as supplied by PDO
	 */
	
	public function select($sql, $arrValues = null )
	{
		$this->init();
		
		$this->echoSQL($sql);
		
		$this->sql = $sql;
			
		$objStatement = $this->pdo->prepare($sql);
		
		if ( $arrValues != null )
		{
			foreach ($arrValues as $key => $value )
			{
				if ( is_object($value) )
				{
					throw new \Exception('Value cannot be an object');
				}
				
				$objStatement->bindValue( $key, $value);
			}
		}
		
		$objStatement->execute();
			
		return $objStatement->fetchAll();
	}
	
	/**
	 * Update rows in the database
	 *
	 * @param string $sql		SQL query
	 * @param array $arrValues		paramaterized values
	 * @return mixed				status of the request, as set by PDO
	 */
	
	public function update($sql, $arrValues = null )
	{
		$this->init();
		
		$this->echoSQL($sql);
		
		$this->sql = $sql;
		
		$objStatement = $this->pdo->prepare($this->sql);
		
		if ( $arrValues != null )
		{
			foreach ($arrValues as $key => $value )
			{
				$objStatement->bindValue( $key, $value);
			}
		}
		
		return $objStatement->execute();      
	}
	
	/**
	 * Insert rows in the database
	 *
	 * @param string $sql		SQL query
	 * @param array $arrValues		paramaterized values
	 * @param boolean $boolReturnPk  return the inserted pk value?
	 * @return mixed				if $boolReturnPk is false, status of the request (true or false), 
	 * 								as set by PDO. if $boolReturnPk is true, either the last inserted pk, 
	 * 								or 'false' for a failed insert. 
	 */
	
	public function insert($sql, $arrValues = null, $boolReturnPk = false)
	{
		$this->init();
		
		$status = $this->update($sql, $arrValues);      
		
		if ($status && $boolReturnPk)
		{
			// ms sql server specific code
			
			if ( $this->rdbms == "mssql" )
			{
				// this returns the last primary key in the 'session', per ms website,
				// which we hope to god is the id we just inserted above and not a 
				// different transaction; need to watch this closely for any racing conditions
				
				$results = $this->select("SELECT @@IDENTITY AS 'Identity'");
				
				if ( $results !== false )
				{
					return (int) $results[0][0];
				}
			}
			else
			{
				return $this->lastInsertId();
			}
		} 
		else
		{
			return $status;
		}
	}
	
	/**
	 * Delete rows in the database
	 *
	 * @param string $sql			SQL query
	 * @param array $arrValues		paramaterized values
	 * @return mixed				status of the request, as set by PDO
	 */
	
	protected function delete($sql, $arrValues = null)
	{
		return $this->update($sql, $arrValues);
	}
	
	/**
	 * Get the last inserted ID
	 */
	
	protected function lastInsertId()
	{
		$this->init();
		return $this->pdo->lastInsertId();
	}

	/**
	 * A utility method for adding single-value data to a table
	 *
	 * @param string $table_name		table name
	 * @param mixed $value_object		object derived from DataValue
	 * @param boolean $boolReturnPk  	default false, return the inserted pk value?
	 * @return bool 					false if failure. on success, true or inserted pk based on $boolReturnPk
	 */
	
	protected function doSimpleInsert($table_name, $value_object, $boolReturnPk = false)
	{
		$arrProperties = array();
		
		foreach ( $value_object->properties() as $key => $value )
		{
			if ( ! is_int($value) && $value == "" )
			{
				unset($value_object->$key);
			}
			else
			{
				$arrProperties[":$key"] = $value;
			}
		}
		
		$fields = implode( ",", array_keys( $value_object->properties() ) );
		$values = implode( ",", array_keys( $arrProperties ) );
		
		$sql = "INSERT INTO $table_name ( $fields ) VALUES ( $values )";
		
		return $this->insert( $sql, $arrProperties, $boolReturnPk );
	}

	/**
	 * For debugging
	 */
	
	private function echoSQL($sql)
	{
		// echo "<p>" . $sql . "</p>";
	}
}
