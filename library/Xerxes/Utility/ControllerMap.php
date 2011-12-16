<?php

namespace Xerxes\Utility;

/**
 * Parses the required configuration files and registers the appropriate commands and views
 * for a given request
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @version 
 * @package  Xerxes
 * @link http://xerxes.calstate.edu
 * @license 
 *
 */

class ControllerMap
{
	private $file = "config/map.xml"; // local configuration file
	private $xml = null; // simplexml object containing instructions
	private $version; // xerxes version number
	
	private $controller; // supplied controller
	private $action; // supplied action
	
	private $view = array(); // view(s) set programmatically
	private $default_controller; // default controller/action for index	 
	
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
			
			$this->addXml($this->xml, $local );
		}
		
		foreach ( $this->xml->controller as $controller )
		{
			$inherits = $controller["inherits"];
			
			if ( $inherits  != "" )
			{
				$controller_to_copy = $this->xml->xpath("//controller[@name='$inherits']");
				
				if ( count($controller_to_copy) > 0 )
				{
					$this->addXml($this->xml, $controller_to_copy[0], $controller );
				}
			}
		}
		
		// header("Content-type: text/xml"); echo $this->xml->asXML(); exit;	
	}
	
	public function getDefaultController()
	{
		return (string) $this->xml->default;
	}
	
	public function setController($controller, $action = 'index')
	{
		if ( ! is_string($controller) )
		{
			throw new \InvalidArgumentException("WTF!");
		}
		
		$this->controller = $controller;
		$this->action = $action;
	}
	
	public function getAliases()
	{
		$aliases = array();
		
		foreach ( $this->xml->controller as $controller )
		{
			if ( $controller["class"] == "" )
			{
				continue;
			}			
			
			$aliases[(string) $controller["name"]] = (string) $controller["class"];
		}
		
		return $aliases;
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
	
	public function setView($view, $format = "html")
	{
		$this->view[$format] = $view;
	}
	
	public function getView($format = "html")
	{
		if ( $format == "" )
		{
			$format = "html";
		}
		
		// already set
		
		if ( array_key_exists($format, $this->view) )
		{
			return $this->view;
		}
		
		// get it out of the config
		
		$format_query = "";
		
		$view =  $this->controller . '/' . $this->action . '.xsl';
		
		if ( $format != "html" )
		{
			$format_query = "[@format='$format']";
		}
		
		$query = "//controller[@name='$this->controller']/action[@name='$this->action']/view" . $format_query;
		$view_def = $this->xml->xpath($query);
		
		foreach ( $view_def as $def )
		{
			$view = $def;
		}
		
		$this->view[$format] = $view; // save for later
		
		return $view;
	}
	
	/**
	 * Append one simple xml element to another 
	 *
	 * @param \SimpleXMLElement $parent			master document
	 * @param \SimpleXMLElement $local			xml nodes to add
	 * @param \SimpleXMLElement $node			[optional] the insertion point to append local
	 * 
	 * @throws Exception
	 */
	
	private function addXml( \SimpleXMLElement $parent, \SimpleXMLElement $local, \SimpleXMLElement $node = null )
	{
		// amazingly, the code below changes the simplexml object itself
		// no need to cast this back to simplexml
	
		$master = dom_import_simplexml( $parent );
		
		// insertion point is main document node, unless we specify one
		
		$insert_node = $master->ownerDocument->documentElement;
		
		if ( $node != null )
		{
			$insert_node = dom_import_simplexml( $node );
		}
	
		// import and append the local xml's child nodes
	
		foreach ( $local->children() as $entry )
		{
			$new = clone dom_import_simplexml( $entry ); // make sure we are copying the node
			
			$import = $master->ownerDocument->importNode( $new, true );
			
			$insert_node->appendChild($import);
		}
	}	
	
		
	/**
	 * Get the Xerxes version number
	 */
	
	public function getVersion()
	{
		return $this->version;
	}
	
	public function saveXML()
	{
		return $this->xml->asXML();
	}
}
