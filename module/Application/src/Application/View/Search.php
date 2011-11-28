<?php

class Xerxes_View_Helper_Search
{
	protected $id;
	protected $request;
	protected $query;
	protected $config;
	protected $registry;
	
	public function __construct($id, Xerxes_Model_Search_Engine $engine)
	{
		$this->request = Xerxes_Framework_Request::getInstance();
		$this->registry = Xerxes_Framework_Registry::getInstance();		
		
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
			"total" => Xerxes_Framework_Parser::number_format( $total )
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
		
		$objXml = new DOMDocument( );
		$objXml->loadXML( "<pager />" );
		
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
				
				$objPage->setAttribute( "link", Xerxes_Framework_Parser::escapeXml( $link ) );
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
						
						$objPage->setAttribute( "link", Xerxes_Framework_Parser::escapeXml( $link ) );
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
				
				$objPage->setAttribute( "link", Xerxes_Framework_Parser::escapeXml( $link ) );
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
		
		$xml = new DOMDocument();
		$xml->loadXML( "<sort_display />" );
		
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
	 * @param Xerxes_Model_Search_Results $results
	 */

	public function addRecordLinks( Xerxes_Model_Search_ResultSet &$results )
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
	 * @param Xerxes_Model_Search_Results $results
	 */	
	
	public function addFacetLinks( Xerxes_Model_Search_ResultSet &$results )
	{	
		// facets

		$facets = $results->getFacets();
		
		if ( $facets != "" )
		{
			foreach ( $facets->getGroups() as $group )
			{
				foreach ( $group->getFacets() as $facet )
				{
					// existing url
						
					$url = $this->currentParams();
							
					// now add the new one
							
					if ( $facet->key != "" ) 
					{
						// key defines a way to pass the (internal) value
						// in the param, while the name is the display value
						
						$url["facet." . $group->name . "." . 
							urlencode($facet->key)] = $facet->name;
					}
					else
					{
						$url["facet." . $group->name] = $facet->name;									
					}
							
					$facet->url = $this->request->url_for($url);
				}
			}
		}
	}
	
	/**
	 * Add links to the query object limits
	 * 
	 * @param Xerxes_Model_Search_Query $query
	 */
	
	public function addQueryLinks(Xerxes_Model_Search_Query $query)
	{
		// we have to pass in the query object here rather than take
		// the property above because adding the links doesn't seem
		// to reflect back in the main object, even though they should 
		// be references, maybe because limit objects are in an array?  
		
		// link to corrected spelling
		
		$spelling_corrected = $this->request->getParam("spelling_query");
		
		if ( $spelling_corrected != null )
		{
			$spell = array();
			$spell["url"] = $this->linkSpelling();
			$spell["text"] = $spelling_corrected;
			
			$query->spelling_url = $spell;
		}
		
		// search option links
		
		$search = $this->registry->getConfig('search');
		
		if ( $search instanceof SimpleXMLElement )
		{
			foreach ( $search->xpath("//option") as $option )
			{
				// format the number
				
				// is this the current tab?

				if ( $this->request->getParam('base') == (string) $option["id"] 
				     && ( $this->request->getParam('source') == (string) $option["source"] 
				     	|| (string) $option["source"] == '') )
				    {
				    	$option->addAttribute('current', "1");	
				    }
				
				// url
				
				$params = $query->extractSearchParams();
				
				$params['base'] = (string) $option["id"];
				$params['action'] = "results";
				$params['source'] = (string) $option["source"];
				
				$url = $this->request->url_for($params);
				
				$option->addAttribute('url', $url);
				
				// cached search hit count?
		
				foreach ( $this->request->getAllSession() as $session_id => $session_value )
				{
					// does this value in the cache have the save id as our tab?
					
					$id = str_replace("_" . $query->getHash(), "", $session_id);
					
					if ( $id == (string) $option["id"] )
					{
						// yup, so add it
						
						$option->addAttribute('hits', Xerxes_Framework_Parser::number_format($session_value));
					}
				}
			}
			
			$this->registry->setConfig('search', $search);
		}
		
		// links to remove facets
		
		foreach ( $query->getLimits() as $limit )
		{
			$url = new Xerxes_Framework_URL($this->currentParams());
			$url->removeParam($limit->field, $limit->value);
			$limit->remove_url = $this->request->url_for($url);
		}
	}
	
	/**
	 * Link for spelling correction
	 */
	
	public function linkSpelling()
	{
		$params = $this->currentParams();
		$params["query"] = $this->request->getProperty("spelling_query");
		
		return $this->request->url_for($params);
	}
	
	/**
	 * URL for the full record display
	 * 
	 * @param $result Xerxes_Record object
	 * @return string url
	 */
	
	public function linkFullRecord( Xerxes_Record $result )
	{
		$arrParams = array(
			"base" => $this->request->getProperty("base"),
			"action" => "record",
			"id" => $result->getRecordID()
		);
		
		return $this->request->url_for($arrParams);
	}
	
	/**
	 * URL for the full record display
	 * 
	 * @param Xerxes_Record $result
	 * @return string url
	 */
	
	public function linkSaveRecord( Xerxes_Record $result )
	{
		$arrParams = array(
			"base" => $this->request->getProperty("base"),
			"action" => "save",
			"id" => $result->getRecordID()
		);
		
		return $this->request->url_for($arrParams);
	}
	
	/**
	 * URL for the sms feature
	 * 
	 * @param Xerxes_Record $result
	 * @return string url
	 */	
	
	public function linkSMS(  Xerxes_Record $result )
	{
		$arrParams = array(
			"base" => $this->request->getProperty("base"),
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
	
	public function linkOther( Xerxes_Model_Search_Result $result )
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
		$params = $this->query->getAllSearchParams();
		$params["base"] = $this->request->getProperty("base");
		$params["action"] = $this->request->getProperty("action");
		$params["sort"] = $this->request->getProperty("sort");
		
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