<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Controller;

use Application\Model\Saved\Engine;
use Application\Model\Solr;
use Application\View\Helper\Folder as FolderHelper;
use Xerxes\Utility\User;

class FolderController extends SearchController
{
	protected $id = "folder";

	protected function init()
	{
		parent::init();
	
		$this->helper = new FolderHelper($this->event, $this->id, $this->engine);
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
		
		return $this->redirectTo($params);
	}
	
	public function resultsAction()
	{
		// ensure we've got the right user
		
		if ( $this->request->getParam('username') != $this->request->getSessionData('username') )
		{
			$params = array(
				'controller' => 'folder',
				'action' => 'results',
				'username' => $this->request->getSessionData('username')
			);
				
			return $this->redirectTo($params);
		}		
		
		$total = $this->engine->getHits($this->query)->getTotal();
		
		// user is not logged in, and has no temporary saved records, so nothing to show here;
		// force them to login
		
		if ( ! $this->request->getUser()->isAuthenticated() && $total == 0 )
		{
			// link back here, but minus any username
			
			$folder_link = $this->request->url_for(	array('controller' => 'folder'), true );
			
			// auth link, with return back to here
			
			$params = array(
					'controller' => 'authenticate',
					'action' => 'login',
					'return' => $folder_link
			);
			
			// redirect them out
			
			return $this->redirectTo($params);
		}
		
		return parent::resultsAction();
	}

	public function recordAction()
	{
		$response = parent::recordAction();
	
		$resultset = $response->getVariable('results');
		$result = $resultset->getRecord(0);
		$record = $result->getXerxesRecord();
	
		// if a solr record, fetch holdings
	
		if ( $record instanceof Solr\Record )
		{
			try
			{
				$engine = new Solr\Engine;
	
				$solr_results = $engine->getRecord($result->original_id);
				$holdings = $solr_results->getRecord(0)->getHoldings();
				$result->setHoldings($holdings);
			}
			catch ( \Exception $e )
			{
				trigger_error('saved records holdings lookup: ' . $e->getMessage(), E_USER_WARNING);
			}
		}
	
		return $response;
	}
}
