<?php

/**
 * Parse multiple MARC-XML records contained in a single xml document
 * 
 * @author David Walker
 * @copyright 2009 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Marc.php 1483 2010-11-12 17:02:44Z dwalker@calstate.edu $
 * @todo ->__toString() madness below due to php 5.1 object-string casting problem
 * @package Xerxes
 */

class Xerxes_Marc_Document
{
	protected $namespace = "http://www.loc.gov/MARC21/slim";
	protected $_length = 0;
	protected $_records = array();
	protected $record_type = "Xerxes_Marc_Record";
	
	/**
	 * Load a MARC-XML document from string or object
	 *
	 * @param mixed $xml	XML as string or DOMDocument
	 */
	
	public function loadXML($xml)
	{
		$objDocument = new DOMDocument();
		$objDocument->recover = true;
		
		if ( is_string($xml) )
		{
			$objDocument->loadXML($xml);
		}
		elseif ( $xml instanceof DOMDocument )
		{
			$objDocument = $xml;
		}
		else
		{
			throw new Exception("param 1 must be XML of type DOMDocument or string");
		}
		
		$this->parse($objDocument);
	}
	
	/**
	 * Load a MARC-XML document from file
	 *
	 * @param string $file		location of file, can be uri
	 */
	
	public function load($file)
	{
		$objDocument = new DOMDocument();
		$objDocument->load($file);
		
		$this->loadXML($objDocument);
	}
	
	/**
	 * Parse the XML into objects
	 *
	 * @param DOMDocument $objDocument
	 */

	protected function parse(DOMDocument $objDocument)
	{
		$objXPath = new DOMXPath($objDocument);
		$objXPath->registerNamespace("marc", $this->namespace);
		
		$objRecords = $objXPath->query("//marc:record");
		$this->_length = $objRecords->length;
		
		foreach ( $objRecords as $objRecord )
		{
			$record = new $this->record_type();
			$record->loadXML($objRecord);
			array_push($this->_records, $record);
		}
	}
	
	/**
	 * Get the record at the specific position
	 *
	 * @param int $position		[optional] position of the record (index starts at 1), default is 1
	 * @return Xerxes_Marc_Record
	 */
	
	public function record($position = 1)
	{
		$position--;
		return $this->_records[$position];
	}
	
	/**
	 * List of MARC-XML records from the Document
	 *
	 * @return array of Xerxes_Marc_Record objects
	 */
	
	public function records()
	{
		return $this->_records;
	}
	
	/**
	 * The number of MARC-XML records in the Document
	 *
	 * @return unknown
	 */
	
	public function length()
	{
		return $this->_length;
	}
	
	public function __get($property)
	{
		if ( method_exists($this, $property) )
		{
			return $this->$property();
		}
	}
}

/**
 * Parse single MARC-XML record
 * 
 * @author David Walker
 * @copyright 2009 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Marc.php 1483 2010-11-12 17:02:44Z dwalker@calstate.edu $
 * @package Xerxes
 */

/**
 * Parse single MARC-XML record
 */

class Xerxes_Marc_Record
{
	private $leader;
	private $namespace = "http://www.loc.gov/MARC21/slim";
	private $_controlfields = array();
	private $_datafields = array();
	
	protected $document;
	protected $xpath;
	protected $node;
	
	/**
	 * Create an object for a MARC-XML Record
	 *
	 * @param DOMNode $objNode
	 */
	
	public function loadXML($node = null)
	{
		$objNode = null;
		
		if ( is_string($node) )
		{
			$objNode = new DOMDocument();
			$objNode->recover = true;
			$objNode->loadXML($node);
		}
		else 
		{
			$objNode = $node;
		}
		
		if ( $objNode != null )
		{
			$objLeader = $objNode->getElementsByTagName("leader");
			$objControlFields = $objNode->getElementsByTagName("controlfield");
			$objDataFields = $objNode->getElementsByTagName("datafield");
			
			$this->leader = new Xerxes_Marc_Leader($objLeader->item(0));
			
			foreach ( $objControlFields as $objControlField )
			{
				$controlfield = new Xerxes_Marc_ControlField($objControlField);
				array_push($this->_controlfields, $controlfield);
			}
			
			foreach ( $objDataFields as $objDataField )
			{
				$datafield = new Xerxes_Marc_DataField($objDataField);
				array_push($this->_datafields, $datafield);
			}
			
			// if this was actually a DOMDocument in itself
			
			if ( $objNode instanceof DOMDocument )
			{
				$this->document = $objNode;
				$this->node = $objNode;
			}
			else
			{
				// we'll convert this node to a DOMDocument
				
				// first import it into an intermediate doc, 
				// so we can also import namespace definitions as well as nodes
				
				$intermediate  = new DOMDocument();
				$intermediate ->loadXML("<wrapper />");
				
				$import = $intermediate->importNode($objNode, true);
				$our_node = $intermediate->documentElement->appendChild($import);
				
				// now get just our xml, minus the wrapper
				
				$this->document = new DOMDocument();
				$this->document->loadXML($intermediate->saveXML($our_node));
				$this->node = $this->document->documentElement;
			}
				
			// now create an xpath object and the current node as properties
			// so we can query based on this node, not the wrapper parent
			// see the xpath() function below
			
			$this->xpath = new DOMXPath($this->document);
			$this->xpath->registerNamespace("marc", $this->namespace);
			
			// sub-class implements this
			
			$this->map();
		}
	}
	
	protected function map()
	{
		
	}
	
	/**
	 * Leader
	 * 
	 * @return Xerxes_Marc_Leader
	 */
	
	public function leader()
	{
		return $this->leader;
	}
	
	/**
	 * Control field
	 *
	 * @param string $tag			the marc tag number
	 * @return Xerxes_Marc_ControlField object
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
		
		return new Xerxes_Marc_ControlField();
	}

	/**
	 * Return a list of control fields, essentially for 007
	 *
	 * @param string $tag			the marc tag number
	 * @return Xerxes_Marc_FieldList object
	 */	
	
	public function controlfields($tag)
	{
		$list = new Xerxes_Marc_FieldList();
		
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
	 * @return Xerxes_Marc_DataFieldList
	 */
	
	public function datafield($tag, $ind1 = null, $ind2 = null)
	{
		$regex = str_replace("X", "[0-9]{1}", $tag);
		
		$list = new Xerxes_Marc_DataFieldList();
		
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
	 * @return DOMNodeLIst
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
				array_push($return, $subfield->__toString() );
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
	
	public function addControlField(Xerxes_Marc_ControlField $field)
	{
		array_push($this->_controlfields, $field);
	}
	
	public function addDataField(Xerxes_Marc_DataField $field)
	{
		array_push($this->_datafields, $field);
	}
	
}

/**
 *  Abstract field object
 */

abstract class Xerxes_Marc_Field
{
	protected $value;
	
	public function __toString()
	{
		return (string) $this->value;
	}
}

/**
 * MARC Leader
 *
 */

class Xerxes_Marc_Leader extends Xerxes_Marc_ControlField 
{
	public $value;					// the entire leader
	
	public function __construct(DOMNode $objNode = null)
	{
		if ( $objNode != null )
		{
			$this->value = $objNode->nodeValue;
		}
	}
}

/**
 * MARC Controlfield
 * 
 * For all intents and purposes, this just gets called directly and converted
 * to a string by client code
 *
 */

class Xerxes_Marc_ControlField extends Xerxes_Marc_Field 
{
	public $tag;
	public $value;
	
	public function __construct(DOMNode $objNode = null)
	{
		if ( $objNode != null )
		{
			$this->tag = $objNode->getAttribute("tag");
			$this->value = $objNode->nodeValue;
		}
	}

	public function position($position)
	{
		$arrPosition = explode("-", $position);
		
		$start = $arrPosition[0];
		$stop = $start;
				
		if ( count($arrPosition) == 2 )
		{
			$stop = $arrPosition[1];
		}
		
		$end = $stop - $start + 1;
		
		if ( strlen($this->value) >= $stop + 1)
		{
			return substr($this->value, $start, $end);
		}
		else
		{
			return null;
		}
	}
}

/**
 * MARC Datafield
 *
 */

class Xerxes_Marc_DataField
{
	public $tag;
	public $ind1;
	public $ind2;
	
	private $_subfields = array();
	
	public function __construct(DOMNode $objNode = null )
	{
		if ( $objNode != null )
		{
			$this->tag = $objNode->getAttribute("tag");
			$this->ind1 = $objNode->getAttribute("ind1");
			$this->ind2 = $objNode->getAttribute("ind2");
	
			foreach ( $objNode->getElementsByTagName("subfield") as $objSubfield )
			{
				$objMarcSubField = new Xerxes_Marc_Subfield($objSubfield);
				array_push($this->_subfields, $objMarcSubField);
			}
		}
	}
	
	/**
	 * Get the subfield of this datafield
	 *
	 * @param string $code		[optional] single subfield code, or multiple subfield codes listed together,
	 * 							empty value returns all subfields
	 * @param bool 				[optional] return fields in the order specified in $code
	 * @return Xerxes_Marc_SubFieldList
	 */
	
	public function subfield($code = "", $specified_order = false)
	{
		$codes = str_split($code);
		
		$list = new Xerxes_Marc_SubFieldList();
		
		if ( $code == "" )
		{
			foreach ( $this->_subfields as $subfield )
			{
				$list->addField($subfield);
			}
		}
		else
		{
			if ( $specified_order == true)
			{
				// do it this way so fields are returned in the order in 
				// which they were specified in the paramater

				foreach ( $codes as $subfield_code )
				{
					foreach ( $this->_subfields as $subfield )
					{
						if ( $subfield->code == $subfield_code )
						{
							$list->addField($subfield);
						}
					}
				}				
				
			}
			else
			{
				// $code is just defining fields to include, not order of codes,
				// so take them in the order in which they appear
				
				foreach ( $this->_subfields as $subfield )
				{
					if ( in_array($subfield->code, $codes ) )
					{
						$list->addField($subfield);
					}
				}
			}
		}
		
		return $list;
	}
	
	/**
	 * Get all subfields and return them with a space separator
	 *
	 * @return unknown
	 */

	function __toString()
	{
		$content = "";
		
		foreach ( $this->_subfields as $subfield )
		{
			$content .= " " . $subfield->__toString();
		}
		
		return trim($content);
	}
	
	public function addSubField(Xerxes_Marc_Subfield $field)
	{
		array_push($this->_subfields, $field);
	}
}

/**
 * MARC Subfield
 *
 */

class Xerxes_Marc_Subfield extends Xerxes_Marc_Field 
{
	public $code;
	public $value;
	
	public function __construct(DOMNode $objNode = null )
	{
		if ( $objNode != null )
		{
			$this->code = $objNode->getAttribute("code");
			$this->value = $objNode->nodeValue;
		}
	}
}

/**
 * A generic class for MARC fields, implemented by datafield and subfield list objects
 *
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

class Xerxes_Marc_DataFieldList extends Xerxes_Marc_FieldList 
{
	public function subfield($code, $specified_order = false) // convenience method
	{
		if ( count($this->list) == 0 )
		{
			return new Xerxes_Marc_Subfield(); // return empty subfield object
		}
		else
		{
			if ( strlen($code) == 1)
			{
				// only one subfield specified, so as a convenience to caller
				// return the first (and only the first) subfield of the 
				// first (and only the first) datafield  
				
				$subfield = $this->list[0]->subfield($code,$specified_order)->item(0);
				
				if ( $subfield == null )
				{
					return new Xerxes_Marc_Subfield(); // return empty subfield object
				}
				else
				{
					return $subfield;
				}
			}
			else
			{
				// multiple subfields specified, so return them all, but 
				// again only from the first occurance of the datafield
				
				return $this->list[0]->subfield($code,$specified_order);
			}
		}
	}
}

class Xerxes_Marc_SubFieldList extends Xerxes_Marc_FieldList 
{
}


?>
