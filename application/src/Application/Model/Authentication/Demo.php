<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Authentication;

/**
 * Authenticate users against the 'demo_users' list in configuration file
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Demo extends Scheme 
{
	/**
	* Authenticate the user against the local config user list
	*/
	
	public function onCallBack()
	{
		$strUsername = $this->request->getParam( "username" );
		$strPassword = $this->request->getParam( "password" );
		
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
			return $this->register();
		}
		else
		{
			return self::FAILED;
		}
	}
}
