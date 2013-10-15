<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\View\Helper;

use Application\Model\Search\Config;
use Application\Model\Search\Engine;
use Application\Model\Search\Result;
use Application\Model\Search\ResultSet;
use Application\Model\Search\Query;
use Application\Model\Search\Spelling\Suggestion;
use Xerxes\Record;
use Xerxes\Record\Author;
use Xerxes\Record\Bibliographic\LinkedItem;
use Xerxes\Record\Subject;
use Xerxes\Utility\Parser;
use Xerxes\Mvc\MvcEvent;
use Xerxes\Mvc\Request;
use Xerxes\Utility\Labels;
use Xerxes\Utility\Registry;

/**
 * View helper for search results
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Search
{
	/**
	 * @var string
	 */
	protected $id;
	
	/**
	 * @var Query
	 */
	protected $query;
	
	/**
	 * @var Config
	 */
	protected $config;
	
	/**
	 * @var Request
	 */
	protected $request;
	
	/**
	 * @var Registry
	 */
	protected $registry;
	
	/**
	 * @var Labels
	 */
	protected $labels;
	
	/**
	 * Create new Search Helper
	 * 
	 * @param MvcEvent $e
	 * @param string $id
	 * @param Engine $engine
	 */
	
	public function __construct(MvcEvent $e, $id, Engine $engine)
	{
		$this->request = $e->getRequest();
		$this->registry = Registry::getInstance();
		
		$this->id = $id;
		$this->query = $engine->getQuery($this->request);
		$this->config = $engine->getConfig();
		
		$this->labels = $e->getLabels();
	}
	
	/**
	 * Displays paged information (e.g., 11-20 of 34 results)
	 *
	 * @param int $total  total # of hits for query
	 * @param int $start  start value for the page
	 * @param int $max    maximum number of results to show
	 *
	 * @return array or null  summary of page results 
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
		
		// weird boundary case introduced by ebsco hacks
		
		if ( $start > $stop )
		{
			$start = $stop - $max;
		}
		
		return array ( 
			"range" => "$start-$stop",
			"total" => Parser::number_format( $total )
		);
	}
	
	/**
	 * Paging element
	 * 
	 * @param int $total  total # of hits for query
	 * @param int $start  start value for the page
	 * @param int $max    maximum number of results to show
	 * 
	 * @return DOMDocument  formatted paging navigation
	 */
	
	public function pager( $total, $start, $max )
	{
		if ( $total < 1 )
		{
			return null;
		}
		
		$xml = Parser::convertToDOMDocument( "<pager />" );
		
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
				$page = $xml->createElement( "page", "1" );
				
				$params = $this->currentParams();
				$params["start"] = 1;
				
				$link = $this->request->url_for( $params );
				
				$page->setAttribute( "link", Parser::escapeXml( $link ) );
				$page->setAttribute( "type", "first" );
				$xml->documentElement->appendChild( $page );
			}
			
			// create pages and links
			
			while ( $base_record <= $total )
			{
				if ( $page_number >= $bottom_range && $page_number <= $top_range )
				{
					
					$page = $xml->createElement( "page", $page_number );
						
					if ( $current_page == $page_number )
					{	
						$page->setAttribute( "here", "true" );
					} 

					$params = $this->currentParams();
					$params["start"] = $base_record;
					
					$link = $this->request->url_for( $params );
					
					$page->setAttribute( "link", Parser::escapeXml( $link ) );
					$xml->documentElement->appendChild( $page );
				}
				
				$page_number++;
				$base_record += $max;
			}
			
			$next = $start + $max;
			
			if ( $next <= $total )
			{
				$page = $xml->createElement( "page", "" ); // element to hold the text_results_next label
				
				$params = $this->currentParams();
				$params["start"] =  $next;
				
				$link = $this->request->url_for( $params );
				
				$page->setAttribute( "link", Parser::escapeXml( $link ) );
				$page->setAttribute( "type", "next" );
				$xml->documentElement->appendChild( $page );
			}
		}
		
		return $xml;
	}
	
	/**
	 * Creates a sorting page element
	 *
	 * @param string $sort  current sort
	 *
	 * @return DOMDocument  sort navigation
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
		
		foreach ( $sort_options as $id )
		{
			$here = $xml->createElement('option');
			$here->setAttribute('id', $id);
			
			if ( $id == $sort )
			{
				$here->setAttribute( 'active', 'true' );
			} 
			else
			{
				$params = $this->sortLinkParams();
				$params['sort'] = $id;
				
				$here->setAttribute('active', 'false' );
				$here->setAttribute('link', $this->request->url_for($params) );
			}
			
			$xml->documentElement->appendChild( $here );
			
			$x++;
		}
		
		return $xml;
	}
	
	
	######################
	#   LINKS & LABELS   #
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
			
			$this->addBibRecordLinks($xerxes_record);

			foreach ( $xerxes_record->getSucceedingTitles() as $title )
			{
				$title->url = $this->linkRelatedTitle($title);
			}			
			
			// full-record link
			
			$result->url = $this->linkFullRecord($xerxes_record);
			$result->url_full = $result->url; // backwards compatibility
				
			// sms link
			
			$result->url_sms = $this->linkSMS($xerxes_record);
				
			// save or delete link
			
			$result->url_save = $this->linkSaveRecord($xerxes_record);
			$result->url_save_delete = $result->url_save; // backwards compatibility
			
			// openurl redirect
			
			$result->url_open_redirect = $this->linkOpenURL($xerxes_record);
			
			// other links
			
			$this->linkOther($result);
		}
	}
	
	/**
	 * Add links for author, subject, related titles
	 * 
	 * @param Record $xerxes_record
	 */
	
	public function addBibRecordLinks(Record $xerxes_record )
	{
		// author links
			
		foreach ( $xerxes_record->getAuthors() as $author )
		{
			$author->url = $this->linkAuthor($author);
			$author->url_title = $this->linkAuthorTitle($author);
		}
			
		// subject links
			
		foreach ( $xerxes_record->getSubjects() as $subject )
		{
			$subject->url = $this->linkSubject($subject);
		}
		
		// related titles link
			
		foreach ( $xerxes_record->getPrecedingTitles() as $title )
		{
			$title->url = $this->linkRelatedTitle($title);
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
			$group_id = 0;
			
			foreach ( $facets->getGroups() as $group )
			{
				// this is used for javascript selecting
				
				$group_id++;
				$facet_id = 0;
				
				// group identifiers
				
				$group->group_id = 'facet-' . $group_id;
				$group->param_name = 'facet.' . $group->name;
				
				// link to multi-select facet page
				
				$group_params = $this->query->getAllSearchParams();
				$group_params['controller'] = $this->request->getParam('controller');
				$group_params['action'] = 'facet';
				$group_params['group'] = $group->param_name;
				
				$group->url = $this->request->url_for($group_params);
				
				// print_r($group->getFacets());
				
				foreach ( $group->getFacets() as $facet )
				{
					$facet_id++;
					
					$param_name = Query::getParamFromParts($group->name, urlencode($facet->key), $facet->is_excluded);
					
					// link
					
					$url = $this->facetParams();
					
					if ( $facet->is_excluded == true )
					{
						// selecting this option removes our exclude param
						
						foreach ( $url as $key => $value )
						{
							if ( $key == $param_name)
							{
								// if we have multiple values, only remove the matching one
								
								if ( is_array($value) )
								{
									$new_array = array();
									
									foreach ( $value as $entry )
									{
										if ( $entry != $facet->name )
										{
											$new_array[] = $entry;
										}
									}
									
									$url[$key] = $new_array;
								}
								else
								{
									$url[$key] = '';
								}
							}
						}
					}
					
					else
					{
						// selecting this option adds our param
					
						$url[$param_name] = $facet->name;
					}
					
					$facet->url = $this->request->url_for($url);

					// facet identifiers
					
					$facet->input_id = $group->group_id  . '-' . $facet_id;
					
					// add the name of the param as well
					
					$facet->param_name = $param_name;
					
					// see if this facet is selected (for multi-select facets)
					
					if ( $this->request->hasParamValue($param_name, $facet->name) )
					{
						$facet->selected = true;
					}
					
					// exclude facet param
					
					$facet->param_exclude = str_replace('facet.', 'facet.remove.', $param_name);				
				}
			}
		}
	}
	
	/**
	 * Add labels to search config
	 * 
	 * @param Config $config
	 */
	
	public function addSearchLabels(Config $config)
	{
		$xml = $config->getXML();
		
		$entries = $xml->xpath("//*[@label != '']"); // any config entry that has a label attribute
		
		if ( $entries !== false )
		{
			foreach ( $entries as $entry )
			{
				$entry['public'] = $this->labels->getLabel((string) $entry['label']);
			}
		}
	}
	
	/**
	 * Helper function for XSLT to supress hidden limit inputs for a specific facet
	 *
	 * @param string $field     name of field to check if it is excluded
	 * @param string $excluded  comma delimited list of fields to be exlcuded
	 *
	 * @return bool  false if the field is in the exlcude list
	 */
	
	public static function shouldIncludeLimit($field, $excluded)
	{
		// if this is our three-part field, then take only the base
		
		$field_parts = explode('.', $field);
		
		if ( count($field_parts) > 2 )
		{
			array_pop($field_parts);
			
			$field = implode('.', $field_parts );
		}
		
		// compare it to excluded fields
		
		$exclude_array = explode(',', $excluded);
	
		foreach ( $exclude_array as $exclude )
		{
			if ( $field == $exclude )
			{
				return false;
			}
		}
	
		return true;
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
			
			// combined results
			
			$combined  = $controller_map->getUrlAlias('combined');
			$combined_id = $combined . '_' . $query->getHash();
			
			if ( $this->request->getSessionData($combined_id) != null )
			{
				$params = $query->extractSearchParams();
					
				$params['controller'] = $combined;
				$params['action'] = "results";
					
				$search->combined_url = $this->request->url_for($params);
			}
			
			// individual search options
			
			foreach ( $search->xpath("//option") as $option )
			{
				$id = (string) $option["id"];
				
				if ( (string) $option["source"] != '' )
				{
					$id .=  '_' . (string) $option["source"];
				}
				
				$id .=  '_' . $this->query->getHash();
				
				// format the number
				
				// is this the current tab?

				if ( $this->request->getControllerName() == (string) $option["id"] 
				     && ( $this->request->getParam('source') == (string) $option["source"] 
				     	|| (string) $option["source"] == '') )
				{
				   	// mark as current
					
					$option->addAttribute('current', "1");
				}
				
				// create url based on the search terms only!

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
			
			// header('Content-type: text/xml'); echo $search->asXML(); exit;
			
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
	
	/**
	 * Add link to Suggestion object
	 * 
	 * @param Suggestion $spelling
	 */
	
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
	 * @param Record $record
	 * @return string url
	 */
	
	public function linkFullRecord( Record $record )
	{
		$arrParams = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'record',
			'id' => $record->getRecordID()
		);
		
		return $this->request->url_for($arrParams);
	}
	
	/**
	 * URL for Open URL
	 *
	 * @param Record $record
	 * @return string url
	 */
	
	public function linkOpenUrl( Record $record )
	{
		$arrParams = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'openurl',
			'id' => $record->getRecordID()
		);
	
		return $this->request->url_for($arrParams);
	}	
	
	/**
	 * URL for save/delete action
	 * 
	 * @param Record $record
	 * @return string url
	 */
	
	public function linkSaveRecord( Record $record )
	{
		$arrParams = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'save',
			'id' => $record->getRecordID()
		);
		
		return $this->request->url_for($arrParams);
	}
	
	/**
	 * URL for the sms feature
	 * 
	 * @param Record $record
	 * @return string url
	 */	
	
	public function linkSMS( Record $record )
	{
		$arrParams = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'sms',
			'id' => $record->getRecordID()
		);
		
		return $this->request->url_for($arrParams);	
	}
	
	/**
	 * URL for author
	 *
	 * @param Author $author
	 * @return string url
	 */
	
	public function linkAuthor( Author $author )
	{
		$arrParams = $this->lateralLink();
		$arrParams['field'] = 'author';
		
		// we've defined a specific searchable string for this author,
		// so take that instead and make it quoted for exactness
		
		if ( $author->search_string != "" )
		{
			$arrParams['query'] = '"' . $author->search_string . '"';
		}
		else // just the regular author name, no quotes
		{		
			$arrParams['query'] = $author->getName();
		}

		return $this->request->url_for($arrParams);
	}
	
	/**
	 * URL for author/title
	 *
	 * @param Author $author
	 * @return string url
	 */
	
	public function linkAuthorTitle( Author $author )
	{
		if ( $author->title != '')
		{
			$arrParams = $this->lateralLink();
			$arrParams['field'] = 'title';
			$arrParams['query'] = $author->title;
		
			return $this->request->url_for($arrParams);
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * URL for related title
	 *
	 * @param LinkedItem $item
	 * @return string url
	 */
	
	public function linkRelatedTitle( LinkedItem $item )
	{
		if ( $item->title != '')
		{
			$arrParams = $this->lateralLink();
			$arrParams['field'] = 'title';
			$arrParams['query'] = $item->title;
	
			return $this->request->url_for($arrParams);
		}
		else
		{
			return null;
		}
	}	
	
	/**
	 * URL for Subject
	 *
	 * @param Subject $subject
	 * @return string url
	 */
	
	public function linkSubject( Subject $subject )
	{
		$arrParams = $this->lateralLink();
		$arrParams['field'] = 'subject';
		$arrParams['query'] = $subject->value;
	
		return $this->request->url_for($arrParams);
	}
	
	/**
	 * Lateral link base
	 * 
	 * @return array
	 */
	
	public function lateralLink()
	{
		return array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'search'
		);
	}

	/**
	 * Other links for the record beyond those supplied by the framework
	 * 
	 * @param Result $result 
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
	 * 
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
	 * 
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
	 * 
	 * @return array
	 */
	
	public function searchRedirectParams()
	{
		$params['controller'] = $this->request->getParam('controller');
		$params['action'] = "results";
		$params['sort'] = $this->request->getParam('sort');
		
		return $params;
	}
	
	/**
	 * Parameters to construct the links for the paging element
	 * 
	 * @return array
	 */
	
	public function pagerLinkParams()
	{
		$params = $this->currentParams();
		return $params;
	}
	
	/**
	 * Parameters to construct the links for the sort
	 * 
	 * @return array
	 */
	
	public function sortLinkParams()
	{
		$params = $this->currentParams();
		
		unset($params["sort"]); // remove the current sort
		unset($params["start"]); // send us back to the first page
		
		return $params;
	}
	
	/**
	 * Return identifier for this query = search ID + query hash
	 * 
	 * @return string
	 */
	
	public function getQueryID()
	{
		return $this->id . "_" . $this->query->getHash();
	}	
	
}
