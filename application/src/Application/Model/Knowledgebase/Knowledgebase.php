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
	
	/**
	 * Add a database
	 *
	 * @param Database $database
	 */
	
	public function addDatabase(Database $database)
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
	
	public function addCategory(Category $category)
	{
		if ( $category->getNormalized() == '')
		{
			if ( $category->getName() != '')
			{
				$category->setNormalizedFromName();
			}
		}
		
		$this->entityManager->persist($category);
		$this->entityManager->flush();
	}
	
	/**
	 * Get all categories
	 * 
	 * @return array
	 */
	
	public function getCategories()
	{
	}
	
	/**
	 * Get category
	 *
	 * @param string $normalized normalized category name
	 * @return Category
	 */
	
	public function getCategory($normalized)
	{
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
