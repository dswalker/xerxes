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
	
		$this->entityManager->persist($subcategory);
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
		$this->entityManager->persist($database);
		$this->entityManager->flush();
	}
	
	/**
	 * Add a category
	 * 
	 * @param Category $category
	 */
	
	public function updateCategory(Category $category)
	{
		$category->setOwner($this->owner);
		
		$this->entityManager->persist($category);
		$this->entityManager->flush();
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
	 * @param string $normalized normalized category name
	 * @return Category
	 */
	
	public function getCategory($normalized)
	{
		$category_repo = $this->entityManager->getRepository('Application\Model\Knowledgebase\Category');
		$results = $category_repo->findBy(
			array(
				'owner' => 'admin',
				'normalized' => $normalized
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
	 * Get database(s) by ID
	 * 
	 * you supply an array, you get back an array
	 *
	 * @param string|array $id
	 * @return Database|Database[]
	 */
	
	public function getDatabase($id)
	{
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
	}
	
	/**
	 * Get databases from the knowledgebase
	 *
	 * @param string $query [optional] query to search for dbs. 
	 * @return array        of Database objects
	 */
	
	public function getDatabases($query = null)
	{
	}
}
