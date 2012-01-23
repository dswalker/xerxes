<?php

namespace Xerxes\Marc;

/**
 * A generic class for MARC fields, implemented by datafield and subfield list objects
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class FieldList implements \Iterator 
{
	protected $list = array();
	protected $position = 0;
	
	/**
	 * Add a Field to the list
	 * 
	 * @param Field $field
	 */
	
	public function addField(Field $field)
	{
		array_push($this->list, $field);
	}
	
	/**
	 * Retrieve a Field from the specified position
	 * 
	 * @param int $position
	 */
	
	public function item($position)
	{
		if ( array_key_exists($position, $this->list) )
		{
			return $this->list[$position];
		}
		else
		{
			return null;
		}
	}
	
	public function rewind()	// iterator interface
	{
		$this->position = 0;
	}
	
	public function current() // iterator interface
	{
		return $this->list[$this->position];
	}
	
	public function key() // iterator interface
	{
		return $this->position;		
	}
	
	public function next() // iterator interface
	{
		++$this->position;
	}
	
	public function valid() // iterator interface
	{
		return isset($this->list[$this->position]);
	}
	
	/**
	 * Return list as string separated by space
	 * 
	 * @return string
	 */

	public function __toString() // convenience method
	{
		$content = "";
		
		foreach ( $this->list as $field )
		{
			$content .= " " . $field->__toString();
		}
		
		return trim($content);
	}
	
	/**
	 * Get the list's length
	 * 
	 * @return int
	 */

	public function length()
	{
		return count($this->list);
	}
}