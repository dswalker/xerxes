<?php

class Xerxes_Controller_Databases extends Xerxes_Framework_Controller
{
	private $lang;
	
	public function __construct()
	{
		parent::__construct();
		$this->lang = $this->request->getParam("lang");
	}
	
	public function index()
	{
		$this->response->add("categories", $categories);
	}
	
	public function subject()
	{
		$subject = $this->request->getParam("subject");
		
		$this->response->add("subject", $category);
	}	
}
