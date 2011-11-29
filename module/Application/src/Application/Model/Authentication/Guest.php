<?php

namespace Application\Model\Authentication;

/**
 * Guest Authentication
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @version $Id: GuestAuthentication.php 1145 2010-04-30 22:22:23Z dwalker@calstate.edu $
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Guest extends AbstractAuthentication 
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
