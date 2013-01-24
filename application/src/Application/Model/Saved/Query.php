<?php

namespace Application\Model\Saved;

use Application\Model\Search;

/**
 * Summon Search Query
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Query extends Search\Query
{
	/**
	 * @see Application\Model\Search.Query::checkSpelling()
	 */
	
	public function checkSpelling()
	{
		return null;
	}
}
