<?php

namespace Application\View\Helper;

use Application\Model\Search\Engine,
	Application\Model\Search\Result,
	Application\Model\Search\ResultSet,
	Application\Model\Search\Query,
	Application\Model\Search\Spelling\Suggestion,
	Xerxes\Record,
	Xerxes\Utility\Parser,
	Xerxes\Utility\Request,
	Xerxes\Utility\Registry,
	Zend\Mvc\MvcEvent;

class Search
{
	protected $id;
	protected $query;
	protected $config;
	
	protected $request; // request
	protected $registry; // reistry
	
	public function __construct(MvcEvent $e, $id, Engine $engine)
	{
		$this->request = $e->getRequest();
		$this->registry = Registry::getInstance();
		
		$this->id = $id;
		$this->query = $engine->getQuery($this->request);
		$this->config = $engine->getConfig();
	}
	
	/**
	 * Displays paged information (e.g., 11-20 of 34 results)
	 *
	 * @param int $total 		total # of hits for query
	 * @param int $start 		start value for the page
	 * @param int $max 			maximum number of results to show
	 *
	 * @return array or null	summary of page results 
	 */
	
	public function summary( $total, $start, $max )
	{
		if ( $total < 1 )
		{
			return null;
		}
		
		if ( $start == 0 )
		{
			$start = 1;
		}
			
		// set end point
		
		$stop = $start + ($max - 1);
		
		// if end value of group of 10 exceeds total number of hits,
		// take total number of hits as end value 
		
		if ( $stop > $total )
		{
			$stop = $total;
		}
		
		return array ( 
			"range" => "$start-$stop",
			"total" => Parser::number_format( $total )
		);
	}
	
	/**
	 * Paging element
	 * 
	 * @param int $total 		total # of hits for query
	 * @param int $start 		start value for the page
	 * @param int $max 			maximum number of results to show
	 * 
	 * @return DOMDocument formatted paging navigation
	 */
	
	public function pager( $total, $start, $max )
	{
		if ( $total < 1 )
		{
			return null;
		}
		
		$objXml = Parser::convertToDOMDocument( "<pager />" );
		
		$base_record = 1; // starting record in any result set
		$page_number = 1; // starting page number in any result set
		$bolShowFirst = false; // show the first page when you get past page 10
		
		if ( $start == 0 ) 
		{
			$start = 1;
		}
		
		$current_page = (($start - 1) / $max) + 1; // calculates the current selected page
		$bottom_range = $current_page - 5; // used to show a range of pages
		$top_range = $current_page + 5; // used to show a range of pages
		
		$total_pages = ceil( $total / $max ); // calculates the total number of pages
		
		// for pages 1-10 show just 1-10 (or whatever records per page)
		
		if ( $bottom_range < 5 )
		{
			$bottom_range = 0;
		}
		
		if ( $current_page < $max )
		{
			$top_range = 10;
		} 
		else
		{
			$bolShowFirst = true;
		}
		
		// chop the top pages as we reach the end range
		
		if ( $top_range > $total_pages )
		{
			$top_range = $total_pages;
		}
		
		// see if we even need a pager
		
		if ( $total > $max )
		{
			// show first page
			
			if ( $bolShowFirst == true )
			{
				$objPage = $objXml->createElement( "page", "1" );
				
				$params = $this->currentParams();
				$params["start"] = 1;
				
				$link = $this->request->url_for( $params );
				
				$objPage->setAttribute( "link", Parser::escapeXml( $link ) );
				$objPage->setAttribute( "type", "first" );
				$objXml->documentElement->appendChild( $objPage );
			}
			
			// create pages and links
			
			while ( $base_record <= $total )
			{
				if ( $page_number >= $bottom_range && $page_number <= $top_range )
				{
					if ( $current_page == $page_number )
					{
						$objPage = $objXml->createElement( "page", $page_number );
						$objPage->setAttribute( "here", "true" );
						$objXml->documentElement->appendChild( $objPage );
					} 
					else
					{
						$objPage = $objXml->createElement( "page", $page_number );
						
						$params = $this->currentParams();
						$params["start"] = $base_record;
						
						$link = $this->request->url_for( $params );
						
						$objPage->setAttribute( "link", Parser::escapeXml( $link ) );
						$objXml->documentElement->appendChild( $objPage );
					
					}
				}
				
				$page_number++;
				$base_record += $max;
			}
			
			$next = $start + $max;
			
			if ( $next <= $total )
			{
				$objPage = $objXml->createElement( "page", "" ); // element to hold the text_results_next label
				
				$params = $this->currentParams();
				$params["start"] =  $next;
				
				$link = $this->request->url_for( $params );
				
				$objPage->setAttribute( "link", Parser::escapeXml( $link ) );
				$objPage->setAttribute( "type", "next" );
				$objXml->documentElement->appendChild( $objPage );
			}
		}
		
		return $objXml;
	}
	
	/**
	 * Creates a sorting page element
	 *
	 * @param string $sort			current sort
	 *
	 * @return DOMDocument 			sort navigation
	 */
	
	public function sortDisplay($sort)
	{
		$sort_options = $this->config->sortOptions();
		
		if ( count($sort_options) == 0 )
		{
			return null;
		}
		
		$xml = Parser::convertToDOMDocument( "<sort_display />" );
		
		$x = 1;
		
		foreach ( $sort_options as $key => $value )
		{
			if ( $key == $sort )
			{
				$here = $xml->createElement( "option", $value );
				$here->setAttribute( "active", "true" );
				$xml->documentElement->appendChild( $here );
			} 
			else
			{
				$params = $this->sortLinkParams();
				$params["sort"] = $key;
				
				$here = $xml->createElement( "option", $value );
				$here->setAttribute( "active", "false" );
				$here->setAttribute( "link", $this->request->url_for($params) );
				$xml->documentElement->appendChild( $here );
			}
			
			$x++;
		}
		
		return $xml;
	}
	
	
	######################
	#        LINKS       #
	######################
	
	
	/**
	 * Add links to search results
	 * 
	 * @param ResultSet $results
	 */

	public function addRecordLinks( ResultSet &$results )
	{	
		// results
				
		foreach ( $results->getRecords() as $result )
		{
			$xerxes_record = $result->getXerxesRecord();
			
			// full-record link
			
			$result->url = $this->linkFullRecord($xerxes_record);
			$result->url_full = $result->url; // backwards compatibility
				
			// sms link
			
			$result->url_sms = $this->linkSMS($xerxes_record);
				
			// save or delete link
			
			$result->url_save = $this->linkSaveRecord($xerxes_record);
			$result->url_save_delete = $result->url_save; // backwards compatibility
			
			// other links
			
			$this->linkOther($result);
		}
	}
	
	/**
	 * Add links to facets
	 * 
	 * @param ResultSet $results
	 */	
	
	public function addFacetLinks( ResultSet &$results )
	{	
		// facets

		$facets = $results->getFacets();
		
		if ( $facets != "" )
		{
			foreach ( $facets->getGroups() as $group )
			{
				foreach ( $group->getFacets() as $facet )
				{
					
					$param_name = '';
												
					if ( $facet->key != "" ) 
					{
						// key defines a way to pass the (internal) value
						// in the param, while the name is the display value
						
						$param_name = 'facet.' . $group->name . '.' . urlencode($facet->key);
					}
					else
					{
						$param_name = 'facet.' . $group->name;									
					}
					
					// existing url plus our param
					
					$url = $this->facetParams();
					$url[$param_name] = $facet->name;
					$facet->url = $this->request->url_for($url);
					
					// add the name of the param as well
					
					$facet->param_name = $param_name;
					
					// see if this facet is selected (for multi-select facets)
					
					if ( $this->request->hasParamValue($param_name, $facet->name) )
					{
						$facet->selected = true;
					}
				}
			}
		}
	}
	
	/**
	 * Add links to the query object limits
	 * 
	 * @param Query $query
	 */
	
	public function addQueryLinks(Query $query)
	{
		// we have to pass in the query object here rather than take
		// the property above because adding the links doesn't seem
		// to reflect back in the main object, even though they should 
		// be references, maybe because limit objects are in an array?  
		
		// search option links
		
		$search = $this->registry->getConfig('search');
		
		if ( $search instanceof \SimpleXMLElement )
		{
			$controller_map = $this->request->getControllerMap();
			
			foreach ( $search->xpath("//option") as $option )
			{
				// format the number
				
				// is this the current tab?

				if ( $controller_map->getControllerName() == (string) $option["id"] 
				     && ( $this->request->getParam('source') == (string) $option["source"] 
				     	|| (string) $option["source"] == '') )
				    {
				    	$option->addAttribute('current', "1");	
				    }
				
				// url
				
				$params = $query->extractSearchParams();
				
				$params['controller'] = $controller_map->getUrlAlias((string) $option["id"]);
				$params['action'] = "results";
				$params['source'] = (string) $option["source"];
				$params['sort'] = $this->request->getParam('sort');
				
				$url = $this->request->url_for($params);
				
				$option->addAttribute('url', $url);
				
				// cached search hit count?
		
				foreach ( $this->request->getAllSessionData() as $session_id => $session_value )
				{
					// does this value in the cache have the save id as our tab?
					
					$id = str_replace("_" . $query->getHash(), "", $session_id);
					
					if ( $id == (string) $option["id"] )
					{
						// yup, so add it
						
						$option->addAttribute('hits', Parser::number_format($session_value));
					}
				}
			}
			
			$this->registry->setConfig('search', $search);
		}
		
		// links to remove facets
		
		foreach ( $query->getLimits() as $limit )
		{
			$params = $this->currentParams();
			
			// urlencode here necessary to support the urlencode above on 'key' urls
			
			$params = Parser::removeFromArray($params, urlencode($limit->field), $limit->value);
			
			$limit->remove_url = $this->request->url_for($params);
		}
	}
	
	public function addSpellingLink( Suggestion $spelling = null )
	{
		if ( $spelling == null )
		{
			return;
		}
		
		// link to corrected spelling
		
		if ( $spelling->hasSuggestions() )
		{
			$term = $spelling->getTerm(0);
			
			$params = $this->currentParams();
			$params["field"] = $term->field;
			$params["query"] = $term->phrase;
				
			$spelling->url = $this->request->url_for($params);
		}
	}
	
	/**
	 * URL for the full record display
	 * 
	 * @param $result Record object
	 * @return string url
	 */
	
	public function linkFullRecord( Record $result )
	{
		$arrParams = array(
			'controller' => $this->request->getParam('controller'),
			"action" => "record",
			"id" => $result->getRecordID()
		);
		
		return $this->request->url_for($arrParams);
	}
	
	/**
	 * URL for the full record display
	 * 
	 * @param Record $result
	 * @return string url
	 */
	
	public function linkSaveRecord( Record $result )
	{
		$arrParams = array(
			'controller' => $this->request->getParam('controller'),
			"action" => "save",
			"id" => $result->getRecordID()
		);
		
		return $this->request->url_for($arrParams);
	}
	
	/**
	 * URL for the sms feature
	 * 
	 * @param Record $result
	 * @return string url
	 */	
	
	public function linkSMS( Record $result )
	{
		$arrParams = array(
			'controller' => $this->request->getParam('controller'),
			"action" => "sms",
			"id" => $result->getRecordID()
		);
		
		return $this->request->url_for($arrParams);	
	}

	/**
	 * Other links for the record beyond those supplied by the framework,
	 * such as lateral subject or author links
	 * 
	 * @param Xerxes_Model_Search_Result $result 
	 */	
	
	public function linkOther( Result $result )
	{
		return $result;
	}
	
	
	######################
	#  PARAMS FOR LINKS  #
	######################
	
	
	/**
	 * The current search-related parameters 
	 * @return array
	 */
	
	public function currentParams()
	{
		$params = $this->request->getParams();
		$params['controller'] = $this->request->getParam('controller');
		$params["action"] = $this->request->getParam("action");
		$params["sort"] = $this->request->getParam("sort");
		
		return $params;
	}
	
	/**
	 * Parameters to construct the facet links
	 * @return array
	 */
	
	public function facetParams()
	{
		$params = $this->currentParams();
		$params["start"] = null; // send us back to page 1
		
		return $params;
	}	
	
	/**
	 * Parameters to construct the url on the search redirect
	 * @return array
	 */
	
	public function searchRedirectParams()
	{
		$params = $this->currentParams();
		$params["action"] = "results";
		
		return $params;
	}
	
	/**
	 * Parameters to construct the links for the paging element
	 * @return array
	 */
	
	public function pagerLinkParams()
	{
		$params = $this->currentParams();
		return $params;
	}
	
	/**
	 * Parameters to construct the links for the sort
	 * @return array
	 */
	
	public function sortLinkParams()
	{
		$params = $this->currentParams();
		
		// remove the current sort, since we'll add the new
		// sort explicitly to the url
		
		unset($params["sort"]);
		
		return $params;
	}
	
	/**
	 * Return identifier for this query = search ID + query hash
	 */
	
	public function getQueryID()
	{
		return $this->id . "_" . $this->query->getHash();
	}	
	
}