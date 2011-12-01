<?php

namespace Xerxes\Utility;

/**
 * Response Object
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Response.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes_Framework
 * @uses Parser
 */

class ViewRenderer
{
	private $_script_path;
	
	public function __construct($script_path)
	{
		$this->_script_path = $script_path;
	}
	
	/**
	 * Display the response by calling view
	 */
	
	public function render($view, $vars)
	{
		// xslt view
			
		if (strstr($view, '.xsl') )
		{
			$xml = $this->toXML($vars);
			$html = $this->transform($xml, $view);
			return $html;
		}
			
		// php view
			
		else
		{			
			foreach ( $vars as $id => $value )
			{
				$this->$id = $value;
			}			
			
			require_once $this->_script_path . "/" . $view;
		}
	}
	
	public function toXML($vars)
	{
		$xml = new \DOMDocument();
		$xml->loadXML("<xerxes />");
	
		foreach ( $vars as $id => $object )
		{
			$this->addToXML($xml, $id, $object);
		}
	
		return $xml;
	}
	
	/**
	 * Recursively convert data to XML
	 */
	
	private function addToXML(\DOMDocument &$xml, $id, $object)
	{
		$object_xml = null;
	
		if ( is_int($id) )
		{
			$id = "object_$id";
		}
	
		// no value, no mas!
	
		if ( $object == "" )
		{
			return null;
		}
	
		// already in xml, so take it
	
		elseif ( $object instanceof \DOMDocument )
		{
			$object_xml = $object;
		}
	
		// simplexml, same deal, but make it dom, yo
	
		elseif ( $object instanceof \SimpleXMLElement )
		{
			$simple_xml = $object->asXML();
	
			if ( $simple_xml != "" )
			{
				if ( ! strstr($simple_xml, "<") )
				{
					throw new \Exception("SimpleXMLElement was malformed");
				}
	
				$object_xml = new \DOMDocument();
				$object_xml->loadXML($simple_xml);
			}
		}
	
		// object
	
		elseif ( is_object($object) )
		{
			// this object defines its own toXML method, so use that
	
			if ( method_exists($object, "toXML") )
			{
				$object_xml = $object->toXML();
			}
			else
			{
				// this object tells us to use this id in the xml
	
				if ( property_exists($object, "nodeName") )
				{
					$id = $object->nodeName;
				}
	
				$object_xml = new \DOMDocument();
				$object_xml->loadXML("<$id />");
	
				// only public properties
	
				$reflection = new \ReflectionObject($object);
	
				foreach ( $reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property )
				{
					$this->addToXML($object_xml, $property->name, $property->getValue($object));
				}
			}
		}
	
		// array
	
		elseif ( is_array($object) )
		{
			if ( count($object) == 0 )
			{
				return null;
			}
	
			$object_xml = new \DOMDocument();
			$object_xml->loadXML("<$id />");
	
			foreach ( $object as $property => $value )
			{
				// if the name of the array is plural, then make the childen singular
				// if this is an array of objects, then the object may override this
	
				if ( is_int($property) && substr($id,-1) == "s" )
				{
					$property = substr($id,0,-1);
				}
	
				$this->addToXML($object_xml, $property, $value);
			}
		}
	
		// assumed to be primitive type (string, bool, or int, etc.)
	
		else
		{
			// just create a simple new element and return this thing
	
			$element = $xml->createElement($id, Parser::escapeXml($object) );
			$xml->documentElement->appendChild($element);
			return $xml;
		}
	
		// if we got this far, then we've got a domdocument to add
	
		$import = $xml->importNode($object_xml->documentElement, true);
		$xml->documentElement->appendChild($import);
	
		return $xml;
	}	
	
	protected function transform($xml, $path_to_xsl, $params = array())
	{
		$registry = Registry::getInstance();

		$import_array = array();
		
		// the xsl lives here

		$distro_xsl_dir = $this->_script_path . "/";
		$local_xsl_dir = realpath(getcwd()) . "/views/";
		
		### language file
		
		$request = new Request();
		$language = $request->getParam("lang");
		
		if ( $language == "" )
		{
			$language = $registry->defaultLanguage();
		}
		
		// english file is included by default (as a fallback)
		
		array_push($import_array, "labels/eng.xsl");
		
		// if language is set to something other than english
		// then include that file to override the english labels
		
		if ( $language != "eng" ) 
		{
			array_push($import_array, "labels/$language.xsl");
		}		
		
		### make sure we've got a reference to the local includes too
		
		array_push($import_array, $local_xsl_dir . "includes.xsl");
		
		$xsl = new Xsl($distro_xsl_dir, $local_xsl_dir);
		
		return $xsl->transformToXml($xml, $path_to_xsl, $params, $import_array);
	}
}
