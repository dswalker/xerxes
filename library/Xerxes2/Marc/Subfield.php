<?php

namespace Xerxes\Marc;

/**
 * MARC Sub Field
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class SubField extends Field 
{
	public $code;
	public $value;
	
	/**
	 * Create a MARC Sub Field
	 * 
	 * @param \DOMNode $node
	 */
	
	public function __construct(\DOMNode $node = null )
	{
		if ( $node != null )
		{
			$this->code = $node->getAttribute("code");
			$this->value = $node->nodeValue;
		}
	}
}