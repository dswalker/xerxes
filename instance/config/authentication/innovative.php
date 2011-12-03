<?php

/**
 * custom authentication for iii patron api
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Xerxes_CustomAuth_Innovative extends Xerxes_Model_Authentication_Abstract
{ 
	/**
	 * Implement code in this function to authorize the user and/or map
	 * the user's informtion from the Patron API
	 * 
	 * User has already been authenticated when this function is called. 
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
