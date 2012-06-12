<?php

namespace Xerxes\Utility;

/**
 * Provides basic helper function for instatiated Value Objects
 *
 * @abstract
 * @author David Walker
 * @copyright 2008 California State University
 * @version
 * @package  Xerxes_Framework
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
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
		$arrReturn = array();
		$arrProperties = get_object_vars($this);
		
		foreach ( $arrProperties as $key => $value )
		{
			$array_key = $prefix . $key;
			
			// exclude arrays
			
			if ( ! is_array($value) )
			{
				$arrReturn[$array_key] = $value;
			}
		}
		
		return $arrReturn;
	}
	
	/**
	 * Assign values to the object's properties via a supplied associative
	 * array, where the array keys correspond to the properties, such as an
	 * individual result array from a PDO result set
	 *
	 * @param array $arrResult
	 */
	
	public function load($arrResult)
	{
		$arrProperties = $this->properties();
		
		foreach ( $arrResult as $key => $value )
		{
			// exclude the numeric keys as these are repetitions
			// of the data in PDO
			
			if ( is_string($key) )
			{
				if ( array_key_exists($key, $arrProperties) )
				{
					$this->$key = $value;
				}
			}
		}
	}
}
