<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Databases;

use Xerxes\Utility\Cache;
use Xerxes\Utility\User;

/**
 * Knowledgebase
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Knowledgebase
{
	private $owner;
	
	public function __construct(User $user)
	{
		$this->owner = $user->username;
	}
	
	/**
	 * Add a database
	 *
	 * @param Database $database
	 */
	
	public function addDatabase(Database $database)
	{
	}
	
	/**
	 * Add a category
	 * 
	 * @param Category $category
	 */
	
	public function addCategory(Category $category)
	{
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
	 * Get catergory
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
