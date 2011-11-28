<?php

/**
 * Search Facets
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Facets.php 1656 2011-02-15 21:16:30Z dwalker@calstate.edu $
 * @package Xerxes
 */

/**
 * Data structure for facets
 */

class Xerxes_Model_Search_Facets
{
	public $groups = array();
	
	/**
	 * Add a facet grouping
	 * 
	 * @param Xerxes_Model_Search_FacetGroup $group
	 */
	
	public function addGroup(Xerxes_Model_Search_FacetGroup $group)
	{
		array_push($this->groups, $group);
	}
	
	/**
	 * Return facet groups
	 * 
	 * @return array of Xerxes_Model_Search_FacetGroup's
	 */	
	
	public function getGroups()
	{
		return $this->groups;
	}	
}
