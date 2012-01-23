<?php

namespace Xerxes\Utility;

/**
 * Labels Access Object
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
	private $labels = null; // label values
	private $path; // path to label file
	
	/**
	 * Create Labels access object
	 * 
	 * @param string $language		language identifier
	 */
	
	public function __construct($path)
	{
		$this->path = $path;
		$this->init();
	}
	
	/**
	 * Initial process of distro and local eng label files
	 */
	
	protected function init()
	{
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
	}
	
	/**
	 * Set the language
	 * 
	 * @param string $language		language identifier
	 */
	
	public function setLanguage($language)
	{
		// if language is set to something other than english
		// then also include that file to override the english labels

		if ( $language != "eng" &&  $language != "" )
		{
			// distro
			
			$language_xml = new \DOMDocument();
			$language_xml->load("$this->path/$language.xsl");
			
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
	 * 
	 * @return array
	 */
	
	public function getLabels()
	{
		return  $this->labels()->getIterator()->getArrayCopy();
	}
	
	/**
	 * Internal labels array object
	 * 
	 * @return ArrayObject
	 */
	
	public function labels()
	{
		// lazy load the labels into an array
		
		if ( ! $this->labels instanceof \ArrayObject )
		{
			$this->labels = new \ArrayObject();
		
			$labels = $this->xml->getElementsByTagName("variable");
		
			// set the values in the master array
			// last ones takes precedence
		
			foreach ( $labels as $label )
			{
				$this->labels->offsetSet((string) $label->getAttribute("name"), $label->nodeValue );
			}
		}
		
		return $this->labels;
	}
	
	/**
	 * Get label value
	 * 
	 * @param string $name		label identifier or label id if no corresponding label found
	 */
	
	public function getLabel($name)
	{
		if ( $this->labels()->offsetExists($name) )
		{
			return $this->offsetGet($name);
		}
		else
		{
			return $name;
		}
	}
}
