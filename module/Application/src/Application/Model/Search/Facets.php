<?php

namespace Application\Model\Search;

/**
 * Search Facets
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

/**
 * Data structure for facets
 */

class Facets
{
	public $groups = array();
	
	/**
	 * Add a facet grouping
	 * 
	 * @param FacetGroup $group
	 */
	
	public function addGroup(FacetGroup $group)
	{
		array_push($this->groups, $group);
	}
	
	/**
	 * Return facet groups
	 * 
	 * @return array of FacetGroup's
	 */	
	
	public function getGroups()
	{
		return $this->groups;
	}	
}
