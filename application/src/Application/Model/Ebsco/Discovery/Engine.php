<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Ebsco\Discovery;

use Application\Model\Search;
use Application\Model\Search\ResultSet;
use Xerxes\Utility\HttpClient;
use Xerxes\Utility\Json;
use Xerxes\Utility\Factory;
use Xerxes\Utility\Parser;
use Xerxes\Mvc\Request;

/**
 * Summon Search Engine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Search\Engine 
{
	/**
	 * @var string
	 */
	protected $base;
	
	/**
	 * @var HttpClient
	 */
	protected $client;
	
	/**
	 * @var string
	 */
	protected $session_id;
	
	/**
	 * @var array
	 */
	protected $headers;
	
	/**
	 * New EDS Engine
	 * 
	 * @param $session  session information
	 */
	
	public function __construct( $session = "" )
	{
		parent::__construct();
		
		$profile = 'edsapi';
		
		$this->base = 'http://eds-api.ebscohost.com/edsapi/rest/';
		
		$this->client = new HttpClient();
		
		if ( $session == "" )
		{
			$this->session_id = $this->createSession($profile);
		}
		else
		{
			$this->session_id = $session;
		}
		
		$this->headers =  array(
			'Accept' => 'application/json',
			'x-sessionToken' => $this->session_id
		);
	}

	/**
	 * Search and return results
	 * 
	 * @param Query $search  search object
	 * @param int $start     [optional] starting record number
	 * @param int $max       [optional] max records
	 * @param string $sort   [optional] sort order
	 * @param bool $facets   [optional] whether to include facets
	 * 
	 * @return Results
	 */	
	
	public function searchRetrieve( Search\Query $search, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		// get results
		
		$results = parent::searchRetrieve($search, $start, $max, $sort, $facets);
		
		// enhance
		
		if ( $this->config->getConfig('mark_fulltext_using_export', false, false ) )
		{
			$results->markFullText(); // sfx data
		}
		
		return $results;
	}	
	
	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */
	
	public function getRecord( $id )
	{
		// get result
		
		$results = parent::getRecord( $id );
		
		// enhance
		
		$results->getRecord(0)->addRecommendations(); // bx
		
		if ( $this->config->getConfig('mark_fulltext_using_export', false, false ) )
		{
			$results->markFullText(); // sfx data
		}
		
		return $results;
	}

	/**
	 * Do the actual fetch of an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */	
	
	protected function doGetRecord( $id )
	{
		if ( $id == "" )
		{
			throw new \DomainException('No record ID supplied');
		}
		
		$database = Parser::removeRight($id,"-");
		$id = Parser::removeLeft($id,"-");
		
		// build request
		
		$url = $this->base . 'retrieve?';
		$url .= 'dbid=' . $database;
		$url .= '&an=' . urlencode($id);
		$url .= '&includefacets=n';
		
		// get the data
		
		$response = $this->client->getUrl($url, null, $this->headers);
		$json = new Json($response);
		
		// create the result set
		
		$result_set = new ResultSet($this->config);
		
		$xerxes_record = new Record();
		$xerxes_record->load($json->extractData('Record'));
		$result_set->addRecord($xerxes_record);
		
		return $result_set;
	}		
	
	/**
	 * Do the actual search and return results
	 *
	 * @param Query $search  search object
	 * @param int $start     [optional] starting record number
	 * @param int $max       [optional] max records
	 * @param string $sort   [optional] sort order
	 * @param bool $facets   [optional] whether to include facets
	 *
	 * @return Results
	 */	
	
	protected function doSearch( Search\Query $search, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		// limit to local users?
		
		if ( $search->getUser()->isAuthorized() )
		{
			
		}
		
		// prepare the query
		
		$query = $search->toQuery();
		
		// limit to local holdings unless told otherwise
		
		if ( $this->config->getConfig('LIMIT_TO_HOLDINGS', false) )
		{
			
		}

		// format filters
		
		// newspapers are a special case, i.e., they can be optional
		
		if ( $this->config->getConfig('NEWSPAPERS_OPTIONAL', false) )
		{
			
		}

		// EDS deals in pages, not start record number
		
		if ( $max > 0 )
		{
			$page = ceil ($start / $max);
		}
		else
		{
			$page = 1;
		}
		
		// get the results
		
		$url = $this->base . 'Search?';
		$url .= $query;
		$url .= '&view=detailed';
		$url .= '&resultsperpage=' . $max;
		$url .= '&pagenumber=' . $page;
		$url .= '&sort=' . $sort;
		$url .= '&searchmode=all';
		$url .= '&highlight=n';
		$url .= '&includefacets=y';
		
		$response = $this->client->getUrl($url, null, $this->headers);
		
		return $this->parseResponse($response);
	}
	
	/**
	 * Parse the summon response
	 *
	 * @param string $response  eds results response
	 * @return ResultSet
	 */
	
	protected function parseResponse($response)
	{
		$json = new Json($response);
		
		// just an error, so throw it
		// @todo throw an error
		
		// results
		
		$result_set = new ResultSet($this->config);
		
		
		// total
		
		$total = $json->extractValue('SearchResult/Statistics/TotalHits');
		$result_set->total = $total;
		
		// extract records
		
		foreach ( $this->extractRecords($json) as $xerxes_record )
		{
			$result_set->addRecord($xerxes_record);
		}
		
		// extract facets

		$facets = $this->extractFacets($json);
		$result_set->setFacets($facets);
		
		return $result_set;
	}

	/**
	 * Parse records out of the response
	 *
	 * @param Json $json
	 * @return Record[]
	 */
	
	protected function extractRecords(Json $json)
	{
		$records = array();
		
		foreach ( $json->extractData('SearchResult/Data/Records') as $document )
		{
			$xerxes_record = new Record();
			$xerxes_record->load($document);
			array_push($records, $xerxes_record);
		}
		
		return $records;
	}
	
	/**
	 * Parse facets out of the response
	 *
	 * @param Json $json
	 * @return Facets
	 */	
	
	protected function extractFacets(Json $json)
	{
		if ( array_key_exists('debug', $_GET) )
		{
			print_r($json); exit;
		}
		
		$facets = new Search\Facets();
		
		$facet_fields = $json->extractData('SearchResult/AvailableFacets');
		
		// @todo: figure out how to factor out some of this to parent class
			
		// take them in the order defined in config
				
		foreach ( $this->config->getFacets() as $group_internal_name => $config )
		{
			foreach ( $facet_fields as $facetFields )
			{
				if ( $facetFields["Id"] == $group_internal_name)
				{
					$group = new Search\FacetGroup();
					$group->name = $facetFields['Id'];
					$group->public = $facetFields['Label'];
					
					if ( $config['display'] == 'false' )
					{
						$group->display = 'false';
					}
						
					$facets->addGroup($group);
					
					foreach ( $facetFields['AvailableFacetValues'] as $counts )
					{
						$facet = new Search\Facet();
						$facet->name = $counts["Value"];
						$facet->count = $counts["Count"];
						
						$group->addFacet($facet);
					}
				}
			}
		}
		
		return $facets;
	}
	
	/**
	 * Establish a new session with EDS
	 * 
	 * @param string $profile
	 * @return string
	 */
	
	public function createSession($profile)
	{
		$url = $this->base . 'createsession?profile=' . urlencode($profile);
	
		$xml = $this->client->getUrl($url, 10);
	
		$dom = new \DOMDocument();
		$dom->loadXML($xml);
		
		// header('Content-type: text/xml'); echo $dom->saveXML(); exit;
	
		$session_id = $dom->getElementsByTagName('SessionToken')->item(0)->nodeValue;
	
		return $session_id;
	}
	
	/**
	 * Session identifier
	 * 
	 * @return string
	 */
	
	public function getSession()
	{
		return $this->session_id;
	}
	
	/**
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}	
	
	/**
	 * Return the Summon search query object
	 *
	 * @param Request $request
	 * @return Query
	 */
	
	public function getQuery(Request $request )
	{
		if ( $this->query instanceof Query )
		{
			return $this->query;
		}
		else
		{
			return new Query($request, $this->getConfig());
		}
	}
}
