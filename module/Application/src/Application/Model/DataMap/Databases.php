<?php

namespace Application\Model\DataMap;

use Application\Model\KnowledgeBase\Category,
	Application\Model\KnowledgeBase\Database,
	Application\Model\KnowledgeBase\Subcategory,
	Application\Model\KnowledgeBase\Type,
	Xerxes\Utility\DataMap,
	Xerxes\Utility\Parser;

/**
 * Database access mapper for Metalib KB
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Databases extends DataMap
{
	protected $primary_language = "eng"; // primary language
	protected $searchable_fields; // fields that can be searched on for databases
	
	const METALIB_MODE = 'metalib';
	const USER_CREATE_MODE = 'user_created';
	
	/**
	 * Constructor
	 * 
	 * @param string $connection	[optional] database connection info
	 * @param string $username		[optional] username to connect with
	 * @param string $password		[optional] password to connect with
	 */
	
	public function __construct($connection = null, $username = null, $password = null)
	{
		parent::__construct($connection, $username, $password);
		
		$languages = $this->registry->getConfig("languages");
		
		if ( $languages != "")
		{
			$this->primary_language = (string) $languages->language["code"];
		}
		
		// searchable fields
		
		$this->searchable_fields = explode(",", $this->registry->getConfig("DATABASE_SEARCHABLE_FIELDS", false, 
			"title_display,title_full,description,keyword,alt_title"));
	}

	/**
	 * Deletes data from the knowledgebase tables; should only be done
	 * while using transactions
	 */
	
	public function clearKB()
	{
		// delete main kb tables, others will cascade

		$this->delete( "DELETE FROM xerxes_databases" );
		$this->delete( "DELETE FROM xerxes_subcategories" );
		$this->delete( "DELETE FROM xerxes_categories" );
		$this->delete( "DELETE FROM xerxes_types" );
	}
	
	/**
	 * Remove orphaned my saved database associations
	 */
	
	public function synchUserDatabases()
	{
		// user saved databases sit loose to the databases table, so we use this
		// to manually enforce an 'ON CASCADE DELETE' to ensure we don't abandon
		// databases in the my saved databases tables
		
		$this->delete( "DELETE FROM xerxes_user_subcategory_databases WHERE " .
			" database_id NOT IN ( SELECT metalib_id FROM xerxes_databases )");
	}
	
	/**
	 * Add a database to the local knowledgebase
	 *
	 * @param Database $objDatabase
	 */
	
	public function addDatabase(Database $objDatabase)
	{
		// load our data into xml object
		
		$xml = simplexml_load_string($objDatabase->data);
		
		// these fields have boolen values in metalib
		
		$boolean_fields = array("proxy","searchable","guest_access",
			"subscription","sfx_suppress","new_resource_expiry");

		// normalize boolean values
		
		foreach ( $xml->children() as $child )
		{
			$name = (string) $child->getName();
			$value = (string) $child;
			
			if ( in_array( $name, $boolean_fields) )
			{
				$xml->$name = $this->convertMetalibBool($value);
			}
		}
		
		// remove empty nodes
		
		$dom = Parser::convertToDOMDocument($xml->asXML());
		
		$xmlPath = new \DOMXPath($dom);
		$xmlNullNodes = $xmlPath->query('//*[not(node())]');
		
		foreach($xmlNullNodes as $node)
		{
			$node->parentNode->removeChild($node);
		}
		
		$objDatabase->data = $dom->saveXML();
		
		// add the main database entries
		
		$this->doSimpleInsert( "xerxes_databases", $objDatabase );
		
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
				
				$strSQL = "INSERT INTO xerxes_databases_search ( database_id, field, term ) " . 
					"VALUES ( :metalib_id, :field, :term )";
				
				foreach ( $searchable_terms as $unique_term )
				{
					$this->insert( $strSQL, array (
						":metalib_id" => $objDatabase->metalib_id, 
						":field" => $search_field, 
						":term" => $unique_term ) 
					);
				}			
			}
		}
	}
	
	/**
	 * Add a type to the local knowledgebase
	 *
	 * @param Type $objType
	 * @return int status
	 */
	
	public function addType(Type $objType)
	{
		return $this->doSimpleInsert( "xerxes_types", $objType );
	}
	
	/**
	 * Add a category to the local knowledgebase; should also include
	 * Subcategory subcategories ( as array in subcategory property) 
	 * and databases Database as array in subcategory property.
	 *
	 * @param Category $objCategory
	 */
	
	public function addCategory(Category $objCategory)
	{
		$this->doSimpleInsert( "xerxes_categories", $objCategory );
		
		$s = 1;
		
		foreach ( $objCategory->subcategories as $objSubcategory )
		{
			$objSubcategory->category_id = $objCategory->id;
			$objSubcategory->sequence = $s;
			
			$this->doSimpleInsert( "xerxes_subcategories", $objSubcategory );
			
			$d = 1;
			
			foreach ( $objSubcategory->databases as $objDatabase )
			{
				$strSQL = "INSERT INTO xerxes_subcategory_databases " .
					"( database_id, subcategory_id, sequence ) " . 
					"VALUES ( :database_id, :subcategory_id, :sequence )";
				
				$arrValues = array (
					":database_id" => $objDatabase->metalib_id, 
					":subcategory_id" => $objSubcategory->metalib_id, 
					":sequence" => $d 
				);
				
				$this->insert( $strSQL, $arrValues );
				$d++;
			}
			
			$s++;
		}
	}
	
	/**
	 * Add a user-created category; Does not add subcategories or databases,
	 * just the category. Category should not have an 'id' property, will be
	 * supplied by auto-incremented db column.
	 *
	 * @param Category $objCategory
	 */
	
	public function addUserCreatedCategory(Category $objCategory)
	{
		// We don't use metalib-id or old for user-created categories
		unset( $objCategory->metalib_id );
		unset( $objCategory->old );
		
		$new_pk = $this->doSimpleInsert( "xerxes_user_categories", $objCategory, true );
		
		$objCategory->id = $new_pk;
		return $objCategory;
	}
	
	/**
	 * Does not update subcategory assignments, only the actual category
	 * values, at present. Right now, just name and normalized!
	 * 
	 * @param Category $objCategory 	a category object
	 */

	public function updateUserCategoryProperties(Category $objCategory)
	{
		$objCategory->normalized = Category::normalize( $objCategory->name );
		
		$sql = "UPDATE xerxes_user_categories " .
			"SET name = :name, normalized = :normalized, published = :published " .
			"WHERE id = " . $objCategory->id;
		
		return $this->update( $sql, 
			array (
				":name" => $objCategory->name, 
				":normalized" => $objCategory->normalized, 
				":published" => $objCategory->published 
				) 
			);
	}
	
	/**
	 * Add a user-created subcategory; Does not add databases joins,
	 * just the subcategory. Category should not have an 'id' property, will be
	 * supplied by auto-incremented db column.
	 *
	 * @param Subcategory $objSubcat
	 * @return Subcategory subcategory
	 */
	
	public function addUserCreatedSubcategory(Subcategory $objSubcat)
	{
		//We don't use metalib-id, we use id instead, sorry. 
		unset( $objSubcat->metalib_id );
		
		$new_pk = $this->doSimpleInsert( "xerxes_user_subcategories", $objSubcat, true );
		$objSubcat->id = $new_pk;
		return $objSubcat;
	}
	
	/**
	 * Delete a user subcategory
	 *
	 * @param Subcategory $objSubcat	subcategort oject
	 * @return int 									delete status
	 */
	
	public function deleteUserCreatedSubcategory(Subcategory $objSubcat)
	{
		$sql = "DELETE FROM xerxes_user_subcategories WHERE ID = :subcategory_id";
		return $this->delete( $sql, array (":subcategory_id" => $objSubcat->id ) );
	}
	
	/**
	 * Delete a user created category
	 *
	 * @param Category $objCat		category object
	 * @return int 								detelete status
	 */
	
	public function deleteUserCreatedCategory(Category $objCat)
	{
		$sql = "DELETE FROM xerxes_user_categories WHERE ID = :category_id";
		return $this->delete( $sql, array (":category_id" => $objCat->id ) );
	}
	
	/**
	 * Add a database to a user-created subcategory; 
	 *
	 * @param String $databaseID the metalib_id of a Xerxes database object
	 * @param Subcategory $objSubcat object representing user created subcat
	 * @param int sequence optional, will default to end of list if null. 
	 */

	public function addDatabaseToUserCreatedSubcategory($databaseID, Subcategory $objSubcat, $sequence = null)
	{
		if ( $sequence == null )
			$sequence = count( $objSubcat->databases ) + 1;
		
		$strSQL = "INSERT INTO xerxes_user_subcategory_databases ( database_id, subcategory_id, sequence ) " . 
			"VALUES ( :database_id, :subcategory_id, :sequence )";
		
		$arrValues = array (
			":database_id" => $databaseID, 
			":subcategory_id" => $objSubcat->id, 
			":sequence" => $sequence
		);
		
		$this->insert( $strSQL, $arrValues );
	}
	
	/**
	 * Does not update database assignments, only the actual subcat values. 
	 * Right now, just name and sequence!
	 *
	 * @param Subcategory $objSubcat	subcatgeory object
	 * @return int 									update status
	 */
	
	public function updateUserSubcategoryProperties(Subcategory $objSubcat)
	{
		$sql = "UPDATE xerxes_user_subcategories " .
			"SET name = :name, sequence = :sequence " .
			"WHERE id = " . $objSubcat->id;
			
		return $this->update( $sql, 
			array (
				":name" => $objSubcat->name, 
				":sequence" => $objSubcat->sequence 
				) 
			);
	}
	
	/**
	 * Remove database from user created subcategory
	 *
	 * @param string $databaseID					database id		
	 * @param Subcategory $objSubcat	subcategory object
	 */
	
	public function removeDatabaseFromUserCreatedSubcategory($databaseID, Subcategory $objSubcat)
	{
		$strDeleteSql = "DELETE from xerxes_user_subcategory_databases " .
			"WHERE database_id = :database_id AND subcategory_id = :subcategory_id";
			
		$this->delete( $strDeleteSql, 
			array (
				":database_id" => $databaseID, 
				":subcategory_id" => $objSubcat->id 
				) 
			);
	}
	
	/**
	 * Update the 'sequence' number of a database in a user created category
	 *
	 * @param Database $objDb			database object 
	 * @param Subcategory $objSubcat	subcategory
	 * @param int $sequence							sequence number
	 */
	
	public function updateUserDatabaseOrder(Database $objDb, Subcategory $objSubcat, $sequence)
	{
		$this->beginTransaction();
		
		//first delete an existing join object.
		$this->removeDatabaseFromUserCreatedSubcategory( $objDb->metalib_id, $objSubcat );
		
		// Now create our new one with desired sequence. 
		$this->addDatabaseToUserCreatedSubcategory( $objDb->metalib_id, $objSubcat, $sequence );
		
		$this->commit(); //commit transaction
	}
	
	/**
	 * Convert metalib boolean values to 1 or 0
	 *
	 * @param string $strValue	'yes' or 'Y' will become 1. "no" or "N" will become 0. All others null. 
	 * @return int				1 or 0 or null
	 */
	
	private function convertMetalibBool($strValue)
	{
		if ( $strValue == "yes" || $strValue == "Y" )
		{
			return 1;
		} 
		elseif ( $strValue == "no" || $strValue == "N" )
		{
			return 0;
		} 
		else
		{
			return null;
		}
	}
	
	/**
	 * Get the top level categories (subjects) from the knowledgebase
	 *
	 * @return array		array of Category objects
	 */
	
	public function getCategories($lang = "")
	{
		if ( $lang == "" )
		{
			$lang = $this->primary_language;
		}
				
		$arrCategories = array ( );
		
		$strSQL = "SELECT * from xerxes_categories WHERE lang = :lang ORDER BY UPPER(name) ASC";
		
		$arrResults = $this->select( $strSQL, array(":lang" => $lang) );
		
		foreach ( $arrResults as $arrResult )
		{
			$objCategory = new Category( );
			$objCategory->load( $arrResult );
			
			array_push( $arrCategories, $objCategory );
		}
		
		return $arrCategories;
	}
	
	/**
	 * Get user-created categories for specified user. 
	 * @param string $username
	 * @return array		array of Category objects
	 */
	
	public function getUserCreatedCategories($username)
	{
		if ( ! $username )
		{
			throw new \Exception( "Must supply a username argument" );
		}
		
		$arrCategories = array ( );
		$strSQL = "SELECT * from xerxes_user_categories WHERE username = :username ORDER BY UPPER(name) ASC";
		$arrResults = $this->select( $strSQL, array (":username" => $username ) );
		
		foreach ( $arrResults as $arrResult )
		{
			$objCategory = new Category( );
			$objCategory->load( $arrResult );
			
			array_push( $arrCategories, $objCategory );
		}
		
		return $arrCategories;
	}
	
	/**
	 * ->getSubject can be used in two modes, metalib-imported  categories, or user created categories. 
	 * We take from different db tables depending
	 *
	 * @param string $mode		'metalib' or 'user_created' mode 
	 * @return array
	 */
	
	protected function schema_map_by_mode($mode)
	{
		if ( $mode == self::METALIB_MODE )
		{
			return array (
				"categories_table" => "xerxes_categories", 
				"subcategories_table" => "xerxes_subcategories", 
				"database_join_table" => "xerxes_subcategory_databases", 
				"subcategories_pk" => "metalib_id", 
				"extra_select" => "", 
				"extra_where" => " AND lang = :lang " 
			);
		} 
		elseif ( $mode == self::USER_CREATE_MODE )
		{
			return array (
				"categories_table" => "xerxes_user_categories", 
				"subcategories_table" => "xerxes_user_subcategories", 
				"database_join_table" => "xerxes_user_subcategory_databases", 
				"subcategories_pk" => "id", 
				"extra_select" => ", xerxes_user_categories.published AS published, " .
					"xerxes_user_categories.username AS username", 
				"extra_where" => " AND xerxes_user_categories.username = :username "
			);
		} 
		else
		{
			throw new \Exception( "unrecognized mode" );
		}
	}
	
	/**
	 * Get an inlined set of subcategories and databases for a subject. In
	 * METALIB_MODE, empty subcategories are not included. In USER_CREATE_MODE,
	 * they are. 
	 *
	 * @param string $normalized		normalized category name
	 * @param string $lang 			language code, can be empty string
	 * @param string $mode  		one of constants METALIB_MODE or USER_CREATE_MODE, 
	 * 					for metalib-imported categories or user-created categories, 
	 * 					using different tables.
	 * @param string $username 		only used in USER_CREATE_MODE, the particular user must be specified, 
	 * 					becuase normalized subject names are only unique within a user. 
	 * @return Category		a Category object, filled out with subcategories and databases. 
	 */
	
	public function getSubject($normalized, $lang = "", $mode = self::METALIB_MODE, $username = null )
	{
		if ( $mode == self::USER_CREATE_MODE && $username == null )
		{
			throw new \Exception( "a username argument must be supplied in USER_CREATE_MODE" );
		}
		
		$lang_query = $lang;	
		
		if ( $lang_query == "" )
		{
			$lang_query = $this->primary_language;
		}
			
		// This can be used to fetch personal or metalib-fetched data. We get
		// from different tables depending. 

		$schema_map = $this->schema_map_by_mode( $mode );
		
		$strSQL = "SELECT $schema_map[categories_table].id as category_id, 
			$schema_map[categories_table].name as category,
			$schema_map[subcategories_table].$schema_map[subcategories_pk] as subcat_id,
			$schema_map[subcategories_table].sequence as subcat_seq, 
			$schema_map[subcategories_table].name as subcategory, 
			$schema_map[database_join_table].sequence as sequence,
			xerxes_databases.*
			$schema_map[extra_select]
			FROM $schema_map[categories_table]
			LEFT OUTER JOIN $schema_map[subcategories_table] ON $schema_map[categories_table].id = $schema_map[subcategories_table].category_id
			LEFT OUTER JOIN $schema_map[database_join_table] ON $schema_map[database_join_table].subcategory_id = $schema_map[subcategories_table].$schema_map[subcategories_pk]
			LEFT OUTER JOIN xerxes_databases ON $schema_map[database_join_table].database_id = xerxes_databases.metalib_id
			WHERE $schema_map[categories_table].normalized = :value
			AND 
			($schema_map[subcategories_table].name NOT LIKE UPPER('All%') OR
			$schema_map[subcategories_table].name is NULL)
			$schema_map[extra_where]
			ORDER BY subcat_seq, sequence";
		  
		$args = array (":value" => $normalized );
		
		if ( $username )
		{
			$args[":username"] = $username;
		}
		else
		{
			$args[":lang"] = $lang_query;
		}
		
		$arrResults = $this->select( $strSQL, $args );
		
		if ( $arrResults != null )
		{
			$objCategory = new Category();
			$objCategory->id = $arrResults[0]["category_id"];
			$objCategory->name = $arrResults[0]["category"];
			$objCategory->normalized = $normalized;
			
			// these two only for user-created categories, will be nil otherwise.
			
			if ( array_key_exists( "username", $arrResults[0] ) )
			{
				$objCategory->owned_by_user = $arrResults[0]["username"];
			}
			
			if ( array_key_exists( "published", $arrResults[0] ) )
			{
				$objCategory->published = $arrResults[0]["published"];
			}
			
			$objSubcategory = new Subcategory( );
			$objSubcategory->id = $arrResults[0]["subcat_id"];
			$objSubcategory->name = $arrResults[0]["subcategory"];
			$objSubcategory->sequence = $arrResults[0]["subcat_seq"];
			
			$objDatabase = new Database( );
			
			foreach ( $arrResults as $arrResult )
			{
				// if the current row's subcategory name does not match the previous
				// one, then push the previous one onto category obj and make a new one

				if ( $arrResult["subcat_id"] != $objSubcategory->id )
				{
					// get the last db in this subcategory first too.
					
					if ( $objDatabase->metalib_id != null )
					{
						array_push( $objSubcategory->databases, $objDatabase );
					}
					
					$objDatabase = new Database( );
					
					// only add subcategory if it actually has databases, to
					// maintain consistency with previous semantics.
					
					if ( ($mode == self::USER_CREATE_MODE && 
						$objSubcategory->id) || ! empty( $objSubcategory->databases ) )
					{
						array_push( $objCategory->subcategories, $objSubcategory );
					}
					
					$objSubcategory = new Subcategory();
					$objSubcategory->id = $arrResult["subcat_id"];
					$objSubcategory->name = $arrResult["subcategory"];
					$objSubcategory->sequence = $arrResult["subcat_seq"];
				}
				
				// if the previous row has a different id, then we've come 
				// to a new database, otherwise these are values from the outer join

				if ( $arrResult["metalib_id"] != $objDatabase->metalib_id )
				{
					// existing one that isn't empty? save it.
					
					if ( $objDatabase->metalib_id != null )
					{
						array_push( $objSubcategory->databases, $objDatabase );
					}
					
					$objDatabase = new Database( );
					$objDatabase->load( $arrResult );
				}
				
				// if the current row's outter join value is not already stored,
				// then we've come to a unique value, so add it

				$arrColumns = array ("usergroup" => "group_restrictions" );
				
				foreach ( $arrColumns as $column => $identifier )
				{
					if ( array_key_exists( $column, $arrResult ) && ! is_null( $arrResult[$column] ) )
					{
						if ( ! in_array( $arrResult[$column], $objDatabase->$identifier ) )
						{
							array_push( $objDatabase->$identifier, $arrResult[$column] );
						}
					}
				}
			
			}
			
			// last ones
			
			if ( $objDatabase->metalib_id != null )
			{
				array_push( $objSubcategory->databases, $objDatabase );
			}
			
			if ( ($mode == self::USER_CREATE_MODE && $objSubcategory->id) || ! empty( $objSubcategory->databases ) )
			{
				array_push( $objCategory->subcategories, $objSubcategory );
			}
			
			// subcategories excluded by config
			
			$strSubcatInclude = $this->registry->getConfig( "SUBCATEGORIES_INCLUDE", false, null, $lang );
			
			if ( $strSubcatInclude != "" && $mode == self::METALIB_MODE)
			{							
				// this is kind of funky, but if we simply unset the subcategory, the array keys get out
				// of order, and the first one may therefore not be 0, which is a problem in higher parts of 
				// the system where we look for the first subcategory as $category->subcategories[0], so
				// we take them all out and put them all back in, including only the ones we want
				
				$arrInclude = explode(",", $strSubcatInclude);
				
				$arrSubjects =  $objCategory->subcategories;
				$objCategory->subcategories = null;
				
				foreach ( $arrSubjects as $subcat )
				{
					foreach ( $arrInclude as $strInclude )
					{
						$strInclude = trim($strInclude);
						
						if ( stristr($subcat->name, $strInclude) )
						{
							$objCategory->subcategories[] = $subcat;
							break;
						}
					}
				}
			}
			
			return $objCategory;
		} 
		else
		{
			return null;
		}
	
	}
	
	/**
	 * Get a single database from the knowledgebase
	 *
	 * @param string $id				metalib id
	 * @return Database
	 */
	
	public function getDatabase($id)
	{
		$arrResults = $this->getDatabases( $id );
		
		if ( count( $arrResults ) > 0 )
		{
			return $arrResults[0];
		} 
		else
		{
			return null;
		}
	}
	
	/**
	 * Get the starting letters for database titles
	 *
	 * @return array of letters
	 */	
	
	public function getDatabaseAlpha()
	{
		$strSQL = "SELECT DISTINCT alpha FROM " .
			"(SELECT SUBSTRING(UPPER(title_display),1,1) AS alpha FROM xerxes_databases) AS TEMP " .
			"ORDER BY alpha";
			
		$letters = array();
		$results = $this->select( $strSQL );
		
		foreach ( $results as $result )
		{
			array_push($letters, $result['alpha']);	
		}
		
		return $letters;
	}

	/**
	 * Get databases that start with a particular letter
	 *
	 * @param string $alpha letter to start with 
	 * @return array of Database objects
	 */	

	public function getDatabasesStartingWith($alpha)
	{
		return $this->getDatabases(null, null, $alpha);	
	}
	
	/**
	 * Get one or a set of databases from the knowledgebase
	 *
	 * @param mixed $id			[optional] null returns all database, array returns a list of databases by id, 
	 * 							string id returns single id
	 * @param string $query   user-entered query to search for dbs. 
	 * @return array			array of Database objects
	 */
	
	public function getDatabases($id = null, $query = null, $alpha = null)
	{
		$configDatabaseTypesExclude = $this->registry->getConfig("DATABASES_TYPE_EXCLUDE_AZ", false);
		$configAlwaysTruncate = $this->registry->getConfig("DATABASES_SEARCH_ALWAYS_TRUNCATE", false, false);		
		
		$arrDatabases = array ( );
		$arrResults = array ( );
		$arrParams = array ( );
		$where = false;
		$sql_server_clean = null;
		
		$strSQL = "SELECT * from xerxes_databases";

		// single database
		
		if ( $id != null && ! is_array( $id ) )
		{
			$strSQL .= " WHERE xerxes_databases.metalib_id = :id ";
			$arrParams[":id"] = $id;
			$where = true;
		} 		
		
		// databases specified by an array of ids
		
		elseif ( $id != null && is_array( $id ) )
		{
			$strSQL .= " WHERE ";
			$where = true;
			
			for ( $x = 0 ; $x < count( $id ) ; $x ++ )
			{
				if ( $x > 0 )
				{
					$strSQL .= " OR ";
				}
				
				$strSQL .= "xerxes_databases.metalib_id = :id$x ";
				$arrParams[":id$x"] = $id[$x];
			}
		} 
		
		// alpha query
		
		elseif ( $alpha != null )
		{
			$strSQL .= " WHERE UPPER(title_display) LIKE :alpha ";
			$arrParams[":alpha"] = "$alpha%";
			$where = true;
		}
		
		// user-supplied query
		
		elseif ( $query != null )
		{
			$where = true;
			$sql_server_clean = array();
			
			$arrTables = array(); // we'll use this to keep track of temporary tables
			
			// we'll deal with quotes later, for now 
			// and gives us each term in an array
			
			$arrTerms = explode(" ", $query);
			
			// grab databases that meet our query
			
			$strSQL .= " WHERE metalib_id IN  (
				SELECT database_id FROM ";
			
			// by looking for each term in the xerxes_databases_search table 
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
				array_push($sql_server_clean, ":term$x");
				
				$strSQL .= " (SELECT distinct database_id AS $alias FROM xerxes_databases_search WHERE term $operator :term$x) AS table$x ";
				
				// if there is another one, we need to add a comma between them
				
				if ( $x + 1 < count($arrTerms))
				{
					$strSQL .= ", ";
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
				$strSQL .= " WHERE " . implode(" AND ", $arrTables);
			}
			
			$strSQL .= ")";
		}
		
		// remove certain databases based on type(s), if so configured
		// unless we're asking for specific id's, yo	
	
		if ( $configDatabaseTypesExclude != null && $id == null )
		{
			$arrTypes = explode(",", $configDatabaseTypesExclude);
			$arrTypeQuery = array();
			
			// specify that the type NOT be one of these
		
			for ( $q = 0; $q < count($arrTypes); $q++ )
			{
				array_push($arrTypeQuery, "xerxes_databases.type != :type$q");
				$arrParams[":type$q"] = trim($arrTypes[$q]);
			}
				
			// AND 'em but then also catch the case where type is null
			
			$joiner = "WHERE";
			
			if ( $where == true )
			{
				$joiner = "AND";
			}
			
			$strSQL .= " $joiner ( (" . implode (" AND ", $arrTypeQuery) . ") OR xerxes_databases.type IS NULL )";
		}
			
		$strSQL .= " ORDER BY UPPER(title_display)";
		
		// echo $strSQL; print_r($arrParams); // exit;
		
		$arrResults = $this->select( $strSQL, $arrParams, $sql_server_clean );
		
		// transform to internal data objects
		
		if ( $arrResults != null )
		{
			foreach ( $arrResults as $arrResult )
			{
				$objDatabase = new Database();
				$objDatabase->load( $arrResult );
				array_push($arrDatabases, $objDatabase);
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
	
	/**
	 * Get the list of types
	 *
	 * @return array	array of Type objects
	 */
	
	public function getTypes()
	{
		$arrTypes = array ( );
		
		$strSQL = "SELECT * from xerxes_types ORDER BY UPPER(name) ASC";
		
		$arrResults = $this->select( $strSQL );
		
		foreach ( $arrResults as $arrResult )
		{
			$objType = new Type( );
			$objType->load( $arrResult );
			
			array_push( $arrTypes, $objType );
		}
		
		return $arrTypes;
	}
}
