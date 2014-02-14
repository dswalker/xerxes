<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

use Application\Model\Search\Spelling\Suggestion;
use Xerxes\Utility\Factory;
use Xerxes\Utility\Registry;
use Xerxes\Mvc\Request;

/**
 * Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query
{
	/**
	 * maximum records to return
	 * @var string
	 */
	
	public $start;
	
	/**
	 * default records per page
	 * @var int
	 */
	public $max = 10;
	
	/**
	 * upper-limit per page
	 * @var int
	 */
	public $max_allowed = 30;

	/**
	 * internal sort
	 * @var string
	 */
	public $sort = 'relevance';
	
	/**
	 * external sort id
	 */
	public $sort_id = 'relevance';
	
	/**
	 * @var string
	 */
	
	public $simple;
	
	/**
	 * @var QueryTerm[]
	 */
	
	public $terms = array();
	
	/**
	 * @var LimitTerm[]
	 */	
	
	public $limits = array();
	
	/*
	 * @var bool
	 */
	protected $facets = true;
	
	/**
	 * @var string
	 */
	
	protected $stop_words = "";
	
	/**
	 * @var string
	 */	
	
	protected $search_fields_regex = '^advanced$|^query[0-9]{0,1}$|^field[0-9]{0,1}$|^boolean[0-9]{0,1}$';
	
	/**
	 * @var string
	 */
	
	protected $limit_fields_regex = '^facet.*';	
	
	/**
	 * @var Request
	 */
	
	protected $request;

	/**
	 * @var Registry
	 */
	
	protected $registry;
	
	/**
	 * @var Config
	 */
	
	protected $config;
	
	/**
	 * Create a Search Query
	 * 
	 * @param Request $request
	 * @param Config $config
	 */
	
	public function __construct(Request $request = null, Config $config = null )
	{
		// registry
		
		$this->registry = Registry::getInstance();
		
		if ( $config != null )
		{
			// config
			
			$this->config = $config;
			
			// defaults set in config(s)
				
			$this->max = $this->registry->getConfig("RECORDS_PER_PAGE", false, $this->max);
			$this->max = $this->config->getConfig("RECORDS_PER_PAGE", false, $this->max);
				
			$this->max_allowed = $this->registry->getConfig("MAX_RECORDS_PER_PAGE", false, $this->max_allowed);
			$this->max_allowed = $this->config->getConfig("MAX_RECORDS_PER_PAGE", false, $this->max_allowed);
				
			$this->sort = $this->registry->getConfig("SORT_ORDER", false, $this->sort);
			$this->sort = $this->config->getConfig("SORT_ORDER", false, $this->sort);
		}
			
		// xerxes request
		
		if ( $request != null )
		{
			// make these available
			
			$this->request = $request;
	
			// populate it with the 'search' related params out of the url
			
			foreach ( $this->extractSearchGroupings() as $term )
			{
				$this->addTerm(
					$term["id"], 
					$term["boolean"], 
					$term["field"], 
					$term["relation"], 
					$term["query"]);
			}
	
			// also limits
			
			foreach ( $this->extractLimitGroupings() as $limit )
			{
				$this->addLimit($limit["boolean"], $limit["field"], $limit["relation"], $limit["value"]);
			}
			
			// start, max, sort
			
			$this->start = $this->request->getParam('start', 1);
			$this->max = $this->request->getParam('max', $this->max);
			$this->sort = $this->request->getParam('sort', $this->sort);
			
			// store the original (public) sort as the sort_id,
			// we'll take sort as the (internal) sort
			
			$this->sort_id = $this->sort;
			
			// swap for internal
			
			if ( $this->config != null )
			{
				$this->sort = $this->config->swapForInternalSort($this->sort);
			}
			
			// make sure records per page does not exceed upper bound
			
			if ( $this->max > $this->max_allowed )
			{
				$this->max = $this->max_allowed;
			}
		}
	}
	
	/**
	 * Get a specific query term
	 *
	 * @param int $id		the position of the term in the list of terms
	 * @return QueryTerm
	 */
	
	public function getQueryTerm($id)
	{
		if ( ! array_key_exists($id, $this->terms) )
		{
			throw new \Exception("No query term with id '$id'");
		}
		
		return $this->terms[$id];
	}
	
	/**
	 * Return the query terms
	 * 
	 * @return array
	 */	
	
	public function getQueryTerms()
	{
		return $this->terms;
	}
	
	/**
	 * Return search terms
	 * 
	 * Just the phrase of each query term, as a string
	 * 
	 * @return string
	 */
	
	public function getSearchTerms()
	{
		$final = array();
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$final[] = $term->phrase;
		}
		
		return implode(' ', $final);
	}
	
	/**
	 * Get a specific limit
	 *
	 * @param string $id             the limit name
	 * @param bool $facets_to_field  whether to return facets with key convention as limits
	 * @return LimitTerm
	 */
	
	public function getLimit($id, $facets_to_field = false)
	{
		foreach ( $this->getLimits($facets_to_field) as $limit )
		{
			if ( $limit->field == $id )
			{
				return $limit;
			}
		}
		
		// we didn't find it, so return empty limit term
		
		return new LimitTerm();
	}	
	
	/**
	 * Return all limits
	 *
	 * @param bool $facets_to_field		whether to return facets with key convention as limits
	 * @return array
	 */
	
	public function getLimits($facets_to_field = false)
	{
		if ( $facets_to_field == false )
		{
			return $this->limits;
		}
		else
		{
			$final = array();
			
			foreach ( $this->limits as $limit )
			{
				$new_limit = clone $limit; // make a copy
				
				// take the field name out of our facet.* param
				
				if ( strstr($new_limit->field,"facet.") )
				{
					$parts = explode('.', $new_limit->field);
					
					// if it has 3 parts, it's our special 'key' convention, where
					// the third part is the value, so swap it in for the value
					
					if ( count($parts) == 3 )
					{
						$new_limit->value = array_pop($parts);
						$new_limit->field = array_pop($parts);
						$new_limit->key = true;
						$new_limit->display = $limit->value;  // @todo: make this not 'display'
					}
					else
					{
						// the field name is the only thing there, value is already
						// populated in the value of the limit object
						
						$new_limit->field = array_pop($parts);
					}
				}
				
				// put it into the return array
				
				array_push($final, $new_limit);
			}

			return $final;
		}
	}
	
	/**
	 * Add a query term
	 * 
	 * @param string $id		identifier for this query term
	 * @param string $boolean	boolean operator combining this phrase to the total query
	 * @param string $field		field to search on
	 * @param string $relation	operator
	 * @param string $phrase	search term value
	 */
	
	public function addTerm($id, $boolean, $field, $relation, $phrase)
	{
		if ( $field == "" )
		{
			$field = "keyword";
		}
		
		// alter query based on config
		
		$field_internal = "";
				
		if ( $this->config != null )
		{
			$field_internal = $this->config->swapForInternalField($field);
			$phrase = $this->alterQuery( $phrase, $field );
		}		
		
		$term = new QueryTerm($id, $boolean, $field, $field_internal, $relation, $phrase);
		array_push($this->terms , $term);
	}
	
	/**
	 * Add a limit
	 * 
	 * @param string $boolean	boolean combine type
	 * @param string $field		field name
	 * @param string $relation	operator
	 * @param string $phrase	the value of the limit
	 */
	
	public function addLimit($boolean, $field, $relation, $phrase)
	{
		$term = new LimitTerm();
		$term->boolean = $boolean;
		$term->field = $field;
		$term->relation = $relation;
		$term->value = $phrase;
		
		if ( $boolean == 'NOT' )
		{
			$term->param = str_replace('facet.', 'facet.remove.', $field);
		}
		else
		{
			$term->param = $field;
		}
		
		array_push($this->limits , $term);
	}
	
	/**
	 * Check the spelling of the search terms
	 * 
	 * @return null|Suggestion
	 */
	
	public function checkSpelling()
	{
		// don't check multiple terms, for now @todo: fix
		
		$terms = $this->getQueryTerms();
		
		if ( count($terms) > 1 )
		{
			return null;
		}
		
		$spell_type = $this->registry->getConfig('SPELL_CHECKER');
		
		if ( $spell_type != null )
		{
			$class_name = 'Application\Model\Search\Spelling\\' . ucfirst($spell_type);
			
			$spell_checker = new $class_name();
			
			return $spell_checker->checkSpelling($terms);
		}
	}
	
	/**
	 * Return an md5 hash of the request uri
	 */	
	
	public function getUrlHash()
	{
		if ( ! $this->request instanceof Request )
		{
			throw new \Exception("No Request object set");
		}
		
		return md5($this->request->getRequestUri());
	}
	
	/**
	 * Return an md5 hash of the main search parameters, bascially to identify the search
	 */
	
	public function getHash()
	{
		// give me the hash!
		
		return md5($this->getNormalizedQuery());
	}
	
	/**
	 * Get the search query parameters in a normalized form
	 */
	
	protected function getNormalizedQuery()
	{
		// get the search params
		
		$params = $this->extractSearchParams();
		
		// and sort them alphabetically
		
		ksort($params);
		
		$query_normalized = "";
		
		// now put them back together in a normalized form
		
		foreach ( $params as $key => $value )
		{
			if ( is_array($value) )
			{
				foreach ($value as $part)
				{
					$query_normalized .= "&amp;$key=" . urlencode($part);
				}
			}
			else
			{
				$query_normalized .= "&amp;$key=" . urlencode($value);
			}
		}
		
		return $query_normalized;
	}
	
	/**
	 * Get 'limit' params out of the URL
	 * 
	 * @return array
	 */	
	
	protected function extractLimitParams()
	{
		if ( $this->limit_fields_regex != "" )
		{
			return $this->request->getParams($this->limit_fields_regex);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Get 'search' params out of the URL
	 * 
	 * @return array
	 */		
	
	public function extractSearchParams()
	{
		if ( $this->search_fields_regex != "" )
		{
			return $this->request->getParams($this->search_fields_regex);
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Get 'limit' params out of the URL, organized into groupings for the 
	 * query object to parse
	 * 
	 * @return array
	 */	
	
	protected function extractLimitGroupings()
	{
		$arrFinal = array();
		
		if ( $this->limit_fields_regex != "" )
		{
			foreach ( $this->extractLimitParams() as $key => $value )
			{
				if ( $value == "" )
				{
					continue;
				}
				
				$key = urldecode($key);
					
				if ( strstr($key, "_relation") )
				{
					continue;
				}
				
				$arrTerm = array();
				

				if ( strstr($key, "facet.remove.") )
				{
					$key = str_replace('remove.', '', $key);
					$arrTerm["boolean"] = "NOT";
				}
				else
				{
					$arrTerm["boolean"] = "";
				}
				
				$arrTerm["field"] = $key;
				$arrTerm["relation"] = "=";
				$arrTerm["value"] = $value;
					
				$relation = $this->request->getParam($key . "_relation");
				
				if ( $relation != null )
				{
					$arrTerm["relation"] = $relation;
				}
				
				array_push($arrFinal, $arrTerm);
			}
		}
		
		return $arrFinal;
	}	
	
	/**
	 * Get 'search' params out of the URL, organized into groupings for the 
	 * query object to parse
	 * 
	 * @return array
	 */		
	
	protected function extractSearchGroupings()
	{
		$arrFinal = array();
		
		foreach ( $this->request->getParams() as $key => $value )
		{
			$key = urldecode($key);
			
			// if we see 'query' as the start of a param, check if there are corresponding
			// entries for field and boolean; these will have a number after them
			// if coming from an advanced search form
				
			if ( preg_match("/^query/", $key) )
			{
				if ( $value == "" )
				{
					continue;
				}			

				$id = str_replace("query", "", $key);
				
				$arrTerm = array();
				
				$arrTerm["id"] = $id;
				$arrTerm["query"] = $value;
				$arrTerm["relation"] = $this->request->getParam("relation$id");			
				$arrTerm["field"] = $this->request->getParam("field$id");
				
				// boolean only counts if this is not the first query term
				
				if ( count($arrFinal) > 0 )
				{
					$arrTerm["boolean"] = $this->request->getParam("boolean$id");
				}
				else
				{
					$arrTerm["boolean"] = "";
				}
				
				array_push($arrFinal, $arrTerm);
			}
		}
		
		return $arrFinal;
	}

	/**
	 * Extract both query and limit params from the URL
	 * @return array
	 */
	
	public function getAllSearchParams()
	{
		$search = $this->extractSearchParams();
		$limits = array();
		
		if ( $this->request->getParam('clear-facets') != "true" )
		{
			$limits = $this->extractLimitParams();
		}
		
		return array_merge($search, $limits);
	}
	
	/**
	 * Get just the limit params
	 */
	
	public function getLimitParams()
	{
		return  $this->extractLimitParams();
	}
	
	/**
	 * Change the case or add truncation to a search based on config
	 * 
	 * @param string $phrase		the search phrase
	 * @param string $field			field to search on
	 * 
	 * @return string 				altereted phrase, or original as supplied if field has no definitions
	 */

	public function alterQuery($phrase, $field)
	{
		$phrase = trim($phrase);
		
		$case = $this->config->getFieldAttribute($field, "case");
		$trunc = $this->config->getFieldAttribute($field, "truncate");

		switch($case)
		{
			case "upper":
				$phrase = strtoupper($phrase);
				break;
			case "lower":
				$phrase = strtolower($phrase);
				break;			
		}

		switch($trunc)
		{
			case "left":
				$phrase = "*" . $phrase;
				break;
			case "right":
				$phrase = $phrase . "*";
				break;
			case "both":
				$phrase = "*" . $phrase . "*";
				break;	
		}
		
		return $phrase;
	}
	
	/**
	 * Get the User performing this search
	 * 
	 * @return Xerxes\Utility\User
	 */
	
	public function getUser()
	{
		return $this->request->getUser();
	}
	
	/**
	 * Create URL parameter from limit term parts
	 * 
	 * @param string $field
	 * @param string $key
	 * @param bool $excluded
	 */
	
	public static function getParamFromParts($field, $key, $excluded)
	{
		$param_name = 'facet';
		
		if ( $excluded == true )
		{
			$param_name .= '.remove';
		}
		
		$param_name .= '.' . $field;
		
		// key defines a way to pass the (internal) value
		// in the param, while the 'name' is the display value
		
		if ( $key != "" )
		{
			$param_name .= '.' . $key;
		}
		
		return $param_name;
	}
	
	/**
	 * Return the (internal) search string
	 */
	
	public function toQuery()
	{
		$query = '';
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$query .= " " . $term->boolean;
		
			// is this a fielded search?
				
			if ( $term->field_internal != "" ) // yes
			{
				$query .= " " . $term->internal . ':(' . $term->phrase . ')';
		
			}
			else // keyword
			{
				$query .= " " . $term->phrase;
			}
		}
		
		return $query;
	}
	
	/**
	 * Check to see if any of the search terms have an undefined (hence unsupported)
	 * internal search field
	 * 
	 * @return boolean
	 */
	
	public function hasUnsupportedField()
	{
		foreach ( $this->terms as $term )
		{
			if ( $term->field_internal == Config::UNSUPPORED_FIELD )
			{
				return true;
			}
		}
		
		return false; // got this far nada
	}
	
	/**
	 * @return Request
	 */
	
	public function getRequest()
	{
		return $this->request;
	}
}
