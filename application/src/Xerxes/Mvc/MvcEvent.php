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
use Symfony\Component\Stopwatch\Stopwatch;
use Xerxes\Utility\Labels;
use Xerxes\Utility\Registry;

/**
 * MVC Event
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class MvcEvent
{
	/**
	 * @var string
	 */
	protected $app_dir;
	
	/**
	 * @var Stopwatch
	 */
	protected $stopwatch;
	
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
	 * @var Labels
	 */
	protected $labels;
	
	/**
	 * Create a new Mvc Event
	 * 
	 * This wires-up the various Mvc classes and makes them
	 * available as a convenient package to the application
	 */
	
	public function __construct(Bootstrap $bootstrap)
	{
		// $this->stopwatch = new Stopwatch();
		
		// framework config
		
		$this->bootstrap = $bootstrap;
		
		// path to application root
		
		$this->app_dir = $bootstrap->getApplicationDir();
		
		// application config
		
		$this->registry = Registry::getInstance();
		
		// controller config
			
		$this->controller_map = new ControllerMap($this->app_dir);

		// incoming request
		
		$this->request = Request::createFromGlobals($this->controller_map); 
		
		// outgoing response
		
		$this->response = $this->getNewResponse();
		
		// set default view
		
		$alias = $this->request->getParam('controller', 'index');
		
		$controller = $this->controller_map->getControllerName($alias);
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
	
	public function setBootstrap(Bootstrap $bootstrap)
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
	public function getNewResponse()
	{
		$response = new Response();
		
		if ( $this->request instanceof Request )
		{
			$response->setRequest($this->request);
		}
		
		// set view dir
		
		$response->setViewDir($this->app_dir . '/views/');
		
		return $response;
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
	
	/**
	 * @return Labels
	 */
	public function getLabels()
	{
		if ( ! $this->labels instanceof Labels )
		{
			$path = $this->getBootstrap()->getApplicationDir();
			$this->labels = new Labels($path);
	
			// @todo need a proper language grabber
			// $lang = $this->registry->defaultLanguage();
				
			$lang = $this->request->getParam("lang");
				
			$this->labels->setLanguage($lang);
		}
	
		return $this->labels;
	}	
}
