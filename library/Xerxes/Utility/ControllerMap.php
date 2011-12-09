<?php

namespace Xerxes\Utility;

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

class ControllerMap
{
	private $file = "config/map.xml"; // local configuration file
	private $xml = null; // simplexml object containing instructions
	private $version; // xerxes version number
	
	private $controller; // supplied controller
	private $action; // supplied action
	
	public function __construct($distro)
	{
		// distro map.xml
			
		if ( file_exists($distro) )
		{
			$this->xml = simplexml_load_file($distro);
			$this->version = (string) $this->xml["version"];
		}
		else
		{
			throw new \InvalidArgumentException("param 1 must be path to map.xml configuration file, you gave '$distro'");
		}

		// local map.xml overrides, if any
			
		if ( file_exists($this->file) )
		{
			$local = simplexml_load_file($this->file);
			
			if ( $local === false )
			{
				throw new \Exception("could not parse local map.xml");
			}
			
			$this->addLocalFile($this->xml, $local );
		}				

		// header("Content-type: text/xml"); echo $this->xml->asXML(); exit;	
	}
	
	public function setController($controller, $action)
	{
		$this->controller = $controller;
		$this->action = $action;
	}

	/**
	 * Adds instructions from the local map.xml file into the master one
	 * 
	 * @param \SimpleXMLElement $parent
	 * @param \SimpleXMLElement $local
	 * @throws Exception
	 */
	
	private function addLocalFile( \SimpleXMLElement $parent, \SimpleXMLElement $local )
	{
		// amazingly, the code below changes the simplexml object itself
		// no need to cast this back to simplexml
		
		$master = dom_import_simplexml ( $parent );
		
		// import and append the local file
		
		foreach ( $local->children() as $entry )
		{
			$new = dom_import_simplexml ( $entry );
			$import = $master->ownerDocument->importNode( $new, true );
			$master->ownerDocument->documentElement->appendChild($import);
		}
	}
	
	public function isRestricted()
	{
		$restrict = "";
		
		$controller_restricted = $this->xml->xpath("//controller[@name='$this->controller']/@restricted");
		$action_restricted = $this->xml->xpath("//controller[@name='$this->controller']/action[@name='$this->action']/@restricted");
		
		// see what the controller block says
		
		foreach ( $controller_restricted as $controller_restrict )
		{
			$restrict = (string) $controller_restrict;
		}
		
		// action block will always override

		foreach ( $action_restricted as $action_restrict )
		{
			$restrict = (string) $action_restrict;
		}
		
		// to bool
		
		if ( $restrict == "true" )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function requiresLogin()
	{
		$requires_login = "";
	
		$controller_restricted = $this->xml->xpath("//controller[@name='$this->controller']/@login");
		$action_restricted = $this->xml->xpath("//controller[@name='$this->controller']/action[@name='$this->action']/@login");
	
		// see what the controller block says
	
		foreach ( $controller_restricted as $controller_restrict )
		{
			$requires_login = (string) $controller_restrict;
		}
	
		// action block will always override
	
		foreach ( $action_restricted as $action_restrict )
		{
			$requires_login = (string) $action_restrict;
		}
		
		// to bool
	
		if ( $requires_login == "true" )
		{
			return true;
		}
		else
		{
			return false;
		}
	}	
	
		
	/**
	 * Get the Xerxes version number
	 */
	
	public function getVersion()
	{
		return $this->version;
	}
}
