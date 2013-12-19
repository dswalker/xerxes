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

use Application\Model\Search;
use Application\Model\Search\Query\Url;
use Xerxes\Mvc\Request;
use Xerxes\Utility\Parser;

/**
 * Ebsco Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
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
		
		$url = 'http://eit.ebscohost.com/Services/SearchService.asmx/Search?' .
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
		
			$query .= ' ' . $term->boolean . ' (';
		
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
			// get 'em from config
		
			$databases_xml = $this->config->getConfig("EBSCO_DATABASES");
				
			if ( $databases_xml == "" )
			{
				throw new \Exception("No databases defined");
			}
				
			foreach ( $databases_xml->database as $database )
			{
				array_push($databases, (string) $database["id"]);
			}
		}
		
		// construct url
		
		$url = 'http://eit.ebscohost.com/Services/SearchService.asmx/Search?' .
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
}
