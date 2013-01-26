<?php

namespace Application\Model\Authentication;

/**
 * Sibboleth Authentication
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @version
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license 
 */

class Shibboleth extends Scheme 
{
	/**
	 * Register the user after authentication
	 * 
	 * For shibboleth, if user got this far, we should have authentication
	 * params in header already from the Shib SP and apache config, just read 
	 * what's been provided. 
	 */
	
	public function onLogin()
	{
		// get username header from proper psuedo-HTTP header set by apache
		$strUsername = $this->request->getServer( $this->getUsernameHeader() );
		
		if ( $strUsername != null )
		{
			$this->user->username = $strUsername;
			
			// set usergroups to null meaning unless the delegate sets
			// usergroups, we'll just keep what's in the db, if anything. 
			
			$this->user->usergroups = null;
		
			// let the 'local' child class parse the headers
			
			$this->mapUserData();
			
			// register the user
			
			return $this->register();
		}
		else 
		{
			throw new \Exception("Couldn't find Shibboleth supplied username in header");	
		}
	}
	
	/**
	 *  HTTP header that the username will be found in
	 *
	 *  Subclass can over-ride if different.
	 *
	 *  @return string
	 */
	
	protected function getUsernameHeader()
	{
		// apache might have this one if you are using mod_rewrite
	
		if ( $this->request->getServer("REDIRECT_REMOTE_USER") != "" )
		{
			return "REDIRECT_REMOTE_USER";
		}
		elseif ( $this->request->getServer("HTTP_REMOTE_USER") != "" )
		{
			// apache might have this if so configured; iis will always have this
				
			return "HTTP_REMOTE_USER";
		}
		else
		{
			return "REMOTE_USER";
		}
	}	
	
	/**
	 * Map headers to user object
	 * 
	 * Local Shibboleth class defines this
	 */
	
	protected function mapUserData()
	{
		
	}
}
