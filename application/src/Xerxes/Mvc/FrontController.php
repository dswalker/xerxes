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

use Xerxes\Utility\Registry;

/**
 * Front Controller
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class FrontController
{
	/**
	 * @var Bootstrap
	 */
	
	protected $bootstrap;
	
	/**
	 * Set the bootstrapper
	 * 
	 * @param Bootstrap $bootstrap
	 */
	
	public function setBootstrap(Bootstrap $bootstrap)
	{
		$this->bootstrap = $bootstrap;
		return $this;
	}
	
	/**
	 * Do it
	 */
	
	public function execute()
	{
		// this creates/bundles the request, response, registry, & controller map objects
		
		$event = new MvcEvent($this->bootstrap);
		
		try
		{
			// display errors
			
			if ( $event->registry->getConfig('DISPLAY_ERRORS') == true )
			{
				error_reporting( E_ALL );
				ini_set('display_errors', '1');
			}
			
			// controller/actions to execute with every request
			
			$actions = $event->controller_map->getGlobalActions();
			
			// add current controller/action
			
			$current_controller = $event->request->getParam('controller', 'index');
			$current_action = $event->request->getParam('action', 'index');
			$actions[$current_controller] = $current_action;
			
			// do them all in turn
			
			foreach ( $actions as $controller_name => $action )
			{
				// execute specified action
				
				$controller = $event->controller_map->getControllerObject($controller_name, $event);
				$response = $controller->execute($action);
				
				// this was a redirect or something, so stop execution
				
				if ( ! $response instanceof Response )
				{
					break;
				}
			}
			
			// apply the view to the data 
			
			if ( $response instanceof Response )
			{
				$response->render($event->request->getParam('format', 'html'));
			}
			
			// send it to browser
			
			$response->send();
		}
		catch ( \Exception $e )
		{
			header("Content-type: text/plain");
			throw $e; exit; // @todo: catch errors
		}		
	}
}
