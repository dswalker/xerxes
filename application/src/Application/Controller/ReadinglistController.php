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
use Application\Model\Saved\Engine;
use Application\Model\Saved\ReadingList\Engine as ListEngine;
use Application\View\Helper\ReadingList as ListHelper;
use Xerxes\Lti;
use Xerxes\Mvc\MvcEvent;
use Xerxes\Utility\Parser;

/**
 * Actions for creating and editing a reading list
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class ReadinglistController extends SearchController
{
	protected $id = 'readinglist';
	protected $registry; // registry
	protected $reading_list; // reading list
	protected $course_id; // course id
	
	/**
	 * New Readinglist Controller
	 * 
	 * @param MvcEvent $event
	 */
	
	public function __construct(MvcEvent $event)
	{
		parent::__construct($event);
		
		// don't show the header in readinglist 
		// (including errors, which is why it's here in the constructor)
	
		$this->response->setVariable('no_header', 'true');
		
		
		// inital oauth request
		// @todo: this needs to be folded into the authentication framework or something?
		
		if ( $this->request->getParam("oauth_consumer_key") )
		{
			$key = $this->registry->getConfig('BLTI_KEY', true);
			$secret = $this->registry->getConfig('BLTI_SECRET', true);
			
			$lti = new Lti\Basic($key, $secret);
			
			// extract course id
			
			$this->course_id = $lti->getID();
			$this->request->setParam('course_id', $this->course_id);
			
			// save in session for subsequent actions
			
			$this->request->setSessionData('username', $this->extractUsername($lti));
			$this->request->setSessionData("role", "named");
			
			$this->request->setSessionData('course_id', $this->course_id);
			$this->request->setSessionObject("lti_" . $this->course_id, $lti);		
		}
		
		// subsequent requests with course_id in URL
		
		$course_id = $this->request->getParam('course_id');
		
		if ( $course_id != "")
		{
			$this->course_id = $course_id;
			
			// add value from saved lti session @todo make this universal or something
			
			$session_id = 'lti_' . $course_id;
			
			if ( $this->request->existsInSessionData($session_id) )
			{
				$lti = $this->request->getSessionObject($session_id);
				$this->response->setVariable('resource_link_title', $lti->getParam('resource_link_title'));
				$this->response->setVariable('resource_link_description', $lti->getParam('resource_link_description'));
			}
		}
		
		// save lti in the response
		
		$lti = $this->request->getSessionObject("lti_" . $this->course_id);
		$this->response->setVariable('lti', $lti);
	}
	
	protected function init()
	{
		parent::init();
	
		$this->helper = new ListHelper($this->event, $this->id, $this->engine);
		
		$this->response->setVariable('course_nav', $this->helper->getNavigation());
	}	
	
	/**
	 * Register the LTI request in session and authenticate the user
	 */
	
	public function indexAction()
	{
		// see if we have records already stored
	
		if ( $this->readinglist()->hasRecords() )
		{
			$params = array(
				'controller' => $this->id,
				'action' => 'results',
				'course_id' => $this->getCourseID()
			);
		}
		else
		{
			$params = array(
				'controller' => $this->id,
				'action' => 'home',
				'course_id' => $this->getCourseID()
			);
		}
		
		// redirect out
		
		return $this->redirectTo($params);
	}
	
	public function homeAction()
	{
		return $this->response;
	}
	
	/**
	 * Select previously saved records for inclusion in the reading list
	 */
	
	public function selectAction()
	{
		$engine = new Engine();
		
		$query = $engine->getQuery($this->request);
		$username = $this->request->getSessionData('username');
		
		$query->addTerm('username', null, 'query', null, $username);
		
		$results = $engine->searchRetrieve($query, 1, 500);
		
		// echo '<pre>' . print_r($results) . '</pre>'; exit;
				
		$this->response->setVariable('results', $results);
		
		return $this->response;
	}
	
	/**
	 * Assign saved records to the reading list
	 */
	
	public function assignAction()
	{
		// get the ids that were selected for export
	
		$record_array = $this->request->getParam("record", null, true);
	
		if ( count($record_array) > 0 )
		{
			// assign them to our course
	
			$this->readinglist()->assignRecords($record_array);
		}
	
		// construct return url back to reading list for results
	
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'results',
			'course_id' => $this->getCourseID()
		);
		
		return $this->redirectTo($params);
	}	
	
	/**
	 * Reorder records in the reading list
	 */
	
	public function reorderAction()
	{
		// get the ids that were selected for export
	
		$reorder_array = $this->request->getParam("reader_list", null, true);
	
		// assign them to our course
	
		$this->readinglist()->reorderRecords($reorder_array);
	
		if ( $this->request->getParam("noredirect") == "" )
		{
			// construct return url back to reading list for results
			
			$params = array(
				'controller' => $this->request->getParam('controller'),
				'action' => 'results',
				'course_id' => $this->getCourseID()
			);
			
			return $this->redirectTo($params);
		}
		else
		{
			$this->response->noView(); // ajax action, no need for a view
		}
	}
	
	/**
	 * Alias for remove action
	 */
	
	public function saveAction()
	{
		return $this->removeAction();
	}
	
	/**
	 * Remove the selected record from the reading list
	 */
	
	public function removeAction()
	{
		// get the ids that were selected for export
	
		$record_id = $this->request->getParam("id");
	
		if ( $record_id != "" )
		{
			$this->readinglist()->removeRecord($record_id);
		}
	
		// return to reading list
	
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'results',
			'course_id' => $this->getCourseID()
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * Records that are in this reading list
	 */
	
	public function resultsAction()
	{
		if ( $this->readinglist()->hasRecords() )
		{
			return parent::resultsAction();
		}
	}	
		
	/**
	 * Lazyload reading list
	 * 
	 * @return ReadingList
	 */
	
	protected function readinglist()
	{
		if ( ! $this->reading_list instanceof ReadingList )
		{
			$this->reading_list = new ReadingList($this->getCourseID());
		}
	
		return $this->reading_list;
	}
	
	
	/**
	 * Map username from LMS to local Xerxes user
	 */
	
	protected function extractUsername(Lti\Basic $lti)
	{
		$username = $lti->getParam('lis_person_contact_email_primary');
		$username = Parser::removeRight($username, '@');
		
		return $username;
	}
	
	/**
	 * Override: Don't check spelling, since there's nothing to check
	 * 
	 * (non-PHPdoc)
	 * @see Application\Controller.SearchController::checkSpelling()
	 */
	
	protected function checkSpelling()
	{
	}
	
	/**
	 * @return Engine
	 */
	
	protected function getEngine()
	{
		return new ListEngine($this->getCourseID());
	}
	
	/**
	 * Get the course ID
	 */
	
	protected function getCourseID()
	{
		return $this->request->requireSessionData('course_id', 'Session has expired');
	}
}
