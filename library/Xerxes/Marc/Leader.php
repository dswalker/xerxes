<?php

/**
 * MARC Leader
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Leader.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Xerxes_Marc_Leader extends Xerxes_Marc_ControlField 
{
	public $value;					// the entire leader
	
	public function __construct(DOMNode $objNode = null)
	{
		if ( $objNode != null )
		{
			$this->value = $objNode->nodeValue;
		}
	}
}