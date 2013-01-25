<?php

namespace Application\Model\Bx;

use Xerxes\Record\ContextObject;

/**
 * Bx Record
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @version
 * @package Xerxes
 */

class Record extends ContextObject
{
	protected $database_name = "bX";
	
	protected function map()
	{
		parent::map();
	}
}
