<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Marc;

/**
 * MARC Leader
 * 
 * @author David Walker <dwalker@calstate.edu>
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