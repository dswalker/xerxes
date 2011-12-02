<?php

namespace Xerxes\Marc;

/**
 * MARC Document
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Document
{
	protected $namespace = "http://www.loc.gov/MARC21/slim";
	protected $_length = 0;
	protected $_records = array();
	
	/**
	 * Load a MARC-XML document from string or object
	 *
	 * @param mixed $xml	XML as string or \DOMDocument
	 */
	
	public function loadXML($xml)
	{
		$objDocument = new \DOMDocument();
		
		if ( is_string($xml) )
		{
			$objDocument->loadXML($xml);
		}
		elseif ( $xml instanceof \DOMDocument )
		{
			$objDocument = $xml;
		}
		else
		{
			throw new \Exception("param 1 must be XML of type DOMDocument or string");
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
		$objDocument = new \DOMDocument();
		$objDocument->load($file);
		
		$this->loadXML($objDocument);
	}
	
	/**
	 * Parse the XML into objects
	 *
	 * @param \DOMDocument $objDocument
	 */

	protected function parse(\DOMDocument $objDocument)
	{
		$objXPath = new \DOMXPath($objDocument);
		$objXPath->registerNamespace("marc", $this->namespace);
		
		$objRecords = $objXPath->query("//marc:record");
		$this->_length = $objRecords->length;
		
		foreach ( $objRecords as $objRecord )
		{
			$record = new Record;
			$record->loadXML($objRecord);
			array_push($this->_records, $record);
		}
	}
	
	/**
	 * Get the record at the specific position
	 *
	 * @param int $position		[optional] position of the record (index starts at 1), default is 1
	 * @return Record
	 */
	
	public function record($position = 1)
	{
		$position--;
		return $this->_records[$position];
	}
	
	/**
	 * List of MARC-XML records from the Document
	 *
	 * @return array of Record objects
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