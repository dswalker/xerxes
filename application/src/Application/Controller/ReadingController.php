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

use Application\Model\DataMap\ReadingList;
use Xerxes\Mvc\ActionController;
use Xerxes\Mvc\MvcEvent;

class ReadingController extends ActionController
{
	protected $id = 'reading';
	protected $controller; // search controller
	
	/**
	 * New Reading Controller
	 *
	 * @param MvcEvent $event
	 */
	
	public function __construct(MvcEvent $event)
	{
		parent::__construct($event);
	
		// don't show the header in readinglist 
		// (including errors, which is why it's here in the constructor)
	
		$this->response->setVariable('no_header', 'true');	
	}
	
	public function init()
	{
		$this->controller = new SummonController($this->event);	
		$this->request->replaceParam('controller', $this->id);
		
		$course_id = $this->request->getParam('course_id');
		
		if ( $course_id != "")
		{
			$this->request->setSessionData('course_id', $course_id);
		}
	}
	
	public function indexAction()
	{
		$response = $this->controller->execute('index');
	
		$response->setView('readinglist/search/index.xsl');
	
		return $response;
	}

	public function searchAction()
	{
		return $this->controller->execute('search');
	}
	
	public function resultsAction()
	{
		$response = $this->controller->execute('results');
		
		$response->setView('readinglist/search/results.xsl');
		
		return $response;
	}
	
	public function recordAction()
	{
		$response = $this->controller->execute('record');
	
		$response->setView('readinglist/search/record.xsl');
	
		return $response;
	}

	public function saveAction()
	{
		$response = $this->controller->execute('save');
		
		$id = $this->response->getVariable('savedRecordID');
		$course_id = $this->request->getSessionData('course_id');
		
		$reading_list = new ReadingList($this->request->requireSessionData('course_id', 'Session has expired'));
		
		$reading_list->assignRecords(array($id));
	
		return $response;
	}
}
