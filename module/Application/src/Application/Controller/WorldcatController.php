<?php

namespace Application\Controller;

use Application\Model\Worldcat\Engine;

class WorldcatController extends SearchController
{
	protected $id = "worldcat";
	
	protected function getEngine()
	{
		$role = $this->request->getSessionData("role");
		$source = $this->request->getParam("source");
		
		return new Engine($role, $source);
	}
}
