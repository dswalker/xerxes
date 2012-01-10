<?php

namespace Application\Controller;

use Application\Model\Metalib\Engine;

class MetalibController extends SearchController
{
	protected $id = "metalib";
	
	protected function getEngine()
	{
		return new Engine();
	}
	
	public function searchAction()
	{
		// print_r($this->query);
		
		$group = $this->engine->search($this->query);
		
		echo $group; exit;
	}
}
