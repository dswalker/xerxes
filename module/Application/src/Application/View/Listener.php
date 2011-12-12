<?php

namespace Application\View;

use Application\Controller\SearchController;

use Application\View\Helper\Navigation,
	ArrayAccess,
	Xerxes\Utility\ControllerMap,
	Xerxes\Utility\Registry,
    Xerxes\Utility\ViewRenderer,
    Zend\Di\Locator,
    Zend\EventManager\EventCollection,
    Zend\EventManager\ListenerAggregate,
    Zend\EventManager\StaticEventCollection,
    Zend\Http\PhpEnvironment\Response,
    Zend\Mvc\Application,
    Zend\Mvc\MvcEvent,
    Zend\View\Renderer;

class Listener implements ListenerAggregate
{
    protected $listeners = array();
    protected $staticListeners = array();
    protected $displayExceptions = false;
    
    protected $view_renderer; // xerxes view renderer
    
    public function __construct(ViewRenderer $view_renderer)
    {
    	$this->view_renderer = $view_renderer;
    }

    public function setDisplayExceptionsFlag($flag)
    {
        $this->displayExceptions = (bool) $flag;
        return $this;
    }

    public function displayExceptions()
    {
        return $this->displayExceptions;
    }

    public function attach(EventCollection $events)
    {
        $this->listeners[] = $events->attach('dispatch.error', array($this, 'renderError'));
    }

    public function detach(EventCollection $events)
    {
        foreach ($this->listeners as $key => $listener)
        {
            $events->detach($listener);
            unset($this->listeners[$key]);
            unset($listener);
        }
    }

    public function registerStaticListeners(StaticEventCollection $events, $locator)
    {
        $ident   = 'Zend\Mvc\Controller\ActionController';
        $handler = $events->attach($ident, 'dispatch', array($this, 'renderView'), -50);
        $this->staticListeners[] = array($ident, $handler);
    }

    public function detachStaticListeners(StaticEventCollection $events)
    {
        foreach ($this->staticListeners as $i => $info) {
            list($id, $handler) = $info;
            $events->detach($id, $handler);
            unset($this->staticListeners[$i]);
        }
    }

    public function renderView(MvcEvent $e)
    {
        $response = $e->getResponse();
        $request = $e->getRequest();
        
        // error
        
        if ( ! $response->isSuccess() )
        {
            return;
        }
        
        // set the view
        
        $script = $request->getControllerMap()->getView($request->getParam('format'));
        
        
        ##### @todo: HACK 
        
        $controller =  $request->getParam('controller', 'index');
        $action =  $request->getParam('action', 'index');
        
        if ( $controller != "authenticate" && $action != "results" && $action != "record" ) 
        {
        	$script = "search" . '/' . $action . '.xsl';
        }
        
        ##### END HACK
        
        
        
       
        
        // set up the response
        
        $vars = array();
        
        $vars["base_url"] = $e->getRequest()->getBaseUrl();
        $vars["request"] = $e->getRequest();
        $vars["config"] = Registry::getInstance();
        
        // navigation
        
        $nav = new Navigation($e);
        $vars["navbar"] = $nav->getNavbar();
        
        
        // get results from controller(s)

        $result = $e->getResult();
        
        if ( is_scalar($result) ) 
        {
            $result = array('content' => $vars);
        } 
        elseif ( is_object($result) && ! $vars instanceof ArrayAccess ) 
        {
            $result = (array) $result;
        }
        
		$vars = array_merge($vars,$result);
        
        // show internal xml
        
        if ( $request->getParam('format') == 'xerxes' )
        {
        	$response->headers()->addHeaderLine("Content-type", "text/xml");
        	$content = $this->view_renderer->toXML($vars)->saveXML();
        }
        
        // render as html
        
        else
        {	
	        $content = $this->view_renderer->render($script, $vars);
        }

        $e->setResult($content);
        $response->setContent($content);
        
        return $response;
    }

    public function renderError(MvcEvent $e)
    {
        $error = $e->getError();
        $response = $e->getResponse();
        
        if (!$response)
        {
            $response = new Response();
            $e->setResponse($response);
        }

        switch ($error)
        {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:

            	$vars = array(
                    'message' => 'Page not found.',
                    'exception' => $e->getParam('exception'),
                    'display_exceptions' => $this->displayExceptions(),
                );
                
                $response->setStatusCode(404);
                break;

            case Application::ERROR_EXCEPTION:
            default:
            	
                $exception = $e->getParam('exception');
                
                $vars = array(
                    'message' => 'An error occurred during execution; please try again later.',
                    'exception' => $e->getParam('exception'),
                    'display_exceptions' => $this->displayExceptions(),
                );
                
                $response->setStatusCode(500);
                break;
        }
        
        $script = 'error/index.phtml';
        
        if ( $e->getRequest()->isXmlHttpRequest() )
        {
        	$script = 'error/ajax.phtml';
        }

        $content = $this->view_renderer->render($script, $vars);

        $e->setResult($content);

        return $this->renderView($e);
    }
}
