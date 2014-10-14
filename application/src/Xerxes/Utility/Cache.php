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
 * Application-wide Cache
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Cache
{
	protected $_data = array(); // this data already retrieved
	
	protected $cache;
	
	/**
	 * Application Cache
	 */
	
	public function __construct()
	{
		$registry = Registry::getInstance();
		
		$cache_type =  $registry->getConfig('CACHE_TYPE', false, 'Database');
		$cache_type = 'Xerxes\\Utility\\Cache\\' . ucfirst(strtolower($cache_type));
		
		if ( ! class_exists($cache_type) )
		{
			throw new \Exception("You specified a cache type of '$cache_type', but no such class exists");
		}
		
		$this->cache = new $cache_type();
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
		
		// now add it to the cache store
		
		$this->cache->set($id, $data, $expiry);
	}
	
	/**
	 * Get data from the cache
	 *
	 * @param string|array $id  unique id(s)
	 * @return string|array     you supply arrray you get back array
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
		
		// get it from the cache store
		
		return $this->cache->get($id);
	}
}
