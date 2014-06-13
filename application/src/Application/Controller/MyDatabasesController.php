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
	 * Create new category
	 */
	
	public function createNewAction()
	{
		$new_category_name = 'My Saved Databases';
		$match = true;
		
		$categories = $this->knowledgebase->getCategories()->toArray(false);
		
		while ( $match == true )
		{
			$loop_match = false;
			
			foreach ( $categories as $category )
			{
				// already have a default name
				
				if ( $new_category_name == $category['name'] )
				{
					$last_char = substr($category['name'], -1, 1);
					
					// it also already has a number on the end
					
					if ( is_numeric($last_char) )
					{
						$last_char += 1; // so increment it
						$new_category_name = substr($category['name'], 0, -1) . $last_char;
						$loop_match = true; // need to keep checking
					}
					else // this is the first time so add 1
					{
						$new_category_name .= '1';
						$loop_match = true; // need to keep checking
					}
				}
			}
			
			// not matches on any of the existing categories
			
			if ($loop_match == false)
			{
				$match = false; // stop the while loop
			}
		}
		
		// create new one
		
		$category = $this->knowledgebase->createCategory();
		$category->setName($new_category_name);
	
		$subcategory = new Subcategory();
		$subcategory->setName('Databases');
	
		$category->addSubcategory($subcategory);
	
		$this->knowledgebase->updateCategory($category);
	
		// redirect
	
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'subject',
			'id' => $category->getId()
		);
	
		return $this->redirectTo($params);
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
