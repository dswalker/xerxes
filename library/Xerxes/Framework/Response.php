<?php

/**
 * Response Object
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Response.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes_Framework
 * @uses Xerxes_Framework_Parser
 */

class Xerxes_Framework_Response
{
	private $_data = array(); // data
	private $_redirect; // redirect url
	private $_view = array(); // view file
	
	private static $instance; // singleton pattern

	protected function __construct() { }
	
	/**
	 * Get an instance of the file; Singleton to ensure correct data
	 *
	 * @return Xerxes_Framework_Request
	 */
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Xerxes_Framework_Response();
		}
		
		return self::$instance;
	}	
	
	/**
	 * Add data to the response
	 *
	 * @param string $id		an identifier for this object
	 * @param mixed $object		any kind of object you likes
	 */
	
	public function add($id, $object)
	{
		$this->_data[$id] = $object;
	}
	
	/**
	 * Get data from the response
	 * 
	 * @param string $id		an identifier for this object
	 */
	
	public function get($id)
	{
		if ( array_key_exists($id, $this->_data) )
		{
			return $this->_data[$id];
		}
		else
		{
			throw new Exception("no data with id '$id'");
		}
	}
	
	/**
	 * Set the URL for redirect
	 *
	 * @param string $url
	 */
	
	public function setRedirect($url)
	{
		$this->_redirect = $url;
	}
	
	/**
	 * Get the URL to redirect user
	 *
	 * @return unknown
	 */
	
	public function getRedirect()
	{
		return $this->_redirect;
	}
	
	/**
	 * Serialize to XML
	 * 
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
		$xml = new DOMDocument();
		$xml->loadXML("<xerxes />");
		
		foreach ( $this->_data as $id => $object )
		{
			$this->addToXML($xml, $id, $object);
		}
		
		return $xml;
	}
	
	/**
	 * Recursively convert data to XML
	 */
	
	private function addToXML(DOMDocument &$xml, $id, $object)
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
		
		elseif ( $object instanceof DOMDocument )
		{
			$object_xml = $object;
		}
		
		// simplexml, same deal, but make it dom, yo
		
		elseif ( $object instanceof SimpleXMLElement )
		{
			$simple_xml = $object->asXML();
			
			if ( $simple_xml != "" )
			{
				if ( ! strstr($simple_xml, "<") )
				{
					throw new Exception("SimpleXMLElement was malformed");
				}				
				
				$object_xml = new DOMDocument();
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
				
				$object_xml = new DOMDocument();
				$object_xml->loadXML("<$id />");
				
				// only public properties
				
				$reflection = new ReflectionObject($object);
				
				foreach ( $reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property )
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
			
			$object_xml = new DOMDocument();
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
			
			$element = $xml->createElement($id, Xerxes_Framework_Parser::escapeXml($object) );
			$xml->documentElement->appendChild($element);
			return $xml;
		}
		
		// if we got this far, then we've got a domdocument to add
		
		$import = $xml->importNode($object_xml->documentElement, true);
		$xml->documentElement->appendChild($import);			
		
		return $xml;
	}
	
	/**
	 * Display the response by calling view
	 */
	
	public function display($format = "html")
	{
		$this->setFormat($format);
		
		$request = Xerxes_Framework_Request::getInstance();
		
		// default
		
		if ( ! array_key_exists($format, $this->_view) ) 
		{
			$this->_view['html'] = "xsl/" . $request->getParam("base") . "/" . $request->getParam("action") . ".xsl";
		}
		
		// just dump the internal xml
		
		if ( $format == "xerxes" )
		{
			$xml = $this->toXML();
			return $xml->saveXML();
		}
		
		if ( array_key_exists($format, $this->_view) )
		{
			$view = $this->_view[$format]; // file location
			
			// xslt view
			
			if (strstr($view, '.xsl') )
			{
				$xml = $this->toXML();
				$html = $this->transform($xml, $view);
				return $html;
			}
			
			// php view
			
			else
			{
				require_once "views/$view";
			}
		}
	}
	
	protected function transform($xml, $path_to_xsl, $params = array())
	{
		$registry = Xerxes_Framework_Registry::getInstance();	

		$import_array = array();
		
		// the xsl lives here

		$distro_xsl_dir = XERXES_APPLICATION_PATH . "views/";
		$local_xsl_dir = $registry->getConfig("LOCAL_DIRECTORY", true) . "/views/";
		
		### language file
		
		$request = Xerxes_Framework_Request::getInstance();
		$language = $request->getProperty("lang");
		
		if ( $language == "" )
		{
			$language = $registry->defaultLanguage();
		}
		
		// english file is included by default (as a fallback)
		
		array_push($import_array, "xsl/labels/eng.xsl");
		
		// if language is set to something other than english
		// then include that file to override the english labels
		
		if ( $language != "eng" ) 
		{
			array_push($import_array, "xsl/labels/$language.xsl");
		}		
		
		### make sure we've got a reference to the local includes too
		
		array_push($import_array, $local_xsl_dir . "xsl/includes.xsl");
		
		$xsl = new Xerxes_Framework_XSL($distro_xsl_dir, $local_xsl_dir);
		
		return $xsl->transformToXml($xml, $path_to_xsl, $params, $import_array);
	}
	
	/**
	 * Set the view file to use
	 * 
	 * @param string $file			location to view file
	 * @param string $format		[optional] only do this view when this format is requested 
	 */
	
	public function setView($file, $format = "html")
	{
		$this->_view[$format] = $file;
	}
	
	/**
	 * Set the header type based on format
	 * 
	 * @param string $format		format id
	 */

	public function setFormat($format)
	{
		$arrFormats = array 
		(
			// basic types
		
			"javascript" => "Content-type: application/javascript", 
			"json" => "Content-type: application/json", 
			"pdf" => "Content-type: application/pdf", 
			"text" => "Content-type: text/plain", 
			"xml" => "Content-type: text/xml", 
		
			// complex types
		
			"atom" => "Content-type: text/xml", 
			"bibliographic" => "Content-type: application/x-research-info-systems", 
			"embed_html_js" => "Content-type: application/javascript", 
			"ris" => "Content-type: text/plain", 
			"rss" => "Content-type: text/xml", 
			"xerxes" => "Content-type: text/xml", 
			"text-file" => "Content-Disposition: attachment; Content-type: text/plain; filename=download.txt", 
			"ris-file" => "Content-Disposition: attachment; Content-type: text/plain; filename=download.ris" 
		);
		
		if ( array_key_exists( $format, $arrFormats ) )
		{
			header( $arrFormats[$format] . "; charset=UTF-8" );
		}
	}
}
