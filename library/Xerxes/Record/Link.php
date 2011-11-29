<?php

namespace Xerxes\Record;

/**
 * Record Subject
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Link.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Link
{
	protected $type;
	protected $display;
	protected $url;
	
	const PDF = "pdf"; // link is to full-text pdf document
	const HTML = "html"; // link is to full-text in HTML
	const ONLINE = "online"; // link is to the online full-text, but we're unsure of exact format
	const INFORMATIONAL = "none"; // this is merely an informational link about the item, e.g., TOC or publisher desc.
	const ORIGINAL_RECORD = "original"; // link to the original record in the system of origin, no indication of full-text
	
	public function __construct($url, $type = null, $display = null)
	{
		$this->url = $url;
		$this->type = $this->extractType($type);
		$this->display = $display;
	}
	
	public function extractType($data)
	{
		if ( $data == null )
		{
			return null;
		}
		elseif ( stristr( $data, "PDF" ) )
		{
			return self::PDF;
		} 
		elseif ( stristr( $data, "HTML" ) )
		{
			return self::HTML;
		}
		else
		{
			return self::ONLINE;
		}
	}
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getDisplay()
	{
		return $this->display;
	}
	
	public function getURL()
	{
		return $this->url;
	}	
	
	public function isFullText()
	{
		if ( $this->type == self::PDF || $this->type == self::HTML || $this->type == self::ONLINE )
		{
			return true; 
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 			// if this is a "construct" link, then the second element is an associative 
				// array of marc fields and their values for constructing a link based on
				// the metalib IRD record linking syntax
				
				if ( is_array($arrLink[1]) )
				{
					foreach ( $arrLink[1] as $strField => $strValue )
					{
						$objParam = $objXml->createElement("param", Parser::escapeXml($strValue));
						$objParam->setAttribute("field", $strField);
						$objLink->appendChild($objParam);
					}
				}
	 */
}
