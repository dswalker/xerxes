<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

/**
 * Search Facets
 *
 * @author David Walker <dwalker@calstate.edu>
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
