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

use Application\Model\Knowledgebase\Config;
use Application\Model\Knowledgebase\Knowledgebase;
use Application\View\Helper\Databases as DatabasehHelper;
use Xerxes\Mvc\ActionController;
use Xerxes\Utility\Cache;

/**
 * Databases Controller
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class DatabasesController extends ActionController
{
	/**
	 * @var Cache
	 */
	protected $cache;
	
	/**
	 * @var Knowledgebase
	 */
	protected $knowledgebase;
	
	/**
	 * @var DatabasehHelper
	 */
	protected $helper;
	
	/**
	 * @var Config
	 */
	protected $config;
	
	/**
	 * Identifies the cached alpha listing
	 * @var string
	 */
	protected $database_alpha_id = 'database-alpha';
	
	/**
	 * (non-PHPdoc)
	 * @see Xerxes\Mvc.ActionController::init()
	 */
	
	public function init()
	{
		$this->knowledgebase = $this->getKnowledgebase();
		
		// view helper
		
		$this->helper = new DatabasehHelper($this->event);
		
		// config
		
		$this->config = Config::getInstance();
		
		$this->response->setVariable('config_local', $this->config);
		
		// local navigation links
		
		$this->response->setVariable('edit_link', $this->helper->getEditLink());
		
		// cached data
		
		$this->setCachedData();
	}
	
	/**
	 * Categories page
	 */
	
	public function indexAction()
	{
		// get all categories
		
		$categories = $this->knowledgebase->getCategories();
		
		$this->helper->injectDataLinks($categories);
		
		$this->response->setVariable('categories', $categories->toArray(false)); // shallow copy
		
		return $this->response;
	}
	
	/**
	 * Individual subject page
	 */

	public function subjectAction()
	{
		$subject = $this->request->getParam('subject');
		$id = $this->request->getParam('id');
		
		// if the request included the internal id, redirect
		// the user to the normalized form
		
		if ( $id != "" )
		{
			$category = $this->knowledgebase->getCategoryById($id);
			$normalized = $category->getNormalized();
			
			$params = array(
				'controller' => $this->request->getParam('controller'),
				'action' => $this->request->getParam('action'),
				'subject' => $normalized
			);
			
			return $this->redirectTo($params);
		}
		
		$category = $this->knowledgebase->getCategory($subject);
		
		$this->helper->injectDataLinks($category);
		
		$this->response->setVariable('category', $category);
		
		return $this->response;
	}
	
	/**
	 * Database page
	 */
	
	public function databaseAction()
	{
		$id = $this->request->getParam('id');
	
		$database = $this->knowledgebase->getDatabase($id);
		
		if ( $database == null )
		{
			$database = $this->knowledgebase->getDatabaseBySourceId($id);
		}
		
		$this->helper->injectDataLinks($database);
	
		$this->response->setVariable('database', $database);
	
		return $this->response;
	}

	/**
	 * Librarian page
	 */
	
	public function librarianAction()
	{
		$id = $this->request->getParam('id');
	
		$librarian = $this->knowledgebase->getLibrarian($id);
	
		$this->response->setVariable('librarians', $librarian);
	
		return $this->response;
	}
	
	/**
	 * Librarians
	 */
	
	public function librariansAction()
	{
		$librarian = $this->knowledgebase->getLibrarians();
		$this->response->setVariable('librarians', $librarian);
	
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
		
		// this is a query
		
		if ( $query != null )
		{
			$databases = $this->knowledgebase->searchDatabases($query);
		}
		
		// limited to specific letter
		
		elseif ( $alpha != null )
		{
			$databases = $this->knowledgebase->getDatabasesStartingWith($alpha);
		}
		
		// redirect to the first letter
		
		else 
		{
			$params = array(
				'controller' => $this->request->getParam('controller'),
				'action' => $this->request->getParam('action'),
				'alpha' => 'A',
			);
			
			return $this->redirectTo($params);
		}
		
		$this->helper->injectDataLinks($databases);
		
		$this->response->setVariable('databases', $databases);
		
		return $this->response;
	}
	
	/**
	 * Librarian image
	 */
	
	public function librarianImageAction()
	{
		$librarian_id = $this->request->getParam("id");
		
		$librarian = $this->knowledgebase->getLibrarian($librarian_id);
		
		$thumb = $librarian->getImage();
		
		if ( $thumb == "" )
		{
			$url = $librarian->getImageUrl();
			
			if ( $url != "" )
			{
				return $this->redirectTo($url);
			}
			
			exit;
		}

		// output image
		
		header("Content-type: image/jpg");
		imagejpeg($thumb, null, 100);
		
		imagedestroy($thumb);
		
		exit;
	}
	
	/**
	 * Proxy a database URL
	 * 
	 * @throws \Exception
	 */
	
	public function proxyAction()
	{
		$id = $this->request->requireParam('id', 'Missing database ID'); 

		$database = $this->knowledgebase->getDatabase($id);
		
		if ( $database == null )
		{
			throw new \Exception("Couldn't find database '$id'");
		}
		
		$final = $database->getProxyUrl();
			
		return $this->redirectTo($final);
	}
	
	/**
	 * Add cached data to response
	 */
	
	protected function setCachedData()
	{
		// get cached information
		
		$databases_alpha = $this->getDatabaseAlpha();
		
		// create links
		
		$databases_alpha = $this->helper->injectAlphaLinks($databases_alpha);
		
		// add to response
		
		$this->response->setVariable('database_alpha', $databases_alpha);
	}
	
	/**
	 * Get database alpha listing
	 */
	
	protected function getDatabaseAlpha()
	{
		$types = $this->cache()->get($this->database_alpha_id );
	
		if ( $types == null )
		{
			$types = $this->knowledgebase->getDatabaseAlpha();
			$this->cache()->set($this->database_alpha_id , $types, time() + (12 * 60 * 60) ); // 12 hour cache
		}
	
		return $types;
	}
	
	/**
	 * @return Knowledgebase
	 */
	protected function getKnowledgebase()
	{
		// model
	
		$knowledgebase = new Knowledgebase();
		
		// make sure this is admin
		
		$knowledgebase->setOwner('admin');
	
		// remove excluded types from database alpha listings and such
		// but not from subject pages
	
		$knowledgebase->setFilterResults(true);
		
		return $knowledgebase;
	}	
	
	/**
	 * @return Cache
	 */
	
	protected function cache()
	{
		if ( ! $this->cache instanceof Cache )
		{
			$this->cache = new Cache();
		}
	
		return $this->cache;
	}
}
