<?php

namespace Application;

use Xerxes\Utility\Request,
	Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider,
	Zend\Module\Manager,
	Zend\Mvc\MvcEvent;

class Module implements AutoloaderProvider
{
    protected $view;
    protected $viewListener;

    public function init(Manager $moduleManager)
    {
        $events = StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'initializeView'), 100);
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
                ),
            ),
        );
    }

    public function getConfig($env = null)
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function initializeView($e)
    {
        $app          = $e->getParam('application');
        $locator      = $app->getLocator();
        $config       = $e->getParam('config');
        $view         = $locator->get('view');
        $viewListener = $this->getViewListener($view, $config);
        
        $app->events()->attach('dispatch', array($this, 'setRequest'), 1000);
        $app->events()->attachAggregate($viewListener);
        
        $events = StaticEventManager::getInstance();
        $viewListener->registerStaticListeners($events, $locator);
    }

    protected function getViewListener($view, $config)
    {
        if ($this->viewListener instanceof View\Listener) 
        {
            return $this->viewListener;
        }

        $viewListener = new View\Listener($view);
        
        $viewListener->setDisplayExceptionsFlag($config->display_exceptions);

        $this->viewListener = $viewListener;
        
        return $viewListener;
    }
    
    public function setRequest(MvcEvent $e)
    {
    	$request = new Request();
    	$request->setRouter($e->getRouter());
    	$e->setRequest($request);
    }    
}
