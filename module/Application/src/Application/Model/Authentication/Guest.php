<?php

namespace Application\Model\Authentication;

/**
 * Guest Authentication
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @version
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Guest extends Authentication 
{
	/**
	 * Just register the user with a role of guest
	 */
	
	public function onLogin() 
	{
		$this->role = "guest";
		$this->user->username = "guest@" . session_id ();
		$this->register ();
		
		return true;
	}
}
