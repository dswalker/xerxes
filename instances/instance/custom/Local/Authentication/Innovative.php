<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Local\Authentication;

use Application\Model\Authentication;
use Xerxes\Mvc\AccessDeniedException;

/**
 * Custom authentication for III patron api
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Innovative extends Authentication\Innovative
{ 
	/**
	 * Implement code in this function to authorize the user and/or map
	 * the user's informtion from the Patron API
	 * 
	 * User has already been authenticated when this function is called. 
	 * 
	 * 1) Throw an AccessDeniedException exception to deny user access 
	 * 2) Set various propertes in $this->user to fill out user information
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
