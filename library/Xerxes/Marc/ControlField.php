<?php

namespace Xerxes\Marc;

/**
 * MARC ControlField
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: ControlField.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class ControlField extends Field 
{
	public $tag;
	public $value;
	
	public function __construct(DOMNode $objNode = null)
	{
		if ( $objNode != null )
		{
			$this->tag = $objNode->getAttribute("tag");
			$this->value = $objNode->nodeValue;
		}
	}

	public function position($position)
	{
		$arrPosition = explode("-", $position);
		
		$start = $arrPosition[0];
		$stop = $start;
				
		if ( count($arrPosition) == 2 )
		{
			$stop = $arrPosition[1];
		}
		
		$end = $stop - $start + 1;
		
		if ( strlen($this->value) >= $stop + 1)
		{
			return substr($this->value, $start, $end);
		}
		else
		{
			return null;
		}
	}
}