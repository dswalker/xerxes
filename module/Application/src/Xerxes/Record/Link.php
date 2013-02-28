<?php

namespace Xerxes\Record;

use Xerxes\Utility\Parser;

/**
 * Record Link
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Link
{
	protected $type;
	protected $display;
	protected $url;
	
	/**
	 * link is to full-text pdf document
	 */
	
	const PDF = "pdf";
	
	/**
	 * link is to full-text in HTML
	 */

	const HTML = "html";
	
	/**
	 * link is to the online full-text, but we're unsure of exact format
	 */
	
	const ONLINE = "online";

	/**
	 * this is merely an informational link about the item, e.g., TOC or publisher desc.
	 */
	
	const INFORMATIONAL = "none";
	
	/**
	 * link to the original record in the system of origin, no indication of full-text
	 */
	
	const ORIGINAL_RECORD = "original";
	
	/**
	 * Create a Record Link
	 * 
	 * @param string $url			URL
	 * @param string $type			[optional] type of link, or data from which to determine that
	 * @param string $display		[optional] text to display
	 */
	
	public function __construct($url, $type = null, $display = null)
	{
		$this->url = $url;
		$this->type = $type;
		$this->display = $display;
	}
	
	/**
	 * Determine type of link from supplied data
	 * 
	 * @param string $data
	 */
	
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
	
	/**
	 * Set link type
	 * 
	 * @param string $type
	 */
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	/**
	 * Get link type
	 * 
	 * @return string
	 */
	
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Get text to display
	 * 
	 * @return string
	 */
	
	public function getDisplay()
	{
		return $this->display;
	}
	
	/**
	 * Get URL
	 * 
	 * @return string
	 */
	
	public function getURL()
	{
		return $this->url;
	}	
	
	/**
	 * Whether link is to full-text
	 * 
	 * @return bool
	 */
	
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
	
	public function addProxyPrefix($proxy_server)
	{
		// no link, no proxy, don't do nuttin'
		
		if ( $this->url == '' || $proxy_server == "")
		{
			return false;
		}		
		
		// make sure the link doesn't include the proxy server prefix already
			
		if ( preg_match('/http:\/\/[0-9]{1,3}-.*/', $this->url) != 0 )
		{
			// WAM proxy: this is kind of a rough estimate of a WAM-style
			// proxy link, but I think sufficient for our purposes?
		
			return false;
		}
		elseif ( stristr($this->url, $proxy_server) )
		{
			// EZProxy
		
			return false;
		}
			
		// we made it this far, we need to proxy
			
		// if WAM proxy, take the base url and port out and 'prefix';
		// otherwise we only support EZPRoxy, so cool to take as else ?
		
		if ( strstr($proxy_server, '{WAM}') )
		{
			$arrMatch = array();
				
			if ( preg_match('/http[s]{0,1}:\/\/([^\/]*)\/{0,1}(.*)/', $this->url, $arrMatch) != 0 )
			{
				$port = "0";
				$arrPort = array();
	
				// port specifically included
	
				if ( preg_match("/:([0-9]{2,5})/", $arrMatch[1], $arrPort) != 0 )
				{
					if ( $arrPort[1] != "80") 
					{
						$port = $arrPort[1];
					}
					
					$arrMatch[1] = str_replace($arrPort[0], "", $arrMatch[1]);
				}
	
				$base = str_replace("{WAM}", $port . "-" . $arrMatch[1], $proxy_server);
	
				$this->url =  $base . "/" . $arrMatch[2];
	
				$this->url = str_replace("..", ".", $this->url);
			}
			else
			{
				throw new \Exception("could not construct WAM link");
			}
		}
		else
		{
			// check if this is using EZProxy qurl param, in which case urlencode that mo-fo
				
			if ( strstr($proxy_server, "qurl=") )
			{
				$this->url = urlencode($this->url);
			}
				
			$this->url = $proxy_server . $this->url;
		}
		
		return true;
	}
	
	/**
	 * Serialize to XML
	 */
	
	public function toXML()
	{
		$xml = new \SimpleXMLElement('<link />');
		
		if ( $this->isFullText() )
		{
			$xml->addAttribute("type", "full");
			$xml->addAttribute("format", $this->getType());
		}
		else
		{
			$xml->addAttribute("type", $this->getType());
		}
		
		$xml->display = $this->getDisplay();
		$xml->url = $this->getURL();
		
		return Parser::convertToDOMDocument($xml);
	}
}
