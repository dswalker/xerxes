<?php

namespace Application\View;

use Xerxes\Utility\Registry,
	Zend\Http\Request,
	Zend\Mvc\MvcEvent,
	Zend\Mvc\Router\RouteMatch;

class Helper
{
	protected $router; // route stack
	protected $request; // request
	protected $registry; // reistry
	
	public function __construct(MvcEvent $e)
	{
		$this->request = $e->getRequest();
		$this->registry = Registry::getInstance();
		$this->router = $e->getRouter();
	}	
}