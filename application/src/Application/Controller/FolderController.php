<?php

namespace Application\Controller;

use Xerxes\Utility\User,
	Application\Model\Saved\Engine;

class FolderController extends SearchController
{
	protected $id = "folder";
	
	public function init()
	{
		// make the username the query
		
		$this->request->replaceParam("query", $this->request->getSessionData('username'));
		
		parent::init();
	}
	
	protected function getEngine()
	{
		return new Engine();
	}
	
	public function indexAction()
	{
		// register the return url in session so we can send the user back
		
		$this->request->setSessionData("return", $this->request->getParam("return"));
		
		// redirect to the results page
		
		$params = array (
			'controller' => 'folder',
			'action' => 'results',
			'username' => $this->request->getSessionData('username')
		);
		
		return $this->redirect($params);
	}
	
	public function resultsAction()
	{
		$total = $this->engine->getHits($this->query)->getTotal();
		
		// user is not logged in, and has no temporary saved records, so nothing to show here;
		// force them to login
		
		if ( ! $this->request->getUser()->isAuthenticated() && $total == 0 )
		{
			// link back here, but minus any username
			
			$folder_link = $this->request->url_for(
				array('controller' => 'folder')	
				);
			
			// auth link, with return back to here
			
			$params = array(
					'controller' => 'authenticate',
					'action' => 'login',
					'return' => $folder_link
			);
			
			// redirect them out
			
			$this->redirect($params);
		}
		
		return parent::resultsAction();
	}
}
