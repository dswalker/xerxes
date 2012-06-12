<?php

namespace Application\Controller;

use Application\Model\Metalib\Engine,
	Xerxes\Utility\Cache;

class MetalibController extends SearchController
{
	protected $id = "metalib";
	
	protected function getEngine()
	{
		return new Engine();
	}
	
	public function searchAction()
	{
		$group_id = $this->engine->search($this->query);
		
		// redirect to status action
		
		$url = $this->request->url_for(array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'status',
			'group' => $group_id	
		));
		
		return $this->redirect()->toUrl($url);
	}
	
	public function statusAction()
	{
		$group_id = $this->request->getParam("group");
		
		$status = $this->engine->getSearchStatus($group_id);
		
		if ( $status->isFinished() )
		{
			// redirect to results action
			
			$url = $this->request->url_for(array(
				'controller' => $this->request->getParam('controller'),
				'action' => 'results',
				'group' => $group_id	
			));
		
			return $this->redirect()->toUrl($url);
		}
		else
		{
			//////////////////// TESTING
			
			foreach ( $status->getDatabaseResultSet() as $set )
			{
				echo $set->database->title_display . "<br>";
				echo $set->find_status . "<br>";
				echo $set->total . "<br>";
				echo "<hr>";
			}
		}
	}
}
