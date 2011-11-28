<?php

/**
 * Authenticate users against the 'demo_users' list in configuration file
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Authentication_Demo extends Xerxes_Model_Authentication_Abstract 
{
	/**
	* Authenticates the user against the directory server
	*/
	
	public function onCallBack()
	{
		$strUsername = $this->request->getProperty( "username" );
		$strPassword = $this->request->getProperty( "password" );
		
		$configDemoUsers = $this->registry->getConfig( "DEMO_USERS", false );

		// see if user is in demo user list
		
		$bolAuth = false;
		
		if ( $configDemoUsers != null )
		{
			// get demo user list from config
			
			$arrUsers = explode( ",", $configDemoUsers );
			
			foreach ( $arrUsers as $user )
			{
				$user = trim( $user );
				
				// split the username and password

				$arrCredentials = array ( );
				$arrCredentials = explode( ":", $user );
				
				$strDemoUsername = $arrCredentials[0];
				$strDemoPassword = $arrCredentials[1];
				
				if ( $strUsername == $strDemoUsername && $strPassword == $strDemoPassword )
				{
					$bolAuth = true;
				}
			}
		}			
		
		if ( $bolAuth == true )
		{
			// register the user and stop the flow
			
			$this->user->username = $strUsername;
			$this->register();
			return true;
		}
		else
		{
			return false;
		}
	}
}
