<?php

namespace Application\Model\Search;

use Xerxes\Utility\Parser;

/**
 * Search Item
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Item
{
	protected $bib_id; 		// the bibliographic record ID
    protected $availability; // boolean: is this item available for checkout?
    protected $status; 	// string describing the status of the item
    protected $location; // string describing the physical location of the item
    protected $reserve; // string indicating “on reserve” status – legal values: 'Y' or 'N'
    protected $callnumber; // the call number of this item
    protected $duedate; // string showing due date of checked out item (null if not checked out)
    protected $number; 	// the copy number for this item (note: although called “number”, 
    					// this may actually be a string if individual items are named rather than numbered)
    protected $barcode; // the barcode number for this item
	
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
	 * Serialize to XML
	 * 
	 * @return DOMDocument
	 */
	
	public function toXML() // @todo: replace with toArray
	{
		$xml = new \DOMDocument();
		$xml->loadXML("<item />");
		
		foreach ( $this as $key => $value )
		{
			if ( $value == "")
			{
				continue;
			}
			
			$key = preg_replace('/\W|\s/', '', $key);
			
			$element = $xml->createElement($key, Parser::escapeXml($value));
			$xml->documentElement->appendChild($element);
		}
		
		return $xml;
	}
}
