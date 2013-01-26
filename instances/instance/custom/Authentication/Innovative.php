<?php

namespace Local\Authentication;

use Application\Model\Authentication;

/**
 * custom authentication for iii patron api
 * 
 * @author David Walker
 * @copyright 2013 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Innovative extends Authentication\Innovative
{ 
	/**
	 * Implement code in this function to authorize the user and/or map
	 * the user's informtion from the Patron API
	 * 
	 * User has already been authenticated when this function is called. 
	 * 
	 * This function may:
	 * 
	 * 1) Throw an Authentication\AccessDeniedException exception if you want to deny user 
	 *    access to logging into Xerxes at all. The message should explain why. 
	 * 
	 * 2) Set various propertes in $this->user if you want to fill out some more user properties
	 */
	
	protected function mapUserData()
	{
		/* EXAMPLE:

		$arrName = explode(",", $this->user_data["PATRN NAME"]);
		
		if ( count($arrName) == 2 )
		{
			$this->user->first_name = trim($arrName[1]);
			$this->user->last_name = trim($arrName[2]);
		}
		
		$this->user->email_addr = $this->user_data["EMAIL ADDR"];

		*/
		
	}
}
