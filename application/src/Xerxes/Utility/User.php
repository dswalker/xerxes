<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Utility;

use Xerxes\Mvc\Request;

/**
 * User
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class User extends DataValue
{
	/**
	 * Username
	 * @var string
	 */
	public $username;
	
	/**
	 * Date of last login
	 * @var string
	 */
	public $last_login;
	
	/**
	 * Is account suspended
	 * @var boolean
	 */
	public $suspended;
	
	/**
	 * First name
	 * @var string
	 */
	public $first_name;
	
	/**
	 * Last name
	 * @var string
	 */
	public $last_name;
	
	/**
	 * Email address
	 * @var string
	 */
	public $email_addr;

	/**
	 * Usergroups
	 * @var array
	 */
	public $usergroups = array();	
	
	/**
	 * User's role (local, guest, named, etc.)
	 * @var string
	 */
	private $role;

	/**
	 * Is this an admin user?
	 * @var bool
	 */
	private $admin = false;	
	
	/**
	 * IP address
	 * @var string
	 */
	private $ip_address;
	
	/**
	 * Campus IP Range
	 * @var string
	 */
	private $ip_range;
	
	/**
	 * @var Request
	 */
	private static $request;

	/**
	 * @var Registry
	 */
	private static $registry;	
	
	const LOCAL = "local";
	const GUEST = "guest";
	
	/**
	 * Create a User
	 * 
	 * @param Request $request  [optional] create user from current Request
	 */

	public function __construct(Request $request = null)
	{
		self::$request = $request;
		$this->registry = Registry::getInstance();
		
		if ( $request != "" )
		{
			// user attributes
			
			$this->username = $request->getSessionData("username");
			$this->role = $request->getSessionData("role");
			$this->ip_address = $request->getClientIp();
			$this->admin = $request->getSessionData('user_admin');
			
			// local ip range from config
			
			$this->ip_range = $this->registry->getConfig( "LOCAL_IP_RANGE", false, null );
			
			// temporarily authenticate users
			
			if ( $this->username == "")
			{
				// on campus
				
				if ( $this->isInLocalIpRange() == true )
				{
					$this->username = self::genRandomUsername(self::LOCAL);
					$this->role = self::LOCAL;
				}
				else
				{
					$this->username = self::genRandomUsername(self::GUEST);
					$this->role = self::GUEST;
				}
				
				$request->setSessionData("username", $this->username);
				$request->setSessionData("role", $this->role);
			}		
		}
	}
	
	/**
	 * Is the user a local user or authenticated
	 */	
	
	public function isAuthorized()
	{
		if ( $this->isAuthenticated() || $this->isInLocalIpRange() )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Is the user authenticated
	 * 
	 * Not guest or temporary local user 
	 */
	
	public function isAuthenticated()
	{
		return ( $this->username != "" && $this->role != self::LOCAL && $this->role != self::GUEST );
	}
	
	/**
	 * Is the user inside the local ip range
	 */
	
	public function isInLocalIpRange()
	{
		return Parser::isIpAddrInRanges( $this->ip_address, $this->ip_range );
	}
	
	/**
	 * Generate a random username 
	 * 
	 * Used for local and guest users
	 * 
	 * @param string $prefix		local or guest
	 * @return string
	 */
	
	public static function genRandomUsername($prefix)
	{
		$string = "";
		
		// take value from session id
		
		if ( self::$request instanceof Request )
		{
			$session_id = self::$request->getSession()->getId();
			
			if ( $session_id != "" )
			{
				$string = $session_id;
			}
		}
		else // let's construct one randomly
		{
			$length = 10;
			$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
			$string = "";    
			
			for ($p = 0; $p < $length; $p++)
			{
				$string .= $characters[mt_rand(0, strlen($characters) - 1)];
			}
		}
		
		return $prefix . '@' . $string;
	}
	
	/**
	 * Whether this is a guest user
	 */
	
	public function isGuest()
	{
		if ( $this->role == self::GUEST )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Whether this is a (temporary) local user
	 */
	
	public function isLocal()
	{
		if ( $this->role == self::LOCAL )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Whether user is admin
	 */
	
	public function isAdmin()
	{
		return $this->admin;
	}	
	
	/**
	 * Get remote IP address of user
	 */
	
	public function getIpAddress()
	{
		return $this->ip_address;
	}
}
