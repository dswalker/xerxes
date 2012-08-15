<?php

namespace Application\Controller;

use Zend\Mvc\Controller\ActionController;

class IndexController extends ActionController
{
	public function indexAction()
	{
		if ( $this->request->getParam('base') != '')
		{
			$params = $this->request->getParams();
			
			$params['controller'] = $params['base'];
			unset($params['base']);
			
			$url = $this->request->url_for($params);
			
			//print_r($params); echo "<p>$url</p>"; exit;
			
			return $this->redirect()->toUrl($url);
		}
		
		
		
		
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
