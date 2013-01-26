<?php

namespace Application\Model\Bx;

use Xerxes\Record\ContextObject;

/**
 * Bx Record
 * 
 * @author David Walker
 */

class Record extends ContextObject
{
	protected $database_name = "bX";
	
	protected function map()
	{
		parent::map();
	}
}
