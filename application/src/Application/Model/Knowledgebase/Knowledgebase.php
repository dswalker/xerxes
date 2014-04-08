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
		
		/*
		$datamap = $this->datamap();
		$sql = 'DELETE FROM subcategories WHERE id = :id';
		return $datamap->delete($sql, array(':id' => $subcategory_id));
		*/
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
	 * @return Category[]
	 */
	
	public function getCategories()
	{
		$category_repo = $this->entityManager->getRepository('Application\Model\Knowledgebase\Category');
		$results = $category_repo->findBy(
			array('owner' => 'admin'),
			array('name' => 'asc')
		);
		
		return $results;
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
	 * Get the starting letters for database titles
	 *
	 * @return array of letters
	 */	
	
	public function getDatabaseAlpha()
	{
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
