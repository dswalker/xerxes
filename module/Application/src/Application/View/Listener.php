<?php

namespace Application\View;

use Xerxes\Utility\Registry;

use ArrayAccess,
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
    protected $view;
    protected $displayExceptions = false;
    
    public function __construct(ViewRenderer $view)
    {
    	$this->view = $view;
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
        $this->listeners[] = $events->attach('dispatch', array($this, 'render404'), -80);
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
        $ident   = 'Application\Controller\PageController';
        $handler = $events->attach($ident, 'dispatch', array($this, 'renderPageController'), -50);
        $this->staticListeners[] = array($ident, $handler);

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

    public function renderPageController(MvcEvent $e)
    {
        $page = $e->getResult();
        
        if ($page instanceof Response) 
        {
            return;
        }

        $response = $e->getResponse();
        
        if ($response->isNotFound()) 
        {
            return;
        } 

        $routeMatch = $e->getRouteMatch();

        if (!$routeMatch) 
        {
            $page = '404';
        } 
        else 
        {
            $page = $routeMatch->getParam('action', '404');
        }

        if ($page == '404') {
            $response->setStatusCode(404);
        }

        $script     = 'error/' . $page . '.phtml';

        // Action content
        
        $content    = $this->view->render($script);
        $e->setResult($content);

        return $this->renderLayout($e);
    }

    public function renderView(MvcEvent $e)
    {
        $response = $e->getResponse();
        
        // header("Content-type: text/plain"); print_r($response); exit;
        
        if ( ! $response->isSuccess() )
        {
            return;
        }
        
        // set the view
        
        $routeMatch = $e->getRouteMatch();
        $controller = $routeMatch->getParam('controller', 'index');
        $action = $routeMatch->getParam('action', 'index');
        
        
        ##### @todo: HACK 
        if ( $action != "results" && $action != "record") $controller = "search";
        ##### END HACK
        
        
        
        $script = $controller . '/' . $action . '.xsl';
        
        // get the results

        $vars = $e->getResult();
        
        if ( is_scalar($vars) ) 
        {
            $vars = array('content' => $vars);
        } 
        elseif ( is_object($vars) && ! $vars instanceof ArrayAccess ) 
        {
            $vars = (array) $vars;
        }
        
        $vars["request"] = $e->getRequest()->toXML();
        $vars["config"] = Registry::getInstance()->toXML();
        $vars["base_url"] = $e->getRequest()->getBaseUrl();
        
        // show internal xml
        
        if ( $e->getRequest()->getParam('format') == 'xerxes' )
        {
        	$response->headers()->addHeaderLine("Content-type", "text/xml");
        	$content = $this->view->toXML($vars)->saveXML();
        }
        
        // render as html
        
        else
        {	
	        $content = $this->view->render($script, $vars);
        }

        $e->setResult($content);
        $response->setContent($content);
        
        return $response;
    }

    public function render404(MvcEvent $e)
    {
        $vars = $e->getResult();
        if ($vars instanceof Response) {
            return;
        }

        $response = $e->getResponse();
        if ($response->getStatusCode() != 404) {
            // Only handle 404's
            return;
        }

        $vars = array(
            'message'            => 'Page not found.',
            'exception'          => $e->getParam('exception'),
            'display_exceptions' => $this->displayExceptions(),
        );

        $content = $this->view->render('error/404.phtml', $vars);

        $e->setResult($content);

        return $this->renderView($e);
    }

    public function renderError(MvcEvent $e)
    {
        $error    = $e->getError();
        $app      = $e->getTarget();
        $response = $e->getResponse();
        if (!$response) {
            $response = new Response();
            $e->setResponse($response);
        }

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
                $vars = array(
                    'message'            => 'Page not found.',
                    'exception'          => $e->getParam('exception'),
                    'display_exceptions' => $this->displayExceptions(),
                );
                $response->setStatusCode(404);
                break;

            case Application::ERROR_EXCEPTION:
            default:
                $exception = $e->getParam('exception');
                $vars = array(
                    'message'            => 'An error occurred during execution; please try again later.',
                    'exception'          => $e->getParam('exception'),
                    'display_exceptions' => $this->displayExceptions(),
                );
                $response->setStatusCode(500);
                break;
        }

        $content = $this->view->render('error/index.phtml', $vars);

        $e->setResult($content);

        return $this->renderView($e);
    }
}
