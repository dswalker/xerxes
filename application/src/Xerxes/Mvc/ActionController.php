<?php

namespace Xerxes\Mvc;

use Symfony\Component\HttpFoundation,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Xerxes\Mvc\Exception\NotFoundException,
	Xerxes\Mvc\Response,
	Xerxes\Utility\Registry;

/**
 * Action Controller
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @version
 * @package  Xerxes
 * @link
 * @license
 */

abstract class ActionController
{
	protected $id; // controller id
	
	/**
	 * @var MvcEvent
	 */
	
	protected $event;
	
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
	 * Create Action Controller
	 */
	
	public function __construct(MvcEvent $event)
	{
		$this->event = $event;
		$this->registry = $event->registry;
		$this->request = $event->request;
		$this->response = $event->response;
		$this->controller_map = $event->controller_map;
		
		// controller id
		
		// get_class() always returns the child class name
		// ~ here makes sure we get only the last occurance of Controller
		
		$this->id = get_class($this); 
		$this->id = str_replace('Controller~', '', $this->id . '~');
		
		$parts = explode('\\', $this->id);
		$this->id = array_pop($parts);
		
		$this->id = strtolower($this->id);
		
	}
	
	/**
	 * Run specified action
	 * 
	 * @param string $action		action name
	 * @throws NotFoundException
	 */
	
	final public function execute($action)
	{
		$action_name = $action . 'Action';
		
		if ( ! method_exists($this, $action_name) ) // this is always the child class
		{
			throw new NotFoundException("Could not find '$action'");
		}
		
		// initial tasks
		
		$init = $this->init();
		
		if ( $init instanceof HttpFoundation\Response )
		{
			return $init;
		}
		
		// check authentication
		
		$auth = $this->checkAuthentication($action);
		
		if ( $auth instanceof HttpFoundation\Response )
		{
			return $init;
		}
		
		// run the action
		
		$response = $this->$action_name();
		
		// make sure we got a response
		
		if ( ! $response instanceof HttpFoundation\Response )
		{
			// nope, but see if we still have the original
			
			$response = $this->response;
			
			if ( ! $response instanceof HttpFoundation\Response )
			{
				throw new \Exception("Action '$action' returned no Response");
			}
		}
		
		// this was a redirect or something
		
		if ( ! $response instanceof Response )
		{
			return $response; 
		}
		
		// add event objects to response
		
		if ( $this->response->getVariable('base_url') != '') // unless already set
		{
			$this->response->setVariable('base_url', $this->request->getBasePath());
			$this->response->setVariable('request', $this->request);
			$this->response->setVariable('config', $this->registry);
		}
		
		// clean-up tasks
		
		$shutdown = $this->shutdown();
		
		if ( $shutdown instanceof HttpFoundation\Response )
		{
			return $shutdown;
		}
		
		return $response;
	}
	
	/**
	 * Tasks to perform before the action
	 */
	
	protected function init()
	{
		return null;
	}
	
	/**
	 * Tasks to perform after the action
	 */
	
	protected function shutdown()
	{
		return null;
	}	
	
	/**
	 * Redirect to a new URL
	 * 
	 * @param array|string $location	location to redirect to
	 */
	
	protected function redirect($location)
	{
		$url = $location;
		
		if ( is_array($location) )
		{
			$url = $this->request->url_for( $location );
		}
		
		return new RedirectResponse($url);
	}
	
	/**
	 * Perform an authentication check on this request
	 */	
	
	protected function checkAuthentication($action)
	{
		$restricted = $this->controller_map->isRestricted($this->id, $action); 
		$requires_login = $this->controller_map->requiresLogin($this->id, $action);
		
		// get user from session
		
		$user = $this->request->getUser(); 
		
		// this action requires authentication
		
		if ( $restricted || $requires_login )
		{
			$redirect_to_login = false;
			
			// this action requires a logged-in user, but user is not logged-in
			
			if ( $requires_login && ! $user->isAuthenticated() )
			{
				$redirect_to_login = true;
			}
			
			// this action requires that the user either be logged-in or in local ip range
			// but user is neither
			
			elseif ( $restricted && ! $user->isAuthenticated() && ! $user->isInLocalIpRange() )
			{
				$redirect_to_login = true;
			}
			
			// redirect to login page
			
			if ( $redirect_to_login == true )
			{
				$params = array (
					'controller' => 'authenticate', 
					'action' => 'login',
					'return' => $this->request->getRequestUri()
				);
				
				return $this->redirect($params);
			}
		}
	}
}
