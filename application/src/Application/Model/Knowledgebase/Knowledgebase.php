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
use Xerxes\Utility\DataMap;
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
	 * @var EntityManager
	 */
	protected $entityManager;
	
	/**
	 * @var DataMap
	 */
	protected $datamap;
	
	/**
	 * @var Config
	 */
	protected $config;
	
	/**
	 * @var array
	 */
	protected $searchable_fields = array();
	
	/**
	 * @var DatabaseFilter
	 */
	
	protected $filter;
	
	/**
	 * Create new Knowledgebase object
	 * 
	 * @param User $user
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		$this->config = Config::getInstance();
		
		$this->searchable_fields = explode(",", $this->config->getConfig("DATABASE_SEARCHABLE_FIELDS", false,
			"title,description,creator,publisher,alternate_titles,keywords,coverage"));
		
		$this->filter = new DatabaseFilter();
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
		$subcategory->setOwner($this->owner);
		
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
		$subcategory = $this->getOwnedEntity('Application\Model\Knowledgebase\Subcategory', $subcategory_id);
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
		
		$this->indexDatabase($database);
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
			array('owner' => $this->owner),
			array('name' => 'asc')
		);
		
		return new Categories($results);
	}
	
	/**
	 * Get category
	 *
	 * @param string $normalized  normalized category name
	 * @param int $id             [optional] internal category id
	 * 
	 * @return Category
	 */
	
	public function getCategory($normalized, $id = null)
	{
		$where = 'c.owner = :owner'; // where query
		$value = ''; // value
		
		if ( $id != null )
		{
			$where .= ' AND c.id = :id';
			$value = $id;
		}
		else
		{
			$where .= ' AND c.normalized = :id';
			$value = $normalized;
		}
		
		$dql_query = $this->filter->getDqlQuery();
		
		// only include active databases
			
		$dql = 'SELECT c, s, j, d FROM Application\Model\Knowledgebase\Category c
			LEFT JOIN c.subcategories s
			LEFT JOIN s.database_sequences j
			LEFT JOIN j.database d WITH (' . $dql_query . ')  
			WHERE ' . $where;
			
		$query = $this->entityManager()->createQuery($dql);
		$query->setParameter(':id', $value);
		$query->setParameter(':owner', $this->owner);
		$results = $query->getResult();
		
		if ( count($results) != 1 )
		{
			throw new \Exception('Could not find category');
		}
		
		return	$results[0];
	}
	
	/**
	 * Get a Category
	 *
	 * @param int $id  internal category id
	 * @return Category
	 */
	
	public function getCategoryById($id)
	{
		return $this->getCategory(null, $id);
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
		return $this->getOwnedEntity('Application\Model\Knowledgebase\Subcategory', $id);
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
		$sql = "SELECT id, title FROM research_databases ORDER BY title";
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
		$where = $this->filter->getSqlQuery();
		
		$sql = "SELECT DISTINCT LEFT(title, 1) as letter FROM (SELECT * FROM research_databases $where) AS all_dbs ORDER BY letter";
		
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
		$sql = "SELECT DISTINCT type FROM research_databases";
		return $this->datamap()->select($sql);
	}	
	
	/**
	 * Get databases that start with a particular letter
	 *
	 * @param string $alpha letter to start with 
	 * @return array Database[]
	 */	

	public function getDatabasesStartingWith($alpha)
	{
		$dql = 'SELECT d FROM Application\Model\Knowledgebase\Database d WHERE d.title LIKE :alpha ORDER BY d.title ASC';
		
		$query = $this->entityManager()->createQuery($dql);
		$query->setParameter('alpha', "$alpha%");
		$results = $query->getResult();
		
		return $this->filter->filterResults($results);
	}
	
	/**
	 * Get databases from the knowledgebase
	 *
	 * @param array $criterion  findBy supplied criterion 
	 * @return array Database[]
	 */
	
	public function getDatabases(array $criterion = array())
	{
		$databases_repo = $this->entityManager()->getRepository('Application\Model\Knowledgebase\Database');
		$results = $databases_repo->findBy(
			$criterion,
			array('title' => 'asc')
		);
		
		return $this->filter->filterResults($results);
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
	 * Index a Database so it can be searched
	 * 
	 * @param Database $database
	 */
	
	public function indexDatabase(Database $database)
	{
		$this->datamap()->beginTransaction();
		
		// remove any existing entries
		
		$delete_sql = 'DELETE FROM research_databases_search WHERE database_id = :id';
		$delete_params = array(':id' => $database->getId());
		$this->datamap()->delete($delete_sql,$delete_params);
		
		// see which fields we want to index
		
		// get 'em all
		
		$fields = $database->toArray();
		
		foreach ( $fields as $field => $value )
		{
			// this is not the field you're looking for
			
			if ( ! in_array($field, $this->searchable_fields))
			{
				continue;
			}
			
			// keyword and the like
			
			if ( is_array($value) )
			{
				$value = implode(' ', $value);
			}
				
			$searchable_terms = array();
	
			foreach ( explode(" ", (string) $value) as $term )
			{
				// only numbers and letters please
					
				$term = preg_replace('/[^a-zA-Z0-9]/', '', $term);
				$term = trim(strtolower($term));

				// no searchable terms
				
				if ( $term == "")
				{
					continue;
				}
				
				// anything over 50 chars is likley a URL or something
				
				if ( strlen($term) > 50 )
				{
					continue;
				}
					
				array_push($searchable_terms, $term);
			}
	
			// remove duplicate terms
	
			$searchable_terms = array_unique($searchable_terms);
	
			// insert em
	
			$sql = "INSERT INTO research_databases_search ( database_id, field, term ) " .
				"VALUES ( :database_id, :field, :term )";
		
			foreach ( $searchable_terms as $unique_term )
			{
				$this->datamap()->insert( $sql, array (
					":database_id" => $database->getId(),
					":field" => $field,
					":term" => $unique_term )
				);
			}
		}
		
		$this->datamap()->commit();
	}
	
	/**
	 * Search for Databases
	 * 
	 * @param string $query
	 * @return array Database[]
	 */
	
	public function searchDatabases($query)
	{
		$arrDatabases = array();
		
		$configDatabaseTypesExclude = $this->registry->getConfig("DATABASES_TYPE_EXCLUDE_AZ", false);
		$configAlwaysTruncate = $this->registry->getConfig("DATABASES_SEARCH_ALWAYS_TRUNCATE", false, false);
	
		// lowercase the query
	
		$query = strtolower($query);
	
		$sql = "SELECT id from research_databases";
	
		$where = true;
				
		$arrTables = array(); // we'll use this to keep track of temporary tables
				
		// we'll deal with quotes later, for now
		// and gives us each term in an array
				
		$arrTerms = explode(" ", $query);
				
		// grab databases that meet our query
				
		$sql .= " WHERE id IN  ( SELECT database_id FROM ";
				
		// by looking for each term in the research_databases_search table
		// making each result a temp table
				
		for ( $x = 0; $x < count($arrTerms); $x++ )
		{
			$term = $arrTerms[$x];
	
			// to match how they are inserted
	
			$term = preg_replace('/[^a-zA-Z0-9\*]/', '', $term);
	
			// do this to reduce the results of the inner table to just one column
	
			$alias = "database_id";
	
			if ( $x > 0 )
			{
				$alias = "db";
			}
	
			// wildcard
	
			$operator = "="; // default operator is equal
	
			// user supplied a wildcard
	
			if ( strstr($term,"*") )
			{
				$term = str_replace("*","%", $term);
				$operator = "LIKE";
			}
	
			// site is configured for truncation
	
			elseif ($configAlwaysTruncate == true )
			{
				$term .= "%";
				$operator = "LIKE";
			}
	
			$arrParams[":term$x"] = $term;
	
			$sql .= " (SELECT distinct database_id AS $alias FROM research_databases_search WHERE term $operator :term$x) AS table$x ";
	
			// if there is another one, we need to add a comma between them
	
			if ( $x + 1 < count($arrTerms))
			{
				$sql .= ", ";
			}
	
			// this essentially AND's the query by requiring results from all tables
	
			if ( $x > 0 )
			{
				for ( $y = 0; $y < $x; $y++)
				{
					$column = "db";
	
					if ( $y == 0 )
					{
						$column = "database_id";
					}
	
					array_push($arrTables, "table$y.$column = table" . ($y + 1 ). ".db");
				}
			}
		}
		
		// add the AND'd tables to the SQL
						
		if ( count($arrTables) > 0 )
		{
			$sql .= " WHERE " . implode(" AND ", $arrTables);
		}
					
		$sql .= ")";
	
		$sql .= " ORDER BY UPPER(title)";
	
		// echo $sql; print_r($arrParams); // exit;
	
		$arrResults = $this->datamap()->select( $sql, $arrParams );
		
		// print_r($arrResults); exit;
	
		// transform to internal data objects
	
		if ( $arrResults != null )
		{
			foreach ( $arrResults as $arrResult )
			{
				$arrDatabases[] = $this->getDatabase($arrResult['id']);
			}
		}
		
		// limit to quoted phrases
	
		if ( strstr($query, '"') )
		{
			// unload the array, we'll only refill the ones that match the query
			
			$arrCandidates = $arrDatabases;
			$arrDatabases = array();
			
			$found = false;
			
			$phrases = explode('"', $query);
			
			foreach ( $arrCandidates as $database )
			{
				$data = $database->toArray();
				
				foreach ( $phrases as $phrase )
				{
					$phrase = trim($phrase);
					
					if ( $phrase == "" )
					{
						continue;
					}
					
					$text = " ";
		
					foreach ( $this->searchable_fields as $searchable_field )
					{
						if ( array_key_exists($searchable_field, $data) )
						{
							$value = $data[$searchable_field];
							
							if ( is_array($value) )
							{
								$value = implode(' ', $value);
							}
							
							$text .= $value . " ";
						}
					}
					
					if ( ! stristr($text,$phrase) )
					{
						$found = false;
						break;
					}
					else
					{
						$found = true;
					}
				}
		
				if ( $found == true )
				{
					$arrDatabases[$database->getId()] = $database;
				}
			}
		}
	
		return $this->filter->filterResults($arrDatabases);
	}
	
	/**
	 * Fetch an entity by id, limited to the current owner
	 * 
	 * @param string $entity  entity name
	 * @param int $id         entity id
	 * @return mixed          entity
	 */
	
	protected function getOwnedEntity($entity, $id)
	{
		$repo = $this->entityManager()->getRepository($entity);
		
		// enforce ownership
		
		$results = $repo->findBy(
			array(
				'owner' => $this->owner,
				'id' => $id
			)
		);
		
		if ( count($results) == 1 )
		{
			return $results[0];
		}
		else
		{
			throw new \Exception("Could not find $entity with id '$id'");
		}
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
	
	/**
	 * Should filter databases?
	 * 
	 * @param bool $bool
	 */
	
	public function setFilterResults($bool)
	{
		return $this->filter->setToFilter($bool);
	}
	
	/**
	 * Set owner
	 * 
	 * @param string $owner
	 */
	
	public function setOwner($owner)
	{
		$this->owner = $owner;
	}
}
