<?php

/**
 * Simple object to hold URL values
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: URL.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes_Framework
 */

class Xerxes_Framework_URL
{
	private $arrParams;
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $params
	 */
	
	public function __construct($params = array())
	{
		$this->params = $params;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $key		the name of the param
	 * @param string $value		the value to assign
	 */
	
	public function setParam($key, $value)
	{
		if ( array_key_exists( $key, $this->params ) )
		{
			if ( ! is_array( $this->params[$key] ) )
			{
				$this->params[$key] = array ($this->params[$key] );
			}
			
			array_push( $this->params[$key], $value );
		} 
		else
		{
			$this->params[$key] = $value;
		}
	}
	
	/**
	 * Remove a URL parameter
	 *
	 * @param string $key		the name of the param
	 * @param string $value		[optional] only if the param has this value
	 */
	
	public function removeParam($key, $value = "")
	{
		if ( array_key_exists( $key, $this->params ) )
		{
			$stored = $this->params[$key];
			
			// if this is an array, we need to find the right one
			
			if ( is_array( $stored ) )
			{
				for ( $x = 0; $x < count($stored); $x++ )
				{
					if ( $stored[$x] == $value )
					{
						unset($this->params[$key][$x]);
					}
				}
				
				// reset the keys
				
				$this->params[$key] = array_values($this->params[$key]);
			}
			else
			{
				unset($this->params[$key]);
			}
		} 
	}
	
	/**
	 * Return the internal array
	 *
	 * @return array
	 */
	
	public function toArray()
	{
		return $this->params;
	}
}
