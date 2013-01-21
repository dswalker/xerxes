<?php

namespace Xerxes\Mvc;

use Xerxes\Utility\Registry;

/**
 * Front Controller
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @version
 * @package  Xerxes
 * @link
 * @license
 */

class FrontController
{
	/**
	 * Do it
	 */
	
	public static function execute(array $config)
	{
		// this creates/bundles the request, response, registry, & controller map objects
		
		$event = new MvcEvent($config);
		
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
			
			$current_controller = $event->request->getControllerName('index');
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
				$response->render();
			}
			
			// send it to browser
			
			$response->send();
		}
		catch ( \Exception $e )
		{
			throw $e; exit; // @todo: catch errors
		}		
	}
}
