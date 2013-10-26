<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Utility\Cache;

use Xerxes\Utility\DataMap;

/**
 * Database Cache
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Database extends DataMap
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
		// set-up the data
		
		$arrParams = array();
		$arrParams[":id"] = $id;
		$arrParams[":data"] = serialize($data); // we always serialize the value
		$arrParams[":timestamp"] = time();
		$arrParams[":expiry"] = $expiry;
		
		// insert or replace any previous value
		
		$strSQL = "REPLACE INTO xerxes_cache (id, data, timestamp, expiry) VALUES (:id, :data, :timestamp, :expiry)";
		$this->insert($strSQL, $arrParams);
	}
	
	/**
	 * Get data from the cache
	 *
	 * @param string|array $id  unique id(s)
	 * @return string|array     you supply arrray you get back array
	 */	
	
	public function get($id)
	{
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
			$arrCache[$arrResult['id']] = unserialize($arrResult['data']); // always unserialize it
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
