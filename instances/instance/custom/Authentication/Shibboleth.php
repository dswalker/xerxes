<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Local\Authentication;

use Application\Model\Authentication;
use Application\Model\Authentication\Exception\AccessDeniedException;

/**
 * Custom authentication for shibboleth
 * 
 * @author Jonathan Rochkind
 * @author David Walker
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
	 * This function may either:
	 * 
	 * 1) Throw an AccessDeniedException exception to deny user access 
	 * 2) Set various propertes in $this->user to fill out user information
	 */
	
	protected function mapUserData()
	{
		/* Example:
		
		$this->user->email = $this->request->server->get("email"); 
		
		*/
	}
}
