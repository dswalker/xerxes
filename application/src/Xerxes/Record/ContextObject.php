<?php

namespace Xerxes\Record;

use Xerxes\Record,
	Xerxes\Utility\Parser;

/**
 * Extract properties from OpenURL context object
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ContextObject extends Record
{
	protected $xpath;
	
	/**
	 * Create Record from OpenURL Context Object
	 */
	
	public function __construct()
	{
		parent::__construct();
		$this->utility[] = "xpath";
	}
	
	/**
	 * Load data from XML
	 */

	public function loadXML($xml)
	{
		$this->document = Parser::convertToDOMDocument($xml);
		$this->xpath = new \DOMXPath($this->document);
		
		// test to see what profile the context object is using
		// set namespace accordingly

		if ($this->document->getElementsByTagNameNS( "info:ofi/fmt:xml:xsd:book", "book" )->item(0) != null)
		{
			$this->xpath->registerNamespace( "rft", "info:ofi/fmt:xml:xsd:book" );
		} 
		elseif ($this->document->getElementsByTagNameNS( "info:ofi/fmt:xml:xsd:dissertation", "dissertation" )->item(0) != null)
		{
			$this->xpath->registerNamespace( "rft", "info:ofi/fmt:xml:xsd:dissertation" );
		} 		
		elseif ($this->document->getElementsByTagNameNS( "info:ofi/fmt:xml:xsd", "journal" )->item(0) != null)
		{
			$this->xpath->registerNamespace( "rft", "info:ofi/fmt:xml:xsd" );
		}
		else
		{
			$this->xpath->registerNamespace( "rft", "info:ofi/fmt:xml:xsd:journal" );
		}
		
		$this->map();
		$this->cleanup();
	}
	
	/**
	 * Map the data to properties
	 */
	
	protected function map()
	{
		// extract values
		
		$title = $this->extractValue("rft:title");
		$atitle = $this->extractValue("rft:atitle");
		$btitle = $this->extractValue("rft:btitle");
		
		if ( $atitle != null )
		{
			$this->title = $atitle;
			
			if ( $btitle != null )
			{
				$this->book_title = $btitle;
			}
		}
		elseif ( $btitle != null )
		{
			$this->title = $btitle;
		}
		elseif ( $title != null )
		{
			$this->title = $title;
		}
		
		$this->journal_title = $this->extractValue("rft:jtitle");
		$this->short_title = $this->extractValue("rft:sitle");
		
		$this->volume = $this->extractValue("rft:volume");
		$this->issue = $this->extractValue("rft:issue");
		$this->start_page = $this->extractValue("rft:spage");
		$this->end_page = $this->extractValue("rft:epage");
		
		
		
		
		
		// @todo: proper format mapping
		
		$format = $this->extractValue("rft:genre");
		
		$this->format->setInternalFormat($format);
		$this->format->setNormalizedFormat(Format::Article);
		$this->format->setPublicFormat( ucfirst($format) );
		
		if ( $format == "proceeding") $this->format->setInternalFormat(Format::ConferenceProceeding);
		if ( $format == "dissertation") $this->format->setInternalFormat(Format::Thesis);
		
		// date
		
		$this->year = substr($this->extractValue("rft:date"), 0, 4);
		
		// standard numbers
		
		$this->issns[] = $this->extractValue("rft:issn");
		$this->isbns[] = $this->extractValue("rft:isbn");
		
		// authors

		$authors = $this->xpath->query( "//rft:author[rft:aulast != '' or rft:aucorp != '']" );
		
		foreach ( $authors as $objAuthor )
		{
			$author_object = new Author();
			
			foreach ( $objAuthor->childNodes as $objAuthAttr )
			{					
				switch ( $objAuthAttr->localName )
				{
					case "aulast":
						$author_object->last_name = $objAuthAttr->nodeValue;
						$author_object->type = "personal";
						break;
						
					case "aufirst":
						$author_object->first_name = $objAuthAttr->nodeValue;
						break;
						
					case "auinit":
						$author_object->init = $objAuthAttr->nodeValue;
						break;
						
					case "aucorp":
						$author_object->name = $objAuthAttr->nodeValue;
						$author_object->type = "corporate";
						break;							
				}
				
				array_push($this->authors, $author_object);
			}
		}
	}
	
	/**
	 * Convenience function to extract the value from the XML
	 * 
	 * @param string $ref		element name
	 */
	
	protected function extractValue($ref)
	{
		$node = $this->xpath->query( "//$ref" )->item(0);
			
		if ( $node != null )
		{
			return $node->nodeValue;
		}
		else
		{
			return null;
		}
	}
}