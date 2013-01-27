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

use Application\Controller;
use Xerxes\Mvc\Bootstrap;

/**
 * Controller Map
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class ControllerMap
{
	private $file = "config/map.xml"; // configuration file
	private $xml = null; // simplexml object containing instructions
	private $version; // xerxes version number
	private $default_controller; // default controller/action for index
	
	/**
	 * Create a Controller Map
	 * 
	 * @param $app_dir   application dir
	 */
	
	public function __construct($app_dir)
	{
		// distro map.xml
		
		$distro_file = $app_dir . '/' . $this->file;
		
		if ( ! file_exists($distro_file) )
		{
			throw new \InvalidArgumentException("Could not find file '$distro_file'");
		}

		$this->xml = simplexml_load_file($distro_file);
		$this->version = (string) $this->xml["version"];

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
		
		// header("Content-type: text/xml"); echo $this->xml->asXML(); exit;	
	}
	
	/**
	 * Get the default controller
	 * 
	 * @return string
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
	 * Get the internal controller name for the supplied URL alias 
	 *
	 * @param string $alias
	 * @return string;
	 */	
	
	public function getControllerName($alias)
	{
		foreach ( $this->xml->url_alias as $url_alias )
		{
			if ( $alias == (string) $url_alias["name"] )
			{
				return (string) $url_alias["controller"];
			}
		}
		
		return $alias; // the alias was the controller name
	}
	
	/**
	 * Get the URL alias for the supplied internal controller name
	 * 
	 * @param string $controller
	 * @return string;
	 */
	
	public function getUrlAlias($controller)
	{
		foreach ( $this->xml->url_alias as $url_alias )
		{
			if ( $controller == (string) $url_alias["controller"] )
			{
				return (string) $url_alias["name"];
			}
		}
		
		return $controller; // no alias, return the original name
	}
	
	/**
	 * Get specified route mappings
	 * 
	 * @param string $controller
	 * @param string $action
	 * @return array
	 */
	
	public function getRouteInfo($controller, $action)
	{
		$path = array();
	
		$controller_routes = $this->xml->xpath("//controller[@name='$controller']/path");
		$action_routes = $this->xml->xpath("//controller[@name='$controller']/action[@name='$action']/path");
	
		// see what the controller block says
	
		if ( $controller_routes !== false )
		{
			foreach ( $controller_routes as $controller_route )
			{
				$path[(string) $controller_route['index']] = (string) $controller_route['param'];
			}
		}
	
		// action block will always override
	
		if ( $action_routes !== false )
		{
			foreach ( $action_routes as $action_route )
			{
				$path[(string) $action_route['index']] = (string) $action_route['param'];
			}
		}
	
		return $path;
	}
	
	/**
	 * Get an instance of the controller class
	 * 
	 * @param string $controller
	 * @param Event $event
	 * @return ActionController;
	 */
	
	public function getControllerObject($controller, MvcEvent $event)
	{
		$class_name = $this->getControllerClassName($controller);
		
		if ( ! class_exists($class_name) )
		{
			throw new \Exception("Could not find class '$class_name'");
		}
	
		$controller = new $class_name($event);
			
		if ( ! $controller instanceof ActionController )
		{
			throw new \Exception("Controller $controller ('$class_name') must be instance of ActionController");
		}
			
		return $controller;
	}
	
	/**
	 * Class name for supplied controller id
	 * 
	 * @param string $controller
	 */
	
	public function getControllerClassName($controller)
	{
		// make sure we have real controller name, no alias
		
		$controller = $this->getControllerName($controller); 
		
		// see if it is defined in our config
		
		$controller_classes = $this->xml->xpath("//controller[@name='$controller']/@class");
		
		if ( $controller_classes !== false )
		{
			if ( count($controller_classes) > 0 )
			{
				$class_name = '';
				
				// last one always overrides
				
				foreach ( $controller_classes as $controller_class )
				{
					$class_name = (string) $controller_class;
				}
				
				return $class_name;
			}
		}
		
		// based on convention
		
		return 'Application\\Controller\\' . ucfirst($controller) . 'Controller';		
	}
	
	/**
	 * Actions to be executed with every request
	 * 
	 * @return array
	 */
	
	public function getGlobalActions()
	{
		$actions = array();
		
		foreach ( $this->xml->global_action as $global_action )
		{
			 $actions[(string) $global_action["controller"]] = (string) $global_action["action"];
		}
		
		return $actions;
	}
	
	/**
	 * Is the currently set action restricted?
	 *
	 * @param string $controller
	 * @param string $action
	 * @return boolean
	 */
	
	public function isRestricted($controller, $action)
	{
		$restrict = "";
	
		$controller_restricted = $this->xml->xpath("//controller[@name='$controller']/@restricted");
		$action_restricted = $this->xml->xpath("//controller[@name='$controller']/action[@name='$action']/@restricted");
	
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
	 * @param string $controller
	 * @param string $action
	 * @return boolean
	 */
	
	public function requiresLogin($controller, $action)
	{
		$requires_login = "";
	
		$controller_restricted = $this->xml->xpath("//controller[@name='$controller']/@login");
		$action_restricted = $this->xml->xpath("//controller[@name='$controller']/action[@name='$action']/@login");
	
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
