<?php

namespace Application\Controller;

use Zend\Mvc\Controller\ActionController;

class DatabasesController extends ActionController
{
	public function index()
	{
		$params = array('databases');
		
		$url = $this->request->url_for($params);
		
		return $this->redirect()->toUrl($url);
	}
}
