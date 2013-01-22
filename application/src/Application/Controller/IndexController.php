<?php

namespace Application\Controller;

use Xerxes\Mvc\ActionController;

class IndexController extends ActionController
{
	public function indexAction()
	{
		// get the default controller configured in map
		
		$default_controller = $this->controller_map->getDefaultController();
		
		// update the request
		
		$this->request->replaceParam('controller', $default_controller);
		
		// invoke the controller
		
		$controller = $this->controller_map->getControllerObject($default_controller, $this->event);
		
		return $controller->execute('index');
	}
}
