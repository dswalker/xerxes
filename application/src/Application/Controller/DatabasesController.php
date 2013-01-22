<?php

namespace Application\Controller;

use Xerxes\Mvc\ActionController;

class DatabasesController extends ActionController
{
	public function indexAction()
	{
		$this->response->setVariable('hello', 'world');
		
		return $this->response;
	}
}
