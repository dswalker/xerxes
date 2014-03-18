<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Summon;

use Application\Model\Search;
use Application\Model\Search\Query\Url;
use Xerxes\Mvc\Request;

/**
 * Summon Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * hostname
	 * @var string
	 */
	protected $host;
	
	/**
	 * summon key
	 * @var unknown_type
	 */
	protected $api_key;
	
	/**
	 * summon application id
	 * @var string
	 */
	protected $app_id;
	
	/**
	 * role
	 * @var string
	 */
	protected $role;
	
	/**
	 * current session
	 * @var string
	 */
	protected $session_id;
	
	/**
	 * facets that should be included in the response
	 * @var array
	 */
	protected $facets_to_include = array();
	
	/**
	 * date ranges that should be included in the response filters
	 * @var string
	 */
	protected $date_ranges_to_include;
	
	/**
	 * filter on these facets
	 * @var array
	 */
	protected $facet_filters = array();
	
	/**
	 * start date in range filter
	 * @var string
	 */
	protected $start_date = '*';
	
	/**
	 * end date in range filter
	 * @var string
	 */
	protected $end_date = '*';
	
	/**
	 * complex facet filters
	 * @var unknown_type
	 */
	protected $complex_filters = array();
	
	/**
	 * limit to library's holdings
	 * @var boolean
	 */
	protected $holdings_only = false;
	
	/**
	 * language
	 * @var string
	 */
	protected $lang = 'en';
	
	/**
	 * expand query
	 * @var boolean
	 */
	protected $query_expansion = false;
	
	/**
	 * formats configured to exclude
	 * @var array
	 */
	protected $formats_exclude = array();
	
	/**
	 * Summon query
	 * 
	 * @param Request $request
	 * @param Config $config
	 */
	
	public function __construct(Request $request = null, Config $config = null )
	{
		parent::__construct($request, $config);
		
		$this->host = 'http://api.summon.serialssolutions.com';
		$this->app_id = $this->config->getConfig("SUMMON_ID", true);
		$this->api_key = $this->config->getConfig("SUMMON_KEY", true);
		
		// limit to local users?
		
		if ( $this->getUser()->isAuthorized() )
		{
			$this->role = 'authenticated';
		}
		
		$this->formats_exclude = explode(',', $this->config->getConfig("EXCLUDE_FORMATS"));
	}
	
	/**
	 * Convert to Summon individual record syntax
	 *
	 * @param string $id
	 * @return Url
	 */
	
	public function getRecordUrl($id)
	{
		$options = array('s.q' => "id:$id");
		
		// always set to authenticated if you know the id
		
		$options['s.role'] = 'authenticated';
		
		return $this->createUrl($options);
	}
	
	/**
	 * Convert to Summon query syntax
	 * 
	 *  @return Url
	 */
	
	public function getQueryUrl()
	{
		### prepare the query
		
		$query = "";
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$query .= " " . $term->boolean;

			// is this a fielded search?
			
			if ( $term->field_internal != "" ) // yes
			{
				$query .= " " . $term->field_internal . ':(' . $this->escape($term->phrase) . ')';

			}
			else // keyword
			{
				$query .= " " . $term->phrase;
			}
		}
		
		$query = trim($query);
		
		### prepare the limits
		
		// facets to include in the response
		
		foreach ( $this->config->getFacets() as $facet_config )
		{
			if ( $facet_config['type'] == 'date' )
			{
				// create date ranges in groups defined in config
		
				$range_start = 1950; // start of the range
				$interval = 2; // year intervals
		
				if ( array_key_exists('start', $facet_config) )
				{
					$range_start = $facet_config["start"];
				}
		
				if ( array_key_exists('interval', $facet_config) )
				{
					$interval = $facet_config["interval"];
				}
		
				$range_stop = (int) date('Y', time()); // current year
				$range = array(); // hold them in groups
					
				while ( $range_start < $range_stop )
				{
					$range[] = $range_start . ':' . ($range_start + $interval);
					$range_start = $range_start + $interval + 1;
				}
		
				$range_string = implode(',', $range);
				
				
				$this->date_ranges_to_include = $range_string;
			}
				
			else
			{
				$this->facets_to_include[] = (string) $facet_config["internal"] .",or,1," . (string) $facet_config["max"];
			}
		}
		
		// limit to local holdings unless told otherwise
		
		if ( $this->config->getConfig('LIMIT_TO_HOLDINGS', false) )
		{
			$this->holdings_only = true;
		}
		
		// query expansion
		
		$this->query_expansion = (bool) $this->config->getConfig('QUERY_EXPANSION', false, false);
		
		// limits
		
		foreach ( $this->getLimits() as $limit )
		{
			if ( $limit->field == 'newspapers' )
			{
				continue; // we'll handle you later
			}
				
			// query expansion overriden by user
				
			if ( $limit->field == 'qe' )
			{
				if ( $limit->value == 0 )
				{
					$this->query_expansion = false;
				}
				elseif ( $limit->value == 1 )
				{
					$this->query_expansion = true;
				}
		
				continue;
			}
				
			// holdings only
				
			if ( $limit->field == 'holdings' )
			{
				if ( $limit->value == 'false')
				{
					// this is actually an expander to search everything
						
					$this->holdings_only = false;
				}
				else
				{
					$this->holdings_only = true;
				}
			}
				
			// date type
				
			elseif ( $this->config->getFacetType($limit->field) == 'date' )
			{
				// @todo: make this not 'display'
		
				if ( $limit->value == 'start' && $limit->display != '')
				{
					$this->start_date = $limit->display;
				}
				elseif ( $limit->value == 'end' && $limit->display != '')
				{
					$this->end_date = $limit->display;
				}
			}
				
			// regular type
				
			else
			{
				$value = '';
				$boolean = 'false';
					
				if ( $limit->boolean == "NOT" )
				{
					$boolean = 'true';
				}
		
				// multi-select filter
					
				if ( is_array($limit->value) )
				{
					// exclude
						
					if ( $boolean == 'true' )
					{
						foreach ( $limit->value as $limited )
						{
							$value = str_replace(',', '\,', $limited) ;
							$this->facet_filters[] = $limit->field . ",$value,$boolean";
						}
					}
						
					// inlcude
						
					else
					{
						foreach ( $limit->value as $limited )
						{
							$value .= ',' . str_replace(',', '\,', $limited);
						}
							
						$this->complex_filters[] = $limit->field . ',' . $boolean . $value;
					}
				}
		
				// regular filter
		
				else
				{
					$value = str_replace(',', '\,', $limit->value);
					$this->facet_filters[] = $limit->field . ",$value,$boolean";
				}
			}
		}
		
		// format filters
		
		// newspapers are a special case, i.e., they can be optional
		
		if ( $this->config->getConfig('NEWSPAPERS_OPTIONAL', false) )
		{
			$news_limit = $this->getLimit('newspapers');
				
			if ( $news_limit->value != 'true' )
			{
				$this->formats_exclude[] = 'Newspaper Article';
			}
		}
		
		// always exclude these
		
		foreach ( $this->formats_exclude as $format )
		{
			$this->facet_filters[] = "ContentType,$format,true";
		}
		
		// summon deals in pages, not start record number
		
		$page = 1;
		
		if ( $this->max > 0 )
		{
			$page = ceil ($this->start / $this->max);
		}
		
		// language
		
		$this->lang = $this->getLanguage();
		

		#### convert options to summon query string
		
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
		
		// language
		
		$options['s.l'] = $this->lang;
		
		// sort
		
		if ( $this->sort != "" )
		{
			$options['s.sort'] = $this->sort;
		}
		
		// paging
		
		$options['s.ps'] = $this->max;
		$options['s.pn'] = $page;
		
		// facets to return in response
		
		$options['s.ff'] = $this->facets_to_include;
		
		// lighten the response to make things load faster
		
		$options['s.light'] = 'true';
		
		// query expansion
		
		if ( $this->query_expansion == true )
		{
			$options['s.exp'] = 'true';
		}
		
		// date groupings to return in response
		
		if ( $this->date_ranges_to_include != '' )
		{
			$options['s.rff'] = 'PublicationDate,' . $this->date_ranges_to_include;
		}
		
		return $this->createUrl($options);
	}
	
	/**
	 * Package up the request and return url with auth headers
	 * 
	 * @param array $params
	 * @return Url
	 */
	
	protected function createUrl(array $params)
	{
		$service = '2.0.0/search';
		
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
		$query_string = implode('&', $query);
		
		// main headers
		
		$headers = array(
			'Accept' => 'application/json' ,
			'x-summon-date' => date('D, d M Y H:i:s T') ,
			'Host' => 'api.summon.serialssolutions.com'
		);
		
		// set auth header based on hash
		
		$data = implode($headers, "\n") . "\n/$service\n" . urldecode($query_string) . "\n";
		$hmacHash = $this->hmacsha1($this->api_key, $data);
		
		$headers['Authorization'] = "Summon " . $this->app_id . ";" . $hmacHash;
			
		// keep the same session id
		
		if ( $this->session_id )
		{
			$headers['x-summon-session-id'] = $this->session_id;
		}
		
		// send it back
		
		$request = new Url();
		$request->url = $this->host . "/$service?$query_string";
		$request->headers = $headers;
		
		// $url = $request->url; $url = urldecode($url); $parts = explode('&', $url); print_r($parts); exit;
		
		return $request;
	}
	
	/**
	 * Escape reserved characters
	 *
	 * @param string $string
	 */
	
	protected function escape($string)
	{
		$chars = str_split(',:\()${}');
	
		foreach ( $chars as $char )
		{
			$string = str_replace($char, "", $string);
		}
	
		return $string;
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
	 * Get specified language
	 * 
	 * @todo make this not so hacky
	 */
	
	public function getLanguage()
	{
		$lang = $this->request->getParam('lang');
	
		if ( $lang == 'cze' )
		{
			return 'cs';
		}
		else
		{
			return 'en';
		}
	}
	
	/**
	 * Should query be expanded
	 *
	 * @todo make this not so hacky
	 */
	
	public function shouldExpandQuery()
	{
		return $this->request->getParam('expand');
	}
	
	/**
	 * Formats excluded
	 */
	
	public function getExcludedFormats()
	{
		return $this->formats_exclude;
	}
}
