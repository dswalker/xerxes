<?php

namespace Xerxes\Utility;

/**
 * User
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @version
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class User
{
	public $username;
	
	private $role;
	private $ip_address;
	private $ip_range;

	public function __construct(Request $request = null)
	{
		if ( $request != "" )
		{
			// user attributes
			
			$this->username = $request->getSession("username");
			$this->role = $request->getSession("role");
			$this->ip_address = $request->server()->get('REMOTE_ADDR');
			
			// local ip range from config
			
			$registry = Registry::getInstance();
			$this->ip_range = $registry->getConfig( "LOCAL_IP_RANGE", false, null );
			
			// temporarily authenticate local users
			
			if ( $this->username == "" && $this->isInLocalIpRange() == true )
			{
				$this->username = "local@" . session_id(); // @todo: move session_id() to request?
				$this->role = "local";
				
				$request->setSession("username", $this->username);
				$request->setSession("role", $this->role);
			}		
		}
	}
	
	public function isAuthenticated()
	{
		return ( $this->username != "" && $this->role != "local" && $this->role != "guest" );
	}
	
	public function isInLocalIpRange()
	{
		return Parser::isIpAddrInRanges( $this->ip_address, $this->ip_range );
	}
}
