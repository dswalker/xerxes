<?php

namespace Xerxes\Utility;

use Zend\Http\PhpEnvironment\Request as ZendRequest,
	Zend\Mvc\Router\RouteStack,
	Zend\Mvc\Router\RouteMatch;

/**
 * Process parameter in the request, either from HTTP or CLI, as well as session
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes_Framework
 */

class Request extends ZendRequest
{
	private $commandline = false;
	private $params = array(); // request paramaters
	private $router; // router
	private $route_match; // route stack match
	private $registry; // registry
	
	public function __construct()
	{
		parent::__construct();

		$this->registry = Registry::getInstance();
		
		$this->extractQueryParams();
	}
	
    public function setRouter(RouteStack $router)
    {
        $this->router = $router;
        
        // now extract the route elements and set them as params
        
        $this->route_match = $router->match($this);
        
        foreach ($this->route_match->getParams() as $name => $value )
        {
        	$this->setParam($name, $value);
        }
    }
	
	/**
	 * Process the incoming request paramaters
	 */
	
	protected function extractQueryParams()
	{
		// coming from http
			
		if ( isset($_SERVER) )
		{
			// got a query string? 
				
			if ( $_SERVER['QUERY_STRING'] != "" )
			{
				// querystring can be delimited either with ampersand
				// or semicolon
					
				$params = preg_split( "/&|;/", $_SERVER['QUERY_STRING'] );
					
				foreach ( $params as $strParam )
				{
					// split out key and value on equal sign
						
					$iEqual = strpos( $strParam, "=" );
						
					if ( $iEqual !== false )
					{
						$strKey = substr( $strParam, 0, $iEqual );
						$strValue = substr( $strParam, $iEqual + 1 );
						
						$this->setParam( $strKey, urldecode( $strValue ) );
					}
				}
			}
			
			// set mobile
				
			if ( $this->getSession('is_mobile') == null )
			{
				$this->setSession('is_mobile', (string) $this->isMobileDevice());
			}
				
			// troubleshooting mobile
				
			if ( $this->getParam("is_mobile") != "" )
			{
				$this->setSession('is_mobile', $this->getParam("is_mobile"));
			}
		} 
		else
		{
			// request has come in from the command line
				
			$this->commandline = true;
				
			foreach ( $_SERVER['argv'] as $arg )
			{
				if ( strpos( $arg, "=" ) )
				{
					list ( $key, $val ) = explode( "=", $arg );
					$this->setParam( $key, $val );
				}
			}
		}
			
		### reverse proxy
			
		// check to see if xerxes is running behind a reverse proxy and swap
		// host and remote ip here with their http_x_forwarded counterparts;
		// but only if configured for this, since client can spoof the header 
		// if xerxes is not, in fact, behind a reverse proxy
				
		if ( $this->registry->getConfig("REVERSE_PROXY", false, false ) == true )
		{
			$forward_host = $_SERVER['HTTP_X_FORWARDED_HOST'];
			$forward_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
					
			if ( $forward_host != "" )
			{
				$_SERVER['SERVER_NAME'] = $forward_host;
			}
					
			// last ip address is the user's
					
			if ( $forward_address != "" )
			{
				$arrIP = explode(",", $forward_address);
				$_SERVER['REMOTE_ADDR'] = trim(array_pop($arrIP));
			}		
		}
	}
	
	/**
	 * Whether the request came in on the command line
	 *
	 * @return bool		true if came in on the cli
	 */
	
	public function isCommandLine()
	{
		return $this->commandline;
	}
	
	/**
	 * Simple function to detect if the user has a mobile device
	 */
	
	public function isMobileDevice()
	{
		require_once( __DIR__ . '/mobile/mobile_device_detect.php');		
		$is_mobile = @mobile_device_detect(true, false); // supress errors because this library is goofy
		return $is_mobile[0];
	}
	
	/**
	 * Add a value to the request parameters
	 *
	 * @param string $key		key to identify the value
	 * @param string $value		value to add
	 * @param bool $bolArray	[optional] set to true will ensure property is set as array
	 */
	
	public function setParam( $key, $value, $bolArray = false, $override = false )
	{
		if ( ! is_array($value) )
		{
			$value = trim($value);
		}
		
		if ( array_key_exists( $key, $this->params ) && $override == false )
		{
			// if there is an existing element, then we always push in the
			// the new value into an array, first converting the exising value
			// to an array if it is not already one 
			
			if ( ! is_array( $this->params[$key] ) )
			{
				$this->params[$key] = array ($this->params[$key] );
			}
			
			array_push( $this->params[$key], $value );
		} 
		elseif ( $bolArray == true )
		{
			// no existing value in property, but the calling code says 
			// this *must* be added as an array, so make it an array, if not one already
			
			if ( ! is_array( $value ) )
			{
				$value = array ($value );
			}
			
			$this->params[$key] = $value;
		} 
		else
		{
			$this->params[$key] = $value;
		}
	}
	
	/**
	 * Retrieve a value from the request parameters
	 *
	 * @param string $key		key that identify the value
	 * @param string $default	[optional] a default value to return if no param supplied
	 * @param bool $bolArray	[optional] whether value should be returned as an array, even if only one value
	 * 
	 * @return mixed 			[string or array] value if available, otherwise default
	 */
	
	public function getParam( $key, $default = null, $bolArray = false )
	{
		if ( array_key_exists( $key, $this->params ) )
		{
			// if the value is requested as array, but is not array, make it one!
			
			if ( $bolArray == true && ! is_array( $this->params[$key] ) )
			{
				return array ($this->params[$key] );
			} 
			
			// the opposite: if the the value is not requested as array but is,
			// take just the first value in the array
			
			elseif ( $bolArray == false && is_array( $this->params[$key] ) )
			{
				return $this->params[$key][0];
			} 
			else
			{
				return $this->params[$key];
			}
		} 
		else
		{
			return $default;
		}
	}

	/**
	 * Get a group of properties using regular expression
	 * 
	 * @param string $regex		(optional) regular expression for properties to get
	 * @param bool $shrink		(optional) whether to collapse properties stored as 
	 * 				array into simple element, default false
	 * @param string $shrink_del 	(opptional) if $shrink is true, then separate multiple 
	 *				elements by this character, default comma
	 * 
	 * @return array
	 */
	
	public function getParams( $regex = "", $shrink = false, $shrink_del = "," )
	{
		$arrFinal = array();
		
		// no filter supplied, so return all params
		
		if ( $regex == "")
		{
			return $this->params;
		}
		
		foreach ( $this->params as $key => $value )
		{
			$key = urldecode($key);
			
			// find specific params
			
			if ( preg_match("/" . $regex . "/", $key) )
			{
				// slip empty fields
				
				if ( is_array($value) )
				{
					$check = implode("", array_values($value));
					
					if ( $check == "" )
					{
						continue;
					}
				}				
				
				if ( $value == "")
				{
					continue;
				}
				
				if ( is_array($value) && $shrink == true )
				{
					$concated = "";
					
					foreach ( $value as $data )
					{
						if ( $data != "" )
						{
							if ($concated == "")
							{
								$concated = $data;
							}
							else
							{
								$concated .= $shrink_del . $data;
							}
						}
					}
					
					if ( $concated == "" )
					{
						continue;
					}
				
					$value = $concated;
				}
				
				$arrFinal[$key] = $value;
			}
		}
		
		return $arrFinal;
	}
	
	/**
	 * Remove a URL parameter
	 *
	 * @param string $key		the name of the param
	 * @param string $value		[optional] only if the param has this value
	 */
	
	public function removeParam($key, $value = "")
	{
		if ( array_key_exists( $key, $this->params ) )
		{
			$stored = $this->params[$key];
	
			// if this is an array, we need to find the right one
	
			if ( is_array( $stored ) )
			{
				for ( $x = 0; $x < count($stored); $x++ )
				{
					if ( $stored[$x] == $value )
					{
						unset($this->params[$key][$x]);
					}
				}
	
				// reset the keys
	
				$this->params[$key] = array_values($this->params[$key]);
			}
			else
			{
				unset($this->params[$key]);
			}
		}
	}
	
	public function url_for($params = array(), $options = array())
	{
		// use the default route if no option supplied
		
		if ( count($options) == 0 )
		{
			$options["name"] = "default";
		}
		
		if ( null === $this->router )
		{
			return '';
		}
		
		// this only returns the route, no querystring
		
		$url = $this->router->assemble($params, $options);
		
		// remove trailing '/index' from generated URLs.
		
		if ((6 <= strlen($url)) && '/index' == substr($url, -6)) 
		{
			$url = substr($url, 0, strlen($url) - 6);
		}
		
		// append query string
		
		$query_string = array();
		
		// insepect each supplied params to see if was matched
		// in the route
		
		foreach ( $params as $id => $param )
		{
			// skip empty ones
			
			if ( $param == "" )
			{
				continue;
			}
			
			// not in the route, so add it to the query string
			
			if ( $this->route_match->getParam($id) == "" )
			{
				$query_string[$id] = $param;
			}
		}
		
		// if we have a query string
		
		if ( count($query_string) > 0 )
		{
			$url .= "?";
			
			$x = 0;

			 foreach ( $query_string as $name => $value )
			 {
			 	if ( $x > 0 ) // first param doesn't need & prefix
			 	{
			 		$url .= '&amp;';
			 	}
			 	
			 	$url .= $name . '=' . urlencode($value);
			 	
			 	$x++;
			 }
		}
		
		return $url;
	}
	
	/**
	 * serialize to xml
	 * 
	 * @param bool $bolHideServer	[optional]	true will exclude the server variables from the response, default false
	 *
	 * @return DOMDocument
	 */
	
	public function toXML($bolHideServer = false)
	{
		// add the url parameters and session and server global arrays
		// to the master xml document
		
		$xml = new \DOMDocument( );
		$xml->loadXML( "<request />" );
		
		// session and server global arrays will have parent elements
		// but querystring will be at the root of request
		
		$this->addElement( $xml, $xml->documentElement, $this->params );
		
		// add the session global array
		
		$session = $xml->createElement( "session" );
		$xml->documentElement->appendChild( $session );
		$this->addElement( $xml, $session, $_SESSION );
		
		// add the server global array
		// but only if the request asks for it, for security purposes
		
		if ( $bolHideServer == true )
		{
			$server = $xml->createElement( "server" );
			$xml->documentElement->appendChild( $server );
			$this->addElement( $xml, $server, $_SERVER );
		}
		
		return $xml;
	}
	
	/**
	 * Add global array as xml to request xml document
	 *
	 * @param DOMDocument $xml		[by reference] request xml document
	 * @param DOMNode $objAppend		[by reference] node to append values to
	 * @param array $arrValues			global array
	 */
	
	private function addElement(&$xml, &$objAppend, $arrValues)
	{
		foreach ( $arrValues as $key => $value )
		{
			// need to make sure the xml element has a valid name
			// and not something crazy with spaces or commas, etc.
			
			$strSafeKey = Parser::strtolower( preg_replace( '/\W/', '_', $key ) );
			
			if ( is_array( $value ) )
			{
				foreach ( $value as $strKey => $strValue )
				{
					$objElement = $xml->createElement( $strSafeKey );
					$objElement->setAttribute( "key", $strKey );
					$objAppend->appendChild( $objElement );
					
					if ( is_array( $strValue ) )
					{
						// multi-dimensional arrays will be recursively added
						$this->addElement($xml, $objElement, $strValue);
					}
					else
					{
						$objElement->nodeValue = Parser::escapeXml( $strValue );
					}
				}
			}
			else
			{
				$objElement = $xml->createElement( $strSafeKey, Parser::escapeXml( $value ) );
				$objAppend->appendChild( $objElement );
			}
		}
	}

	
	
	
	
	
	
	
	
	
	
	// @todo: use zend\session
	
	
	
	/**
	 * Get a value stored in the session
	 *
	 * @param string $key	variable name
	 * @return mixed
	 */
	
	public function getSession($key)
	{
		if ( isset($_SESSION) )
		{
			if ( array_key_exists( $key, $_SESSION ) )
			{
				return $_SESSION[$key];
			}
		}
	
		return null;
	}
	
	/**
	 * Get all session variables
	 *
	 * @return array
	 */
	
	public function getAllSession()
	{
		return $_SESSION;
	}
	
	/**
	 * Save a value in session state
	 *
	 * @param string $key		name of the variable
	 * @param mixed $value		value of the variable
	 */
	
	public function setSession($key, $value)
	{
		$_SESSION[$key] = $value;
	}	
	
}
