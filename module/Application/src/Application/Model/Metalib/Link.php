<?php

namespace Application\Model\Metalib;

use Xerxes\Record;

/**
 * Metalib Record Link
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Link extends Record\Link
{
	/**
	 * Create a Metalib Record Link
	 *
	 * @param string|array $url		URL or array to construct link from metalib templates
	 * @param string $type			[optional] type of link, or data from which to determine that
	 * @param string $display		[optional] text to display
	 */
	
	public function __construct($url, $type = null, $display = null)
	{
		// special metalib construct link
		
		if ( is_array($url) )
		{
			
		}
		
		parent::__construct($url, $type, $display);
	}
	
	/**
	 // if this is a "construct" link, then the second element is an associative
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
