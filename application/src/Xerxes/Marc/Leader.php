<?php

namespace Xerxes\Marc;

/**
 * MARC Leader
 * 
 * @author David Walker
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