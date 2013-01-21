<?php

namespace Xerxes\Utility;

/**
 * Application-wide Cache
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Cache extends DataMap
{
	private $_data = array(); // this data already retrieved
	
	/**
	 * Set data in the cache
	 *
	 * @param string $id		unique id
	 * @param string $data		data to add to cache
	 * @param int $expiry		[optional] timestamp when this data should expire
	 */
	
	public function set( $id, $data, $expiry = null )
	{
		// ensure proper data
		
		if ( $id == "" )
		{
			throw new \InvalidArgumentException("you must supply an id for the cache");
		}
		
		if ( $data === "" )
		{
			throw new \InvalidArgumentException("cached data was empty");
		}		
		
		// if no expiry specified, set a 6 hour cache

		if ( $expiry == null )
		{
			$expiry = time() + (6 * 60 * 60);
		}
		
		// first save it within the scope of this request
		
		$this->_data[$id] = $data;
		
		// now add it to the database too
		
		$this->beginTransaction(); // wrap this in a transaction
		
		// set-up the data
		
		$arrParams = array();
		$arrParams[":id"] = $id;

		// delete any previously stored value under this id		
		
		$strSQL = "DELETE FROM xerxes_cache WHERE id = :id";
		$this->delete( $strSQL, $arrParams );
		
		// now insert the new value

		$arrParams[":data"] = $data;
		$arrParams[":timestamp"] = time();
		$arrParams[":expiry"] = $expiry;		
		
		$strSQL = "INSERT INTO xerxes_cache (id, data, timestamp, expiry) VALUES (:id, :data, :timestamp, :expiry)";
		$this->insert($strSQL, $arrParams);
		
		$this->commit();
	}
	
	/**
	 * Get data from the cache
	 *
	 * @param string or array $id		unique id(s)
	 * @return mixed					string of cache data if string supplied, 
	 * 									otherwise array of data if supplied array
	 */	
	
	public function get($id)
	{
		// integrity check
		
		if ( is_array($id) ) // id can be specified as array 
		{
			if ( count($id) == 0 )
			{
				throw new \InvalidArgumentException("no id specified in cache call");
			}
		}
		elseif ( $id == "" ) // or string
		{
			throw new \InvalidArgumentException("no id specified in cache call");
		}

		// first check if it exists in the scope of our request
		
		if ( ! is_array($id) ) // but only if this is not an array
		{		
			if ( array_key_exists($id, $this->_data) )
			{
				return $this->_data[$id];
			}
		}
		
		// otherwise we're going to the database
		
		$now = time();
		$arrParams = array();
		$arrCache = array ();
		
		// set up the query
		
		$strSQL = "SELECT * FROM xerxes_cache WHERE expiry > :expiry ";
		
		$arrParams[":expiry"] = $now;
		
		if ( is_array($id) )
		{
			// grab any of the ids supplied
			
			$strSQL .= " AND (";
			
			for ( $x = 0 ; $x < count( $id ) ; $x ++ )
			{
				if ( $x > 0 )
				{
					$strSQL .= " OR";
				}
				
				$strSQL .= " id = :id$x ";
				$arrParams[":id$x"] = $id[$x];
			}
			
			$strSQL .= ")";
		}
		else
		{
			// just the id supplied
			
			$strSQL .= " AND id = :id";
			$arrParams[":id"] = $id;
		}
		
		// get just the data
		
		$arrResults = $this->select( $strSQL, $arrParams );
		
		foreach ( $arrResults as $arrResult )
		{
			$arrCache[$arrResult['id']] = $arrResult['data'];
		}
		
		// you supply array, we return array
		
		if ( is_array($id) )
		{
			return $arrCache;
		}
		else // you supply string, we return just the (string) data
		{
			if ( count($arrCache) > 0 )
			{
				$this->_data[$id] = $arrCache[$id]; // also save it in scope of this request
				return $arrCache[$id];
			}
			else
			{
				return null;
			}
		}
	}
	
	/**
	 * Clear out old items in the cache
	 *
	 * @param int $timestamp		[optional] clear only objects older than a given timestamp
	 * @return int					SQL status code
	 */
	
	public function prune($timestamp = "")
	{
		$arrParams = array();
		
		if ( $timestamp == null )
		{
			$timestamp = time() - (2 * 24 * 60 * 60); // default timestamp is two days previous
		}
		
		$arrParams[":timestamp"] = $timestamp;
		
		$strSQL = "DELETE FROM xerxes_cache WHERE timestamp < :timestamp";
		
		return $this->delete( $strSQL, $arrParams );
	}
}
