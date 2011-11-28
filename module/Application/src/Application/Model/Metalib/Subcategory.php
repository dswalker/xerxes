<?php

/**
 * Metalib SubCategory
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Metalib_Subcategory extends Xerxes_Framework_DataValue
{
	public $metalib_id;
	public $name;
	public $sequence;
	public $category_id;
	public $databases = array();
	
	public function toXML()
	{
		$xml = new DOMDocument();
		$xml->loadXML("<subcategory />");
		$xml->documentElement->setAttribute("name", $this->name);
		$xml->documentElement->setAttribute("sequence", $this->sequence);
		$xml->documentElement->setAttribute("id", $this->metalib_id);
		
		foreach ( $this->databases as $database )
		{
			$import = $xml->importNode($database->toXML()->documentElement, true);
			$xml->documentElement->appendChild($import);
		}
		
		return $xml;
	}
}