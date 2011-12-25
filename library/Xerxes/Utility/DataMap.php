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
 * @abstract
 * @author David Walker
 * @copyright 2008 California State University
 * @version
 * @package  Xerxes_Framework
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

abstract class DataMap
{
	private $pdo; // pdo data object
	
	private $connection; // database connection info
	private $username; // username to connect with
	private $password; // password to connect with	
	
	private $sql = null;	// sql statement, here for debugging
	private $arrValues = array(); // values passed to insert or update statement, here for debugging

	protected $registry; // registry object, here for convenience
	protected $rdbms; // the explicit rdbms name (should be 'mysql' or 'mssql' as of 1.5.1) 

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
		
		// pdo can't tell us which rdbms we're using exactly, especially for 
		// ms sql server, since we'll be using odbc driver, so we make this
		// explicit in the config
		
		$this->rdbms = $this->registry->getConfig("RDBMS", false, "mysql");
		
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
			
			if ( $this->rdbms == "mysql" )
			{
				// php 5.3.0 and 5.3.1 have a bug where this is not defined
				
				if ( ! defined("PDO::MYSQL_ATTR_INIT_COMMAND") )
				{
					$init_command = 1002;
				}
				else
				{
					$init_command = \PDO::MYSQL_ATTR_INIT_COMMAND;
				}
				
				$arrDriverOptions[$init_command] = "SET NAMES 'utf8'";
			}
			
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
	
	public function select($sql, $arrValues = null, $arrClean = null)
	{
		$this->init();
		
		$this->sqlServerFix($sql, $arrValues, $arrClean);
		
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
	
	public function update($sql, $arrValues = null, $arrClean = null)
	{
		$this->init();
		
		$this->sqlServerFix($sql, $arrValues, $arrClean);
		
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
	
	public function insert($sql, $arrValues = null, $boolReturnPk = false, $arrClean = null)
	{
		$this->init();
		
		$this->sqlServerFix($sql, $arrValues, $arrClean);
		
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
	 * @return false if failure. on success, true or inserted pk based on $boolReturnPk
	 */
	
	protected function doSimpleInsert($table_name, $value_object, $boolReturnPk = false)
	{
		$arrProperties = array ( );
		
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
	
	/**
	 * Nasty hacks for MS SQL Server
	 * 
	 * @param string $sql	SQL statement
	 * @param array $params		bound parameters
	 * @param array $clean		parameters to clean
	 */
	
	private function sqlServerFix(&$sql, &$params, $clean = null)
	{
		// a bug in the sql server native client makes this necessary, barf!
		
		if ( $this->rdbms == "mssql")
		{
			// these values need cleaning, likely because they are in a sub-query
			
			if( is_array($clean) )
			{
				$dirtystuff = array("\"", "\\", "/", "*", "'", "=", "#", ";", "<", ">", "+");
				
				foreach ( $params as $key => $value )
				{
					if ( in_array($key, $clean) )
					{
						$value = str_replace($dirtystuff, "", $value); 
						$sql = str_replace($key, "'$value'", $sql);
						unset($params[$key]);
					}
				}
			}
		}
	}
}
