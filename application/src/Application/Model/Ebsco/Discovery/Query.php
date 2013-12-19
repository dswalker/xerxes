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
use Application\Model\Search\Query\Url;
use Xerxes\Mvc\Request;
use Xerxes\Utility\Factory;
use Xerxes\Utility\Parser;

/**
 * Ebsco Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * eds server address
	 * @var string
	 */
	protected $server;
	
	/**
	 * eds session id
	 * @var string
	 */
	protected $session_id;

	/**
	 * headers
	 * @var array
	 */
	protected $headers = array();	
	
	/**
	 * Create an EDS Query
	 *
	 * @param Request $request
	 * @param Config $config
	 */
	
	public function __construct(Request $request = null, Config $config = null )
	{
		parent::__construct($request, $config);
	
		// server address
	
		$this->server = 'http://eds-api.ebscohost.com/edsapi/rest/';
		$profile = 'edsapi';
		
		if ( $request != null )
		{
			// set session id
				
			$this->session_id = $request->getSessionData('ebsco_session');

			if ( $this->session_id == "" )
			{
				$this->session_id = $this->createSession($profile);
			}
			
			$this->request->setSessionData('ebsco_session', $this->session_id);
		}

		$this->headers =  array(
			'Accept' => 'application/json',
			'x-sessionToken' => $this->session_id
		);
	}
	
	/**
	 * Convert to EDS individual record syntax
	 *
	 * @param string $id
	 * @return Url
	 */
	
	public function getRecordUrl($id)
	{
		if ( $id == "" )
		{
			throw new \DomainException('No record ID supplied');
		}
		
		$database = Parser::removeRight($id,"-");
		$id = Parser::removeLeft($id,"-");
		
		// build request
		
		$url = $this->server . 'retrieve?';
		$url .= 'dbid=' . $database;
		$url .= '&an=' . urlencode($id);
		$url .= '&includefacets=n';
		
		return new Url($url, $this->headers);
	}
	
	/**
	 * Convert to EDS query syntax
	 * 
	 * @return Request
	 */
	
	public function getQueryUrl()
	{
		$url = ""; // final url
		$query = ""; // query
		
		// search terms

		$x = 1;
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$boolean = $term->boolean;
			$value = $this->escapeChars($term->phrase);
			
			if ( $boolean == "")
			{
				$boolean = 'AND';
			}
			
			$query .= '&query-' . $x . '=' . urlencode($boolean . ',');

			if ( $term->field_internal != "")
			{
				$query .= urlencode($term->field_internal . ':');
			}
			
			$query .= urlencode($value);
			
			$x++;
		}
		
		// limits
		
		$y = 1;
		
		foreach ( $this->getLimits(true) as $limit )
		{
			$field = $limit->field;
			$value = $limit->value;
			
			if ( is_array($value) )
			{
				$value = implode(',', $value);
			}
			
			$query .= '&facetfilter=' . urlencode($y . ',' . $field . ':' . $this->escapeChars($value) );			
			$y++;
		}
		
		$query = trim($query);
		
		// limit to local users?
		
		if ( $this->getUser()->isAuthorized() )
		{
				
		}
		
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
		
		if ( $this->max > 0 )
		{
			$page = ceil ($this->start / $this->max);
		}
		else
		{
			$page = 1;
		}
		
		// get the results
		
		$url = $this->server . 'Search?';
		$url .= $query;
		$url .= '&view=detailed';
		$url .= '&resultsperpage=' . $this->max;
		$url .= '&pagenumber=' . $page;
		$url .= '&sort=' . $this->sort;
		$url .= '&searchmode=all';
		$url .= '&highlight=n';
		$url .= '&includefacets=y';
		
		return new Url($url, $this->headers);
	}
	
	/**
	 * Establish a new session with EDS
	 *
	 * @param string $profile
	 * @return string
	 */
	
	public function createSession($profile)
	{
		$url = $this->server . 'createsession?profile=' . urlencode($profile);
	
		$client = Factory::getHttpClient();
		$xml = $client->getUrl($url, 10);
		
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
	 * Escape special characters
	 * 
	 * @param string $string
	 * @return string
	 */
	
	protected function escapeChars($string)
	{
		$string = str_replace(':', '\:', $string);
		$string = str_replace(',', '\,', $string);
		$string = str_replace('(', '\(', $string);
		$string = str_replace(')', '\)', $string);
		
		return $string;
	}
}
