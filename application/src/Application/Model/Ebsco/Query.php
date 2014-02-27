<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Ebsco;

use Xerxes\Utility\Cache;

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
	 * eit host
	 * 
	 * @var string
	 */
	
	protected $host = 'http://140.234.254.43/Services/SearchService.asmx/';
	
	/**
	 * ebsco username
	 * @var string
	 */
	protected $username;
	
	/**
	 * ebsco password
	 * @var unknown_type
	 */
	protected $password;
	
	/**
	 * Ebsco databases searchable from this profile
	 * @var array
	 */
	
	protected $databases = array();
	
	/**
	 * Create an Ebsco Query
	 *
	 * @param Request $request
	 * @param Config $config
	 */
	
	public function __construct(Request $request = null, Config $config = null )
	{
		parent::__construct($request, $config);
	
		if ( $this->config != null )
		{
			// server address
				
			$this->username = $this->config->getConfig("EBSCO_USERNAME");
			$this->password = $this->config->getConfig("EBSCO_PASSWORD");
		}
	}
	
	/**
	 * Convert to Ebsco individual record syntax
	 *
	 * @param string $id
	 * @return Url
	 */
	
	public function getRecordUrl($id)
	{
		if ( ! strstr($id, "-") )
		{
			throw new \Exception("could not find record");
		}
		
		// database and id come in on same value, so split 'em
		
		$database = Parser::removeRight($id,"-");
		$id = Parser::removeLeft($id,"-");
		
		// get results
		
		$query = "AN $id";
		
		// construct url
		
		$url = $this->host . '/Search?' .
			'prof=' . $this->username .
			'&pwd=' . $this->password .
			'&authType=&ipprof=' . // empty params are necessary because ebsco is stupid
			'&query=' . urlencode($query) .
			"&db=$database" .
			'&startrec=1&numrec=1&format=detailed';
		
		return new Url($url);
	}
	
	/**
	 * Convert to Ebsco query syntax
	 * 
	 * not url encoded
	 * 
	 * @return string
	 */
	
	public function getQueryUrl()
	{
		$query = "";
		
		foreach ( $this->getQueryTerms() as $term )
		{
			// clone this otherwise the original is updated
			// which updates the user interface search box
		
			$local_term = clone $term;
		
			$local_term->toLower()
			           ->andAllTerms();
			
			$boolean = $term->boolean;
			
			// default to and boolean if not supplied
			
			if ( $term->id > 1 && $boolean == "")
			{
				$boolean = 'AND';
			}
		
			$query .= " $boolean (";
		
			if ( $local_term->field_internal != "" )
			{
				$query .= ' ' . $term->field_internal;
			}
		
			$query .= ' ' . $local_term->phrase;
		
			$query .= ' )';
		}
		
		if ( $this->request->getParam('scholarly') )
		{
			$query = "$query AND PT Academic Journal";
		}
		
		$query = trim($query);
		
		// echo "<p>$query</p>";
		
		// default for sort
		
		if ( $this->sort == "" )
		{
			$sort = "relevance";
		}
		
		// databases
		
		$databases = array();
		
		// see if any supplied as facet limit
		
		foreach ( $this->getLimits(true) as $limit )
		{
			if ( $limit->field == "database")
			{
				array_push($databases, $limit->value);
			}
		}
			
		// nope
			
		if ( count($databases) == 0)
		{
			// get 'em from ebsco
			
			$databases = $this->getDatabases();
			$databases = array_keys($databases); // just the keys
		}
		
		// construct url
		
		$url = $this->host . 'Search?' .
			'prof=' . $this->username .
			'&pwd=' . $this->password .
			'&authType=&ipprof=' . // empty params are necessary because ebsco is stupid
			'&query=' . urlencode($query) .
			'&startrec=' . $this->start . '&numrec=' . $this->max .
			'&sort=' . $this->sort .
			'&format=detailed';
		
		// add in the databases
		
		foreach ( $databases as $database )
		{
			$url .= "&db=$database";
		}
		
		return new Url($url);
	}
	
	/**
	 * Fetch list of databases
	 * 
	 * @param bool $force_new  get data from ebsco 
	 * @return array
	 */
	
	public function getDatabases($force_new = false)
	{
		$cache = new Cache();
		$id = 'ebsco_databases';
		
		if ( $force_new == false ) // no cache override
		{
			// do we have it already?
			
			if ( count($this->databases) > 0 )
			{
				return $this->databases;
			}
			
			// check the cache
			
			$this->databases = $cache->get($id); 
			
			if ( $this->databases != null ) // got 'em cached already?
			{ 
				return $this->databases;
			}
		}
		
		// fetch 'em from ebsco
		
		$url = $this->host . '/Info?' .
			'prof=' . $this->username .
			'&pwd=' . $this->password;
		
		$client = Factory::getHttpClient();
		$response = $client->getUrl($url);
		
		$xml = new \DOMDocument();
		
		$loaded = $xml->loadXML($response);
		
		if ( $loaded == true )
		{
			$nodes = $xml->getElementsByTagName('db');
			
			if ( $nodes->length > 1 )
			{
				foreach ( $nodes as $db )
				{
					if ( (string) $db->getAttribute('dbType') == 'Regular')
					{
						$id = (string) $db->getAttribute('shortName');
						$name = (string) $db->getAttribute('longName');
						
						$this->databases[$id] = $name;
					}
				}
				
				// cache 'em
				
				$cache->set($id, $this->databases);
			}
		}
		
		return $this->databases;
	}
	
	/**
	 * Get the (long) database name from id
	 * 
	 * @param string $id
	 * @return string
	 */
	
	public function getDatabaseName($id)
	{
		$databases =  $this->getDatabases();
		
		if ( array_key_exists($id, $databases) )
		{
			return $databases[$id];
		}
		else
		{
			return null;
		}
	}
}
