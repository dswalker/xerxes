<?php

namespace Application\Model\Metalib;

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

class Group
{
	public $date; // date search was initialized
	public $id; // id for the group
	public $query; // metalib search query
	
	public $total = 0; // total number of hits
	public $resultsets = array(); // resultset objects
	public $facets; // facet object
}
