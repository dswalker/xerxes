<?php

namespace Xerxes\Utility;

/**
 * User Interface
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @version
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

interface User
{
	/**
	 * Is the user authenticated
	 * 
	 * Not guest or temporary local user 
	 */
	
	public function isAuthenticated();
	
	/**
	 * Is the user inside the local ip range
	 */
	
	public function isInLocalIpRange();
	
	/**
	 * Generate a random username 
	 * 
	 * Used for local and guest users
	 * 
	 * @param string $prefix		local or guest
	 * @return string
	 */
	
	public static function genRandomUsername($prefix);
}
