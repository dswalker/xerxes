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

use Xerxes\Utility\Registry;

/**
 * APC Cache
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Apc
{
	private $institution; // xerxes institution id
	
	/**
	 * APC Cache
	 */
	
	public function __construct()
	{
		if ( ! function_exists('apc_store') )
		{
			throw new \Exception('You are using the APC cache in Xerxes, but apc is not installed on your system');
		}
		
		$registry = Registry::getInstance();
		
		$institution = $registry->getConfig('APPLICATION_NAME', false, 'xerxes');
		$this->institution = strtolower(preg_replace('/\W/', '_', $institution));
	}
	
	/**
	 * Set data in the cache
	 *
	 * @param string $id		unique id
	 * @param string $data		data to add to cache
	 * @param int $expiry		[optional] timestamp when this data should expire
	 */
	
	public function set( $id, $data, $expiry = null )
	{
		$expiry = $expiry - time();
		
		if ( $expiry < 0 )
		{
			$expiry = 0;
		}
		
		$result = apc_store($this->institution . '_' . $id, $data, $expiry);
		
		if ( $result === false )
		{
			throw new \Exception('Why?');
		}
	}
	
	/**
	 * Get data from the cache
	 *
	 * @param string|array $id  unique id(s)
	 * @return string|array     you supply arrray you get back array
	 */	
	
	public function get($id)
	{
		if ( is_array($id) )
		{
			$cache_array = array();
			$query = array();
			
			// add the institution prefix here so we can send all id's
			// to apc with a singlt apc_fetch call
			
			foreach ( $id as $cache_id )
			{
				$query[] = $this->institution . '_' . $cache_id;
			}
			
			$values = apc_fetch($query);
			
			// now remove the institution prefix so we return the
			// supplied id's
			
			foreach ( $values as $id => $object )
			{
				$id = str_replace($this->institution . '_' , '', $id);	
				$cache_array[$id] = $object;
			}
			
			return $cache_array;
		}
		else
		{
			$value = apc_fetch($this->institution . '_' . $id);
				
			if ( $value === false )
			{
				return null;
			}
			else
			{
				$this->_data[$id] = $value; // also save it in scope of this request
				return $value;
			}
		}
	}
}
