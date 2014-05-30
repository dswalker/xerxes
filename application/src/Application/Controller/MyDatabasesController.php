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
use Application\Model\Knowledgebase\Subcategory;
/**
 * My Saved Databases Controller
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class MyDatabasesController extends DatabasesEditController
{
	/**
	 * (non-PHPdoc)
	 * @see Xerxes\Mvc.ActionController::init()
	 */
	
	public function init()
	{
		parent::init();
		
		// set owner to the local user (rather than admin!)
		
		$this->knowledgebase->setOwner($this->request->getUser()->username);
		
		// set view on database sub-folder
		
		$action = $this->request->getParam('action', 'index');
		$this->response->setView("databases/saved/$action.xsl");		
	}
	
	/**
	 * Categories page
	 */
	
	public function indexAction()
	{
		$show_categories = $this->request->getParam('show');
		
		$categories = $this->knowledgebase->getCategories();
		
		// special handling for less than two categories
		
		if ( $show_categories == null && $categories->count() < 2 )
		{
			// no categories, so create one
			
			if ( $categories->count() == 0 )
			{
				$category = $this->knowledgebase->createCategory();
				$category->setName('My Saved Databases');
				
				$subcategory = new Subcategory();
				$subcategory->setName('Databases');
				
				$category->addSubcategory($subcategory);
				
				$this->knowledgebase->updateCategory($category);
			}
			
			// only one category
			
			elseif ( $categories->count() == 1 )
			{
				$category = $categories[0];
			}
			
			// redirect
				
			$params = array(
				'controller' => $this->request->getParam('controller'),
				'action' => 'subject',
				'id' => $category->getId()
			);
				
			return $this->redirectTo($params);
		}
		
		$this->response->setVariable('categories', $categories->toArray(false)); // shallow copy
	
		return $this->response;
	}
	
	/**
	 * Make a saved category public
	 */
	
	public function publicAction()
	{
		$id = $this->request->getParam('id');
		$status = (bool) $this->request->getParam('status');
		
		$category = $this->knowledgebase->getCategoryById($id);
		$category->setPublic($status);
		
		$this->knowledgebase->updateCategory($category);
	}
	
	
	protected function enforceLogin()
	{
		// nothing for now
	}
}
