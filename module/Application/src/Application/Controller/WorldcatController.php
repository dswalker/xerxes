<?php

namespace Application\Controller;

use Application\Model\Worldcat\Engine,
	Application\View\Helper\Worldcat as SearchHelper,
	Zend\Mvc\MvcEvent;

class WorldcatController extends SearchController
{
	protected $id = "worldcat";
	
	protected function init(MvcEvent $e)
	{
		parent::init($e);
	
		$this->helper = new SearchHelper($e, $this->id, $this->engine);
	}	
	
	protected function getEngine()
	{
		$role = $this->request->getSessionData("role");
		$source = $this->request->getParam("source");
		
		return new Engine($role, $source);
	}
}
