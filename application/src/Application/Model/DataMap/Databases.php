<?php

namespace Application\Model\DataMap;

use Application\Model\Databases\Category,
	Application\Model\Databases\Database,
	Application\Model\Databases\Subcategory,
	Xerxes\Utility\DataMap,
	Xerxes\Utility\Parser;

/**
 * Database access mapper for Metalib KB
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @package Xerxes
 */

class Databases extends DataMap
{
	protected $searchable_fields = array();
	
	/**
	 * Create Databases object
	 *
	 * @param string $connection	[optional] database connection info
	 * @param string $username		[optional] username to connect with
	 * @param string $password		[optional] password to connect with
	 */
	
	public function __construct($connection = null, $username = null, $password = null)
	{
		parent::__construct($connection, $username, $password);
		
		$fields = $this->registry->getConfig("DATABASE_SEARCHABLE_FIELDS", false, "title_display,title_full,description,keyword,alternate_titles");
		$this->searchable_fields = explode(",", $fields);
	}
	
	/**
	 * Add a resource
	 *
	 * @param Resource $resource
	 */
	
	public function addDatabase(Resource $resource)
	{
		// @todo add the main database entries
		
		
		
		
		
		
		// now also extract searchable fields so we can populate the search table
		
		// get fields from config
		
		foreach ( $this->searchable_fields as $search_field )
		{
			$search_field = trim($search_field);
			
			foreach ( $xml->$search_field as $field )
			{
				$searchable_terms = array();
				
				foreach ( explode(" ", (string) $field) as $term )
				{
					// only numbers and letters please
					
					$term = preg_replace('/[^a-zA-Z0-9]/', '', $term);
					$term = strtolower($term);
					
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
				
				$sql = "INSERT INTO xerxes_databases_search ( resource_id, field, term ) " . 
					"VALUES ( :resource_id, :field, :term )";
				
				foreach ( $searchable_terms as $unique_term )
				{
					$this->insert( $sql, array (
						":resource_id" => $objDatabase->resource_id, 
						":field" => $search_field, 
						":term" => $unique_term ) 
					);
				}			
			}
		}
	}
	
	/**
	 * Add a category
	 * 
	 * @param Category $category
	 */
	
	public function addCategory(Category $category)
	{
		$this->doSimpleInsert( "xerxes_categories", $category );
	}
	
	/**
	 * Get all categories
	 * 
	 * @return array
	 */
	
	public function getCategories()
	{
		$categories = array();
		
		$sql = "SELECT * from xerxes_categories ORDER BY UPPER(name) ASC";
		
		$results = $this->select($sql);
		
		foreach ( $results as $result )
		{
			$category = new Category();
			$category->load( $result );
			
			array_push( $categories, $category );
		}
		
		return $categories;
	}
	
	/**
	 * Get catergory
	 *
	 * @param string $normalized		normalized category name
	 * @return Category
	 */
	
	public function getCategory($normalized)
	{
		$sql = "SELECT * FROM xerxes_categories WHERE normalized = :normalized
			LEFT OUTER JOIN xerxes_subcategories ON xerxes_categories.category_id = xerxes_subcategories.category_id
			LEFT OUTER JOIN xerxes_subcategory_resources ON xerxes_subcategory_resources.subcategory_id = xerxes_subcategories.subcategory_id
			LEFT OUTER JOIN xerxes_resources ON xerxes_subcategory_resources.resouce_id = xerxes_resources.resource_id";	
		
		$results = $this->select( $sql, array(':normalized' => $normalized) );
		
		if ( $results != null )
		{
			$category = new Category();
			$category->id = $results[0]["category_id"];
			$category->name = $results[0]["category"];
			$category->normalized = $normalized;
			
			// we create these initially as the starting point in our comparison
			
			$sub_category = $this->createSubcategoryObject($results[0]);
			$database = $this->createResourceObject($results[0]);
			
			foreach ( $results as $result )
			{
				// if the current row's subcategory id does not match the previous one, 
				// then push the previous subcategory obj onto category obj and make a new subcategory obj
				// otherwise these are values from the outer join

				if ( $result["subcat_id"] != $sub_category->id )
				{
					// get the last db in this subcategory first
					
					if ( $database->resource_id != null )
					{
						array_push( $sub_category->databases, $database );
					}
					
					$database = new Database(); // dummy for now, we'll create it for realz below
					
					// create new subcategory
					
					$sub_category = $this->createSubcategoryObject($result);
				}
				
				// if the current row's database id does not match the previous one, 
				// then push the previous database obj onto subcategory obj and make a new database obj
				// otherwise these are values from the outer join

				if ( $result["resource_id"] != $database->resource_id )
				{
					// existing one that isn't empty? save it.
					
					if ( $database->resource_id != null )
					{
						array_push( $sub_category->databases, $database );
					}
					
					$database = $this->createResourceObject($result);
				}
			}
			
			// last one?
			
			if ( $database->resource_id != null )
			{
				array_push( $sub_category->databases, $database );
			}
			
			return $category;
		} 
		else
		{
			return null;
		}
	}
	
	/**
	 * Create Subcategory object from sql result
	 *
	 * @param array $result
	 * @return Subcategory
	 */
	
	private function createSubcategoryObject(array $result)
	{
		$sub_category = new Subcategory();
		$sub_category->id = $result["subcat_id"];
		$sub_category->name = $result["subcategory"];
		$sub_category->sequence = $result["subcat_seq"];
	
		return $sub_category;
	}
	
	/**
	 * Create Database object from sql result
	 *
	 * @param array $result
	 * @return Database
	 */	
	
	private function createResourceObject(array $result)
	{
		
	}
	
	/**
	 * Get database(s) by ID
	 * 
	 * you supply an array, you get back an array
	 *
	 * @param string|array $id
	 * @return Database|array of Database's
	 */
	
	public function getDatabase($id)
	{
		$params = array();
		
		$sql = "SELECT * from xerxes_databases";
		
		// single database
		
		if ( ! is_array( $id ) )
		{
			$sql .= " WHERE xerxes_databases.resource_id = :id ";
			$params[":id"] = $id;
		} 		
		else // databases specified by an array of ids
		{
			$sql .= " WHERE ";
			
			for ( $x = 0 ; $x < count( $id ) ; $x ++ )
			{
				if ( $x > 0 )
				{
					$sql .= " OR ";
				}
				
				$sql .= "xerxes_databases.resource_id = :id$x ";
				$params[":id$x"] = $id[$x];
			}
		}
		
		$results = $this->select($sql, $params);
		
		if ( count($results) == 0 )
		{
			throw new \Exception("Could not find a database with id '$id'");
		}
		
		if ( ! is_array( $id ) )
		{
			$final = array();
			
			foreach ( $results as $result )
			{
				array_push($final, $this->createResourceObject($result));
			}
		}
		else
		{
			return $this->createResourceObject($results[0]);
		}
	}
	
	/**
	 * Get the starting letters for database titles
	 *
	 * @return array of letters
	 */	
	
	public function getDatabaseAlpha()
	{
		$letters = array();
		
		$sql = "SELECT DISTINCT alpha FROM " .
			"(SELECT SUBSTRING(UPPER(title_display),1,1) AS alpha FROM xerxes_databases) AS TEMP " .
			"ORDER BY alpha";
			
		$results = $this->select($sql);
		
		foreach ( $results as $result )
		{
			array_push($letters, $result['alpha']);	
		}
		
		return $letters;
	}

	/**
	 * Get databases that start with a particular letter
	 *
	 * @param string $alpha 	letter to start with 
	 * @return array 			of Database objects
	 */	

	public function getDatabasesStartingWith($alpha)
	{
		$sql = "SELECT * from xerxes_databases WHERE UPPER(title_display) LIKE :alpha";
		
		$results = $this->select($sql, array(':alpha' => "$alpha%"));
		
	}
	
	/**
	 * Get databases from the knowledgebase
	 *
	 * @param string $query		[optional] query to search for dbs. 
	 * @return array 			of Database objects
	 */
	
	public function getDatabases($query = null)
	{
		$configAlwaysTruncate = $this->registry->getConfig("DATABASES_SEARCH_ALWAYS_TRUNCATE", false, false);		
		
		$sql = "SELECT * from xerxes_databases";
		
		// user-supplied query
		
		if ( $query != null )
		{
			$arrTables = array(); // we'll use this to keep track of temporary tables
			
			// we'll deal with quotes later, for now 
			// and gives us each term in an array
			
			$arrTerms = explode(" ", $query);
			
			// grab databases that meet our query
			
			$sql .= " WHERE resource_id IN  (
				SELECT resource_id FROM ";
			
			// by looking for each term in the xerxes_databases_search table 
			// making each result a temp table
			
			for ( $x = 0; $x < count($arrTerms); $x++ )
			{
				$term = $arrTerms[$x];
				
				// to match how they are inserted
				
				$term = preg_replace('/[^a-zA-Z0-9\*]/', '', $term);
				
				// do this to reduce the results of the inner table to just one column
				
				$alias = "resource_id";
				
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
				
				$params[":term$x"] = $term;
				
				$sql .= " (SELECT distinct resource_id AS $alias FROM xerxes_databases_search WHERE term $operator :term$x) AS table$x ";
				
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
							$column = "resource_id";
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
		}
		
		$sql .= " ORDER BY UPPER(title_display)";
		
		// echo $sql; print_r($params); // exit;
		
		$arrResults = $this->select( $sql, $params );
		
		// transform to internal data objects
		
		if ( $arrResults != null )
		{
			foreach ( $arrResults as $arrResult )
			{
				array_push($arrDatabases, $this->createResourceObject($arrResult));
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
			
			foreach ( $arrCandidates as $objDatabase )
			{
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
						$text .= $objDatabase->$searchable_field . " ";
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
					array_push($arrDatabases, $objDatabase);
				}
			}
		}
		
		return $arrDatabases;
	}
}
