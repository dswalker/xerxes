<?php

namespace Application\Model\Authentication;

use Xerxes\Utility\DataValue,
	Xerxes\Utility,
	Xerxes\Utility\Parser,
	Xerxes\Utility\Registry,
	Xerxes\Utility\Request;

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

class User extends DataValue implements Utility\User
{
	public $username;
	
	public $last_login;
	public $suspended;
	public $first_name;
	public $last_name;
	public $email_addr;
	public $usergroups = array();	
	
	private $role;
	private $ip_address;
	
	private $ip_range;
	private static $request;
	
	const LOCAL = "local";
	const GUEST = "guest";
	
	/**
	 * Create a User
	 * 
	 * @param Request $request		[optional] create user from existing session
	 */

	public function __construct(Request $request = null)
	{
		self::$request = $request;
		
		if ( $request != "" )
		{
			// user attributes
			
			$this->username = $request->getSessionData("username");
			$this->role = $request->getSessionData("role");
			$this->ip_address = $request->server()->get('REMOTE_ADDR');
			
			// local ip range from config
			
			$registry = Registry::getInstance();
			$this->ip_range = $registry->getConfig( "LOCAL_IP_RANGE", false, null );
			
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
			$session_id = self::$request->session()->getId();
			
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
	
	public function getIpAddress()
	{
		return $this->ip_address;
	}
}
