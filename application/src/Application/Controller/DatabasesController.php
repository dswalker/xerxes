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
use Xerxes\Mvc\ActionController;

class DatabasesController extends ActionController
{
	/**
	 * @var Knowledgebase
	 */
	private $knowledgebase;
	
	public function init()
	{
		$this->knowledgebase = new Knowledgebase($this->request->getUser());
	}
	
	public function indexAction()
	{
		$categories = $this->knowledgebase->getCategories();
		
		$this->response->setVariable('categories', $this->knowledgebase->convertToArray($categories));
		
		$this->response->render('xerxes')->send(); exit;
		
		return $this->response;
	}

	public function subjectAction()
	{
		$normalized = $this->request->getParam('subject');
		
		$category = $this->knowledgebase->getCategory($normalized);
		
		$this->response->setVariable('categories', $this->knowledgebase->convertToArray($category, true));
	
		$this->response->render('xerxes')->send(); exit;
		
		return $this->response;
	}	
	
	public function addCategoryAction()
	{
		$name = $this->request->getParam('name');
		$this->knowledgebase->addCategory($name);
		
		exit;
	}
	
	public function addSubcategoryAction()
	{
		$category_name = $this->request->getParam('category');
		$subcategory_name = $this->request->getParam('subcategory');
	
		$this->knowledgebase->addSubcategory($category_name, $subcategory_name);
	
		exit;
	}
	
	public function addDatabaseAction()
	{
		$title = $this->request->requireParam('title', 'You must specify a title');
		$link = $this->request->requireParam('link', 'You must specify a link');
		
		$active = $this->request->getParam('active');
		$coverage = $this->request->getParam('coverage');
		$creator = $this->request->getParam('creator');
		$date_new_expiry = $this->request->getParam('date_new_expiry');
		$description = $this->request->getParam('description');
		$language = $this->request->getParam('language');
		
		$link_guide = $this->request->getParam('link_guide');
		$notes = $this->request->getParam('notes');
		$proxy = $this->request->getParam('proxy');
		$publisher = $this->request->getParam('publisher');
		$search_hints = $this->request->getParam('search_hints');

		$alternate_titles = $this->request->getParam('alternate_title', null, true);
		$keywords = $this->request->getParam('keywords', null, true);	
		
		
		$database = new Database();
		
		$database->setActive($active);
		$database->setCoverage($coverage);
		$database->setCreator($creator);
		$database->setDateNewExpiry($date_new_expiry);
		$database->setDescription($description);
		$database->setLanguage($language);
		$database->setLink($link);
		$database->setLinkGuide($link_guide);
		$database->setNotes($notes);
		$database->setProxy($proxy);
		$database->setPublisher($publisher);
		$database->setSearchHints($search_hints);
		$database->setSourceId('web');
		$database->setTitle($title);
		
		foreach ( $alternate_titles as $alternate_title )
		{
			$database->addAlternateTitle($alternate_title);
		}
		
		foreach ( $keywords as $keyword )
		{
			$database->addKeyword($keyword);
		}
		
		$this->knowledgebase->updateDatabase($database);
	
		exit;
	}
}
