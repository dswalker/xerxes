<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Mvc;

use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Xerxes\Utility\Parser;
use Xerxes\Utility\Registry;
use Xerxes\Utility\User;

/**
 * Process HTTP and CLI requests, as well as Session
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Request extends HttpFoundation\Request
{
	private $controller_name; // controller name
	private $commandline = false; // did this request originate from the command line?
	private $params = array(); // request params
	
	const FLASH_MESSAGE_NOTICE = 'success';
	const FLASH_MESSAGE_ERROR = 'error';

	/**
	 * @var Registry
	 */
	private $registry;

	/**
	 * @var ControllerMap
	 */
	private $controller_map;	
	
	/**
	 * @var User
	 */
	private $user;
	
   /**
    * Create new Xerxes Request
    */
	
    public static function createFromGlobals(ControllerMap $controller_map)
    {
    	$registry = Registry::getInstance();
    	
    	// reverse proxy
    		
    	if ( $registry->getConfig("REVERSE_PROXIES", false ) )
    	{
    		self::$trustProxy = true;
    		self::$trustedProxies = explode(',',  $registry->getConfig("REVERSE_PROXIES"));
    	}
    	
    	// request
    	
		$request = parent::createFromGlobals();
		
		// set cookie path and name
		
		$basepath = $request->getBasePath();
		$id = strtolower($basepath);
		$id = preg_replace('/\//', '_', $id);
		$id = 'xerxessession_' . $id;
		
		$session_options = array(
			'name' => $id,
			'cookie_path' => ($basepath == '' ? '/' : $basepath)
		);
		
		$storage = new NativeSessionStorage($session_options);
		
		// session
		
		$session = new Session($storage);
		$session->start();

		// register these mo-fo's
		
		$request->setRegistry($registry);
		$request->setSession($session);
		$request->setControllerMap($controller_map);

		// do our special mapping
		
		$request->extractQueryParams();
		
		return $request;
	}

	/**
	 * Set Registry
	 *
	 * @param Registry $map;
	 */
	
	public function setRegistry(Registry $registry)
	{
		return $this->registry = $registry;
	}	
	
	/**
	 * Set the Controller Map
	 *
	 * @param ControllerMap $map;
	 */
	
	public function setControllerMap(ControllerMap $map)
	{
		return $this->controller_map = $map;
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
	 * Gets the Session
	 *
	 * @return Session
	 */
	
	public function getSession()
	{
		return $this->session;
	}
	
	/**
	 * Add value to Session
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	
	public function setSessionData($name, $value)
	{
		$this->session->set($name, $value);
	}

	/**
	 * Set object in Session
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	
	public function setSessionObject($name, $object)
	{
		$this->session->set($name, serialize($object));
	}	
	
	/**
	 * Unset a value in Session
	 *
	 * @param string $name
	 */
	
	public function unsetSessionData($name)
	{
		return $this->session->remove($name);
	}	
	
	/**
	 * Check if a key is set in Session
	 *
	 * @param string $name
	 */
	
	public function existsInSessionData($name)
	{
		return $this->session->has($name);
	}	
	
	/**
	 * Get session value
	 * 
	 * @param string $name
	 * @return mixed 		value, if key exists, otherwise null
	 */
	
	public function getSessionData($name)
	{
		return $this->session->get($name);
	}

	/**
	 * Get object from session
	 *
	 * @param string $name
	 */
	
	public function getSessionObject($name)
	{
		return unserialize($this->session->get($name));
	}
	
	/**
	 * Require session value
	 *
	 * @param string $name     session key
	 * @param string $message  [optional] error text
	 * @param bool $is_object  [optional] whether session value is an object
	 * 
	 * @return mixed 		   value, if key exists
	 * @throws \OutOfBoundsException
	 */
	
	public function requireSessionData($name, $message = null, $is_object = false)
	{
		$value = null;
		
		if ( $is_object == true )
		{
			$value = $this->getSessionObject($name);
		}
		else
		{
			$value = $this->getSessionData($name);
		}
		
		if ( $value == null )
		{
			throw new \OutOfBoundsException($message);
		}
		
		return $value;
	}	
	
	/**
	 * Get all session values
	 * 
	 * @return array
	 */
	
	public function getAllSessionData()
	{
		return $this->session->all();
	}
	
	/**
	 * Set a flash message
	 * 
	 * @param string $type
	 * @param string $message
	 */
	
	public function setFlashMessage($type, $message)
	{
		$flash_bag = $this->getSession()->getFlashBag();
		$flash_bag->add($type, $message);
	}
	
	/**
	 * Get flash messages
	 *
	 * @param string $type
	 * @return array
	 */
	
	public function getFlashMessages()
	{
		$flash_bag = $this->getSession()->getFlashBag();
		
		return $flash_bag->all();
	}	
	
	/**
	 * Process the incoming request paramaters
	 */
	
	public function extractQueryParams()
	{
		// coming from http
			
		if ( isset( $_SERVER['QUERY_STRING']) )
		{
			// controller and action in the path
			
			$path = explode('/', $this->getPathInfo());
			
			$controller = null;
			$action = null;
			
			if ( array_key_exists(1, $path) )
			{
				$controller = $path[1];
				
				if ( $controller != '')
				{
					$this->setParam( 'controller', $controller);
				}
			}
			
			if ( array_key_exists(2, $path) )
			{
				$action = $path[2];
				
				if ( $action != '')
				{
					$this->setParam( 'action', $action);
				}
			}
			
			// defined routes for the path
			
			foreach ( $this->controller_map->getRouteInfo($controller, $action) as $index => $param_name )
			{
				if ( array_key_exists($index, $path) )
				{
					$this->setParam($param_name, $path[$index]);
				}
			}
			
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
	}
	
	/**
	 * Get the (internal) controller name for this request
	 * 
	 * @return string
	 */
	
	public function getControllerName()
	{
		if ( $this->controller_name == '')
		{
			// swap any alias for the internal controller name
				
			$this->controller_name = $this->controller_map->getControllerName($this->getParam('controller', 'index'));
		}
		
		return $this->controller_name;
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
	 * @param string $key		key to identify the value
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
			if ( $default == null && $is_array == true )
			{
				return array();
			}
			else
			{
				return $default;
			}
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
	 * Require the param be present in the request
	 * 
	 * will throw an Exception if not
	 * 
	 * @param string $name           parameter name
	 * @param string $error_message  error message if param not present
	 * @param bool $is_array	     [optional] whether value should be returned as an array, even if only one value
	 * 
	 * @throws \OutOfBoundsException
	 */
	
	public function requireParam($name, $error_message, $is_array = false)
	{
		$value = $this->getParam($name, null, $is_array);
		
		if ($value == null)
		{
			throw new \OutOfBoundsException($error_message);
		}
		
		return $value;
	}
	
	
	/**
	 * Construct a URL, taking into account routes, based on supplied parameters
	 * 
	 * @param array $params       the elements of the url
	 * @param bool $full          [optional] should be full url
	 * @param bool $force_secure  [optional] should be https://
	 */
	
	public function url_for(array $params, $full = false, $force_secure = false )
	{
		$controller = null;
		$action = null;
		$route = array();
		
		// controller
		
		if ( array_key_exists('controller', $params) )
		{
			$controller = $params['controller'];
			
			// swap internal controller name for alias
			
			$controller = $this->controller_map->getUrlAlias($controller);
			
			$route[] = $controller;
			unset($params['controller']);
		}
		
		// action
		
		if ( array_key_exists('action', $params) )
		{
			$action = $params['action'];
			$route[] = $action;
			unset($params['action']);
		}
		
		// config defined route information
		
		foreach ( $this->controller_map->getRouteInfo($controller, $action) as $param_name )
		{
			if ( array_key_exists($param_name, $params) )
			{
				$route[] = $params[$param_name];
				unset($params[$param_name]);
			}
		}
		
		// always include the lang if it was supplied, and not overriden by the code
		
		if ( $this->getParam('lang') != null && ! array_key_exists('lang', $params) )
		{
			$params['lang'] = $this->getParam('lang');
		}
		
		// assemble it as the route
		
		$url = implode('/', $route);
		
		// take anything remaining as the query string
		
		if ( count($params) > 0 )
		{
			$url .= "?";
			
			$x = 0; // counter
			$hash = ''; // hash url
			
			foreach ( $params as $name => $value )
			{
				if ( $value == "" )
				{
					continue;
				}
				
				if ( $name == '#' ) // this is a url hash
				{
					$hash .= "#$value";
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
		
		// add hash
		
		$url .= $hash;
		
		// is it supposed to be a full url?
		
		if ( $full == true || $this->getSessionData('is_mobile') == '1') // always do full url for mobile
		{
			$base = $this->getServerUrl($force_secure) . $this->getBasePath() . '/';
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
		if ( $force_secure == true )
		{
			$scheme = "https";
		}
		else
		{
			$scheme = $this->getScheme();
		}
		
		return $scheme . '://' . $this->getHttpHost();
	}
	
	/**
	 * Get the User making this Request
	 * 
	 * @return User
	 */
	
	public function getUser()
	{
		if ( ! $this->user instanceof User )
		{
			$this->user = new User($this);
		}
		
		return $this->user;
	}

	/**
	 * Is this the dev server?
	 *
	 * @return bool
	 */	
	
	public function isDevelopment()
	{
		if ( array_key_exists('APPLICATION_ENV', $_ENV))
		{
			if ( $_ENV['APPLICATION_ENV'] == 'development' )
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Serialize to xml
	 * 
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
		$xml = new \DOMDocument( );
		$xml->loadXML( "<request />" );
		
		// querystring will be at the root of request
		
		$this->addElement( $xml, $xml->documentElement, $this->params );

		// other data will have parent elements
		
		$add = array(
			'flash_messages' => $this->getFlashMessages(),
			'session' => $this->getAllSessionData()
		);
		
		// don't add server global array if request is for internal xerxes xml
		
		if ( $this->getParam('format') != 'xerxes' ) 
		{
			$add['server'] = $_SERVER;
		}
		
		foreach ( $add as $name => $values )
		{
			$element = $xml->createElement($name);
			$xml->documentElement->appendChild($element);
			$this->addElement( $xml, $element, $values );
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
