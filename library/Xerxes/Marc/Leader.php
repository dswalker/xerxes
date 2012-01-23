<?php

namespace Xerxes\Marc;

/**
 * MARC Leader
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Leader extends ControlField 
{
	public $value; // the entire leader
	
	/**
	 * Create a MARC Leader 
	 * 
	 * @param \DOMNode $node
	 */
	
	public function __construct(\DOMNode $node = null)
	{
		if ( $node != null )
		{
			$this->value = $node->nodeValue;
		}
	}
}