<?php

namespace Xerxes\Marc;

/**
 *  Abstract field object
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class Field
{
	protected $value;
	
	public function __toString()
	{
		return (string) $this->value;
	}
}