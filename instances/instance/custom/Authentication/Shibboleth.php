<?php

namespace Local\Authentication;

use Application\Model\Authentication;

/**
 * Custom authentication for shibboleth
 * 
 * @author Jonathan Rochkind
 * @author David Walker
 * @copyright 2013 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Shibboleth extends Authentication\Shibboleth
{ 
	/**
	 * Implement code in this function to authorize the user and/or map
	 * user data to the local User object.
	 * 
	 * User has already been authenticated when this function is called. 
	 * 
	 * HTTP headers are available via $this->request->server->get("header_name");
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
		/* Example:
		
		$this->user->email = $this->request->server->get("email"); 
		
		*/
	}
}
