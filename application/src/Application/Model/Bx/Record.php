<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
