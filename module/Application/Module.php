<?php

namespace Application;

use Application\Model\Authentication\AuthenticationFactory,
	Xerxes\Utility\Registry,
	Xerxes\Utility\Request,
	Xerxes\Utility\Restrict,
	Zend\EventManager\StaticEventManager,
	Zend\Http\PhpEnvironment\Response as HttpResponse,
	Zend\Module\Consumer\AutoloaderProvider,
	Zend\Module\Manager,
	Zend\Mvc\MvcEvent;

class Module implements AutoloaderProvider
{
    protected $request;
    protected $viewListener;

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
                	'XerxesLocal\Authentication' => 'config/authentication',
                ),
            ),
        );
    }

    public function getConfig($env = null)
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function initialize($e)
    {
        $app = $e->getParam('application');
        $config = $e->getParam('config');
        
        $locator = $app->getLocator();
        
        // custom xerxes request object
        
        $app->events()->attach('route', array($this, 'getRequest'), -80);        
        
        // set authentication check
        
        $app->events()->attach('route', array($this, 'checkAuthentication'), -90);
        
        // view listener
        
        $view = $locator->get('view');
        $viewListener = $this->getViewListener($view, $config);
        $app->events()->attachAggregate($viewListener);

        $events = StaticEventManager::getInstance();
        $viewListener->registerStaticListeners($events, $locator);        
    }

    protected function getViewListener($view, $config)
    {
        if ( $this->viewListener instanceof View\Listener ) 
        {
            return $this->viewListener;
        }

        $viewListener = new View\Listener($view);
        
        $viewListener->setDisplayExceptionsFlag($config->display_exceptions);

        $this->viewListener = $viewListener;
        
        return $viewListener;
    }
    
    public function getRequest(MvcEvent $e)
    {
    	if ( $this->request instanceof Request )
    	{
    		return $this->request;
    	}
    	
    	$this->request = new Request();
    	$this->request->setRouter($e->getRouter());
    	$e->setRequest($this->request);
    }
    
    public function checkAuthentication(MvcEvent $e)
    {
    	$request = $this->getRequest($e); // make sure we have a request object
    	
    	if ( $request->getParam('controller') == 'ebsco')
    	{
    		$restrict = new Restrict($request);
    		
    		if ( ! $restrict->isAuthenticatedUser() )
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



