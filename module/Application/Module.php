<?php

namespace Application;

use Application\Model\Authentication\AuthenticationFactory,
	Xerxes\Utility\ControllerMap,
	Xerxes\Utility\Registry,
	Xerxes\Utility\Request,
	Xerxes\Utility\User,
	Zend\EventManager\StaticEventManager,
	Zend\Http\PhpEnvironment\Response as HttpResponse,
	Zend\Module\Consumer\AutoloaderProvider,
	Zend\Module\Manager,
	Zend\Mvc\MvcEvent;

class Module implements AutoloaderProvider
{
	protected $viewListener; // application view listener
	protected $request; // xerxes request object
	protected $controller_map; // xerxes controller map

    public function init(Manager $moduleManager)
    {
        $events = StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'initialize'), 100);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                	'Xerxes' => __DIR__ . '/../../library/Xerxes',
                	'Local\Authentication' => 'config/authentication',
                ),
            ),
        );
    }

    public function getConfig($env = null)
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
    
    public function initialize($e)
    {
        $app = $e->getParam('application');
        $config = $e->getParam('config');
        
        $locator = $app->getLocator();
        
        // xerxes request object
        
        $app->events()->attach('route', array($this, 'getRequest'), -80);     

        // access control
        
        $app->events()->attach('route', array($this, 'checkAuthentication'), -100);
        
        // view listener
        
        $view_renderer = $locator->get('view');
        $viewListener = $this->getViewListener($view_renderer, $config);
        $app->events()->attachAggregate($viewListener);

        $events = StaticEventManager::getInstance();
        $viewListener->registerStaticListeners($events, $locator);        
    }

    protected function getViewListener($view_renderer, $config)
    {
        if ( ! $this->viewListener instanceof View\Listener ) 
        {
	        $this->viewListener = new View\Listener($view_renderer);
	        $this->viewListener->setDisplayExceptionsFlag($config->display_exceptions);
        }

        return $this->viewListener;
    }
    
    public function getRequest(MvcEvent $e)
    {
    	// make sure we have a request object
    	
    	if ( ! $this->request instanceof Request )
    	{
	    	$this->request = new Request();
	    	$this->request->setRouter($e->getRouter());
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
    
    public function getControllerMap()
    {
    	if ( ! $this->controller_map instanceof ControllerMap )
    	{
    		$this->controller_map = new ControllerMap(__DIR__ . '/config/map.xml');
    	}
    	
    	return $this->controller_map;
    }
    
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
}
