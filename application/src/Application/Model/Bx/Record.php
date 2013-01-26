<?php

namespace Application\Model\Bx;

use Xerxes\Record\ContextObject;

/**
 * Bx Record
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Record extends ContextObject
{
	protected $database_name = "bX";
	
	protected function map()
	{
		parent::map();
	}
}
