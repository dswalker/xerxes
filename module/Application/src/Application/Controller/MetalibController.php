<?php

namespace Application\Controller;

use Application\Model\Metalib\Engine,
	Xerxes\Utility\Cache;

class MetalibController extends SearchController
{
	protected $id = "metalib";
	protected $cache;
	
	public function __construct()
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
		
		$id = $group->getId();
		
		$this->cache->set($id, serialize($group));
		
		// redirect to status
		
		$url = $this->request->url_for(array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'status',
			'group' => $id	
		));
		
		sleep(2);

		return $this->redirect()->toUrl($url);
	}
	
	public function statusAction()
	{
		$id = $this->request->getParam("group");
		
		$data = $this->cache->get($id);
		
		$group = unserialize($data);

		print_r($group); exit;		
		
		$status = $group->getSearchStatus();
		
		foreach ( $status->getResultSets() as $result_set )
		{
			echo $result_set->database->title_display . "<br>";
			echo $result_set->find_status . "<br>";
			echo $result_set->total . "<br>";
			echo "<hr>";
		}
	}
}
