<?php

namespace Application\Model\Metalib;

use Application\Model\KnowledgeBase\Database,
	Application\Model\Search;

/**
 * Metalib Search Results
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ResultSet
{
	public $database;
	public $set_number;
	public $find_status;
	
	public function __construct(Database $database)
	{
		$this->database = $database;
	}
}
