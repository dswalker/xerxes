<?php

namespace Xerxes\Utility;

use Zend\Http\PhpEnvironment\Request as ZendRequest,
	Zend\Mvc\Router\RouteStackInterface,
	Zend\Mvc\Router\RouteMatch,
	Zend\Session\Container,
	Zend\Session\AbstractManager as Manager,
	Zend\Session\SessionManager;

/**
 * Process HTTP and CLI requests, as well as Session
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes_Utility
 */

class Request extends ZendRequest
{
	private $commandline = false;
	private $params = array(); // request paramaters

	private $registry; // xerxes registry
	private $controller_map; // xerxes controller map
	private $user; // xerxes user
	
	private $router; // zend router	
	private $session; // zend session manager
	private $containers = array(); // array of zend session containers
	
	/**
	 * Create Request object
	 */
	
	public function __construct()
	{
		parent::__construct();

		$this->registry = Registry::getInstance();
		
		$this->extractQueryParams();
	}
	
	/**
	 * Set the router from the MVC framework
	 * 
	 * This will push the route paths into the request as parameters
	 * 
	 * @param RouteStack $router
	 */
	
	public function setRouter(RouteStackInterface $router)
	{
		$this->router = $router;
		
		// now extract the route elements and set them as params
		
		$route_match = $router->match($this);
		
		if ( $route_match instanceof RouteMatch )
		{
			foreach ($route_match->getParams() as $name => $value )
			{
				$this->setParam($name, $value);
			}
		}
	}
	
	/**
	 * Add the Controller Map 
	 * 
	 * This is really just convenience so other parts of the system can
	 * easily gain access to it
	 * 
	 * @param ControllerMap $controller_map
	 */
	
	public function setControllerMap(ControllerMap $controller_map )
	{
		$this->controller_map = $controller_map;
	}
	
	/**
	 * Get the Controller Map
	 * 
	 * @throws \Exception		if no controller map previous set
	 * @return ControllerMap
	 */
	
	public function getControllerMap()
	{
		if ( ! $this->controller_map instanceof ControllerMap )
		{
			throw new \Exception("No controller map set");
		}
		
		return $this->controller_map;
	}
	
	/**
	 * Set the session manager
	 *
	 * @param  Manager $manager
	 * @return FlashMessenger
	 */
	
	public function setSessionManager(Manager $manager)
	{
		$this->session = $manager;
		return $this;
	}
	
	/**
	 * Retrieve the session manager
	 *
	 * If none composed, lazy-loads a SessionManager instance
	 *
	 * @return Manager
	 */
	
	public function session()
	{
		if (!$this->session instanceof Manager)
		{
			$this->setSessionManager(new SessionManager());
		}
		
		return $this->session;
	}
	
	/**
	 * Get session container
	 * 
	 * @param string $name		[optional] id for the container, default is Public
	 *
	 * @return Container
	 */
	
	public function getContainer($name = "Public")
	{
		// got one already?
		
		if ( array_key_exists($name, $this->containers) )
		{
			if ($this->containers[$name] instanceof Container) 
			{
				return $this->containers[$name];
			}
		}
		
		// make a new one
		
		$manager = $this->session();
		$container = new Container($name, $manager);
		
		$this->containers[$name] = $container; // set for later
		
		return $container;
	}	
	
	/**
	 * Add value to Session
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	
	public function setSessionData($key, $value)
	{
		$this->getContainer()->offsetSet($key, $value);
	}
	
	/**
	 * Unset a value in Session
	 *
	 * @param string $key
	 */
	
	public function unsetSessionData($key)
	{
		$this->getContainer()->offsetUnset($key);
	}	
	
	/**
	 * Check if a key is set in Session
	 *
	 * @param string $key
	 */
	
	public function existsInSessionData($key)
	{
		$this->getContainer()->offsetExists($key);
	}	
	
	/**
	 * Get session value
	 * 
	 * @param string $key
	 * @return mixed 		value, if key exists, otherwise null
	 */
	
	public function getSessionData($key)
	{
		return $this->getContainer()->offsetGet($key);
	}
	
	/**
	 * Get all session values
	 * 
	 * @return array
	 */
	
	public function getAllSessionData()
	{
		return $this->getContainer()->getIterator()->getArrayCopy();
	}
	
	/**
	 * Process the incoming request paramaters
	 */
	
	protected function extractQueryParams()
	{
		// coming from http
			
		if ( isset( $_SERVER['QUERY_STRING']) )
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
			
			// post request parameters
			
			foreach ( $_POST as $key => $value )
			{
				$this->setParam( $key, $value );
			}
			
			// set mobile
				
			if ( $this->getSessionData('is_mobile') == null )
			{
				$this->setSessionData('is_mobile', (string) $this->isMobileDevice());
			}
				
			// troubleshooting mobile
				
			if ( $this->getParam("is_mobile") != "" )
			{
				$this->setSessionData('is_mobile', $this->getParam("is_mobile"));
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
			$forward_host = $this->server()->get('HTTP_X_FORWARDED_HOST');
			$forward_address = $this->server()->get('HTTP_X_FORWARDED_FOR');
					
			if ( $forward_host != "" )
			{
				$this->server()->set('SERVER_NAME', $forward_host);
			}
					
			// last ip address is the user's
					
			if ( $forward_address != "" )
			{
				$arrIP = explode(",", $forward_address);
				$this->server()->set('REMOTE_ADDR', trim(array_pop($arrIP)));
			}		
		}
	}
	
	/**
	 * Whether the request came in on the command line
	 *
	 * @return bool
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
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * @return boolean
	 */
	
	public function isXmlHttpRequest()
	{
		$requested_with = $this->headers()->get('X_REQUESTED_WITH');
		
		if ( $requested_with != null )
		{
			if ( $requested_with->getFieldValue() == 'XMLHttpRequest')
			{
				return true;
			}
		}

		return false;
	}	
	
	/**
	 * Add a parameter to the request
	 *
	 * @param string $key			key to identify the value
	 * @param mixed $value			value to add
	 * @param bool $is_array		[optional] set to true will ensure property is set as array
	 * @param bool $override		[optional] replace any existing values
	 */
	
	public function setParam( $key, $value, $is_array = false, $override = false )
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
		elseif ( $is_array == true )
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
	 * Replace a parameter with supplied value
	 *
	 * @param string $key			key to identify the value
	 * @param mixed $value			value to add
	 * @param bool $is_array		[optional] set to true will ensure property is set as array
	 */	
	
	public function replaceParam( $key, $value, $is_array = false )
	{
		$this->setParam( $key, $value, $is_array, true );
	}
	
	/**
	 * Replace all params
	 * 
	 * @param array $params
	 */
	
	public function setParams(array $params)
	{
		$this->params = $params;
	}
	
	/**
	 * Retrieve a value from the request parameters
	 *
	 * @param string $key		key that identify the value
	 * @param string $default	[optional] a default value to return if no param supplied
	 * @param bool $is_array	[optional] whether value should be returned as an array, even if only one value
	 * 
	 * @return string|array 	returns value if available, otherwise default
	 */
	
	public function getParam( $key, $default = null, $is_array = false )
	{
		if ( array_key_exists( $key, $this->params ) )
		{
			// if the value is requested as array, but is not array, make it one!
			
			if ( $is_array == true && ! is_array( $this->params[$key] ) )
			{
				return array ($this->params[$key] );
			} 
			
			// the opposite: if the the value is not requested as array but is,
			// take just the first value in the array
			
			elseif ( $is_array == false && is_array( $this->params[$key] ) )
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
	 * Get all parameters, or a group of parameters using regular expression
	 * 
	 * @param string $regex			[optional] regular expression for properties to get
	 * @param bool $shrink			[optional] whether to collapse properties stored as 
	 *   array into simple element, default false
	 * @param string $shrink_del 	[optional] if $shrink is true, then separate multiple 
	 *   elements by this character, default comma
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
	 * See if the request contains the corresponding param and value
	 * 
	 * @param string $param
	 * @param string $value
	 */
	
	public function hasParamValue($param, $value)
	{
		foreach ( $this->getParam($param, array(), true) as $param_value )
		{
			if ( $param_value == $value)
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Construct a URL, taking into account routes, based on supplied parameters
	 * 
	 * @param array $params				the elements of the url
	 * @param bool $full				[optional] should be full url
	 * @param bool $force_secure		[optional] should be https://
	 * @param array $options			route/assemble options
	 */
	
	public function url_for(array $params, $full = false, $force_secure = false, $options = array())
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
		
		// figure out which of our params matched the route
		
		$request = new Request();
		$request->setUri($url);
		
		$route_match = $this->router->match($request);
		
		// remove those from the supplied params
		
		if ( $route_match instanceof RouteMatch )
		{
			$in_route = array_keys($route_match->getParams());
			
			foreach ( $params as $key => $value )
			{
				if ( ! in_array($key, $in_route) )
				{
					$query_string[$key] = $value;
				}
			}
		}
		
		// take any remaining as the query string
		
		if ( count($query_string) > 0 )
		{
			$url .= "?";
			
			$x = 0;
			
			foreach ( $query_string as $name => $value )
			{
				if ( $value == "" )
				{
					continue;
				}
				
				if ( $x > 0 ) // first param doesn't need & prefix
				{
					$url .= '&';
				}
				
				// value is array
				
				if ( is_array( $value ) )
				{
					foreach( $value as $v )
					{
						$url .= $name . '=' . urlencode($v) . '&';
					} 
				}
				else // single value
				{
					$url .= $name . '=' . urlencode($value);
				}				
				
				$x++;
			}
		}
		
		// is it supposed to be a full url?
		
		if ( $full == true )
		{
			$base = $this->getServerUrl($force_secure);
			$url = $base .= $url;
		}
		
		return $url;
	}
	
	/**
	 * Get the current server URL, including scheme, hostname, port
	 * 
	 * @param bool $force_secure		[optional] should be https://
	 */
	
	public function getServerUrl($force_secure = false )
	{
		$port = $this->uri()->getPort();
		
		if ( $port == "80" )
		{
			$port = "";
		}
		else
		{
			$port = ":$port";
		}
		
		if ( $force_secure == true )
		{
			$scheme = "https";
		}
		else
		{
			$scheme = $this->uri()->getScheme();
		}
		
		return $scheme . '://' . $this->uri()->getHost() . $port;
	}
	
	/**
	 * Associate User with this Request
	 * 
	 * @param User $user
	 */
	
	public function setUser(User $user)
	{
		$this->user = $user;
	}
	
	/**
	 * Get the User making this Request
	 * 
	 * @throws \Exception
	 */
	
	public function getUser()
	{
		if ( ! $this->user instanceof User )
		{
			throw new \Exception("No User has been set");
		}
		
		return $this->user;
	}
	
	/**
	 * Serialize to xml
	 * 
	 * @param bool $should_hide_server	[optional]	exclude the server variables from the response
	 *
	 * @return DOMDocument
	 */
	
	public function toXML($should_hide_server = false)
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
		$this->addElement( $xml, $session, $this->getAllSessionData() );
		
		// add the server global array
		// but only if the request asks for it, for security purposes
		
		if ( $should_hide_server == true )
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
	 * @param DOMDocument $xml			[by reference] request xml document
	 * @param DOMNode $objAppend		[by reference] node to append values to
	 * @param array $arrValues			global array
	 */
	
	private function addElement(&$xml, &$objAppend, $arrValues)
	{
		foreach ( $arrValues as $key => $value )
		{
			// @todo: change this to 'data' element and fix xslt
			
			// need to make sure the xml element has a valid name
			// and not something crazy with spaces or commas, etc.
			
			$strSafeKey = Parser::strtolower( preg_replace( '/\W/', '_', $key ) );
			
			if ( is_array( $value ) )
			{
				foreach ( $value as $strKey => $strValue )
				{
					$objElement = $xml->createElement( $strSafeKey );
					$objElement->setAttribute('original_key', $key);
					
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
				$objElement->setAttribute('original_key', $key);
				
				$objAppend->appendChild( $objElement );
			}
			
			
		}
	}
}
