<?php

namespace Xerxes\Utility;

/**
 * Restict access to a portion of an application
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @version $Id: Restrict.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Restrict
{
	private $ip_range; // set of local ip ranges
	private $request; // request object
	private $return; // return link

	/**
	 * Constructor
	 * 
	 * @param $objRequest request object
	 */
	
	public function __construct(Request $request)
	{
		$registry = Registry::getInstance();
		
		$this->request = $request;
		
		$authentication_page = $this->request->url_for( array ("base" => "authenticate", "action" => "login" ) );
		
		$this->ip_range = $registry->getConfig( "LOCAL_IP_RANGE", false, null );
		
		// if the return url has a querystring mark in it, then append
		// return url to other params, otherwise it is sole param

		if ( strstr( $authentication_page, "?" ) )
		{
			$this->return = "$authentication_page&return=";
		}
		else
		{
			$this->return = "$authentication_page?return=";
		}
	}
	
	/**
	 * Limit access to users with a named login, otherwise redirect to login page
	 */
	
	public function checkLogin()
	{
		if (! self::isAuthenticatedUser( $this->request ) )
		{
			// redirect to authentication page

			header( "Location: " . $this->return . urlencode( $this->request->getServer( 'REQUEST_URI' ) ) );
			exit();
		}
	}
	
	/**
	 * Checks if the session has a logged in authenticated user. not "guest" or "local" role, 
	 * both of which imply a temporary session, not an authenticated user.
	 */
	
	public static function isAuthenticatedUser(Xerxes_Framework_Request $objRequest)
	{
		return ( $objRequest->getSession( "username" ) != null && 
			$objRequest->getSession( "role" ) != "local" && 
			$objRequest->getSession( "role" ) != "guest"
		);
	}
	
	/**
	 * Limit access to users within the local ip range, assigning local users a temporary
	 * login id, and redirecting non-local users out to login page
	 */
	
	public function checkIP($bolRedirect = true)
	{
		if ( $this->request->getSession( "username" ) == null )
		{
			// check to see if user is coming from campus range						

			$bolLocal = self::isIpAddrInRanges( $this->request->getServer('REMOTE_ADDR'), $this->ip_range );
			
			if ( $bolLocal == true )
			{
				// temporarily authenticate on-campus users

				$_SESSION["username"] = "local@" . session_id();
				$_SESSION["role"] = "local";
			}
			elseif ( $bolRedirect == true )
			{
				// redirect to authentication page

				header( "Location: " . $this->return . urlencode( $this->request->getServer( 'REQUEST_URI' ) ) );
				exit();
			}
		}
	}
	
	/**
	 * Strips periods and pads the subnets of an IP address to three spaces, 
	 * e.g., 144.37.1.23 = 144037001023, to make it easier to see if a remote 
	 * user's IP falls within a range
	 *
	 * @param string $strOriginal		original ip address
	 * @return string					address normalized with extra zeros
	 */
	
	private static function normalizeAddress($strOriginal)
	{
		$strNormalized = "";
		$arrAddress = explode( ".", $strOriginal );
		
		foreach ( $arrAddress as $subnet )
		{
			$strNormalized .= str_pad( $subnet, 3, "0", STR_PAD_LEFT );
		}
		
		return $strNormalized;
	}
	
	/**
	 * Is the ip address within the supplied ip range(s)
	 * For syntax/formatting of an ip range string, see config.xml.
	 * Basically, it's comma separated ranges, where each range can use
	 * wildcard (*) or hyphen to separate endpoints. 
	 *
	 * @param string $strAddress	ip address
	 * @param string $strRanges		ip ranges
	 * @return bool					true if in range, otherwise false
	 */
	
	public static function isIpAddrInRanges($strAddress, $strRanges)
	{
		$bolLocal = false;
		
		// normalize the remote address

		$iRemoteAddress = self::normalizeAddress( $strAddress );
		
		// get the local campus ip range from config

		$arrRange = array ( );
		$arrRange = explode( ",", $strRanges );
		
		// loop through ranges -- can be more than one

		foreach ( $arrRange as $range )
		{
			$range = str_replace( " ", "", $range );
			$iStart = null;
			$iEnd = null;
			
			// normalize the campus range

			if ( strpos( $range, "-" ) !== false )
			{
				// range expressed with start and stop addresses

				$arrLocalRange = explode( "-", $range );
				
				$iStart = self::normalizeAddress( $arrLocalRange[0] );
				$iEnd = self::normalizeAddress( $arrLocalRange[1] );
			}
			else
			{
				// range expressed with wildcards

				$strStart = str_replace( "*", "000", $range );
				$strEnd = str_replace( "*", "255", $range );
				
				$iStart = self::normalizeAddress( $strStart );
				$iEnd = self::normalizeAddress( $strEnd );
			
			}
			
			// see if remote address falls in between the campus range

			if ( $iRemoteAddress >= $iStart && $iRemoteAddress <= $iEnd )
			{
				$bolLocal = true;
			}
		}
		
		return $bolLocal;
	}
}
