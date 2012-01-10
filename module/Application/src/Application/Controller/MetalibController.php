<?php

namespace Application\Controller;

use Application\Model\Metalib\Engine,
	Xerxes\Utility\Cache;

class MetalibController extends SearchController
{
	protected $id = "metalib";
	protected $cache;
	
	protected function __construct()
	{
		$this->cache = new Cache();
	}	
	
	protected function getEngine()
	{
		return new Engine();
	}
	
	public function searchAction()
	{
		$group = $this->engine->search($this->query);
	}
	
	public function statusAction()
	{
		$group->checkStatus();
		
		// print_r($group->getResultSets()); exit;
		
		foreach ( $group->getResultSets() as $result_set )
		{
			echo $result_set->database->title_display . "<br>";
			echo $result_set->find_status . "<br>";
			echo $result_set->total . "<br>";
			echo "<hr>";
		}
	}
}
