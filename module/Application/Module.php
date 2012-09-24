<?php

namespace Application;

use Application\Model\Authentication\AuthenticationFactory,
	Application\Model\Authentication\User,
	Xerxes\Utility\ControllerMap,
	Xerxes\Utility\Parser,
	Xerxes\Utility\Registry,
	Xerxes\Utility\Request,
	Zend\EventManager\StaticEventManager,
	Zend\Http\PhpEnvironment\Response as HttpResponse,
	Zend\Mvc\MvcEvent;

class Module
{
	protected $request; // xerxes request object
	protected $controller_map; // xerxes controller map
	
	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					'Local' => 'custom',
				),
			),
		);
	}
	
	public function getConfig()
	{
		$config = include __DIR__ . '/config/module.config.php';
		
		// grab a copy of the aliases in config
		
		$aliases = $config['di']['instance']['alias'];
		
		// add those supplied by controller map
		
		foreach ( $this->getControllerMap()->getAliases() as $key => $value )
		{
			$aliases[$key] = $value;
		}
		
		// now set it back and return the whole config
		
		$config['di']['instance']['alias'] = $aliases;
		
		return $config;
	}
	
	/**
	 * Bootstrap the module
	 * 
	 * @param mixed $event
	 */
	
	public function onBootstrap($e)
	{
		$app = $e->getParam('application');
		
		// xerxes request object
		
		$app->events()->attach('route', array($this, 'getRequest'), -80);	 
		
		// access control
		
		$app->events()->attach('route', array($this, 'checkAuthentication'), -100);
		
		// xerxes view listener
		
		$app->events()->attach('render', array($this, 'registerViewStrategy'), 100);
	}
	
	/**
	 * Retrieve a populated Xerxes Request object
	 * 
	 * @param MvcEvent $e
	 * @return Request
	 */
	
	public function getRequest(MvcEvent $e)
	{
		// make sure we have a xerxes request object
		
		if ( ! $this->request instanceof Request )
		{
			$this->request = new Request();
			
			// add router
			
			$this->request->setRouter($e->getRouter()); 
			
			// populate user from request and set it in request object
			
			$user = new User($this->request); 
			$this->request->setUser($user);
			
			// set it in the MvcEvent so it gets passed around
			
			$e->setRequest($this->request);
			
			// set the current action for controller map
			
			$controller =  $this->request->getParam('controller', 'index');
			$action =  $this->request->getParam('action', 'index');
			
			$controller_map = $this->getControllerMap();
			$controller_map->setController($controller, $action);
			
			// now stuff it in the request object for later access
			
			$this->request->setControllerMap($controller_map);
		}
		
		return $this->request;
	}
	
	/**
	 * Retrieved a populated Xerxes ControllerMap
	 * 
	 * @return ControllerMap
	 */
	
	public function getControllerMap()
	{
		if ( ! $this->controller_map instanceof ControllerMap )
		{
			$this->controller_map = new ControllerMap(__DIR__ . '/config/map.xml');
		}
		
		return $this->controller_map;
	}
	
	/**
	 * Perform an authentication check on this request
	 *  
	 * @param MvcEvent $e
	 */
	
	public function checkAuthentication(MvcEvent $e)
	{
		$request = $this->getRequest($e); // make sure we have a request object
		$controller_map = $request->getControllerMap(); // make sure we have a controller map
		
		$restricted = $controller_map->isRestricted(); 
		$requires_login = $controller_map->requiresLogin();
		
		// get user from session
		
		$user = $request->getUser(); 
		
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
					'return' => $this->request->server()->get('REQUEST_URI')
				);
				
				$url = $request->url_for( $params );
				
				$response = new HttpResponse();
				$response->headers()->addHeaderLine('Location', $url);
				$response->setStatusCode(302);
				
				return $response;
			}
		}
	}
	
	/**
	 * Hook-up our custom XSLT-based view layer
	 * 
	 * @param MvcEvent $e
	 */
	
	public function registerViewStrategy(MvcEvent $e)
	{
		$application = $e->getTarget();
		$manager = $application->getServiceManager();
		
		// @todo: this is hacky-sack, get a proper error handler in here
		
		try 
		{
			$strategy = $manager->get('Application\View\Strategy');
			$view = $manager->get('Zend\View\View');

			$view->events()->attach( $strategy, 100 );
		}
		catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e)
		{
			$this->message = 'The requested URL ' . $_SERVER["REQUEST_URI"] . ' was not found on this server.';
			
			error_log($this->message);
			require_once __DIR__ . '/views/error/404.phtml';
			exit; 
		}
	}	
}

