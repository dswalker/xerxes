<?php

namespace Application\Model\Metalib;

use Application\Model\Search,
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
	 * Get User from request
	 */
	
	public function getUser()
	{
		return $this->request->getUser();
	}
	
	/**
	 * Get all databases
	 * 
	 * @return array of Database objects
	 */
	
	public function getDatabases()
	{
		return $this->request->getParam('database', null, true);
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
