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
	
	const LOCAL = "local";
	const GUEST = "guest";
	
	/**
	 * Create a User
	 * 
	 * @param Request $request		[optional] create user from existing session
	 */

	public function __construct(Request $request = null)
	{
		if ( $request != "" )
		{
			// user attributes
			
			$this->username = $request->getSessionData("username");
			$this->role = $request->getSessionData("role");
			$this->ip_address = $request->server()->get('REMOTE_ADDR');
			
			// local ip range from config
			
			$registry = Registry::getInstance();
			$this->ip_range = $registry->getConfig( "LOCAL_IP_RANGE", false, null );
			
			// temporarily authenticate local users
			
			if ( $this->username == "" && $this->isInLocalIpRange() == true )
			{
				$this->username = self::genRandomUsername(self::LOCAL);
				$this->role = self::LOCAL;
				
				$request->setSessionData("username", $this->username);
				$request->setSessionData("role", $this->role);
			}		
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
		$length = 10;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$string = "";    
		
		for ($p = 0; $p < $length; $p++)
		{
			$string .= $characters[mt_rand(0, strlen($characters) - 1)];
		}
		
		return $prefix . '@' . $string;
	}
}
