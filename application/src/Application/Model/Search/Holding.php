<?php

namespace Application\Model\Search;

use Xerxes\Utility\Parser;

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
	 * Serialize to XML
	 * 
	 * @return array
	 */
	
	public function toXML()
	{
		$xml = Parser::convertToDOMDocument('<holding />');
		
		foreach ( $this->data as $name => $value )
		{
			$line = $xml->createElement('data', Parser::escapeXml($value));
			$line->setAttribute('key', $name);
			$xml->documentElement->appendChild($line);
		}
		
		return $xml;
	}	
}