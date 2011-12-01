<?php

/**
 * custom authentication for shibboleth
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: shibboleth.php 2018 2011-11-16 00:06:07Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Xerxes_CustomAuth_Shibboleth extends Xerxes_Model_Authentication_Abstract
{ 
	/**
	 * Implement code in this function to authorize the user and/or map
	 * user data to the local User object.
	 * 
	 * User has already been authenticated when this function is called. 
	 * 
	 * HTTP headers are available via $this->request->getServer("header_name");
	 * 
	 * This function may:
	 * 
	 * 1) Throw a Xerxes_Exception_AccessDenied if based on attributes
	 *    you want to deny user access to logging into Xerxes at all.
	 *    The message should explain why. 
	 * 
	 * 2) Set various propertes in $this->user (a Xerxes_Model_Authentication_User) object if you want
   *    to fill out some more user properties  based on attributes in headers 
	 *    set by Shib. You could even pick a new username, if you so choose.
	 */
	
	protected function mapUserData()
	{
		/* Example:
		
		$this->user->email = $this->request->getServer("email"); 
		
		*/

	}
}
