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

use Doctrine\ORM\EntityManager;
use Xerxes\Utility\Cache;
use Xerxes\Utility\DataMap;
use Xerxes\Utility\Doctrine;
use Xerxes\Utility\Registry;
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
	protected $datamap;
	
	/**
	 * Create new Knowledgebase object
	 * 
	 * @param User $user
	 */
	
	public function __construct(User $user)
	{
		parent::__construct();
		
		$this->user = $user->username;
		$this->owner = 'admin'; // @todo: logic for local users
	}
	
	/**
	 * Add a Category
	 * @param string $name  category name
	 */
	
	public function addCategory($name)
	{
		$category = new Category();
		$category->setName($name);
		
		return $this->updateCategory($category);
	}
	
	/**
	 * Add a Subcategory
	 * 
	 * @param int $category_id          internal category id
	 * @param string $subcategory_name  subcategory name
	 */

	public function addSubcategory($category_id, $subcategory_name)
	{
		// get category
		
		$category = $this->getCategoryById($category_id);
		
		// create subcategory
		
		$subcategory = new Subcategory();
		$subcategory->setName($subcategory_name);
		
		// assign subcategory to category
		
		$subcategory->setCategory($category);
		
		// update
	
		$this->update($subcategory);
	}
	
	/**
	 * Permanently remove a Subcategory
	 * 
	 * @param int $subcategory_id  internal subcategory id
	 */

	public function deleteSubcategory($subcategory_id)
	{
		$subcategory = $this->entityManager()->find('Application\Model\Knowledgebase\Subcategory', $subcategory_id);
		$this->entityManager()->remove($subcategory);
		$this->entityManager()->flush();
	}
	
	/**
	 * Remove a Database Sequence
	 * 
	 * @param int $sequence_id  internal database sequence id
	 */
	
	public function deleteDatabaseSequence($sequence_id)
	{
		$sequence = $this->entityManager()->find('Application\Model\Knowledgebase\DatabaseSequence', $sequence_id);
		$this->entityManager()->remove($sequence);
		$this->entityManager()->flush();
	}
	
	/**
	 * Remove a Librarian Sequence
	 *
	 * @param int $sequence_id  internal librarian sequence id
	 */
	
	public function deleteLibrarianSequence($sequence_id)
	{
		$sequence = $this->entityManager()->find('Application\Model\Knowledgebase\LibrarianSequence', $sequence_id);
		$this->entityManager()->remove($sequence);
		$this->entityManager()->flush();
	}	
	
	/**
	 * Update the data object
	 * 
	 * @param object $object
	 */
	
	public function update($object)
	{
		$this->entityManager()->persist($object);
		$this->entityManager()->flush();
	}
	
	/**
	 * Update a database
	 *
	 * @param Database $database
	 */
	
	public function updateDatabase(Database $database)
	{
		$database->setOwner($this->owner);
		$this->update($database);
	}
	
	/**
	 * Update a category
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
		$category_repo = $this->entityManager()->getRepository('Application\Model\Knowledgebase\Category');
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
		$category_repo = $this->entityManager()->getRepository('Application\Model\Knowledgebase\Category');
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
	 * Get a Category
	 *
	 * @param int $id  internal category id
	 * @return Category
	 */
	
	public function getCategoryById($id)
	{
		return $this->entityManager()->find('Application\Model\Knowledgebase\Category', $id);
	}

	/**
	 * Delete Category
	 *
	 * @param int $id  internal category id
	 */
	
	public function deleteCategory($id)
	{
		$category = $this->getCategoryById($id);

		$this->entityManager()->remove($category);
		$this->entityManager()->flush();		
	}	
	
	/**
	 * Get Subcategory
	 *
	 * @param int $id  internal category id
	 * @return Subcategory
	 */
	
	public function getSubcategoryById($id)
	{
		return $this->entityManager()->find('Application\Model\Knowledgebase\Subcategory', $id);
	}
	
	/**
	 * Get database by ID
	 * 
	 * @param int $id
	 * @return Database
	 */
	
	public function getDatabase($id)
	{
		return $this->entityManager()->find('Application\Model\Knowledgebase\Database', $id);
	}
	
	/**
	 * Get databases from the knowledgebase
	 *
	 * @param string $source_id
	 * 
	 * @return Database|null
	 */
	
	public function getDatabaseBySourceId($source_id)
	{
		$databases_repo = $this->entityManager()->getRepository('Application\Model\Knowledgebase\Database');
		$results = $databases_repo->findBy(
			array('source_id' => $source_id)
		);
		
		if ( count($results) == 1)
		{
			return $results[0];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get librarians(s) by ID
	 *
	 * @param int $id
	 * @return Librarian
	 */
	
	public function getLibrarian($id)
	{
		return $this->entityManager()->find('Application\Model\Knowledgebase\Librarian', $id);
	}
	
	/**
	 * Get Librarian from the knowledgebase
	 *
	 * @param string $source_id
	 *
	 * @return Librarian|null
	 */
	
	public function getLibrarianBySourceId($source_id)
	{
		$databases_repo = $this->entityManager()->getRepository('Application\Model\Knowledgebase\Librarian');
		$results = $databases_repo->findBy(
			array('source_id' => $source_id)
		);
	
		if ( count($results) == 1)
		{
			return $results[0];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Remove librarian
	 *
	 * @param int $id  librarian id
	 */
	
	public function removeLibrarian($id)
	{
		$librarian = $this->getLibrarian($id);
		$this->entityManager()->remove($librarian);
		$this->entityManager()->flush();
	}	
	
	/**
	 * Remove database
	 *
	 * @param int $id  database id
	 */
	
	public function removeDatabase($id)
	{
		$database = $this->getDatabase($id);
		$this->entityManager()->remove($database);
		$this->entityManager()->flush();
	}
	
	/**
	 * Just database titles
	 * 
	 * doesn't use Doctrine, for speed
	 * 
	 * @return array
	 */
	
	public function getDatabaseTitles()
	{
		$sql = "SELECT id, title FROM research_databases WHERE owner = 'admin' ORDER BY title";
		return $this->datamap()->select($sql);
	}
	
	/**
	 * Get the letters starting each database title 
	 *
	 * doesn't use Doctrine, for speed
	 *
	 * @return array
	 */
	
	public function getDatabaseAlpha()
	{
		$sql = "SELECT DISTINCT LEFT(title, 1) as letter FROM research_databases ORDER BY letter";
		return $this->datamap()->select($sql);
	}

	/**
	 * Just librarian names
	 *
	 * doesn't use Doctrine, for speed
	 *
	 * @return array
	 */
	
	public function getLibrarianNames()
	{
		$sql = "SELECT id, name FROM librarians ORDER BY name";
		return $this->datamap()->select($sql);
	}
	
	/**
	 * Just database types
	 *
	 * doesn't use Doctrine, for speed
	 *
	 * @return array
	 */
	
	public function getTypes()
	{
		$sql = "SELECT DISTINCT type FROM research_databases WHERE owner = 'admin'";
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
		$query = $this->entityManager()->createQuery('SELECT d FROM Application\Model\Knowledgebase\Database d WHERE d.title LIKE :alpha AND d.owner = :owner ORDER BY d.title ASC');
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
		$databases_repo = $this->entityManager()->getRepository('Application\Model\Knowledgebase\Database');
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
		$databases_repo = $this->entityManager()->getRepository('Application\Model\Knowledgebase\Librarian');
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

	/**
	 * Reorder subcategories
	 *
	 * @param array $reorder_array
	 */
	
	public function reorderDatabaseSequence(array $reorder_array)
	{
		if ( count($reorder_array) > 0 )
		{
			$datamap = $this->datamap();
				
			$datamap->beginTransaction();
				
			$sql = "UPDATE databases_subcategories SET sequence = :sequence WHERE id = :id";
				
			foreach ( $reorder_array as $order => $sequence_id )
			{
				$datamap->update( $sql, array(":sequence" => $order, ":id" => $sequence_id ) );
			}
	
			return $datamap->commit();
		}
	
		return null;
	}	
	
	/**
	 * @return DataMap
	 */
	
	public function datamap()
	{
		if ( ! $this->datamap instanceof DataMap )
		{
			$this->datamap = new DataMap();
			$this->datamap->setFetchStyle(\PDO::FETCH_ASSOC);
		}
		
		return $this->datamap;
	}
	
	/**
	 * @return EntityManager
	 */
	
	public function entityManager($create_new = false)
	{
		if ( ! $this->entityManager instanceof EntityManager || $create_new )
		{
			$this->entityManager = parent::getEntityManager(array(__DIR__));
		}
		
		return $this->entityManager;
	}
}
