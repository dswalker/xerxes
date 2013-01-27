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

use Composer\Autoload\ClassLoader;
use Symfony\Component\HttpFoundation;
use Xerxes\Utility\Registry;

/**
 * MVC Event
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class MvcEvent
{
	/**
	 * @var Bootstrap
	 */
	protected $bootstrap;
	
	/**
	 * @var Registry
	 */
	protected $registry;
	
	/**
	 * @var Request
	 */
	protected $request;
	
	/**
	 * @var Response
	 */
	protected $response;
	
	/**
	 * @var ControllerMap
	 */
	protected $controller_map;
	
	/**
	 * Create a new Mvc Event
	 * 
	 * This wires-up the various Mvc classes and makes them
	 * available as a convenient package to the application
	 */
	
	public function __construct(Bootstrap $bootstrap)
	{
		// framework config
		
		$this->bootstrap = $bootstrap;
		
		// path to application root
		
		$app_dir = $bootstrap->getApplicationDir();
		
		// register local namespaces
		
		$loader = new ClassLoader();

		foreach ( $bootstrap->getLocalNamespaces() as $namespace => $path )
		{
			$loader->add($namespace, $path);
		}
		
		// application config
		
		$this->registry = Registry::getInstance();
		
		// incoming request
		
		$this->controller_map = new ControllerMap($app_dir); 
		$this->request = Request::createFromGlobals($this->controller_map); 
		
		// outgoing response
		
		$this->response = new Response();
		
		// set view dir
		
		$this->response->setViewDir("$app_dir/views/");
		
		// set default view
		
		$controller = $this->request->getParam('controller', 'index');
		$action = $this->request->getParam('action', 'index');
		$this->response->setView("$controller/$action.xsl");
	}
	
	/**
	 * Convenience function to return properties
	 * 
	 * @param string $name
	 */
	
	public function __get($name)
	{
		if ( property_exists($this, $name) )
		{
			return $this->$name;
		}
	}
	
	/**
	 * @return Bootstrap
	 */
	
	public function getBootstrap()
	{
		return $this->bootstrap;
	}
	
	/**
	 * @param Bootstrap $bootstrap
	 */
	
	public function setBootstrap($bootstrap)
	{
		$this->bootstrap = $bootstrap;
	}	
	
	/**
	 * @return Registry
	 */
	
	public function getRegistry() 
	{
		return $this->registry;
	}
	
	/**
	 * @param Registry $registry
	 */
	public function setRegistry(Registry $registry)
	{
		$this->registry = $registry;
	}	

	/**
	 * @return Request
	 */
	
	public function getRequest() 
	{
		return $this->request;
	}
	
	/**
	 * @param HttpFoundation\Request $request
	 */
	public function setRequest(HttpFoundation\Request $request)
	{
		$this->request = $request;
	}	

	/**
	 * @return Response
	 */
	public function getResponse() 
	{
		return $this->response;
	}
	
	/**
	 * @param HttpFoundation\Response $response
	 */
	public function setResponse(HttpFoundation\Response $response)
	{
		$this->response = $response;
	}	

	/**
	 * @return ControllerMap
	 */
	public function getControllerMap() 
	{
		return $this->controller_map;
	}
	
	/**
	 * @param ControllerMap $controller_map
	 */
	public function setControllerMap(ControllerMap $controller_map)
	{
		$this->controller_map = $controller_map;
	}
}
