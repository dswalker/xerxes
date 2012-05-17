<?php

namespace Xerxes\Utility;

/**
 * Map of controllers and views for a given request
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
	
	private $url_alias = array(); // url aliases
	private $view = array(); // view(s) set programmatically
	private $default_controller; // default controller/action for index
	
	private $no_view = false;
	
	/**
	 * Create a Controller Map
	 * 
	 * @param string $distro	path to disto config/map.xml file
	 * 
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	
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
			
			$this->addLocalXml( $local );
		}
		
		// set the version number
		
		$this->version = (string) $this->xml['version'];
		
		// see if any controller inherits from another
		
		foreach ( $this->xml->controller as $controller )
		{
			$inherits = $controller["inherits"];
						
			if ( $inherits  != "" ) // this one does
			{
				// grab the controller that this one inherits fom
				
				$controller_to_copy = $this->xml->xpath("//controller[@name='$inherits']");
				
				if ( count($controller_to_copy) > 0 )
				{
					$this->addActions($controller_to_copy[0], $controller ); // import its nodes
				}
			}
		}
		
		// grab url aliases
		
		foreach ( $this->xml->url_alias as $url_alias )
		{
			$this->url_alias[(string) $url_alias["name"]] = (string) $url_alias["controller"];
		}
		
		// header("Content-type: text/xml"); echo $this->xml->asXML(); exit;	
	}
	
	/**
	 * Get the default controller
	 */
	
	public function getDefaultController()
	{
		$default_controller = "";
		
		// make sure we take the last (local) one
		
		foreach ( $this->xml->default as $default )
		{
			$default_controller = (string) $default;
		}
		
		return $default_controller;
	}
	
	/**
	 * Set the current conroller/action context
	 * 
	 * @param string $controller	controller name
	 * @param string $action		[optional] action name
	 * 
	 * @throws \InvalidArgumentException
	 */
	
	public function setController($controller, $action = 'index')
	{
		if ( ! is_string($controller) )
		{
			throw new \InvalidArgumentException("WTF!");
		}
		
		$this->controller = $controller;
		$this->action = $action;
		
		// this is purely just an alias for the url
		// so switch it here for it's internal name
		
		if ( array_key_exists($controller, $this->url_alias) )
		{
			$this->controller = $this->url_alias[$controller];
		}
	}
	
	/**
	 * Get the controller alias and corresponding class name
	 */
	
	public function getAliases()
	{
		$aliases = array();
		
		// controller definitions
		
		foreach ( $this->xml->controller as $controller )
		{
			if ( $controller["class"] == "" )
			{
				continue;
			}			
			
			$aliases[(string) $controller["name"]] = (string) $controller["class"];
		}
		
		// url aliases
		
		foreach ( $this->url_alias as $url_alias => $controller_name )
		{
			if ( array_key_exists($controller_name, $aliases))
			{
				$aliases[$url_alias] = $aliases[$controller_name];
			}
		}
		
		return $aliases;
	}
	
	/**
	 * Get internal controller name
	 * @return string;
	 */
	
	public function getControllerName()
	{
		return $this->controller;
	}
	
	/**
	 * Get the URL alias for the supplied internal controller name
	 * 
	 * @param string $controller
	 * @return string;
	 */
	
	public function getUrlAlias($controller)
	{
		$alias = $controller; // no alias, return the original name
		
		foreach ( $this->url_alias as $url_alias => $controller_name )
		{
			if ( $controller == $controller_name )
			{
				$alias = $url_alias;
			}
		}
		
		return $alias;
	}
	
	/**
	 * Is the currently set action restricted
	 * 
	 * @return boolean
	 */
	
	public function isRestricted()
	{
		$restrict = "";
		
		$controller_restricted = $this->xml->xpath("//controller[@name='$this->controller']/@restricted");
		$action_restricted = $this->xml->xpath("//controller[@name='$this->controller']/action[@name='$this->action']/@restricted");
		
		// see what the controller block says
		
		if ( $controller_restricted !== false )
		{
			foreach ( $controller_restricted as $controller_restrict )
			{
				$restrict = (string) $controller_restrict;
			}
		}

		// action block will always override
		
		if ( $action_restricted !== false )
		{
			foreach ( $action_restricted as $action_restrict )
			{
				$restrict = (string) $action_restrict;
			}
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
	
	/**
	 * Does the current action require a login
	 * 
	 * @return boolean
	 */
	
	public function requiresLogin()
	{
		$requires_login = "";
	
		$controller_restricted = $this->xml->xpath("//controller[@name='$this->controller']/@login");
		$action_restricted = $this->xml->xpath("//controller[@name='$this->controller']/action[@name='$this->action']/@login");
	
		// see what the controller block says
	
		if ( $controller_restricted !== false )
		{
			foreach ( $controller_restricted as $controller_restrict )
			{
				$requires_login = (string) $controller_restrict;
			}
		}
	
		// action block will always override
		
		if ( $action_restricted !== false )
		{
			foreach ( $action_restricted as $action_restrict )
			{
				$requires_login = (string) $action_restrict;
			}
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
	 * Set view script to use
	 * 
	 * @param string $view			relative path to view script
	 * @param string $format		[optional] for given format, default is 'html'
	 */
	
	public function setView($view, $format = "html")
	{
		$this->view[$format] = $view;
	}
	
	/**
	 * Don't use any view
	 */
	
	public function setNoView()
	{
		$this->no_view = true;
	}
	
	/**
	 * Get relative path to view script
	 * 
	 * @param string $format		[optional] for given format, default is 'html'
	 * @return string
	 */
	
	public function getView($format = "html")
	{
		if ( $format == "" )
		{
			$format = "html";
		}
		
		if ( $this->no_view == true)
		{
			return null;
		}
		
		// already set
		
		if ( array_key_exists($format, $this->view) )
		{
			return $this->view[$format];
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
		
		// last one takes precedence
		
		foreach ( $view_def as $def )
		{
			$view = $def;
		}
		
		$this->view[$format] = $view; // save for later
		
		return $view;
	}
	
	/**
	 * Append local map.xml file 
	 *
	 * @param \SimpleXMLElement $local
	 */
	
	private function addLocalXml( \SimpleXMLElement $local )
	{
		// amazingly, the code below changes the simplexml object itself
		// no need to cast this back to simplexml
	
		$master = dom_import_simplexml( $this->xml );
		
		$insert_node = $master->ownerDocument->documentElement;
	
		// import and append the local xml's child nodes
	
		foreach ( $local->children() as $entry )
		{
			$new = clone dom_import_simplexml( $entry ); // make sure we are copying the node
			
			$import = $master->ownerDocument->importNode( $new, true );
			
			$insert_node->appendChild($import);
		}
	}
	
	/**
	 * Pre-pend the actions of the parent controller to a child controller
	 * 
	 * @param \SimpleXMLElement $parent_controller
	 * @param \SimpleXMLElement $child_controller
	 */
	
	private function addActions( \SimpleXMLElement $parent_controller, \SimpleXMLElement $child_controller )
	{
		$master = dom_import_simplexml(  $this->xml );
	
		$insert_node = dom_import_simplexml( $child_controller );
		
		$before = $insert_node->firstChild;
				
		// insert the actions *before* any existing child nodes, so the local child node
		// takes precendence
	
		foreach ( $parent_controller->children() as $entry )
		{
			$new = clone dom_import_simplexml( $entry ); // make sure we are copying the node
				
			$import = $master->ownerDocument->importNode( $new, true );
				
			$insert_node->insertBefore($import, $before);
		}
	}	
	
		
	/**
	 * Get the Xerxes version number
	 */
	
	public function getVersion()
	{
		return $this->version;
	}
	
	/**
	 * Serialize xml config to string
	 */
	
	public function saveXML()
	{
		return $this->xml->asXML();
	}
}
