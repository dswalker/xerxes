<?php

namespace Xerxes\Utility;

/**
 * Simple ACL control
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @version
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Restrict
{
	private $ip_range; // set of local ip ranges
	private $request; // request object

	/**
	 * Constructor
	 * 
	 * @param $request request object
	 */
	
	public function __construct(Request $request)
	{
		$this->request = $request;
		
		$registry = Registry::getInstance();
		
		$this->ip_range = $registry->getConfig( "LOCAL_IP_RANGE", false, null );
	}
	
	/**
	 * Limit access to users with a named login, otherwise redirect to login page
	 */
	
	public function checkLogin()
	{
		return self::isAuthenticatedUser( $this->request );
	}
	
	/**
	 * Checks if the session has a logged-in, authenticated user.
	 * 
	 * Not "guest" or "local" role, both of which imply a temporary session, 
	 * not an authenticated user.
	 */
	
	public static function isAuthenticatedUser(Request $request)
	{
		return ( $request->getSession( "username" ) != null && 
			$request->getSession( "role" ) != "local" && 
			$request->getSession( "role" ) != "guest"
		);
	}
	
	/**
	 * Limit access to users within the local ip range
	 * 
	 * Assign local users a temporary login id, and 
	 * redirect non-local users out to login page
	 */
	
	public function checkIP()
	{
		if ( $this->request->getSession("username") == null )
		{
			// check to see if user is coming from local IP range						

			$bolLocal = Parser::isIpAddrInRanges( $this->request->server()->get('REMOTE_ADDR'), $this->ip_range );
			
			if ( $bolLocal == true )
			{
				// temporarily authenticate local users

				$this->request->setSession("username", "local@" . session_id() );
				$this->request->setSession("role", "local");
				
				return true;
			}
			else
			{
				return false;
			}
		}
	}
}
