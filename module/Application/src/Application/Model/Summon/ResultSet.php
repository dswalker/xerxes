<?php

namespace Application\Model\Summon;

use Application\Model\Search;

/**
 * Search Results
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ResultSet extends Search\ResultSet
{
	public $database_recommendations;

	public function addRecommendation(Database $database)
	{
		$this->database_recommendations[] = $database;
	}
}
