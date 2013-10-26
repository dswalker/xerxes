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
		$result = apc_store($this->institution . '_' . $id, $data, $expiry);
		
		if ( $result === false )
		{
			// @todo something
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
			$arrCache = array();
			
			foreach ( $id as $cache_id )
			{
				$arrCache[$cache_id] = apc_fetch($this->institution . '_' . $cache_id);
			}
			
			return $arrCache;
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
