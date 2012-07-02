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
 * @version
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
	
	/**
	 * Create a MARC Record
	 */
	
	public function __construct()
	{
		$this->leader = new Leader();
	}
	
	/**
	 * Load from MARC-XML source
	 *
	 * @param string|DOMNode|DOMDocument $node
	 */
	
	public function loadXML($node = null)
	{
		if ( $node != null )
		{
			// make sure we have a DOMDocument
			
			$this->document = Parser::convertToDOMDocument($node);
			
			// extract the three data types
			
			$leader = $this->document->getElementsByTagName("leader");
			$control_fields = $this->document->getElementsByTagName("controlfield");
			$data_fields = $this->document->getElementsByTagName("datafield");
			
			// leader
			
			$this->leader = new Leader($leader->item(0));
			
			// control fields
			
			foreach ( $control_fields as $control_field )
			{
				$controlfield = new ControlField($control_field);
				array_push($this->_controlfields, $controlfield);
			}
			
			// data fields
			
			foreach ( $data_fields as $data_field )
			{
				$datafield = new DataField($data_field);
				array_push($this->_datafields, $datafield);
			}
		}
	}
	
	/**
	 * Retrieve the Leader
	 * 
	 * @return Leader
	 */
	
	public function leader()
	{
		return $this->leader;
	}
	
	/**
	 * Retrieve a Control Field
	 *
	 * @param string $tag			the marc tag number
	 * 
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
	 * Return a list of control fields
	 * 
	 * Essentially for the 007, the only control field (in theory) that is repeatable
	 *
	 * @param string $tag			[optional] the marc tag number
	 * 
	 * @return FieldList object
	 */	
	
	public function controlfields($tag = "")
	{
		$list = new FieldList();
		
		foreach ( $this->_controlfields as $controlfield )
		{
			if ( $controlfield->tag == $tag || $tag == "")
			{
				$list->addField($controlfield);
			}
		}

		return $list;
	}

	/**
	 * Retrieve a list of Data Fields
	 *
	 * @param string $tag			[optional] the marc tag number
	 * @param string $ind1			[optional] first indicator
	 * @param string $ind2			[optional] second indicator
	 * 
	 * @return DataFieldList
	 */
	
	public function datafield($tag = "", $ind1 = null, $ind2 = null)
	{
		$regex = str_replace("X", "[0-9]{1}", $tag);
		
		$list = new DataFieldList();
		
		foreach ( $this->_datafields as $datafield )
		{
			if ( preg_match("/$regex/", $datafield->tag) || $tag  == "")
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
	 * Run an XPath query against this Record
	 *
	 * @param string $query		xpath
	 * 
	 * @return \DOMNodeLIst
	 */
	
	public function xpath($query)
	{
		return $this->getXPathObject()->query($query, $this->node);
	}
	
	/**
	 * Convenience method for returning a group of subfield values as array
	 *
	 * @param string $tag			the marc tag number
	 * @param string $subfield		[optional] subfield, assumes all if null
	 * @param string $ind1			[optional] first indicator
	 * @param string $ind2			[optional] second indicator
	 * 
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
	
	/**
	 * Retrieve the MARC Record as MARC-XML
	 * 
	 * @return DOMDocument
	 */
	
	public function getMarcXML()
	{
		if ( ! $this->document instanceof \DOMDocument )
		{
			// we've created this MARC record from our own objects
			// instead of a marc-xml source, so create marc-xml now
			
			$this->document = Parser::convertToDOMDocument('<record xmlns="' . $this->namespace. '" />');
			
			// leader
			
			if ( $this->leader instanceof Leader )
			{
				$leader_xml = $this->document->createElementNS($this->namespace, "leader", $this->leader->value);
				$this->document->appendChild($leader_xml);
			}
			
			// control fields
			
			foreach ( $this->controlfields() as $controlfield )
			{
				$controlfield_xml = $this->document->createElementNS($this->namespace, "controlfield", $controlfield->value);
				$controlfield_xml->setAttribute("tag", $controlfield->tag);
				$this->document->appendChild($controlfield_xml);	
			}

			// data fields
			
			foreach ( $this->datafield() as $datafield )
			{
				$datafield_xml = $this->document->createElementNS($this->namespace, "datafield");
				$datafield_xml->setAttribute("tag", $datafield->tag);
				$this->document->appendChild($datafield_xml);
				
				// subfields
				
				foreach ( $datafield->subfield() as $subfield )
				{
					$subfield_xml = $this->document->createElementNS($this->namespace, "subfield", $subfield->value);
					$subfield_xml->setAttribute("code", $subfield->code);
					$datafield_xml->appendChild($subfield_xml);
				}
			}		
		}
		
		return $this->document;
	}
	
	/**
	 * Lazy load DOMXPath object
	 */
	
	protected function getXPathObject()
	{
		if ( ! $this->xpath instanceof \DOMXPath )
		{
			// create an xpath object
			
			$this->xpath = new \DOMXPath($this->getMarcXML());
			$this->xpath->registerNamespace("marc", $this->namespace);
		}
		
		return $this->xpath;
	}
	
	/**
	 * Retrieve the MARC Record as MARC-XML
	 * 
	 * @return string
	 */
	
	public function getMarcXMLString()
	{
		return $this->getMarcXML()->saveXML();
	}
	
	/**
	 * Set the Leader for the Record
	 *
	 * @param Leader $leader
	 */
	
	public function setLeader(Leader $leader)
	{
		$this->leader = $leader;
	}	
	
	/**
	 * Add a Control Field to the Record
	 * 
	 * @param ControlField $field
	 */
	
	public function addControlField(ControlField $field)
	{
		array_push($this->_controlfields, $field);
	}
	
	/**
	 * Add a Data Field to the Record
	 * 
	 * @param DataField $field
	 */
	
	public function addDataField(DataField $field)
	{
		array_push($this->_datafields, $field);
	}
	
}