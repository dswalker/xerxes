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
use Application\Model\Knowledgebase\Config;
use Application\Model\Knowledgebase\Database;
use Application\Model\Knowledgebase\Knowledgebase;
use Application\View\Helper\Databases as DatabasehHelper;
use Xerxes\Mvc\ActionController;
use Xerxes\Utility\Cache;

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
	 * (non-PHPdoc)
	 * @see Xerxes\Mvc.ActionController::init()
	 */
	
	public function init()
	{
		// model
		
		$this->knowledgebase = new Knowledgebase($this->request->getUser());
		
		// view helper
		
		$this->helper = new DatabasehHelper($this->event);
		
		// config
		
		$this->config = Config::getInstance();
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
		
		// limited to specific letter
		
		if ( $alpha != null )
		{
			$databases = $this->knowledgebase->getDatabasesStartingWith($alpha);
		}
		else // all databases
		{
			$databases = $this->knowledgebase->getDatabases();
		}
		
		$this->response->setVariable('databases', $databases);
		
		return $this->response;
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
	
	public function pullAction()
	{
		$this->knowledgebase->migrate();
		
		exit;
	}
	
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
}
