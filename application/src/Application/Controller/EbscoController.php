<?php

namespace Application\Controller;

use Application\Model\Ebsco\Engine,
	Application\View\Helper\Ebsco as SearchHelper,
	Zend\Mvc\MvcEvent;

class EbscoController extends SearchController
{
	protected $id = "ebsco";

	protected function init(MvcEvent $e)
	{
		parent::init($e);
	
		$this->helper = new SearchHelper($e, $this->id, $this->engine);
	}	
	
	protected function getEngine()
	{
		return new Engine();
	}
}
