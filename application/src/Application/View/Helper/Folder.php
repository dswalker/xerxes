<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\View\Helper;

use Xerxes\Record;

class Folder extends Search
{
	/**
	 * Add no links!
	 */
	
	public function addBibRecordLinks(Record $xerxes_record )
	{
		// this is purposefully empty
	}
}