<?php

namespace Application\Model\Metalib;

use Application\Model\DataMap\Databases,
	Application\Model\Search,
	Xerxes\Utility\Request;

/**
 * Search Query
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Query extends Search\Query
{
	protected $date; // timestamp of query
	protected $databases = array(); // databases selected
	protected $datamap; // data map
	
	/**
	 * Create Metalib Search Query 
	 * 
	 * @param Request $request
	 * @param Config $config
	 * @throws \Exception
	 */
	
	public function __construct(Request $request = null, Config $config = null )
	{
		parent::__construct($request, $config);
		
		// make sure we got some terms!
		
		if ( count($this->getQueryTerms()) == 0 )
		{
			throw new \Exception("No search terms supplied");
		}
		
		// databases or subject chosen
		
		$databases = $this->request->getParam('database', null, true);
		$subject = $this->getSubject();
		
		// populate the database information from KB
		
		$this->datamap = new Databases(); // @todo: use KB model instead?
		
		// databases specifically supplied
		
		if ( count($databases) >= 0 )
		{
			$this->databases = $this->datamap->getDatabases($databases);
		}
		
		// just a subject supplied, so get databases from that subject, yo!
		
		elseif ( count($databases) == 0 && $subject != null )
		{
			$search_limit = $this->config->getConfig( "SEARCH_LIMIT", true );
				
			// @todo: fix metalib/user kb madness
				
			$subject_object = $this->datamap->getSubject( $subject, null, "metalib", null, $this->getLanguage() );
		
			// did we find a subject that has subcategories?
		
			if ( $subject_object != null && $subject_object->subcategories != null && count( $subject_object->subcategories ) > 0 )
			{
				$subs = $subject_object->subcategories;
				$subcategory = $subs[0];
				$index = 0;
					
				// get databases up to search limit from first subcategory
					
				foreach ( $subcategory->databases as $database_object )
				{
					if ( $database_object->searchable == 1 )
					{
						$this->databases[] = $database_object;
						$index++;
					}
						
					if ( $index >= $search_limit )
					{
						break;
					}
				}
			}
		}
		
		// make sure we have a scope, either databases or subject
		
		if ( count($databases) == 0 && $subject == null )
		{
			throw new \Exception("No databases or subject supplied");
		}		
	}
	
	/**
	 * Convert the search terms to Metalib query
	 */
	
	public function toQuery()
	{
		// construct query
		
		$query = "";
		
		$terms = $this->getQueryTerms();
		
		// normalize terms
		
		$term1 = $terms[0];
		$term1->toLower()->andAllTerms();
		
		// metalib only allows two search boxes
		
		if ( count($terms) == 2 ) // two terms supplied
		{
			$term2 = $terms[1];
			$term2->toLower()->andAllTerms();
				
			$query = $term1->field_internal . "=(" . $term1->phrase . ") " .
					$term2->boolean . " " . $term2->field_internal . "=(" . $term2->phrase . ") ";
		}
		else // just one supplied
		{
			// normalized (special sauce)
				
			// if there was only one search field/term and there was an OR or NOT
			// in the search phrase itself, split the query into two separate
			// fielded searches, since this will improve the chance of the search
			// working correctly
		
			if ( $this->config->getConfig("NORMALIZE_QUERY", false, false) == true )
			{
				// since we dropped the query to lowercase and then uppercased the bare boolean operators above
				// this code only catches an actual OR or NOT not in quotes
		
				$query = preg_replace("/(OR|NOT)/", ") $1 $term1->field_internal=(", $term1->phrase, 1);
				$query = trim($term1->phrase);
				$query = $term1->field_internal . "=(" . $term1->phrase . ")";
			}
			else
			{
				// just regular, good luck!
		
				$query = "$term1->field_internal=($term1->phrase)";
			}
		}
		
		return $query;
	}
	
	/**
	 * Get all databases
	 * 
	 * @return array of Database objects
	 */
	
	public function getDatabases()
	{
		return $this->databases;
	}
	
	/**
	 * Get searchable database IDs
	 * 
	 * @return array
	 */
	
	public function getSearchableDatabases()
	{
		// don't include databases that cannot be searched by user (or at all)
		
		$databases_to_search = array();
		$user = $this->request->getUser();
		
		foreach ( $this->databases as $database_object )
		{
			if ( $database_object->isSearchableByUser($user) )
			{
				$databases_to_search[] = $database_object->metalib_id; // @todo: get rid of this
			}
		}
		
		return $databases_to_search;
	}
	
	/**
	 * Get selected subject
	 */
	
	public function getSubject()
	{
		return $this->request->getParam('subject');
	}
	
	/**
	 * Get selected language
	 */
	
	public function getLanguage()
	{
		return $this->request->getParam('lang');
	}
}
