<?php

namespace Application\View;

use Application\View\Helper\Url,
	Zend\Mvc\MvcEvent,
	Xerxes\Utility\Parser,
	Xerxes\Utility\Request,
	Xerxes\Utility\Registry;

class Helper
{
	protected $request;
	protected $registry;
	protected $route_match;
	protected $router;
	
	public function __construct( MvcEvent $e )
	{
		$this->request = $e->getRequest();
		$this->registry = Registry::getInstance();
		$this->route_match = $e->getRouteMatch();
		$this->router = $e->getRouter();
	}
	
	// @todo figure out a better way to do this
	
	public function getParam( $param )
	{
		return $this->route_match->getParam($param);
	}
	
	public function getUrl($params = array())
	{
		print_r($params);
		
		$options = array('name' => 'default');
		
		if (null === $this->router) 
		{
			return '';
		}

		// Remove trailing '/index' from generated URLs.
		
		$url = $this->router->assemble($params, $options);
		
		if ((6 <= strlen($url)) && '/index' == substr($url, -6)) 
		{
			$url = substr($url, 0, strlen($url) - 6);
		}

		return $url;
	}	
}