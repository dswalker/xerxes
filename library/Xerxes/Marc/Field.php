<?php

/**
 *  Abstract field object
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Field.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

abstract class Xerxes_Marc_Field
{
	protected $value;
	
	public function __toString()
	{
		return (string) $this->value;
	}
}