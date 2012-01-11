<?php

namespace Application\Model\Metalib;

/**
 * Metalib Search Status
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Status
{
	protected $result_sets = array(); // individual database record sets
	protected $timestamp = 0; // timestamp of status check
	protected $finished = false; // whether search is complete
	
	public function __construct()
	{
		$this->timestamp = time();
	}
	
	public function addDatabaseResultSet( DatabaseResultSet $set)
	{
		$this->record_sets[] = $set;
	}
	
	public function getDatabaseResultSet()
	{
		return $this->record_sets;
	}
	
	public function setFinished($finished)
	{
		$this->finished = (bool) $finished;
	}
	
	public function isFinished()
	{
		return $this->finished;
	}
}
