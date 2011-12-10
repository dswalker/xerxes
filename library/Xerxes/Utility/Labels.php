<?php

namespace Xerxes\Utility;

/**
 * Labels class
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Labels
{
	private $xml; // simple xml
	private $labels = array(); // label values
	private $path; // path to label file
	
	private static $instance; // singleton pattern

	protected function __construct() {}
	
	/**
	 * Create Labels access object
	 * 
	 * @param string $language		language identifier
	 */
	
	public static function getInstance($path)
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Labels();
			$object = self::$instance;
			$object->path = $path;
			$object->init();
		}
		
		return self::$instance;
	}
	
	/**
	 * Process distro and local label files
	 * 
	 * @param string $language		language identifier
	 */
	
	public function setLanguage($language)
	{
		// @todo should really set this path outside this class, back in module
		
		// distro file
		
		$this->xml = new \DOMDocument();
		$this->xml->load("$this->path/eng.xsl");
		
		// local file
		
		if ( file_exists("views/labels/eng.xsl") )
		{
			$local_xml = new \DOMDocument();
			$local_xml->load("views/labels/eng.xsl");
			$import = $this->xml->importNode($local_xml->documentElement, true);
			$this->xml->documentElement->appendChild($import);			
		}
		
		// if language is set to something other than english
		// then also include that file to override the english labels

		if ( $language != "" )
		{
			// distro
			
			$language_xml = new \DOMDocument();
			$language_xml->load("$path/$language.xsl");
			
			$import = $this->xml->importNode($language_xml->documentElement, true);
			$this->xml->documentElement->appendChild($import);
			
			// local

			if ( file_exists("views/labels/$language.xsl") )
			{
				$local_xml = new \DOMDocument();
				$local_xml->load("views/labels/$language.xsl");
				$import = $this->xml->importNode($local_xml->documentElement, true);
				$this->xml->documentElement->appendChild($import);			
			}		
		}

		$labels = $this->xml->getElementsByTagName("variable");
		
		// set the values in the master array
		// last ones takes precedence
		
		foreach ( $labels as $label )
		{
			$this->labels[(string) $label->getAttribute("name")] = $label->nodeValue;
		}		
	}
	
	/**
	 * Get internal labels XML file
	 */
	
	public function getXML()
	{
		return $this->xml;
	}
	
	/**
	 * Get all labels
	 */
	
	public function getLabels()
	{
		return $this->labels;
	}
	
	/**
	 * Get label value
	 * 
	 * @param string $name		label identifier or label id if no corresponding label found
	 */
	
	public function getLabel($name)
	{
		if ( array_key_exists($name, $this->labels) )
		{
			return $this->labels[$name];
		}
		else
		{
			return $name;
		}
	}
}
