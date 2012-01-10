<?php

namespace Application\Controller;

use Zend\Mvc\Controller\ActionController;

class IndexController extends ActionController
{
	public function indexAction()
	{
		$controller_map = $this->request->getControllerMap();
		
		// get the default controller configured in map
		
		$controller = $controller_map->getDefaultController();
		
		// update the controller map and request
		
		$controller_map->setController($controller);
		$this->request->replaceParam('controller', $controller);
		
		// invoke the controller
		
		return $this->forward()->dispatch($controller);
	}
}
