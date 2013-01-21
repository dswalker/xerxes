<?php

namespace Application\Model\KnowledgeBase;

use Xerxes\Utility\DataValue,
	Xerxes\Utility\Parser;

/**
 * Metalib SubCategory
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Subcategory extends DataValue
{
	public $databases_id;
	public $name;
	public $sequence;
	public $category_id;
	public $databases = array();
	
	public function toXML()
	{
		$xml = Parser::convertToDOMDocument("<subcategory />");
		$xml->documentElement->setAttribute("name", $this->name);
		$xml->documentElement->setAttribute("sequence", $this->sequence);
		$xml->documentElement->setAttribute("id", $this->databases_id);
		
		foreach ( $this->databases as $database )
		{
			$import = $xml->importNode($database->toXML()->documentElement, true);
			$xml->documentElement->appendChild($import);
		}
		
		return $xml;
	}
}