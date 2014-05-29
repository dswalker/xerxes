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

use Application\Model\Authentication\AuthenticationFactory;

use Application\Model\DataMap\Users;

use Application\Model\DataMap\ReadingList;
use Application\Model\Saved\Engine;
use Application\Model\Saved\ReadingList\Engine as ListEngine;
use Application\Model\Saved\ReadingList\Result;
use Application\View\Helper\ReadingList as ListHelper;
use Xerxes\Lti;
use Xerxes\Mvc\MvcEvent;

/**
 * Actions for creating and editing a reading list
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class ReadinglistController extends SearchController
{
	/**
	 * @var string
	 */
	protected $id = 'readinglist';
	
	/**
	 * @var ReadingList
	 */
	protected $reading_list;
	
	/**
	 * @var string
	 */
	protected $course_id;
	
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
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Application\Controller.SearchController::init()
	 */
	
	public function init()
	{
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
			
			$this->request->setSessionData('course_id', $this->course_id);
			$this->request->setSessionObject("lti_" . $this->course_id, $lti);
			
			$user = $this->request->getUser();
			
			// this is the first time user has come during this session
			
			if ( $this->request->getSessionData('reading_list_user') == null )
			{
				$datamap = new Users();
				
				$user_id = $lti->getUserID(); // the lms user id
				$username = $datamap->getUserFromLti($user_id); // see if user is already in our database
				
				// we don't know this user
				
				if ( $username == "" )
				{
					// and they have not logged in
					
					if ( ! $user->isAuthenticated() )
					{
						return $this->redirectToLogin(); // send them to login
					}
					else // they are logged in
					{
						$datamap->associateUserWithLti($user->username, $user_id); // register them for next time
					}
				}
				else // we know this user
				{
					// swap username
					
					$user->username = $username;
					
					// register them in session and such for single sign on
						
					$auth_factory = new AuthenticationFactory();
					$auth_factory->getAuthenticationObject($this->request)->register($user);
				}

				// and make sure we keep track of that here for subsequent requests
				
				$this->request->setSessionData('reading_list_user', $user_id);
			}
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
		
		
		
		parent::init();
		
		
		
		// display helpers
		
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
		$position = $this->request->getParam("position");
	
		if ( $record_id != "" )
		{
			$this->readinglist()->removeRecord($record_id);
		}
	
		// return to reading list
	
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'results',
			'course_id' => $this->getCourseID(),
			'#' => 'position-' . $position
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * User supplied data to edit the record
	 */
	
	public function editAction()
	{
		$reading_list = new ReadingList($this->request->requireSessionData('course_id', 'Session has expired'));
		
		// this is a reset
		
		if ( $this->request->getParam('reset') != "" )
		{
			$record_id = $this->request->getParam('record_id');
			
			$reading_list->clearRecordData($record_id);
		}
		else // update the user supplied data
		{
			$result = new Result();
			
			$result->record_id = $this->request->getParam('record_id');
			$result->title = $this->request->getParam('title');
			$result->author = $this->request->getParam('author');
			$result->publication = $this->request->getParam('publication');
			$result->description = $this->request->getParam('abstract');
			
			$success = $reading_list->editRecord($result);
		}
		
		// return to reading list
	
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'results',
			'course_id' => $this->getCourseID(),
			'#' => 'record-' . $this->request->getParam('record_id')
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
	 * Minimize the display (abstract, etc.)
	 */	
	
	public function minimizeAction()
	{
		$minimize = $this->request->getParam('minimize');
		
		if ( $minimize == 'true' )
		{
			$this->request->setSessionData('reading_minimize', 'true');
		}
		else
		{
			$this->request->setSessionData('reading_minimize', null);
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
	 * 
	 * @return string
	 */
	
	protected function getCourseID()
	{
		return $this->request->requireSessionData('course_id', 'Session has expired');
	}
}
