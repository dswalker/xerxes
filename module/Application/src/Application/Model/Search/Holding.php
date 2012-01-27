<?php

namespace Application\Model\Search;

/**
 * Search Holding
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Holding
{
	private $data = array();
	
	/**
	 * Set a property for this item
	 * 
	 * @param string $name		property name
	 * @param mixed $value		the value
	 */	
	
	public function setProperty($name, $value)
	{
		if ( $name != "holding" && $name != "id" )
		{
			$this->data[$name] = $value;
		}
	}
	
	/**
	 * Serialize to Array
	 * 
	 * @return array
	 */
	
	public function toArray()
	{
		return $this->data;
	}	
}