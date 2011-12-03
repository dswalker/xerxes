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
	private $xml;
	private $labels = array();
	private static $instance; // singleton pattern

	protected function __construct()
	{
	}
	
	public static function getInstance($language)
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Labels();
			$object = self::$instance;
			$object->init($language);
		}
		
		return self::$instance;
	}
		
	public function init($language)
	{
		$this->xml = new \DOMDocument();
		$this->xml->load( XERXES_APPLICATION_PATH . "views/xsl/labels/eng.xsl");
		
		if ( file_exists("xsl/labels/eng.xsl") )
		{
			$local_xml = new \DOMDocument();
			$local_xml->load("xsl/labels/eng.xsl");
			$import = $this->xml->importNode($local_xml->documentElement, true);
			$this->xml->documentElement->appendChild($import);			
		}
		
		// if language is set to something other than english
		// then include that file to override the english labels

		if ( $language != "" )
		{
			$language_xml = new \DOMDocument();
			$language_xml->load( XERXES_APPLICATION_PATH . "views/xsl/labels/$language.xsl");
			
			$import = $this->xml->importNode($language_xml->documentElement, true);
			$this->xml->documentElement->appendChild($import);

			if ( file_exists("xsl/labels/$language.xsl") )
			{
				$local_xml = new \DOMDocument();
				$local_xml->load("xsl/labels/$language.xsl");
				$import = $this->xml->importNode($local_xml->documentElement, true);
				$this->xml->documentElement->appendChild($import);			
			}		
		}

		$labels = $this->xml->getElementsByTagName("variable");
		
		// last ones takes precedence
		
		foreach ( $labels as $label )
		{
			$this->labels[(string) $label->getAttribute("name")] = $label->nodeValue;
		}		
	}
	
	public function getXML()
	{
		return $this->xml;
	}
	
	public function getLabels()
	{
		return $this->labels;
	}
	
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
