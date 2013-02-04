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
 * Provides basic helper function for instatiated DataValue Objects
 *
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class DataValue
{
	/**
	 * Return all properties from the object as an array; excludes
	 * properties that are themselves arrays, those should be handled
	 * separately
	 *
	 * @param string $prefix		[optional] a value to prepend to the array keys
	 * @return array
	 */
	
	public function properties($prefix = null)
	{
		$return = array();
		$properties = get_object_vars($this);
		
		foreach ( $properties as $key => $value )
		{
			$array_key = $prefix . $key;
			
			// exclude arrays
			
			if ( ! is_array($value) )
			{
				$return[$array_key] = $value;
			}
		}
		
		return $return;
	}
	
	/**
	 * Assign values to the object's properties via a supplied associative
	 * array, where the array keys correspond to the properties, such as an
	 * individual result array from a PDO result set
	 *
	 * @param array $result
	 */
	
	public function load($result)
	{
		$properties = $this->properties();
		
		foreach ( $result as $key => $value )
		{
			// exclude the numeric keys as these are repetitions
			// of the data in PDO
			
			if ( is_string($key) )
			{
				if ( array_key_exists($key, $properties) )
				{
					$this->$key = $value;
				}
			}
		}
	}
}
