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

use Application\Model\Knowledgebase\Database;
use Application\Model\Knowledgebase\DatabaseSequence;
use Application\Model\Knowledgebase\Librarian;
use Application\Model\Knowledgebase\LibrarianSequence;

/**
 * Databases Edit Controller
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class DatabasesEditController extends DatabasesController
{
	/**
	 * @var string
	 */
	protected $librarian_names_id = 'librarian-names';

	/**
	 * @var string
	 */
	protected $database_titles_id = 'database-titles';
	
	/**
	 * @var string
	 */
	protected $database_types_id = 'database-types';
	
	/**
	 * @var string
	 */
	protected $database_alpha_edit_id = 'database-alpha-edit';
	
	/**
	 * (non-PHPdoc)
	 * @see Application\Controller.DatabasesController::init()
	 */
	
	public function init()
	{
		parent::init();
		
		$return = $this->enforceLogin();
		
		if ( $return != null )
		{
			return $return;
		}
		
		// set view on database sub-folder
		
		$action = $this->request->getParam('action', 'index');
		$this->response->setView("databases/edit/$action.xsl");
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
		$normalized = $this->request->getParam('subject');
		
		
		// if the request included the normalizd id, redirect
		// the user to the internal form
		
		if ( $normalized != "" )
		{
			$category = $this->knowledgebase->getCategory($normalized);
			$id = $category->getId();
				
			$params = array(
				'controller' => $this->request->getParam('controller'),
				'action' => $this->request->getParam('action'),
				'id' => $id
			);
				
			return $this->redirectTo($params);
		}		
		
	
		$category = $this->knowledgebase->getCategoryById($id);
		
		$this->helper->injectDataLinks($category);
	
		$this->response->setVariable('category', $category);
		
		return $this->response;
	}	
	
	/**
	 * Add category
	 */
	
	public function addCategoryAction()
	{
		$name = $this->request->getParam('name');
		
		$id = $this->knowledgebase->addCategory($name);
		
		// return them to newly created page
		
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'subject',
			'id' => $id
		);
		
		return $this->redirectTo($params);
	}

	/**
	 * Edit category name
	 */
	
	public function editCategoryAction()
	{
		$category_id = (int) $this->request->getParam('pk');
		$value = $this->request->getParam('value');
		
		if ( $value != "" )
		{
			// update category name
		
			$category = $this->knowledgebase->getCategoryById($category_id);
			$category->setName($value);
			$this->knowledgebase->updateCategory($category);
		}
	
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
		$category_id = $this->request->getParam('category');
		$subcategory_name = $this->request->getParam('subcategory');
	
		$this->knowledgebase->addSubcategory($category_id, $subcategory_name);
	
		return $this->returnToCategory($category_id);
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
		
		return $this->returnToCategory($category_id);
	}
	
	/**
	 * Move subcategory to sidebar
	 */
	
	public function moveToSidebarAction()
	{
		$category_id = $this->request->getParam('category');
		$subcategory_id = $this->request->getParam('subcategory');
		$move = (bool) $this->request->getParam('move');
	
		$subcategory = $this->knowledgebase->getSubcategoryById($subcategory_id);
		
		$subcategory->setSidebar($move);
		
		$this->knowledgebase->update($subcategory);
	
		return $this->returnToCategory($category_id);
	}

	/**
	 * Reorder subcategories in category
	 */
	
	public function reorderSubcategoriesAction()
	{
		$category = $this->request->getParam('cat');
	
		$reorder_array = $this->request->getParam('subcategory', null, true);
	
		// re-order the subcategories
	
		$this->knowledgebase->reorderSubcategories($reorder_array);
	
		// redirect or not
	
		if ( $this->request->getParam("noredirect") == "" )
		{
			return $this->returnToCategory($category);
		}
		else
		{
			$this->response->noView(); // ajax action, no need for a view
		}
	}
	
	/**
	 * Reorder subcategories in category
	 */
	
	public function reorderDatabasesAction()
	{
		$category = $this->request->getParam('cat');
		$subcategory = $this->request->getParam('subcat');
	
		$reorder_array = $this->request->getParam('database', null, true);
	
		// re-order databases
	
		$this->knowledgebase->reorderDatabaseSequence($reorder_array);
	
		// redirect or not
	
		if ( $this->request->getParam("noredirect") == "" )
		{
			return $this->returnToCategory($category);
		}
		else
		{
			$this->response->noView(); // ajax action, no need for a view
		}
	}
	
	/**
	 * Delete database sequence
	 */
	
	public function deleteDatabaseSequenceAction()
	{
		$sequence_id = $this->request->getParam('id');
		$category_id = $this->request->getParam('category');
	
		$this->knowledgebase->deleteDatabaseSequence($sequence_id);
	
		return $this->returnToCategory($category_id);
	}	
	
	/**
	 * Edit (or add) database page
	 */
	
	public function editDatabaseAction()
	{
		$id = $this->request->getParam('id');
		
		if ( $id != null )
		{
			return $this->databaseAction();
		}
	}

	/**
	 * Add a database to the knowledgebase
	 */
	
	public function updateDatabaseAction()
	{
		$id = $this->request->getParam('id');
		
		$title = $this->request->requireParam('title', 'You must specify a title');
		$link = $this->request->requireParam('link', 'You must specify a link');
		
		$description = $this->request->getParam('description');
		$type = $this->request->getParam('type');
		$coverage = $this->request->getParam('coverage');
		
		$active = (bool) $this->request->getParam('active', false, false);
		$proxy = (bool) $this->request->getParam('proxy', false, false);
		
		$date_new_expiry = $this->request->getParam('date_new_expiry');
		$date_trial_expiry = $this->request->getParam('date_trial_expiry');
		
		$keywords = $this->request->getParam('keywords');
		$creator = $this->request->getParam('creator');
		$publisher = $this->request->getParam('publisher');
		$search_hints = $this->request->getParam('search_hints');
		$link_guide = $this->request->getParam('link_guide');
		$link_copyright = $this->request->getParam('link_copyright');
		
		$language = $this->request->getParam('language');
		$notes = $this->request->getParam('notes');
		$alternate_titles = $this->request->getParam('alternate_titles');
		
		// if an id came in, then we are editing 
		// rather than adding, so fetch the database
		
		$database = null;
		
		if ( $id != "" )
		{
			$database = $this->knowledgebase->getDatabase($id);
		}
		else
		{
			$database = new Database();
		}
		
		$database->setType($type);
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
		$database->setAlternateTitles($alternate_titles);
		$database->setKeywords($keywords);
		
		if ( $date_new_expiry != null )
		{
			$date_time = new \DateTime($date_new_expiry);
			$database->setDateNewExpiry($date_time);
		}

		if ( $date_trial_expiry != null )
		{
			$date_time = new \DateTime($date_trial_expiry);
			$database->setDateTrialExpiry($date_time);
		}			
		
		$database->setProxy($proxy);
		$database->setActive($active);
		
		$this->knowledgebase->updateDatabase($database);
		
		$this->clearDatabaseCache();
	
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'database',
			'id' => $database->getId()
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * Remove database from knowledgebase
	 */
	
	public function deleteDatabaseAction()
	{
		$id = $this->request->requireParam('id', 'You must specify a database to delete');
	
		$this->knowledgebase->removeDatabase($id);
	
		$this->clearDatabaseCache();
		
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'alphabetical'
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * Edit (or add) database page
	 */
	
	public function assignDatabasesAction()
	{
		$category_id = $this->request->requireParam('category', 'Request did not include category id');
		$subcategory_id = $this->request->requireParam('subcategory', 'Request did not include subcategory id');
		$databases = $this->request->getParam('database', null, true);
		
		$subcategory = $this->knowledgebase->getSubcategoryById($subcategory_id);
		
		foreach ( $databases as $database )
		{
			$database_object = $this->knowledgebase->getDatabase($database);
			
			$sequence = new DatabaseSequence();
			$sequence->setDatabase($database_object);
			
			$subcategory->addDatabaseSequence($sequence);
		}
		
		$this->knowledgebase->update($subcategory);
		
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'subject',
			'id' => $category_id
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * Edit (or add) database page
	 */
	
	public function assignLibrarianAction()
	{
		$category_id = $this->request->requireParam('category', 'Request did not include category id');
		$librarian_id = $this->request->requireParam('librarian', 'Request did not include subcategory id');
	
		$category = $this->knowledgebase->getCategoryById($category_id);
		$librarian = $this->knowledgebase->getLibrarian($librarian_id);
		
		$librarian_sequence = new LibrarianSequence();
		$librarian_sequence->setLibrarian($librarian);

		$category->addLibrarianSequence($librarian_sequence);
		
		$this->knowledgebase->update($category);
	
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'subject',
			'id' => $category_id
		);
	
		return $this->redirectTo($params);
	}
	
	/**
	 * Delete librarian sequence
	 */
	
	public function deleteLibrarianSequenceAction()
	{
		$sequence_id = $this->request->getParam('id');
		$category_id = $this->request->getParam('category');
	
		$this->knowledgebase->deleteLibrarianSequence($sequence_id);
	
		return $this->returnToCategory($category_id);
	}

	/**
	 * Edit (or add) database page
	 */
	
	public function editLibrarianAction()
	{
		$id = $this->request->getParam('id');
	
		if ( $id != null )
		{
			return $this->librarianAction();
		}
	}
	
	/**
	 * Add (or update) a librarian to the knowledgebase
	 */
	
	public function updateLibrarianAction()
	{
		$id = $this->request->getParam('id');
	
		$name = $this->request->requireParam('name', 'You must specify a name');
		$link = $this->request->requireParam('link', 'You must specify a link');

		$image = $this->request->getParam('image');
		$email = $this->request->getParam('email');
		$phone = $this->request->getParam('phone');
		$office = $this->request->getParam('office');
		$office_hours = $this->request->getParam('office_hours');
	
		// if an id came in, then we are editing
		// rather than adding, so fetch the database
	
		$librarian = null;
	
		if ( $id != "" )
		{
			$librarian = $this->knowledgebase->getLibrarian($id);
		}
		else
		{
			$librarian = new Librarian();
		}
	
		$librarian->setName($name);
		$librarian->setLink($link);
		$librarian->setImage($image);
		$librarian->setEmail($email);
		$librarian->setPhone($phone);
		$librarian->setOffice($office);
		$librarian->setOfficeHours($office_hours);

		$this->knowledgebase->update($librarian);

		$this->clearLibrarianCache();
		
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'librarian',
			'id' => $librarian->getId()
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * Remove librarian from knowledgebase
	 */
	
	public function deleteLibrarianAction()
	{
		$id = $this->request->requireParam('id', 'You must specify a librarian to delete');
	
		$this->knowledgebase->removeLibrarian($id);
	
		$this->clearLibrarianCache();
		
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'librarians'
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * Show or hide databases and descriptions
	 */
	
	public function showDatabaseDescriptionsAction()
	{
		$database = $this->request->getParam('database');
		
		if ( $database == 'on' )
		{
			$this->request->setSessionData('display_databases', 1);
		}
		elseif ( $database == 'off' )
		{
			$this->request->setSessionData('display_databases', 0);
		}		
		
		$description = $this->request->getParam('description');
		
		if ( $description == 'on' )
		{
			$this->request->setSessionData('display_databases', 1); // always make sure databases are on first
			$this->request->setSessionData('display_database_descriptions', 1);
		}
		elseif ( $description == 'off' )
		{
			$this->request->setSessionData('display_database_descriptions', 0);
		}
		
		return $this->redirectTo($this->request->getParam('return'));
	}
	
	/**
	 * Redirect back to subject page
	 * 
	 * @param int $category
	 */
	
	protected function returnToCategory($category)
	{
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'subject',
			'id' => $category
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Application\Controller.DatabasesController::getKnowledgebase()
	 */
	
	protected function getKnowledgebase()
	{
		$knowledgebase = parent::getKnowledgebase();
		
		// don't filter results
	
		$knowledgebase->setFilterResults(false);
		
		return $knowledgebase;
	}
	
	/**
	 * Make sure user is an admin
	 * 
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	
	protected function enforceLogin()
	{
		// make sure user is an admin
		
		$user = $this->request->getUser();
		
		if ( $user->isAdmin() != true )
		{
			return $this->redirectToLogin();
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Application\Controller.DatabasesController::setCachedData()
	 */
	
	protected function setCachedData()
	{
		// get cached information
		
		$this->response->setVariable('database_titles', $this->getDatabaseTitles());
		$this->response->setVariable('database_types', $this->getDatabaseTypes());
		$this->response->setVariable('librarian_names', $this->getLibrarianNames());
		$this->response->setVariable('database_alpha', $this->getDatabaseAlpha());
	}
	
	/**
	 * Get database titles (cache)
	 */
	
	protected function getDatabaseTitles()
	{
		$titles = $this->cache()->get($this->database_titles_id);
		
		if ( $titles == null )
		{
			$titles = $this->knowledgebase->getDatabaseTitles();
			$this->cache()->set($this->database_titles_id, $titles, time() + (12 * 60 * 60) ); // 12 hour cache
		}
		
		return $titles;
	}
	
	/**
	 * Get librarian names (cache)
	 */
	
	protected function getLibrarianNames()
	{
		$titles = $this->cache()->get($this->librarian_names_id);
	
		if ( $titles == null )
		{
			$titles = $this->knowledgebase->getLibrarianNames();
			$this->cache()->set($this->librarian_names_id, $titles, time() + (12 * 60 * 60) ); // 12 hour cache
		}
	
		return $titles;
	}
	
	/**
	 * Get librarian names (cache)
	 */
	
	protected function getDatabaseTypes()
	{
		$types = $this->cache()->get($this->database_types_id);
	
		if ( $types == null )
		{
			$types = $this->knowledgebase->getTypes();
			$this->cache()->set($this->database_types_id, $types, time() + (12 * 60 * 60) ); // 12 hour cache
		}
	
		return $types;
	}
	
	/**
	 * Get database alpha listing for editing pages
	 * this overrides the parent one so we can get alpha for all types
	 */
	
	protected function getDatabaseAlpha()
	{
		// is it cached?
		
		$databases_alpha = $this->cache()->get($this->database_alpha_edit_id );
	
		if ( $databases_alpha == null )
		{
			$databases_alpha = $this->knowledgebase->getDatabaseAlpha();
			$this->cache()->set($this->database_alpha_edit_id , $databases_alpha, time() + (12 * 60 * 60) ); // 12 hour cache
		}
		
		// create links
		
		$databases_alpha = $this->helper->injectAlphaLinks($databases_alpha);
	
		return $databases_alpha;
	}	
	
	/**
	 * Clear database title cache
	 */
	
	protected function clearDatabaseCache()
	{
		$this->cache()->set($this->database_titles_id, null, time() - 1000 );
		$this->cache()->set($this->database_types_id, null, time() - 1000 );
		$this->cache()->set($this->database_alpha_id, null, time() - 1000 );
		$this->cache()->set($this->database_alpha_edit_id, null, time() - 1000 );
	}

	/**
	 * Clear librarian name cache
	 */
	
	protected function clearLibrarianCache()
	{
		$this->cache()->set($this->librarian_names_id, null, time() - 1000 );
	}
}
