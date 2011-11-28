<?php

/**
 * Sibboleth Authentication
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @version $Id$
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Xerxes_Model_Authentication_Shibboleth extends Xerxes_Model_Authentication_Abstract 
{
	/**
	 *  HTTP header that the username will be found in. Subclass can over-ride
	 *  if different. 
	 */
	
	public function usernameHeader()
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
	 * For shibboleth, if user got this far, we should have authentication
	 * params in header already from the Shib SP and apache config, just read 
	 * what's been provided. 
	 */
	
	public function onLogin()
	{
		// get username header from proper psuedo-HTTP header set by apache
		$strUsername = $this->request->getServer( $this->usernameHeader() );
		
		if ( $strUsername != null )
		{
			$this->user->username = $strUsername;
			
			// set usergroups to null meaning unless the delegate sets
			// usergroups, we'll just keep what's in the db, if anything. 
			
			$this->user->usergroups = null;
		
			// let the 'local' child class parse the headers
			
			$this->mapUserData();
			
			// register the user
			
			$this->register();
			
			return true;
		}
		else 
		{
			throw new Exception("Couldn't find Shibboleth supplied username in header");	
		}
	}
	
	/**
	 * Shibboleth_Local class defines this
	 */
	
	protected function mapHeaders()
	{
		
	}
}
