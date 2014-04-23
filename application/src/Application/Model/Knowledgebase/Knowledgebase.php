<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Knowledgebase;

use Xerxes\Utility\DataMap;

use Doctrine\ORM\EntityManager;
use Xerxes\Utility\Cache;
use Xerxes\Utility\Doctrine;
use Xerxes\Utility\User;

/**
 * Knowledgebase
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Knowledgebase extends Doctrine
{
	/**
	 * Owner name
	 * @var string
	 */
	private $owner;
	
	/**
	 * @var User
	 */
	private $user;
	
	/**
	 * @var EntityManager
	 */
	protected $entityManager;
	
	/**
	 * @var DataMap
	 */
	private $datamap;
	
	/**
	 * Create new Knowledgebase object
	 * 
	 * @param User $user
	 */
	
	public function __construct(User $user)
	{
		parent::__construct();
		
		$this->user = $user->username;
		$this->entityManager = $this->getEntityManager(array(__DIR__));
		
		$this->owner = 'admin'; // @todo: logic for local users
	}
	
	public function addCategory($name)
	{
		$category = new Category();
		$category->setName($name);
		
		return $this->updateCategory($category);
	}

	public function addSubcategory($category_id, $subcategory_name)
	{
		// get category
		
		$category = $this->getCategory($category_id);
		
		// create subcategory
		
		$subcategory = new Subcategory();
		$subcategory->setName($subcategory_name);
		
		// assign subcategory to category
		
		$subcategory->setCategory($category);
		
		// update
	
		$this->update($subcategory);
	}

	public function deleteSubcategory($subcategory_id)
	{
		$subcategory = $this->entityManager->find('Application\Model\Knowledgebase\Subcategory', $subcategory_id);
		$this->entityManager->remove($subcategory);
		$this->entityManager->flush();
	}
	
	/**
	 * Update the data object
	 * 
	 * @param object $object
	 */
	
	public function update($object)
	{
		$this->entityManager->persist($object);
		$this->entityManager->flush();
	}
	
	/**
	 * Add a database
	 *
	 * @param Database $database
	 */
	
	public function updateDatabase(Database $database)
	{
		$database->setOwner($this->owner);
		$this->update($database);
	}
	
	/**
	 * Add a category
	 * 
	 * @param Category $category
	 */
	
	public function updateCategory(Category $category)
	{
		$category->setOwner($this->owner);
		$this->update($category);
	}
	
	/**
	 * Get all categories
	 * 
	 * @return Categories
	 */
	
	public function getCategories()
	{
		$category_repo = $this->entityManager->getRepository('Application\Model\Knowledgebase\Category');
		$results = $category_repo->findBy(
			array('owner' => 'admin'),
			array('name' => 'asc')
		);
		
		return new Categories($results);
	}
	
	/**
	 * Get category
	 *
	 * @param string $normalized  normalized category name
	 * @return Category
	 */
	
	public function getCategory($normalized)
	{
		$category_repo = $this->entityManager->getRepository('Application\Model\Knowledgebase\Category');
		$results = $category_repo->findBy(
			array(
				'normalized' => $normalized,
				'owner' => $this->owner
			)
		);
		
		if ( count($results) == 1 )
		{
			return $results[0];
		}
		else
		{
			throw new \Exception('Could not find category');
		}		
		
	}
	
	/**
	 * Get category
	 *
	 * @param int $id  internal category id
	 * @return Category
	 */
	
	public function getCategoryById($id)
	{
		return $this->entityManager->find('Application\Model\Knowledgebase\Category', $id);
	}

	/**
	 * Delete category
	 *
	 * @param int $id  internal category id
	 */
	
	public function deleteCategory($id)
	{
		$category = $this->getCategoryById($id);

		$this->entityManager->remove($category);
		$this->entityManager->flush();		
	}	
	
	/**
	 * Get subcategory
	 *
	 * @param int $id  internal category id
	 * @return Subcategory
	 */
	
	public function getSubcategoryById($id)
	{
		return $this->entityManager->find('Application\Model\Knowledgebase\Subcategory', $id);
	}
	
	/**
	 * Get database(s) by ID
	 * 
	 * you supply an array, you get back an array
	 *
	 * @param string|array $id
	 * @return Database|Database[]
	 */
	
	public function getDatabase($id)
	{
		return $this->entityManager->find('Application\Model\Knowledgebase\Database', $id);
	}

	/**
	 * Get librarians(s) by ID
	 *
	 * you supply an array, you get back an array
	 *
	 * @param string|array $id
	 * @return Librarian|Librarian[]
	 */
	
	public function getLibrarian($id)
	{
		return $this->entityManager->find('Application\Model\Knowledgebase\Librarian', $id);
	}
	
	/**
	 * Remove librarian
	 *
	 * @param string $id  librarian id
	 * @return bool       true on success, false otherwise
	 */
	
	public function removeLibrarian($id)
	{
		$librarian = $this->getLibrarian($id);
		$this->entityManager->remove($librarian);
		$this->entityManager->flush();
	}	
	
	/**
	 * Remove database
	 *
	 * @param string $id  database id
	 * @return bool       true on success, false otherwise
	 */
	
	public function removeDatabase($id)
	{
		$database = $this->getDatabase($id);
		$this->entityManager->remove($database);
		$this->entityManager->flush();
	}
	
	/**
	 * Just database titles
	 * 
	 * Doesn't use Doctrine, for speed
	 * 
	 * @return array
	 */
	
	public function getDatabaseTitles()
	{
		$sql = "select id, title from research_databases where owner = 'admin' order by title";
		return $this->datamap()->select($sql);
	}
	
	/**
	 * Get databases that start with a particular letter
	 *
	 * @param string $alpha letter to start with 
	 * @return array        of Database objects
	 */	

	public function getDatabasesStartingWith($alpha)
	{
		$query = $this->entityManager->createQuery('SELECT d FROM Application\Model\Knowledgebase\Database d WHERE d.title LIKE :alpha AND d.owner = :owner ORDER BY d.title ASC');
		$query->setParameter('alpha', "$alpha%");
		$query->setParameter('owner', $this->owner);
		return $query->getResult();
	}
	
	/**
	 * Get databases from the knowledgebase
	 *
	 * @param string $query [optional] query to search for dbs. 
	 * @return array        of Database objects
	 */
	
	public function getDatabases($query = null)
	{
		$databases_repo = $this->entityManager->getRepository('Application\Model\Knowledgebase\Database');
		$results = $databases_repo->findBy(
			array('owner' => $this->owner),
			array('title' => 'asc')
		);
		
		return $results;
	}

	/**
	 * Get databases from the knowledgebase
	 *
	 * @param string $query [optional] query to search for dbs.
	 * @return array        of Database objects
	 */
	
	public function getLibrarians()
	{
		$databases_repo = $this->entityManager->getRepository('Application\Model\Knowledgebase\Librarian');
		$results = $databases_repo->findBy(
			array(),
			array('name' => 'asc')
		);
	
		return $results;
	}	
	
	/**
	 * Reorder subcategories
	 * 
	 * @param array $reorder_array
	 */
	
	public function reorderSubcategories(array $reorder_array)
	{
		if ( count($reorder_array) > 0 )
		{
			$datamap = $this->datamap();
			
			$datamap->beginTransaction();
			
			$sql = "UPDATE subcategories SET sequence = :sequence WHERE id = :id";
			
			foreach ( $reorder_array as $order => $subcategory_id )
			{
				$datamap->update( $sql, array(":sequence" => $order, ":id" => $subcategory_id ) );
			}
				
			return $datamap->commit();
		}
		
		return null;
	}
	
	public function migrate()
	{
		// databases
		
		$sql = 'SELECT * FROM xerxes_databases';
		$results = $this->datamap()->select($sql);
		
		foreach ( $results as $result )
		{
			$xml = simplexml_load_string($result['data']);
			
			$title = (string) $xml->title_display;
			$metalib_id = (string) $xml->metalib_id;
			
			$active = (string) $xml->active;
			$active = (int) $xml->proxy;
			$subscription = (int) $xml->proxy;
			
			$creator = (string) $xml->creator;
			$publisher = (string) $xml->publisher;
			$description = (string) $xml->description;
			$link = (string) $xml->link_native_home;
			$time_span = (string) $xml->time_span;
			$link_guide = (string) $xml->link_guide;
			
			$type = (string) $xml->type; // @todo: assign types
			
			$database = new Database();
			
			$database->setOwner($this->owner);
			$database->setTitle($title);
			$database->setSourceId($metalib_id);
			$database->setCreator($creator);
			$database->setPublisher($publisher);
			$database->setDescription($description);
			$database->setLink($link);
			$database->setCoverage($time_span);
			$database->setLinkGuide($link_guide);
			
			foreach ( $xml->title_alternate as $title_alternate )
			{
				$database->addAlternateTitle((string) $title_alternate);
			}
			
			$notes = "";
			
			foreach ( $xml->note_cataloger as $note )
			{
				$notes = " " . (string) $note;
			}
			
			foreach ( $xml->note as $note )
			{
				$notes = " " . (string) $note;
			}
			
			$notes = trim($notes);
			
			$database->setNotes($notes);
			
			foreach ( $xml->keyword as $keyword )
			{
				$keyword_array = explode(',', (string) $keyword);
				
				foreach ( $keyword_array as $keyword_term )
				{
					$keyword_term = trim($keyword_term);
					
					$database->addKeyword($keyword_term);
				}
			}
			
			$this->entityManager->persist($database);
		}
		
		$this->entityManager->flush();
		
		// subjects
		
		$url = 'http://library.calstate.edu/sonoma/databases/?format=xerxes';
		$xml = simplexml_load_file($url);
		
		foreach ( $xml->categories->category as $category_xml )
		{
			$name = (string) $category_xml->name;
			$path = (string) $category_xml->url;
			
			$category = new Category();
			$category->setName($name);
			$category->setOwner($this->owner);
			
			$url = "http://library.calstate.edu$path?format=xerxes";
			
			$subject_xml = simplexml_load_file($url);
			
			foreach ( $subject_xml->category->subcategory as $subcategory_xml )
			{
				$name = (string) $subcategory_xml['name']; 
				$sequence = (string) $subcategory_xml['position'];
				
				$subcategory = new Subcategory();
				$subcategory->setName($name);
				$subcategory->setSequence($sequence);
				
				$this->entityManager->persist($subcategory);
				
				$category->addSubcategory($subcategory);
			}
			
			$this->entityManager->persist($category);
		}
		
		$this->entityManager->flush();
		
		
	}
	
	/**
	 * @return DataMap
	 */
	
	protected function datamap()
	{
		if ( ! $this->datamap instanceof DataMap )
		{
			$this->datamap = new DataMap();
		}
		
		return $this->datamap;
	}
}
