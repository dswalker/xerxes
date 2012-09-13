<?php

namespace Xerxes;

use Zend\Http\Client;

/**
 * Summon Client
 * 
 * Based on the work of Andrew Nagy
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Summon
{
	protected $http_client; // zend http client
	protected $host; // hostname
	protected $api_key; // summon key
	protected $app_id; // summon application id
	protected $session_id; // current session
	protected $facets_to_include = array(); // facets that should be included in the response
	protected $date_ranges_to_include = ''; // date ranges that should be included in the response filters
	protected $facet_filters = array(); // filter on these facets
	protected $start_date = '*'; // start date in range filter
	protected $end_date = '*'; // end date in range filter
	protected $complex_filters = array(); // complex facet filters
	
	protected $role; // user's role: authenticated or not
	protected $holdings_only = false;
	
	/**
	 * Create a Summon Client
	 * 
	 * @param string $app_id		summon application id
	 * @param string $api_key		summon application key
	 * @param Client $client		[optional] HTTP client to use
	 */
	
	function __construct($app_id, $api_key, Client $client = null)
	{
		$this->host = 'http://api.summon.serialssolutions.com';
		$this->app_id = $app_id;
		$this->api_key = $api_key;
		
		if ( $client != null )
		{
			$this->http_client = $client;
		}
		else 
		{
			$this->http_client = new Client();
		}
	}
	
	/**
	 * Retrieves a document specified by the ID.
	 *
	 * @param string  $id         The document to retrieve from the Summon API
	 * 
	 * @return array
	 */
	
	public function getRecord($id)
	{
		$options = array('s.q' => "id:$id");
		
		// user role
		
		if ( $this->role != "")
		{
			$options['s.role'] = $this->role;
		}		
		
		return $this->send($options);
	}
	
	/**
	 * Execute a search
	 * 
	 * @param string $query		search query in summon syntax
	 * @param array $this->facet_filter		[optional] filters to apply
	 * @param int $page			[optional] page number to start with
	 * @param int $limit		[optional] total records per page
	 * @param string $sortBy	[optional] sort restlts on this index
	 * 
	 * @return array
	 */
	
	public function query( $query, $page = 1, $limit = 20, $sortBy = null )
	{
		// convert this to summon query string
		
		$options = array();
		
		// search query
		
		if ( $query != '' )
		{
			$options['s.q'] = $query;
		}
		
		// user role
		
		if ( $this->role != "")
		{
			$options['s.role'] = $this->role;
		}
		
		// holdings only
		
		if ( $this->holdings_only == true)
		{
			$options['s.ho'] = 'true';
		}		
		
		// filters to be applied
		
		if ( count($this->facet_filters) > 0 )
		{
			$options['s.fvf'] = $this->facet_filters;
		}
		
		// complex filters to be applied
		
		if ( count($this->complex_filters) > 0 )
		{
			$options['s.fvgf'] = $this->complex_filters;
		}

		// date range filters to be applied
		
		if ( $this->start_date != '*' || $this->end_date != '*' )
		{
			$options['s.rf'] = 'PublicationDate,' . $this->start_date . ":" . $this->end_date;
		}		
		
		// sort
		
		if ( $sortBy != "" )
		{
			$options['s.sort'] = $sortBy;
		}
		
		// paging
		
		$options['s.ps'] = $limit;
		$options['s.pn'] = $page;
		
		// facets to return in response
		
		$options['s.ff'] = $this->getFacetsToInclude();
		
		// date groupings to return in response
		
		if ( $this->date_ranges_to_include != '' )
		{
			$options['s.rff'] = 'PublicationDate,' . $this->date_ranges_to_include;
		}		
		
		return $this->send($options);
	}
	
	/**
	 * Limit response to library holdings
	 * 
	 * @param bool $bool
	 */
	
	public function limitToHoldings($bool = true)
	{
		if ( $bool === true )
		{
			$this->holdings_only = true;
		}
		else
		{
			$this->holdings_only = false;
		}
	}
	
	/**
	 * Spell check
	 * 
	 * @param string $query
	 */
	
	public function checkSpelling($query)
	{
		$options = array();
		
		// spell check
		
		$options['s.dym'] = 'true';
		$options['s.ps'] = 0;
		$options['s.pn'] = 1;
		
		if ( $query != '' )
		{
			$options['s.q'] = $query;
		}
		
		$results = $this->send($options);
		
		// if we got one, return it
		
		if ( array_key_exists('didYouMeanSuggestions', $results) )
		{
			if ( array_key_exists(0, $results['didYouMeanSuggestions']) )
			{
				$suggestion = $results['didYouMeanSuggestions'][0]['suggestedQuery'];
				return urldecode($suggestion);
			}
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Submit search
	 *
	 * @param array $params			An array of parameters for the request
	 * @param string $service    	The api service to call
	 * 
	 * @return array
	 */
	
	private function send( array $params, $service = '2.0.0/search' )
	{
		// build querystring
		
		$query = array();
		
		foreach ( $params as $function => $value )
		{
			if ( is_array($value) )
			{
				foreach ( $value as $additional )
				{
					$additional = urlencode($additional);
					$query[] = "$function=$additional";
				}
			}
			else
			{
				$value = urlencode($value);
				$query[] = "$function=$value";
			}
		}
		
		asort($query);
		$queryString = implode('&', $query);
		
		// set the url

		$this->http_client->setUri($this->host . "/$service?" . $queryString);		
		
		// main headers
		
		$headers = array(
			'Accept' => 'application/json' , 
			'x-summon-date' => date('D, d M Y H:i:s T') , 
			'Host' => 'api.summon.serialssolutions.com'
		);
		
		// set auth header based on hash
		
		$data = implode($headers, "\n") . "\n/$service\n" . urldecode($queryString) . "\n";
		$hmacHash = $this->hmacsha1($this->api_key, $data);
		
		$headers["Authorization"] = "Summon " . $this->app_id . ";" . $hmacHash;
		
		// set them all
		
		$this->http_client->setHeaders($headers);
		
		// keep the same session id
		
		if ( $this->session_id )
		{
			$this->http_client->setHeaders('x-summon-session-id', $this->session_id);
		}
		
		// send the request
		
		$response = $this->http_client->send();
		
		// decode the response into array
		
		return json_decode($response->getBody(), true);
	}
	
	/**
	 * Create the auth hash
	 * 
	 * @param string $key		summon application key
	 * @param string $data		header data
	 */
	
	private function hmacsha1($key, $data)
	{
		$blocksize = 64;
		$hashfunc = 'sha1';
		if ( strlen($key) > $blocksize )
		{
			$key = pack('H*', $hashfunc($key));
		}
		$key = str_pad($key, $blocksize, chr(0x00));
		$ipad = str_repeat(chr(0x36), $blocksize);
		$opad = str_repeat(chr(0x5c), $blocksize);
		$hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $data))));
		return base64_encode($hmac);
	}
	
	/**
	 * Add a facet to those that should be returned in the response
	 * 
	 * @param string $facet
	 */
	
	public function includeFacet($facet)
	{
		$this->facets_to_include[] = $facet;
	}
	
	/**
	 * Get the facets to be included in the response
	 * 
	 * @return array
	 */
	
	public function getFacetsToInclude()
	{
		return $this->facets_to_include;
	}
	
	/**
	 * set the date ranges to include in facet response
	 *
	 * @param string $ranges
	 */
	
	public function setDateRangesToInclude($ranges)
	{
		$this->date_ranges_to_include = $ranges;
	}	
	
	/**
	 * Facet to filter on
	 *
	 * @param string $filter
	 */
	
	public function addFilter($filter)
	{
		$this->facet_filters[] = $filter;
	}
	
	/**
	 * Complex facet to filter on
	 *
	 * @param string $filter
	 */
	
	public function addComplexFilter($filter)
	{
		$this->complex_filters[] = $filter;
	}
	
	/**
	 * Start date to filter on
	 *
	 * @param string $filter
	 */	
	
	public function setStartDate($date)
	{
		$this->start_date = $date;
	}

	/**
	 * End date to filter on
	 *
	 * @param string $filter
	 */
	
	public function setEndDate($date)
	{
		$this->end_date = $date;
	}	
	
	/**
	 * Tell Summon user is authenticated
	 */
	
	public function setToAuthenticated()
	{
		$this->role = 'authenticated';
	}
}
