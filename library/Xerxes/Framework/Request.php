<?php

/**
 * Process parameter in the request, either from HTTP or CLI
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Request.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes_Framework
 */

class Xerxes_Framework_Request
{
	private $init = false; // have we initialized
	private $method = ""; // request method: GET, POST, COMMAND
	private $arrParams = array(); // request paramaters
	private $arrURI = array(); // just the uri (sans-cookie, hidden values) params
	private $path_elements = null; // http path tranlsated into array of elements.
	private $aliases = array(); // url base aliases
	private $arrCookieSetParams = array(); // cookies that will be set with response. 
	                                       // value is array of args to php set_cookie. 
	private $registry; // registry object
	private static $instance; // singleton pattern
	
	protected function __construct() { }
	
	/**
	 * Get an instance of the file; Singleton to ensure correct data
	 *
	 * @return Xerxes_Framework_Request
	 */
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Xerxes_Framework_Request();
			$object = self::$instance;
			$object->init();			
		}
		
		return self::$instance;
	}
	
	/**
	 * Process the incoming request paramaters, cookie values, url path if pretty-uri on
	 */
	
	public function init()
	{
		// only do this once
		
		if ( $this->init == false ) 
		{
			$this->registry = Xerxes_Framework_Registry::getInstance();
			
			// aliases
			
			$alias = $this->registry->getConfig("BASE_ALIASES");
			
			if ( $alias != null )
			{
				$aliases = array();
				
				if ( $alias != null )
				{
					foreach ( explode(";", $alias) as $part )
					{
						$parts = explode("=", $part);
						
						if ( count($parts) == 2 )
						{
							$aliases[$parts[0]] = $parts[1]; 
						}
					}
				}			
				
				$this->aliases = $aliases;
			}
			
			// start a session
			
			if ( isset($_SERVER) )
			{
				// give our session a name to keep sessions distinct between multiple
				// instances of xerxes on one server.  use base_path (preferably) or
				// application_name config directives.
				
				$path_base = $this->registry->getConfig( "base_web_path", false );
				
				$path_key = preg_replace( '/\W/', '_', $path_base );
				
				$session_name = "xerxessession_" . $path_key;
				
				if ( $path_base == "" )
				{
					$path_base = "/";
				}
		
				$session_path = $this->registry->getConfig( "session_path", false, $path_base );
				$session_domain = $this->registry->getConfig( "session_domain", false, null );
		 		
		 		session_name( $session_name );
				session_set_cookie_params( 0, $session_path, $session_domain );
		 		session_start();		
			}
			
			// extract params
			
			if ( array_key_exists( "REQUEST_METHOD", $_SERVER ) )
			{
				// request has come in from GET or POST
				
				$this->method = $_SERVER['REQUEST_METHOD'];
				
				// now extract remaining params in query string. 
				
				if ( $_SERVER['QUERY_STRING'] != "" )
				{
					// querystring can be delimited either with ampersand
					// or semicolon
					
					$arrParams = preg_split( "/&|;/", $_SERVER['QUERY_STRING'] );
					
					foreach ( $arrParams as $strParam )
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
				
				foreach ( $_POST as $key => $value )
				{
					$this->setParam( $key, $value );
				}
				foreach ( $_COOKIE as $key => $value )
				{
					$this->setParam( $key, $value );
				}
							
				// aliases: when base is explicitly in the url
				
				$this->swapAliases();
							
				// if pretty-urls is turned on, extract params from uri. 
				
				if ( $this->registry->getConfig( "REWRITE", false ) )
				{
					$this->extractParamsFromPath();
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
				
				$this->method = "COMMAND";
				
				foreach ( $_SERVER['argv'] as $arg )
				{
					if ( strpos( $arg, "=" ) )
					{
						list ( $key, $val ) = explode( "=", $arg );
						$this->setParam( $key, $val );
					}
				}
			}
			
			if ( isset($_SERVER) )
			{
				### IIS fixes
				
				// to make this consistent with apache
				
				if ( array_key_exists('HTTPS', $_SERVER) )
				{
					if ( $_SERVER['HTTPS'] == "off" )
					{
						unset($_SERVER['HTTPS']);
					}
				}
				
				// since IIS doesn't hold value for request_uri
				
				if ( ! isset( $_SERVER['REQUEST_URI'] ) )
				{
					if ( ! isset( $_SERVER['QUERY_STRING'] ) )
					{
						$_SERVER['REQUEST_URI'] = $_SERVER["SCRIPT_NAME"];
					} 
					else
					{
						$_SERVER['REQUEST_URI'] = $_SERVER["SCRIPT_NAME"] . '?' . $_SERVER['QUERY_STRING'];
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
			
			// just uri params
			
			$this->arrURI = $this->arrParams;
			$keys = array_keys($this->arrParams);
				
			foreach ( $keys as $key )
			{
				if ( array_key_exists($key, $_COOKIE) )
				{
					unset($this->arrURI[$key]);
				}
			}
			
			$this->init = true;
		}		
	}
	
	/**
	 * Whether the request came in on the command line
	 *
	 * @return bool		true if came in on the cli
	 */
	
	public function isCommandLine()
	{
		if ( $this->method == "COMMAND" )
		{
			return true;
		} 
		else
		{
			return false;
		}
	}
	
	/**
	 * Simple function to detect if the user has a mobile device
	 */
	
	public function isMobileDevice()
	{
		require_once('mobile/mobile_device_detect.php');		
		$is_mobile = @mobile_device_detect(true, false); // supress errors because this library is goofy
		return $is_mobile[0];
	}
	
	/**
	 * Extract params from pretty-urls when turned on in config. Requires base url to be set in config.
	 * will get from $_SERVER['REQUEST_URI'], first stripping base url.
	 */
	
	public function extractParamsFromPath()
	{
		$x = 0; // this can change whether language is present or not
		
		// extract language from path, if present
		
		$languages = $this->registry->getConfig("languages");
		$path = $this->pathElements();
		
		if ( $languages != "" && count($path) > 0 )
		{
			foreach ( $languages as $language )
			{
				// first path element includes language, so map it
				// and then push all other path elements up one
				
				if ( array_key_exists(0, $path) )
				{
					if ( $path[0] == $language["code"] )
					{
						$this->mapPathToProperty( 0, "lang" );
						$x = 1;
					}
				}
			}
		}
		
		// base and action
		
		$this->mapPathToProperty( 0 + $x, "base" );
		$this->mapPathToProperty( 1 + $x, "action" );
		
		// need to swap aliases before we fetch data
		
		$this->swapAliases();
		
		// if the action has any specific parmaters it defines beyond base and action
		// they are extracted here
		
		$objMap = Xerxes_Framework_ControllerMap::getInstance()->getPathMapObject();
		$map = $objMap->indexToPropertyMap( $this->getParam( "base" ), $this->getParam( "action" ) );
		
		foreach ( $map as $index => $property )
		{
			$this->mapPathToProperty( $index + $x, $property );
		}
	}

	/**
	 * If alias supplied, swap controller name for alias
	 */
	
	private function swapAliases()
	{
		foreach ( $this->aliases as $old => $alias )
		{
			if ( $this->getParam("base") == $old )
			{
				$this->arrParams["base"] = $alias;
			}
		}		
	}

	/**
	 * Get the name of the controller given the supplied alias
	 *
	 * @param string $new 		the alias
	 * @return string		the controller, or the alias if no controller matched
	 */
	
	private function getAlias( $new )
	{
		foreach ( $this->aliases as $old => $alias )
		{
			if ( $alias == $new )
			{
				return $old;
			}
		}
		
		return $new;
	}
	
	/**
	 * Take the http request path and translate it to an array. 
	 * will get from $_SERVER['REQUEST_URI'], first stripping base url.
	 * If path was just "/", array will be empty. 
	 *
	 * @return array		array of path elements
	 */
	
	private function pathElements()
	{
		// lazy load

		if ( ! $this->path_elements )
		{
			$request_uri = $this->getServer( 'REQUEST_URI' );
			
			// get the path by stripping off base url + querystring
			
			$configBase = $this->registry->getConfig( 'BASE_WEB_PATH', false, "" );
			
			// remove base path, which might be simply '/'
			
			if (substr ( $request_uri, 0, strlen ( $configBase ) + 1 ) == $configBase . "/")
			{
				// $request_uri = str_replace( $configBase . "/", "", $request_uri );
				$request_uri = substr_replace ( $request_uri, '', 0, strlen ( $configBase ) + 1 );
			}
			
			// remove query string
			
			$request_uri = Xerxes_Framework_Parser::removeRight( $request_uri, "?" );
			
			// now get the elements
			
			$path_elements = explode( '/', $request_uri );
			
			for ( $x = 0 ; $x < count( $path_elements ) ; $x ++ )
			{
				$path_elements[$x] = urldecode( $path_elements[$x] );
			}
			
			// for an empty path, we'll have one empty string element, get rid of it.
			
			if ( strlen( $path_elements[0] ) == 0 )
			{
				unset( $path_elements[0] );
			}
			
			$this->path_elements = $path_elements;
		}
		
		return $this->path_elements;
	}
	
	/**
	 * Maps and inserts the path elements into the request parameters
	 *
	 * @param int $path_index		the numbered path element
	 * @param string $property_name		the property name
	 */
	
	public function mapPathToProperty( $path_index, $property_name )
	{
		$path_elements = $this->pathElements();
		
		if ( array_key_exists( $path_index, $path_elements ) )
		{
			$this->setParam( ( string ) $property_name, ( string ) $path_elements[$path_index] );
		}
	}
	
	/**
	* Alias of setParam
	*/
	
	public function setProperty( $key, $value, $bolArray = false, $override = false )
	{
		$this->setParam( $key, $value, $bolArray, $override );
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
		
		if ( array_key_exists( $key, $this->arrParams ) && $override == false )
		{
			// if there is an existing element, then we always push in the
			// the new value into an array, first converting the exising value
			// to an array if it is not already one 
			
			if ( ! is_array( $this->arrParams[$key] ) )
			{
				$this->arrParams[$key] = array ($this->arrParams[$key] );
			}
			
			array_push( $this->arrParams[$key], $value );
		} 
		elseif ( $bolArray == true )
		{
			// no existing value in property, but the calling code says 
			// this *must* be added as an array, so make it an array, if not one already
			
			if ( ! is_array( $value ) )
			{
				$value = array ($value );
			}
			
			$this->arrParams[$key] = $value;
		} 
		else
		{
			$this->arrParams[$key] = $value;
		}
	}
	
	/**
	* Alias of getParam
	*/
	
	public function getProperty( $key, $bolArray = false )
	{
		return $this->getParam($key, $bolArray);
	}
	
	/**
	 * Retrieve a value from the request parameters
	 *
	 * @param string $key		key that identify the value
	 * @param bool $bolArray	[optional] whether value should be returned as an array, even if only one value
	 * @param string $default	[optional] a default value to return if no param supplied
	 * 
	 * @return mixed 			[string or array] value if available, otherwise default
	 */
	
	public function getParam( $key, $bolArray = false, $default = null )
	{
		if ( array_key_exists( $key, $this->arrParams ) )
		{
			// if the value is requested as array, but is not array, make it one!
			
			if ( $bolArray == true && ! is_array( $this->arrParams[$key] ) )
			{
				return array ($this->arrParams[$key] );
			} 
			
			// the opposite: if the the value is not requested as array but is,
			// take just the first value in the array
			
			elseif ( $bolArray == false && is_array( $this->arrParams[$key] ) )
			{
				return $this->arrParams[$key][0];
			} 
			else
			{
				return $this->arrParams[$key];
			}
		} 
		else
		{
			return $default;
		}
	}

	/**
	 * Alias of getAllParams
	 */
	
	public function getAllProperties()
	{
		return $this->getParams();
	}

	/**
	 * Retrieve original URI properties
	 *
	 * @return array
	 */
	
	public function getURIProperties()
	{
		return $this->arrURI;
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
			return $this->arrParams;
		}
		
		foreach ( $this->getAllProperties() as $key => $value )
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
	 * Get a value from the $_SERVER global array
	 *
	 * @param string $key	server variable
	 * @return mixed
	 */
	
	public function getServer($key)
	{
		if ( array_key_exists( $key, $_SERVER ) )
		{
			return $_SERVER[$key];
		} 
		else
		{
			return null;
		}
	}

	/**
	 * Set a value in the $_SERVER global array
	 *
	 * @param string $key	server variable
	 * @return mixed
	 */
	
	public function setServer($key, $value)
	{
		$_SERVER[$key] = $value;
	}
	
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
	
	/**
	 * Include some cookies in response
	 *
	 * Parameters match those of the PHP function setCookie.
	 * Except if path is left blank, will be set to Xerxes base dir.
	 */
	
	public function setCookie($name)
	{
		$cookieParams = func_get_args();
		if ( ! $cookieParams[3] )
		{
			// No path? PHP default is current path, which won't work well in pretty url style.
			 
			$cookieParams[3] = $config->getConfig( 'BASE_WEB_PATH', false, "." ) . "/";
			
			// pad other args if neccesary
			if ( ! $cookieParams[2] )
			{
				// expire, use 0 to pad
				$cookieParams[2] = 0;
			}
			if ( ! $cookieParams[1] )
			{
				// value, use blank to pad. Why would you want a blank value? who knows. 
				$cookieParams[1] = "";
			}
		}
		
		$this->arrCookieSetParams[$name] = $cookieParams;
	}
	
	// called by frontcontroller, nobody else should need it.
	
	public function cookieSetParams()
	{
		return $this->arrCookieSetParams;
	}
	
	/**
	 * Take a url, and set a particular key/value in the url parameters.
	 * Replace existing value if neccesary; works whether or not url
	 * to begin with has a query string component.
	 *
	 * @param string $url		the url you are working on
	 * @param string $key		the key of the pair
	 * @param mixed $value		the value of the pair
	 *
	 * @return string		the new url
	 */
	
	public static function setParamInUrl($url, $key, $value)
	{
		$queryPos = strpos( $url, '?' );
		$queryHash = "";
		
		if ( $queryPos )
		{
			$base = substr( $url, 0, $queryPos + 1 );
			$queryString = substr( $url, $queryPos + 1 );
			parse_str( $queryString, $queryHash );
		} 
		else
		{
			$base = $url . '?';
			$queryHash = array ( );
		}
		
		$queryHash[$key] = $value;
		
		return $base . http_build_query( $queryHash );
	}	
	
	/**
	 * Generate a url for a given action. Used by commands to generate action
	 * urls, rather than calculating manually. This method returns different
	 * urls depending on whether rewrite is on in config. Will use
	 * configured base_web_path if available, which is best.
	 *
	 * Properties will be put in path or query string of url, if pretty-urls are turned on
	 * in config.xml, as per action configuration in actions.xml.
	 *
	 * 
	 * @param array $properties		keys are xerxes request properties, values are 
	 * 					the values. For an action url, "base" and "action" are 
	 *					required as keys.
	 * @param boolean $full 		[optional] force full absolute url with hostname. 
	 *
	 * @return string url 
	 */
	
	public function url_for($properties, $full = false, $force_secure = false)
	{
		if ( $properties instanceof Xerxes_Framework_URL )
		{
			$properties = $properties->toArray();
		}
		
		// check for base
		
		if ( ! array_key_exists( "base", $properties ) )
		{
			throw new Exception( "no base/section supplied in url_for." );
		}
		
		if ($force_secure)
		{
			$full = true;
		}
		
		$base_path = $this->registry->getConfig( 'BASE_WEB_PATH', false, "" ) . "/";
		
		// should we generate full absolute urls with hostname? indicated by a
		// request property, set automatically by snippet embed controllers. 
		
		if ( $this->getParam( "gen_full_urls" ) == 'true' || $full )
		{
			$base_path = $this->registry->getConfig( 'BASE_URL', true ) . "/";
			
			if ($force_secure)
			{
				$base_path = preg_replace ( '/^http\:\/\//', 'https://', $base_path );
			}
		}
		
		$extra_path = "";
		$query_string = "";
		
		// base and action
		
		$base = $properties["base"];
		$action = null;
		
		if ( array_key_exists( "action", $properties ) )
		{
			$action = $properties["action"];
		}
		
		// add in the the language element automatically, 
		// unless it's been set explicitly in the url
		
		if ( $this->getParam("lang") != "" && ! array_key_exists("lang", $properties) )
		{
			$properties["lang"] = $this->getParam("lang");
		}
		
		if ( $this->registry->getConfig( 'REWRITE', false ) )
		{
			$x = 0;
			
			// language goes first, if present
			
			if ( array_key_exists("lang", $properties) )
			{
				$extra_path_arr[0] = urlencode( $properties["lang"]);
				unset( $properties["lang"] );
				$x = 1;
			}
			
			// base in path
			
			$extra_path_arr[0 + $x] = urlencode( $this->getAlias($base)); //alias
			unset( $properties["base"] );
			
			// action in path
			
			if ( array_key_exists( "action", $properties ) )
			{
				$extra_path_arr[1 + $x] = urlencode( $action );
				unset( $properties["action"] );
			}
			
			// action-specific stuff
			
			foreach ( array_keys( $properties ) as $property )
			{
				$controller_map = Xerxes_Framework_ControllerMap::getInstance();
				$index = $controller_map->getPathMapObject()->indexForProperty( $base, $action, $property );
				
				if ( $index )
				{
					$extra_path_arr[$index + $x] = urlencode( $properties[$property] );
					unset( $properties[$property] );
				}
			}
			
			// need to resort since we may have added indexes in a weird order. Silly PHP. 
			
			ksort($extra_path_arr); 	
			$extra_path = implode( "/", $extra_path_arr );
		}
		
		$assembled_path = $base_path . $extra_path;
		
		// everything else, which may be everything if it's ugly uris
		
		$query_string = null;
		
		foreach ( $properties as $key => $value )
		{
			if ( $value == "")
			{
				continue;
			}
			
			if ( $query_string != null )
			{
				$query_string .= '&amp;';
			}
			
			if ( is_array($value) )
			{
				for ( $x = 0; $x < count($value); $x++ )
				{
					if ( $x > 0 )
					{
						$query_string .= '&amp;';
					}
					
					$query_string .= "$key=" . urlencode($value[$x]);
				}
			}
			else
			{
				 $query_string .= "$key=" . urlencode($value);
			}
		}
		
		if ( $query_string != null )
		{
			$assembled_path = $assembled_path . '?' . $query_string;
		}
		
		return $assembled_path;
	}
	
	/**
	 * Check if the user has explicitly logged in
	 *
	 * @return bool		true if user is named and logged in, otherwise false
	 */
	
	public function hasLoggedInUser()
	{
		return Xerxes_Framework_Restrict::isAuthenticatedUser( $this );
	}
	
	/**
	 * Retrieve master XML and all request paramaters
	 * 
	 * @param bool $bolHideServer	[optional]	true will exclude the server variables from the response, default false
	 *
	 * @return DOMDocument
	 */
	
	public function toXML($bolHideServer = false)
	{
		$objRegistry = Xerxes_Framework_Registry::getInstance();
		
		// add the url parameters and session and server global arrays
		// to the master xml document
		
		$objXml = new DOMDocument( );
		$objXml->loadXML( "<request />" );
		
		// session and server global arrays will have parent elements
		// but querystring and cookie params will be at the root of request
		
		$this->addElement( $objXml, $objXml->documentElement, $this->arrParams );
		
		// add the session global array
		
		$objSession = $objXml->createElement( "session" );
		$objXml->documentElement->appendChild( $objSession );
		$this->addElement( $objXml, $objSession, $_SESSION );
		
		// we might add some calculated thigns to xml that aren't actually
		// stored in session.
		
		// okay, yeah, we already have group memberships listed from the session,
		// but it doesn't have all the data we need, plus we need to stick
		// group memberships by virtue of IP address. 
		
		$objAuth = $objXml->createElement( "authorization_info" );
		$objXml->documentElement->appendChild( $objAuth );
		
		// are they an affiliated user at all, meaning either logged in or
		// ip recognized?
		
		$authUser = Xerxes_Framework_Restrict::isAuthenticatedUser( $this );
		
		$authIP = Xerxes_Framework_Restrict::isIpAddrInRanges( $this->getServer( 'REMOTE_ADDR' ), 
			$objRegistry->getConfig( "local_ip_range" ) 
		);
		
		$objElement = $objXml->createElement( "affiliated", ($authUser || $authIP) ? "true" : "false" );
		$objElement->setAttribute( "user_account", $authUser ? "true" : "false" );
		$objElement->setAttribute( "ip_addr", $authIP ? "true" : "false" );
		$objAuth->appendChild( $objElement );
		
		// now each group?
		
		/*
		
		$arrGroups = $objRegistry->userGroups();
		
		if ( $arrGroups != null )
		{
			foreach ( $objRegistry->userGroups() as $group )
			{
				$authUser = array_key_exists( "user_groups", $_SESSION ) && 
					is_array( $_SESSION["user_groups"] ) && 
					in_array( $group, $_SESSION["user_groups"] 
				);
				
				$authIP = Xerxes_Framework_Restrict::isIpAddrInRanges( 
					$this->getServer( 'REMOTE_ADDR' ), 
					$objRegistry->getGroupLocalIpRanges( $group ) 
				);
				
				$objElement = $objXml->createElement( "group", ($authUser || $authIP) ? "true" : "false" );
				$objElement->setAttribute( "id", $group );
				$objElement->setAttribute( "display_name", $objRegistry->getGroupDisplayName( $group ) );
				$objElement->setAttribute( "user_account", $authUser ? "true" : "false" );
				$objElement->setAttribute( "ip_addr", $authIP ? "true" : "false" );
				$objAuth->appendChild( $objElement );
			}
		}

		*/
		
		// add the server global array, but only if the request
		// asks for it, for security purposes
		
		if ( $bolHideServer == true )
		{
			$objServer = $objXml->createElement( "server" );
			$objXml->documentElement->appendChild( $objServer );
			$this->addElement( $objXml, $objServer, $_SERVER );
		}
		
		return $objXml;
	}
	
	/**
	 * Add global array as xml to request xml document
	 *
	 * @param DOMDocument $objXml		[by reference] request xml document
	 * @param DOMNode $objAppend		[by reference] node to append values to
	 * @param array $arrValues			global array
	 */
	
	private function addElement(&$objXml, &$objAppend, $arrValues)
	{
		foreach ( $arrValues as $key => $value )
		{
			// need to make sure the xml element has a valid name
			// and not something crazy with spaces or commas, etc.
			
			$strSafeKey = Xerxes_Framework_Parser::strtolower( preg_replace( '/\W/', '_', $key ) );
			
			if ( is_array( $value ) )
			{
				foreach ( $value as $strKey => $strValue )
				{
					$objElement = $objXml->createElement( $strSafeKey );
					$objElement->setAttribute( "key", $strKey );
					$objAppend->appendChild( $objElement );
					
					if ( is_array( $strValue ) )
					{
						// multi-dimensional arrays will be recursively added
						$this->addElement($objXml, $objElement, $strValue);
					}
					else
					{
						$objElement->nodeValue = Xerxes_Framework_Parser::escapeXml( $strValue );
					}
				}
			}
			else
			{
				$objElement = $objXml->createElement( $strSafeKey, Xerxes_Framework_Parser::escapeXml( $value ) );
				$objAppend->appendChild( $objElement );
			}
		}
	}
}
