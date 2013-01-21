<?php

namespace Xerxes\Marc;

/**
 *  Abstract field object
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

abstract class Field
{
	protected $value;
	
	public function __toString()
	{
		return (string) $this->value;
	}
}