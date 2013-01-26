<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Marc;

/**
 * MARC Sub Field
 * 
 * @author David Walker <dwalker@calstate.edu>
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