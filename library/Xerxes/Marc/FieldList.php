<?php

/**
 * A generic class for MARC fields, implemented by datafield and subfield list objects
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: FieldList.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

abstract class Xerxes_Marc_FieldList implements Iterator 
{
	protected $list = array();
	protected $position = 0;
	
	public function addField($record)
	{
		array_push($this->list, $record);
	}
	
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

	public function __toString() // convenience method
	{
		$content = "";
		
		foreach ( $this->list as $field )
		{
			$content .= " " . $field->__toString();
		}
		
		return trim($content);
	}

	public function length()
	{
		return count($this->list);
	}
}