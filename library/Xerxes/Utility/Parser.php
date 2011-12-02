<?php

namespace Xerxes\Utility;

/**
 * Utility class for basic parsing functions
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Parser.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package  Xerxes_Framework
 */ 

class Parser
{
	public static function toSentenceCase($strInput)
	{						
		if ( strlen($strInput) > 1 )
		{
			// drop everything
			
			$strInput = self::strtolower($strInput);
			
			// capitalize the first letter
			
			$strInput = self::strtoupper(substr($strInput, 0, 1)) . substr($strInput, 1);
			
			// and the start of a subtitle
			
			$strInput = self::capitalizeSubtitle($strInput);
		}
		
		return $strInput;
	}
	
	private static function capitalizeSubtitle($strFinal)
	{
		$arrMatches = array();
		
		if ( preg_match("/: ([a-z])/", $strFinal, $arrMatches) )
		{
			$strLetter = ucwords($arrMatches[1]);
			$strFinal = preg_replace("/: ([a-z])/", ": " . $strLetter, $strFinal );
		}
		
		return $strFinal;
	}
	
	
	/**
	 * Determine whether the url is part of a group of domains
	 * 
	 * @param string $strURL	the url to test
	 * @param string $strDomain	a comma-separated list of domains
	 *
	 * @return bool				true if in domain, false otherwise
	 */
	
	public static function withinDomain($strURL, $strDomain)
	{
		$bolPassed = false;
		
		if ( strlen($strURL) > 4 )
		{
			// only do it if it's an absolute url, local are fine
				
			if ( substr($strURL, 0, 4) == "http" )
			{
				$arrAllowed = explode(",", $strDomain);
				
				// if any in our list match
				
				$bolPassed = false;
				
				foreach ( $arrAllowed as $strAllowed )
				{
					$strAllowed = trim(str_replace(".", "\\.", $strAllowed));
					$strAllowed = trim(str_replace("*", "[^.]*", $strAllowed));
					
					if ( preg_match('/^http[s]{0,1}:\/\/' . $strAllowed .'.*/', $strURL) )
					{
						$bolPassed = true;
					}
				}
			}
		}
		
		return $bolPassed;
	}
	

	/**
	 * Simple function to strip off the previous part of a string
	 * from the start of the term to the beginning, including the term itself
	 * 
	 * @param string $strExpression		whole string to search 
	 * @param string $strRemove			term to match and remove left of from 
	 * @return string 					chopped string
	 * @static
	 */

	public static function removeLeft ( $strExpression, $strRemove ) 
	{		
		$iStartPos = 0;		// start position of removing term
		$iStopPos = 0;		// end position of removing term
		$strRight = "";		// right remainder of the string to return
		
		// if it really is there
		if ( strpos($strExpression, $strRemove) !== false )
		{
			// find the starting position of string to remove
			$iStartPos = strpos($strExpression, $strRemove);
			
			// find the end position of string to remove
			$iStopPos = $iStartPos + strlen($strRemove);
			
			// return everything after that
			$strRight = substr($strExpression, $iStopPos, strlen($strExpression) - $iStopPos);
			
			return $strRight;
		} 
		else 
		{
			return $strExpression;
		}
	}

	/**
	 * Simple function to strip off the remainder of a string
	 * from the start of the term to the end of the string, including the term itself
	 * 
	 * @param string $strExpression		whole string to search 
	 * @param string $strRemove			term to match and remove right of from 
	 * @return string chopped string
	 * @static 
	 */ 

	public static function removeRight ( $strExpression, $strRemove ) 
	{		
		$iStartPos = 0;		// start position of removing term
		$strLeft = "";		// left portion of to return

		// if it really is there
		if ( strpos( $strExpression, $strRemove) !== false ) 
		{

			// find the starting position of to remove
			$iStartPos = strpos( $strExpression, $strRemove);
			
			// get everything before that
			$strLeft = substr( $strExpression, 0, $iStartPos);
							
			return $strLeft;
		} 
		else 
		{
			return $strExpression;
		}
	}
	
	/**
	 * Clean data for inclusion in an XML document, escaping illegal
	 * characters
	 *
	 * @param string $string data to be cleaned
	 * @return string cleaned data
	 * @static 
	 */
	
	public static function escapeXml( $string )
	{
		$string = str_replace('&', '&amp;', $string);
		$string = str_replace('<', '&lt;', $string);
		$string = str_replace('>', '&gt;', $string);
		$string = str_replace('\'', '&#39;', $string);
		$string = str_replace('"', '&quot;', $string);
		
		$string = str_replace("&amp;#", "&#", $string);
		$string = str_replace("&amp;amp;", "&amp;", $string);
		
		// trying to catch unterminated entity references
		
		$string = preg_replace('/(&#[a-hA-H0-9]{2,5})\s/', "$1; ", $string);
		
		return $string;
	}
	
	/**
	 * use multi-byte string lower case if available
	 * 
	 * @param string $string the string to drop to lower case
	 */
	
	public static function strtolower($string)
	{
		if ( function_exists("mb_strtolower") )
		{
			return mb_strtolower($string, "UTF-8");
		}
		else
		{
			return strtolower($string);
		}
	}

	
	/**
	 * use multi-byte string upper case if available
	 * 
	 * @param string $string the string to raise to upper case
	 */

	public static function strtoupper($string)
	{
		if ( function_exists("mb_strtoupper") )
		{
			return mb_strtoupper($string, "UTF-8");
		}
		else
		{
			return strtoupper($string);
		}
	}
	
	public static function preg_replace($pattern, $replacement, $subject)
	{
		if ( function_exists("mb_ereg_replace") )
		{
			// preg strings have / at the start and end, so we need to take those
			// off for this mb_ereg one (annoying!) for it to work correctly
			 
			$pattern = substr($pattern,1);
			$pattern = substr($pattern,0,-1);
			
			return mb_ereg_replace($pattern, $replacement, $subject);
		}
		else
		{
			return preg_replace($pattern, $replacement, $subject);
		}			
	}
	
	public static function number_format($number, $decimals = 0)
	{
		$number = (int) preg_replace('/\D/', '', $number);
		
		$localeconv = localeconv();
		
		if ( $localeconv['thousands_sep'] == "" )
		{
			$localeconv['thousands_sep'] = ",";
		}
		
		return number_format($number, $decimals, $localeconv['decimal_point'], $localeconv['thousands_sep']);
	}
	
	/**
	 * Convert string, DOMNode to DOMDocument
	 */
	
	public static function convertToDOMDocument($xml)
	{
		if ( $xml instanceof \DOMDocument )
		{
			return $xml;
		}
		elseif ( is_string($xml) )
		{
			$document = new \DOMDocument();
			$document->loadXML($xml);
			
			return $document;
		}
		elseif ( $xml instanceof \DOMNode )
		{
			// we'll convert this node to a DOMDocument
				
			// first import it into an intermediate doc, 
			// so we can also import namespace definitions as well as nodes
				
			$intermediate = new \DOMDocument();
			$intermediate->loadXML("<wrapper />");
				
			$import = $intermediate->importNode($xml, true);
			$our_node = $intermediate->documentElement->appendChild($import);
				
			// now get just our xml, minus the wrapper
				
			$document = new \DOMDocument();
			$document->loadXML($intermediate->saveXML($our_node));
			
			return $document;
		}	
		else
		{
			throw new \Exception("param 1 must be of type string, DOMNode, or DOMDocument");
		}
	}
	
	/**
	 * Remove from array based on key or key/value
	 *
	 * @param array $params		array
	 * @param string $key		the name of the param to remove
	 * @param string $value		[optional] only if the param has this value
	 */
	
	public static function removeFromArray(array $array, $key, $value = "")
	{
		if ( array_key_exists( $key, $array ) )
		{
			// delete by key
	
			if ( $value == "" )
			{
				unset($array[$key]);
			}
	
			// delete only if value also matches
	
			else
			{
				$stored = $array[$key];
	
				// if this is an array, we need to find the right one
	
				if ( is_array( $stored ) )
				{
					for ( $x = 0; $x < count($stored); $x++ )
					{
						if ( $stored[$x] == $value )
						{
							unset($array[$key][$x]);
						}
					}
	
					// reset the keys
	
					$array[$key] = array_values($array[$key]);
				}
				elseif ( $stored == $value )
				{
					unset($array[$key]);
				}
			}
		}
	
		return $array;
	}	
}