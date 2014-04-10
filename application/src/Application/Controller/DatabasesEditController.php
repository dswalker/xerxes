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
use Xerxes\Mvc\Exception\AccessDeniedException;

class DatabasesEditController extends DatabasesController
{
	/**
	 * Do a user check
	 */
	
	public function init()
	{
		parent::init();
		
		$user = $this->request->getUser();
		
		if ( $user->isAdmin() != true )
		{
			print_r($user); exit;
			
			throw new AccessDeniedException('Only administrators may access this part of the system');
		}
	}	
	
	/**
	 * Individual subject page
	 * 
	 * We take this version over the parent so we can use the internal id in 
	 * place of the normalized identifier, since we might edit the name of the 
	 * category and that would change the normalized identifier
	 */
	
	public function subjectAction()
	{
		$id = $this->request->getParam('id');
	
		$category = $this->knowledgebase->getCategoryById($id);
	
		$this->response->setVariable('categories', $this->knowledgebase->convertToArray($category, true));
	
		return $this->response;
	}	
	
	/**
	 * Add category
	 */
	
	public function addCategoryAction()
	{
		$name = $this->request->getParam('name');
		$return = $this->request->getParam('return');
		
		$this->knowledgebase->addCategory($name);
		
		return $this->redirectTo($return);
	}

	/**
	 * Edit category name
	 */
	
	public function editCategoryAction()
	{
		$category_id = (int) $this->request->getParam('pk');
		$value = $this->request->getParam('value');
	
		// update category name
	
		$category = $this->knowledgebase->getCategoryById($category_id);
		$category->setName($value);
		$this->knowledgebase->updateCategory($category);
	
		$this->response->noView();
	}

	/**
	 * Delete category
	 */
	
	public function deleteCategoryAction()
	{
		$category_id = (int) $this->request->getParam('id');
	
		$this->knowledgebase->deleteCategory($category_id);
	
		$params = array('controller' => $this->request->getParam('controller'));
		
		return $this->redirectTo($params);
	}	
	
	/**
	 * Add subcategory
	 */
	
	public function addSubcategoryAction()
	{
		$return = $this->request->getParam('return');
		$category_name = $this->request->getParam('category');
		$subcategory_name = $this->request->getParam('subcategory');
	
		$this->knowledgebase->addSubcategory($category_name, $subcategory_name);
	
		return $this->redirectTo($return);
	}
	
	/**
	 * Edit subcategory name
	 */	
	
	public function editSubcategoryAction()
	{
		$subcategory_id = $this->request->getParam('pk');
		$value = $this->request->getParam('value');
		
		// update subcategory name
	
		$subcategory = $this->knowledgebase->getSubcategoryById($subcategory_id);
		$subcategory->setName($value);
		$this->knowledgebase->update($subcategory);
	
		$this->response->noView();
	}
	
	/**
	 * Delete subcategory
	 */
	
	public function deleteSubcategoryAction()
	{
		$category_id = $this->request->getParam('category');
		$subcategory_id = $this->request->getParam('subcategory');
		
		$this->knowledgebase->deleteSubcategory($subcategory_id);
		
		$return = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'subject',
			'id' => $category_id
		);
	
		return $this->redirectTo($return);
	}	
	
	/**
	 * Reorder databases in subcategory list
	 */
	
	public function reorderSubcategoriesAction()
	{
		// get the ids that were selected for export
	
		$category = $this->request->getParam('category');
	
		$reorder_array = $this->request->getParam('subcategory', null, true);
	
		// re-order the subcategories
	
		$this->knowledgebase->reorderSubcategories($reorder_array);
	
		// redirect or not
	
		if ( $this->request->getParam("noredirect") == "" )
		{
			// construct return url back to reading list for results
	
			$params = array(
				'controller' => $this->request->getParam('controller'),
				'action' => 'subject',
				'subject' => $category
			);
	
			return $this->redirectTo($params);
		}
		else
		{
			$this->response->noView(); // ajax action, no need for a view
		}
	}	
	
	/**
	 * Add a database to the knowledgebase
	 */
	
	public function addDatabaseAction()
	{
		$post_back = $this->request->getParam('postback');
		
		if ( $post_back != "" )
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
			
			$database->setCoverage($coverage);
			$database->setCreator($creator);
			$database->setDescription($description);
			$database->setLanguage($language);
			$database->setLink($link);
			$database->setLinkGuide($link_guide);
			$database->setNotes($notes);
			$database->setPublisher($publisher);
			$database->setSearchHints($search_hints);
			$database->setSourceId('web');
			$database->setTitle($title);
			
			if ( $active != null )
			{
				$database->setActive($active);
			}
			
			if ( $date_new_expiry != null )
			{
				$date_time = new \DateTime($date_new_expiry);
				$database->setDateNewExpiry($date_new_expiry);
			}
			
			if ( $proxy != null )
			{
				$database->setProxy($proxy);
			}
			
			foreach ( $alternate_titles as $alternate_title )
			{
				$database->addAlternateTitle($alternate_title);
			}
			
			foreach ( $keywords as $keyword )
			{
				$database->addKeyword($keyword);
			}
			
			$this->knowledgebase->updateDatabase($database);
		
			$params = array(
				'controller' => $this->request->getParam('controller'),
				'action' => 'alphabetical'
			);
			
			// return $this->redirectTo($params);
		}
	}
	
	/**
	 * Remove database from knowledgebase
	 */
	
	public function removeDatabaseAction()
	{
		$id = $this->request->requireParam('id', 'You must specify a database to delete');
		
		$this->knowledgebase->removeDatabase($id);

		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'alphabetical'
		);
		
		return $this->redirectTo($params);	
	}
}
