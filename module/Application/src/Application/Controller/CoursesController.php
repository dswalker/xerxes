<?php

namespace Application\Controller;

use Application\Model\DataMap\ReadingList,
	Application\Model\Saved\Engine,
	Application\Model\Search\Query,
	Xerxes\Lti,
	Xerxes\Utility\Parser,
	Xerxes\Utility\Registry,
	Zend\Mvc\Controller\ActionController;

class CoursesController extends ActionController
{
	protected $registry; // registry
	protected $reading_list; // data map
	protected $context; // lti context object
	
	public function __construct()
	{
		$this->registry = Registry::getInstance();
	}	

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
				'action' => 'display',
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

		$url = $this->request->url_for($params);
		$this->redirect()->toUrl($url);
	}
	
	public function registerAction()
	{
		$this->indexAction();
	}
	
	public function selectAction()
	{
		$engine = new Engine();
		
		$query = new Query();
		$query->addTerm('username', null, 'query', null, $this->request->getSessionData('username'));
		
		$results = $engine->searchRetrieve($query, 1, 500);
	}
	
	public function assignAction()
	{
		// get the ids that were selected for export
	
		$record_array = $this->request->getParam("record", null, true);
	
		if ( count($record_array) > 0 )
		{
			// assign them to our course
	
			$this->readinglist()->assignRecords($record_array);
		}
	
		// construct return url back to reading list for display
	
		$params = array(
			'controller' => 'courses',
			'action' => 'display'
		);
		
		$this->redirect()->toUrl($this->request->url_for($params));
	}	
	
	public function reorderAction()
	{
		// get the ids that were selected for export
	
		$reorder_array = $this->request->getParam("reader_list", null, true);
	
		// assign them to our course
	
		$this->readinglist()->reorderRecords($reorder_array);
	
		if ( $this->request->getParam("noredirect") == "" )
		{
			// construct return url back to reading list for display
			
			$params = array(
				'controller' => 'courses',
				'action' => 'display'
			);
			
			$this->redirect()->toUrl($this->request->url_for($params));
		}
		else
		{
			echo "good!"; // @todo why?
		}
	}
	
	public function removeAction()
	{
		// get the ids that were selected for export
	
		$record_id = $this->request->getParam("record");
	
		if ( $record_id != "" )
		{
			$this->readinglist()->removeCourseRecord($record_id);
		}
	
		// return to reading list
	
		$params = array(
			'base' => 'courses',
			'action' => 'display'
		);
		
		$this->redirect()->toUrl($this->request->url_for($params));
	}
	
	public function displayAction()
	{
		if ( $this->readinglist()->hasCourseRecords() )
		{
			return $this->readinglist()->getCourseRecords();
		}
	}	
		
	/**
	 * Lazyload reading list
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
	 * @throws \Exception
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
}
