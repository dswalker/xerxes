<?php

namespace Application\Model\Search;

/**
 * Search Holding
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Holding.php 1709 2011-02-25 15:54:04Z dwalker@calstate.edu $
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
	 * Serialize to XML
	 * 
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
		$xml = new \DOMDocument();
		$xml->loadXML("<holding />");
		
		foreach ( $this->data as $key => $value )
		{
			$element = $xml->createElement("data");
			$element->setAttribute("key", $key);
			$element->setAttribute("value", $value);
			$xml->documentElement->appendChild($element);
		}
		
		return $xml;
	}	
	
}