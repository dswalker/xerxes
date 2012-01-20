<?php

namespace Application\View;

use Application\Controller\SearchController;

use Application\View\Helper\Navigation,
	ArrayAccess,
	Xerxes\Utility\Request,
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
		
		
		### get results
		
		$vars = array();
		
		$vars["base_url"] = $e->getRequest()->getBaseUrl();
		$vars["request"] = $e->getRequest();
		$vars["config"] = Registry::getInstance();
		
		// navigation
		
		$nav = new Navigation($e);
		$vars["navbar"] = $nav->getNavbar();
		
		// controller action
		
		$result = $e->getResult();
		
		if ( $result == null || is_scalar($result) ) 
		{
			$result = array('content' => $vars);
		} 
		elseif ( is_object($result) && ! $vars instanceof ArrayAccess ) 
		{
			$result = (array) $result;
		}
		
		$vars = array_merge($vars,$result);
		
		
		
		### display the results
		
		// show internal xml
		
		if ( $request->getParam('format') == 'xerxes' )
		{
			$response->headers()->addHeaderLine("Content-type", "text/xml");
			$content = $this->view_renderer->toXML($vars)->saveXML();
		}
		
		// render as html
		
		else
		{
			// determine which view script to use
			
			$script = $request->getControllerMap()->getView($request->getParam('format'));
			
			// test view chosen
			// header("Content-type: text/xml"); echo $request->getControllerMap()->saveXML();	echo "<!-- $script -->"; exit;
			
			// render it
			
			$display_as = "html";
			
			if ( $request->getParam('format') == 'json')
			{
				$display_as = "json";
			}
			
			$content = $this->view_renderer->render($script, $vars, $display_as);
		}
		
		
		### return the result
		
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
		
		// basic web request
		
		$display_as = "html";
		$script = 'error/index.phtml';
		
		// @todo: woraround to bug in zf2 trailing slash problem
		
		if ( $e->getRequest() instanceof Request )
		{
			// ajax request
			
			if ( $e->getRequest()->isXmlHttpRequest() )
			{
				$display_as = "json";
				$script = 'error/ajax.phtml';
			}
			
			// command line request
			
			elseif ( $e->getRequest()->isCommandLine() )
			{
				$display_as = "console";
				$script = 'error/console.phtml';
			}
		}
		
		$content = $this->view_renderer->render($script, $vars, $display_as);
		
		### return the result
		
		$e->setResult($content);
		$response->setContent($content);
		
		return $response;
	}
}
