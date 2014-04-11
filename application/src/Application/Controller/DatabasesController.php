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

use Application\Model\Knowledgebase\Category;
use Application\Model\Knowledgebase\Database;
use Application\Model\Knowledgebase\Knowledgebase;
use Application\View\Helper\Databases as DatabasehHelper;
use Xerxes\Mvc\ActionController;

class DatabasesController extends ActionController
{
	/**
	 * @var Knowledgebase
	 */
	protected $knowledgebase;
	
	/**
	 * @var DatabasehHelper
	 */
	protected $helper;
	
	/**
	 * (non-PHPdoc)
	 * @see Xerxes\Mvc.ActionController::init()
	 */
	
	public function init()
	{
		$this->knowledgebase = new Knowledgebase($this->request->getUser());
		
		// view helper
		
		$this->helper = new DatabasehHelper($this->event);
	}
	
	/**
	 * Categories page
	 */
	
	public function indexAction()
	{
		// get all categories
		
		$categories = $this->knowledgebase->getCategories();
		
		$this->response->setVariable('categories', $categories->toArray());
		
		return $this->response;
	}
	
	/**
	 * Individual subject page
	 */

	public function subjectAction()
	{
		$subject = $this->request->getParam('subject');
		
		$category = $this->knowledgebase->getCategory($subject);
		
		$this->response->setVariable('categories', $category);
	
		return $this->response;
	}
	
	/**
	 * Database A-Z page
	 */
	
	public function alphabeticalAction()
	{
		$alpha = $this->request->getParam('alpha');
		$query = $this->request->getParam('query');
		
		$databases = null; // list of databases
		
		// limited to specific letter
		
		if ( $alpha != null )
		{
			$databases = $this->knowledgebase->getDatabasesStartingWith($alpha);
		}
		
		// all databases
		
		else
		{
			$databases = $this->knowledgebase->getDatabases();
		}
		
		$this->response->setVariable('databases', $databases);
		
		return $this->response;
	}
}
