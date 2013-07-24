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
use Xerxes\Lti;
use Xerxes\Utility\Parser;

/**
 * Actions for creating and editing a reading list
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class CoursesController extends SearchController
{
	protected $registry; // registry
	protected $reading_list; // data map
	protected $context; // lti context object
	
	/**
	 * New Courses Controller
	 * 
	 * @param MvcEvent $event
	 */
	
	public function __construct(MvcEvent $event)
	{
		parent::__construct($event);
		
		// don't show the header in courses (including errors)
		
		$this->response->setVariable('no_header', 'true');
		
		//testing
		
		$this->response->setVariable('lti', array('instructor' => true));
	}
	
	/**
	 * Register the LTI request in session and authenticate the user
	 */
	
	public function indexAction()
	{
		$key = $this->registry->getConfig('BLTI_KEY', true);
		$secret = $this->registry->getConfig('BLTI_SECRET', true);
	
		$lti = new Lti\Basic($key, $secret);
	
		// save the data in session
	
		$this->request->setSessionData('blti', serialize($lti));
		
		// save username in session
		
		// @todo: this needs to be folded into the authentication framework or something
		
		$this->request->setSessionData('username', $this->extractUsername());
		$this->request->setSessionData("role", "named");
		
		// see if we have records already stored
	
		if ( $this->readinglist()->hasRecords() )
		{
			$params = array(
				'controller' => 'courses',
				'action' => 'results',
			);
		}
		else
		{
			$params = array(
				'controller' => 'courses',
				'action' => 'select'
			);
		}
		
		// redirect from above
		
		return $this->redirectTo($params);
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
			'controller' => 'courses',
			'action' => 'results'
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
				'controller' => 'courses',
				'action' => 'results'
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
			'controller' => 'courses',
			'action' => 'results'
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
			$context_id = $this->lti()->getID();
			$this->reading_list = new ReadingList($context_id);
		}
	
		return $this->reading_list;
	}
	
	/**
	 * Lazyload Basic LTI 
	 * 
	 * @return Lti\Basic;
	 */

	protected function lti()
	{
		if ( ! $this->context instanceof Lti\Basic )
		{
			$this->context = unserialize($this->request->getSessionData("blti"));
			
			if ( ! $this->context instanceof Lti\Basic )
			{
				throw new \Exception("no course session");
			}
		}
		
		return $this->context;
	}
	
	/**
	 * Map username from LMS to local Xerxes user
	 */
	
	protected function extractUsername()
	{
		$username = $this->lti()->getParam('lis_person_contact_email_primary');
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
		return new ListEngine($this->lti());
	}
}
