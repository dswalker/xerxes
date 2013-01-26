<?php

namespace Application\Controller;

use Application\Model\Worldcat\Engine;
use Application\View\Helper\Worldcat as SearchHelper;

class WorldcatController extends SearchController
{
	protected $id = "worldcat";
	
	protected function init()
	{
		parent::init();
	
		$this->helper = new SearchHelper($e, $this->id, $this->engine);
	}	
	
	protected function getEngine()
	{
		$role = $this->request->getSessionData("role");
		$source = $this->request->getParam("source");
		
		return new Engine($role, $source);
	}
}
