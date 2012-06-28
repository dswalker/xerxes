<?php

namespace Application\Model\Search;

use Xerxes\Utility\Parser;

/**
 * Electronic Resource
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ElectronicResource
{
	protected $database;
	protected $coverage;
	protected $link;
	protected $package;	
	
	/**
	 * Set a property for this item
	 * 
	 * @param string $name		property name
	 * @param mixed $value		the value
	 */
    
	public function setProperty($name, $value)
	{
		if ( property_exists($this, $name) )
		{
			$this->$name = $value;
		}
	}

	/**
	 * Get a property from this item
	 * 
	 * @param string $name		property name
	 * @return mixed the value
	 */
	
	public function getProperty($name)
	{
		if ( property_exists($this, $name) )
		{
			return $this->$name;
		}
		else
		{
			throw new \Exception("trying to access propety '$name', which does not exist");
		}
	}
	
	/**
	 * Serialize to Array
	 * 
	 * @return array
	 */
	
	public function toArray()
	{
		$array = array();
		
		foreach ( $this as $key => $value )
		{
			if ( $value == "")
			{
				continue;
			}
			
			$array[$key] = $value;
		}
		
		return $array;
	}
}
