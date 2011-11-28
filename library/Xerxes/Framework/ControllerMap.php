<?php

/**
 * Parses the required configuration files and registers the appropriate commands and views
 * for a given request
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @version $Id: ControllerMap.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package  Xerxes_Framework
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 *
 */

class Xerxes_Framework_ControllerMap
{
	private $file = "config/actions.xml"; // actions configuration file
	
	public $xml = null; // simplexml object containing instructions for the actions
	private $path_map = null; // xerxes_framework_pathmap object
	private $version; // xerxes version number
	
	private static $instance; // singleton pattern	
	
	private function __construct() { }
	
	/**
	 * Get an instance of this class; this is singleton to ensure consistency 
	 *
	 * @return Xerxes_Framework_ControllerMap
	 */
	
	public static function getInstance()
	{
		if ( empty( self::$instance) )
		{
			self::$instance = new Xerxes_Framework_ControllerMap();
			$object = self::$instance;
			$object->init();
		}
		
		return self::$instance;
	}
	
	/**
	 * Initialize the object by picking up and storing the config xml file
	 * 
	 * @exception 	will throw exception if no configuration file can be found
	 */
	
	public function init()
	{	
		// don't parse it twice
		
		if ( ! $this->xml instanceof SimpleXMLElement )
		{
			$distro = XERXES_APPLICATION_PATH . $this->file;	
			
			// distro actions.xml
			
			if ( file_exists($distro) )
			{
				$this->xml = simplexml_load_file($distro);
				$this->version = (string) $this->xml["version"];
			}
			else
			{
				throw new Exception("could not find configuration file");
			}

			// local actions.xml overrides, if any
			
			if ( file_exists($this->file) )
			{
				$local = simplexml_load_file($this->file);
				
				if ( $local === false )
				{
					throw new Exception("could not parse local actions.xml");
				}
				
				$this->addSections($this->xml, $local );
			}				
		}

		// header("Content-type: text/xml"); echo $this->xml->asXML(); exit;	
	}

	/**
	 * Adds sections from the local actions.xml file into the master one
	 */
	
	private function addSections( SimpleXMLElement $parent, SimpleXMLElement $local )
	{
		$master = dom_import_simplexml ( $parent );
		
		// global commands
		
		$global = $local->global;
		
		if ( count($global) > 0 )
		{
			$new = dom_import_simplexml ( $global );
			$import = $master->ownerDocument->importNode ( $new, true );
			$master->ownerDocument->documentElement->appendChild ( $import );				
		}

		// sections
		
		// import then in the commands element
		
		$ref = $master->getElementsByTagName( "commands" )->item ( 0 );
		
		if ($ref == null)
		{
			throw new Exception ( "could not find commands insertion node in actions.xml" );
		}

		foreach ( $local->commands->children() as $section )
		{
			$new = dom_import_simplexml ( $section );
			$import = $master->ownerDocument->importNode ( $new, true );
			$ref->appendChild ( $import );
		}
	}
		
	/**
	 * Process the action in the incoming request and parse the xml file to determine
	 * the necessary includes, command classes, and view to call. 
	 * Also translates path to properties in command-specific ways. 
	 * Adds properties to Xerxes Request object. 
	 *
	 * @param string $section		'base' in the url or cli paramaters, corresponds to 'section' in xml
	 * @param string $action		'action' in the url or cli paramaters, corresponds to 'action' in the xml
	 */
	
	public function setAction( $section, $action  )
	{
		
	}
	
	/**
	 * Path Mapping object
	 *
	 * @return Xerxes_Framework_PathMap
	 */	
	
	public function getPathMapObject()
	{
		if ( ! $this->path_map )
		{
			$this->path_map = new Xerxes_Framework_PathMap($this->xml);
		}
		
		return $this->path_map;
	}
	
	/**
	 * Get the Xerxes version number
	 */
	
	public function getVersion()
	{
		return $this->version;
	}
}
