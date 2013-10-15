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
 * Convenience class for basic JSON parsing
 * 
 * @author David Walker <dwalker@calstate.edu>
 */ 

class Json
{
	/**
	 * @var array
	 */
	private $json;
	
	/**
	 * New JSON object
	 * 
	 * @param string|array $json
	 */
	
	public function __construct($json)
	{
		if ( is_array($json) )
		{
			$this->json = $json;
		}
		else
		{
			$this->json = json_decode($json, true);
		}
	}
	
	/**
	 * Extract value from json
	 *
	 * @param string $path  path to the value
	 *
	 * @return string|null  null if not found
	 */
	
	public function extractValue($path)
	{
		$path = explode('/', $path);
		$pointer = $this->json;
	
		foreach ( $path as $part )
		{
			if ( array_key_exists($part, $pointer) )
			{
				$pointer = $pointer[$part];
			}
		}
	
		if ( is_array($pointer) )
		{
			return ""; // we didn't actually get our value
		}
		else
		{
			return strip_tags($pointer);
		}
	}
	
	/**
	 * Extract data from json
	 *
	 * @param string $path  path to the value
	 *
	 * @return array
	 */
	
	public function extractData($path)
	{
		$path = explode('/', $path);
		$pointer = $this->json;
	
		foreach ( $path as $part )
		{
			if ( array_key_exists($part, $pointer) )
			{
				$pointer = $pointer[$part];
			}
			else
			{
				return array();
			}
		}
	
		return $pointer;
	}	
}