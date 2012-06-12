<?php

namespace Xerxes\Utility;

/**
 * Parses and holds basic configuration information from the config
 *
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package  Xerxes_Framework
 */

class Registry
{
	protected $xml = ""; // simple xml object copy
	protected $config_file = "config/config";
	private $authentication_sources = array();
	private $default_language = null;
	private $arrConfig = null; // configuration settings
	private $arrPass = array ( ); // values to pass on to the view
	private static $instance; // singleton pattern

	protected function __construct()
	{
	}
	
	/**
	 * Get an instance of the Config
	 */
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Registry();
			$object = self::$instance;
			$object->init();
		}
		
		return self::$instance;
	}
	
	/**
	 * Initialize the object by picking up and processing the config xml file
	 */
	
	public function init()
	{
		if ( $this->arrConfig == null )
		{
			$file = "";
			$file_xml = $this->config_file . ".xml";
			$file_php = $this->config_file . ".php";
			
			$this->arrConfig = array ();
			
			// check if the config file has an .xml or .php extension
			
			if ( file_exists( $file_xml ) )
			{
				$file = $file_xml;
			}
			elseif ( file_exists($file_php) )
			{
				$file = $file_php;
			}
			else
			{
				throw new \Exception( "could not find configuration file" );
			}
			
			$this->authentication_sources["guest"] = "guest";
			
			// get it!

			$xml = simplexml_load_file( $file );
			$this->xml = $xml;
			
			foreach ( $xml->configuration->config as $config )
			{
				$name = Parser::strtoupper( $config["name"] );
				$lang = (string) $config["lang"];
				
				if ( $lang != "" && $lang != $this->initDefaultLanguage() )
				{
					$name .= "_$lang";
				}
				
				if ( $config["xml"] == "true" ) 
				{
					// special XML config, already parsed as SimpleXML, leave it that way.
					$value = $config;          
				}
				else 
				{
					//simple string
					     
					$value = trim( ( string ) $config );
            
					// convert simple xml-encoded values to something easier 
					// for the client code to digest

					$value = str_replace( "&lt;", "<", $value );
					$value = str_replace( "&gt;", ">", $value );
					$value = str_replace( "&amp;", "&", $value );
				}
        
				// special logic for authentication_source because we can
				// have more than one. 

				if ( $name == "AUTHENTICATION_SOURCE" )
				{
					$this->authentication_sources[( string ) $config["id"]] = $value;

					// and don't overwrite the first one in our standard config array

					if ( ! empty( $this->arrConfig["AUTHENTICATION_SOURCE"] ) )
					{
						$value = "";
					}
				}
					
				if (! empty($value) )
				{
					// add it to the config array

					$this->arrConfig[$name] = $value;
					
					// types that are listed as 'pass' will be forwarded
					// on to the xml layer for use in the view
					
					if ( ( string ) $config["pass"] == "true" )
					{
						$this->arrPass[Parser::strtolower( $name )] = $value;
					}
				}
			}
		}
	}
	
	/**
	 * Get a parsed configuration entry
	 *
	 * @param string $name			name of the configuration setting
	 * @param bool $bolRequired		[optional] whether function should throw exception if no value found
	 * @param mixed $default		[optional] a default value for the constant if none found
	 * @param string $lang			[optional] must include language attribute 
	 * @return mixed  Can return a String or a SimpleXMLElement, depending on whether it was XML config value. 
	 */
	
	public function getConfig($name, $bolRequired = false, $default = null, $lang = "")
	{
		$name = Parser::strtoupper( $name );
		
		if ( $lang != "" && $lang != $this->defaultLanguage() )
		{
			$name .= "_$lang";
		}
		
		if ( $this->arrConfig == null )
		{
			return null;
		} 
		
		if ( array_key_exists( $name, $this->arrConfig ) )
		{
			if ( $this->arrConfig[$name] == "true" )
			{
				return true;
			} 
			elseif ( $this->arrConfig[$name] == "false" )
			{
				return false;
			}
			elseif ( $this->arrConfig[$name] == "" || $this->arrConfig[$name] == null)
			{
				// let this fall to the code below
			} 
			else
			{
				return $this->arrConfig[$name];
			}
		} 

		if ( $bolRequired == true )
		{
			throw new \Exception( "required configuration entry $name missing" );
		}
			
		if ( $default != null )
		{
			return $default;
		} 
		else
		{
				return null;
		}
	}
	
	/**
	 * Get all confuguration settings as array
	 *
	 * @return array
	 */
	
	public function getAllConfigs()
	{
		return $this->arrConfig;
	}
	
	/**
	 * Get all configuration settings that should be passed to the XML and the XSLT
	 *
	 * @return array
	 */
	
	public function getPass()
	{
		return $this->arrPass;
	}
	
	/**
	 * Set a value for a configuration, from code rather than the file
	 *
	 * @param string $key		configuration setting name
	 * @param mixed $value		value. Generally String or SimpleXMLElement. 
	 * @param bool $bolPass		[optional] whether value should be passed to XML (default false)
	 */
	
	public function setConfig($key, $value, $bolPass = false)
	{
		$this->arrConfig[Parser::strtoupper( $key )] = $value;
		
		if ( $bolPass == true )
		{
			$this->arrPass[Parser::strtolower( $key )] = $value;
		}
	}

	/**
	 * Gets an authentication source by id. If id is null or no such
	 * source can be found, returns first authentication source in config file.
	 * If not even that, returns "demo"
	 * 
	 * @param $id		authentication identifier
	 * @return string	authentication source
	 */
	
	public function getAuthenticationSource($id)
	{
		$source = null;
		
		if ( ! empty( $id ) )
		{
			// if $id was set, make sure calling code didn't ask for the main auth, 
			// since that has no 'id'; but code below will return the right name
			
			if ( $id != $this->getConfig( "AUTHENTICATION_SOURCE" ) )
			{
				$source = $this->authentication_sources[$id];
			}
		}
		
		if ( $source == null )
		{
			$source = $this->getConfig( "AUTHENTICATION_SOURCE" );
		}
		
		if ( $source == null )
		{
			$source = "demo";
		}
		
		return $source;
	}
	
	/**
	 * Initialize the default language
	 * 
	 * @return string		default language
	 */
	
	private function initDefaultLanguage()
	{
		$default_language = $this->xml->xpath("configuration/config[@name='languages']/language[position()=1]/@code");
		
		if ( count($default_language) > 0)
		{
			$this->default_language = (string) $default_language[0]["code"];
		}		
		else
		{
			$this->default_language = null;
		}

		return $this->default_language;
	}
	
	/**
	 * Get the default language
	 * 
	 * @return string
	 */

	public function defaultLanguage()
	{
		return $this->default_language;
	}
	
	/**
	 * Get locale name for given language ID
	 * 
	 * @param string		language id
	 * @return string		locale
	 */
	
	public function getLocale($lang)
	{
		$languages = $this->getConfig("languages");
		
		if ( $languages != null )
		{
			foreach ( $languages->language as $language )
			{
				if ( $language["code"] == $lang )
				{
					foreach ($language->attributes() as $name => $value) {
						if ( $name == "locale" ) {
							return (string) $value;
						}
					}
				}
			}
		}
		
		// we got this far, then no matches!
		
		return "C";
	}
	
	/**
	 * Get the config file as XML
	 * 
	 * @return simplexml
	 */
	
	public function getXML()
	{
		return $this->xml;
	}
	
	/**
	 * Publically accessible config entries
	 * 
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
		// pass any configuration options defined as type=pass to the xml
		
		$source = explode('/', $this->config_file);
		$source = array_pop($source);

		$objConfigXml = new \DOMDocument( );
		$objConfigXml->loadXML( "<config source=\"$source\" />" );
			
		foreach ( $this->getPass() as $key => $value )
		{
			if ($value instanceof \SimpleXMLElement) 
			{
				// just spit it back out again as XML
									
				$objElement = $objConfigXml->createElement($key);
				$objConfigXml->documentElement->appendChild( $objElement );
				
				foreach ($value->children() as $child) 
				{
					// need to convert to DOMDocument.
					$domValue = dom_import_simplexml($child);
					$domValue =  $objConfigXml->importNode($domValue, true);
					$objElement->appendChild($domValue);
				}                                
			}
			else 
			{
				// simple string value
				
				$objElement = $objConfigXml->createElement( $key, Parser::escapeXml($value) );
				$objConfigXml->documentElement->appendChild( $objElement );
			}
		}
		
		return $objConfigXml;
	}
}
