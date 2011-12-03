<?php

namespace Application\Controller;

use Zend\Mvc\Controller\ActionController;

class DatabasesController extends ActionController
{
	private $lang;
	
	public function __construct()
	{
		parent::__construct();
		$this->lang = $this->request->getParam("lang");
	}
	
	public function index()
	{
	}
	
	public function subject()
	{
		$subject = $this->request->getParam("subject");
	}	
}
