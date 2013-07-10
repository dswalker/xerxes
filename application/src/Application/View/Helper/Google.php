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

class Google extends Search
{
	/**
	 * URL for the full record display, taken from the record
	 * 
	 * @param Record $record
	 * @return string url
	 */
	
	public function linkFullRecord( Record $record )
	{
		$links = $record->getLinks();
		$link = $links[0];
		
		return $link->getUrl();
	}
}