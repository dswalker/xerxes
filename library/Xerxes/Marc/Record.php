<?php

namespace Xerxes\Marc;

use Xerxes\Utility\Parser;

/**
 * Parse single MARC-XML record
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Record.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Record
{
	private $leader;
	private $namespace = "http://www.loc.gov/MARC21/slim";
	private $_controlfields = array();
	private $_datafields = array();
	
	protected $document;
	protected $xpath;
	protected $node;
	
	public function __construct()
	{
		$this->leader = new Leader();
	}
	
	/**
	 * Create an object for a MARC-XML Record
	 *
	 * @param \DOMNode $objNode
	 */
	
	public function loadXML($node = null)
	{
		if ( $node != null )
		{
			$objNode = Parser::convertToDOMDocument($node);
			
			$objLeader = $objNode->getElementsByTagName("leader");
			$objControlFields = $objNode->getElementsByTagName("controlfield");
			$objDataFields = $objNode->getElementsByTagName("datafield");
			
			$this->leader = new Leader($objLeader->item(0));
			
			foreach ( $objControlFields as $objControlField )
			{
				$controlfield = new ControlField($objControlField);
				array_push($this->_controlfields, $controlfield);
			}
			
			foreach ( $objDataFields as $objDataField )
			{
				$datafield = new DataField($objDataField);
				array_push($this->_datafields, $datafield);
			}
			
			// register it
			
			$this->document = $objNode;
			$this->node = $this->document->documentElement;
				
			// now create an xpath object and the current node as properties
			// so we can query based on this node, not the wrapper parent
			// see the xpath() function below
			
			$this->xpath = new \DOMXPath($this->document);
			$this->xpath->registerNamespace("marc", $this->namespace);
		}
	}
	
	/**
	 * Leader
	 * 
	 * @return Leader
	 */
	
	public function leader()
	{
		return $this->leader;
	}
	
	/**
	 * Control field
	 *
	 * @param string $tag			the marc tag number
	 * @return ControlField object
	 */
	
	public function controlfield($tag)
	{
		foreach ( $this->_controlfields as $controlfield )
		{
			if ( $controlfield->tag == $tag )
			{
				return $controlfield;
			}
		}
		
		// didn't find it, so return empty one
		
		return new ControlField();
	}

	/**
	 * Return a list of control fields, essentially for 007
	 *
	 * @param string $tag			the marc tag number
	 * @return FieldList object
	 */	
	
	public function controlfields($tag)
	{
		$list = new FieldList();
		
		foreach ( $this->_controlfields as $controlfield )
		{
			if ( $controlfield->tag == $tag )
			{
				$list->addField($controlfield);
			}
		}

		return $list;
	}

	/**
	 * Data Field
	 *
	 * @param string $tag			the marc tag number
	 * @param string $ind1			[optional] first indicator
	 * @param string $ind2			[optional] second indicator
	 * @return DataFieldList
	 */
	
	public function datafield($tag, $ind1 = null, $ind2 = null)
	{
		$regex = str_replace("X", "[0-9]{1}", $tag);
		
		$list = new DataFieldList();
		
		foreach ( $this->_datafields as $datafield )
		{
			if ( preg_match("/$regex/", $datafield->tag) )
			{
				if ( ( $ind1 == null || $ind1 == $datafield->ind1 )
					&& ( $ind2 == null || $ind2 == $datafield->ind2 ) ) 
				{
					$list->addField( $datafield );
				}
			}
		}
		
		return $list;
	}

	/**
	 * Run an xpath query against this MARC-XML record
	 *
	 * @param string $query		xpath
	 * @return \DOMNodeLIst
	 */
	
	public function xpath($query)
	{
		return $this->xpath->query($query, $this->node);
	}
	
	/**
	 * Convenience method for returning a group of subfield values as array
	 *
	 * @param string $tag			the marc tag number
	 * @param string $subfield		[optional] subfield, assumes all if null
	 * @param string $ind1			[optional] first indicator
	 * @param string $ind2			[optional] second indicator
	 * @return array
	 */
	
	public function fieldArray($tag, $subfield_code = "", $ind1 = "", $ind2 = "")
	{
		$return = array();
		
		foreach ( $this->datafield($tag, $ind1, $ind2) as $field )
		{
			foreach ( $field->subfield($subfield_code) as $subfield )
			{
				array_push($return, (string) $subfield );
			}
		}
		
		return $return;
	}
	
	public function getMarcXML()
	{	
		return $this->document;
	}
	
	public function getMarcXMLString()
	{
		return $this->document->saveXML();
	}
	
	public function addControlField(ControlField $field)
	{
		array_push($this->_controlfields, $field);
	}
	
	public function addDataField(DataField $field)
	{
		array_push($this->_datafields, $field);
	}
	
}