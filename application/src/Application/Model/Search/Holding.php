<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

use Xerxes\Utility\Parser;

/**
 * Search Holding
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Holding
{
	/**
	 * @var array
	 */
	
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