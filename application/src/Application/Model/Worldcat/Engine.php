<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Worldcat;

use Application\Model\Search;
use Xerxes\Worldcat;
use Xerxes\Marc;
use Xerxes\Utility\Factory;
use Xerxes\Mvc\Request;

/**
 * Worldcat Search Engine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Search\Engine 
{
	protected $worldcat_client; // client
	protected $group; // group config based on source
	
	/**
	 * Create Worldcat Search Engine
	 */
	
	public function __construct($role, $source)
	{
		parent::__construct();

		$config_key = $this->config->getConfig("WORLDCAT_API_KEY", true);
		$config_always_guest = $this->config->getConfig( "WORLDCAT_SEARCH_AS_GUEST", false);
		
		// worldcat search object
		
		$this->worldcat_client = new Worldcat($config_key, Factory::getHttpClient());
		
		$this->group = new ConfigGroup();
		
		// if user is a guest, make it open, and return it pronto, since we
		// can't use the limiters below
		
		if ( $role == "guest" || $config_always_guest != null )
		{
			$this->worldcat_client->setServiceLevel("default");
		}
		
		// extract and set search options that have been configured for this group
		
		elseif ( $source != "" )
		{
			$this->group = $this->config->getWorldcatGroup($source);
			
			// no workset grouping, please
		
			if ( $this->group->frbr == "false" )
			{
				$this->worldcat_client->setWorksetGroupings(false);
			}

			// limit to certain libraries
		
			if ( $this->group->libraries_include != null )
			{
				$this->worldcat_client->limitToLibraries($this->group->libraries_include);
			}
		
			// exclude certain libraries
		
			if ( $this->group->libraries_exclude != null )
			{
				$this->worldcat_client->excludeLibraries($this->group->libraries_exclude);
			}
		
			// limit results to specific document types; a limit entry will
			// take presidence over any format specifically excluded
		
			if ( $this->group->limit_material_types != null )
			{
				$this->worldcat_client->limitToMaterialType($this->group->limit_material_types);
			}
			elseif ( $this->group->exclude_material_types != null )
			{
				$this->worldcat_client->excludeMaterialType($this->group->exclude_material_types);
			}
		}
	}
	
	/**
	 * Return the total number of hits for the search
	 * 
	 * @return int
	 */	
	
	public function getHits( Search\Query $search )
	{
		// always get hits on the full service
		
		$service_level = $this->worldcat_client->getServiceLevel();
		
		$this->worldcat_client->setServiceLevel('full');
		
		// get the results
		
		$results = $this->doSearch( $search, 1, 1 );
		
		// set it back
		
		$this->worldcat_client->setServiceLevel($service_level);

		// return total
		
		return $results->getTotal();
	}

	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */
	
	public function getRecord( $id )
	{
		$results = parent::getRecord( $id );
		
		// add holdings
		
		$record = $results->getRecord(0);
		
		if ($this->group->libraries_include != '' && $record != null && $this->group->show_holdings == true)
		{
			$library_codes = $this->group->libraries_include;
			
			$holdings_xml = $this->worldcat_client->getHoldings($id, $library_codes);
			
			$record->holdings = $this->extractHoldings($holdings_xml);
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
		$xml = $this->worldcat_client->record($id);
		return $this->parseResponse($xml);
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
		// can't search on this field, so return 0
		
		if ( $this->query->hasUnsupportedField() )
		{
			$results = new Search\ResultSet($this->getConfig());
			$results->total = 0;
			return $results;
		}		
		
		// convert query
		
		$query = $search->toQuery();
		
		// get results from Worldcat
		
		$xml = $this->worldcat_client->searchRetrieve($query, $start, $max, $sort);
		
		return $this->parseResponse($xml);
	}
	
	/*
	 * Parse Wolrdcat XML response into search results object
	 * 
	 * @param DOMDocument $xml
	 * 
	 * @return ResultSet
	 */
	
	protected function parseResponse(\DOMDocument $xml)
	{
		// create results
		
		$results = new Search\ResultSet($this->config);
		
		// extract total
		
		$results->total = $this->worldcat_client->getTotal();		
		
		// extract records
		
		foreach ( $this->extractRecords($xml) as $record )
		{
			$results->addRecord($record);
		}
		
		return $results;		
	}
	
	/**
	 * Parse records out of the response
	 *
	 * @param DOMDocument $xml
	 * 
	 * @return Record[]
	 */
	
	protected function extractRecords(\DOMDocument $xml)
	{
		$records = array();
		
		$document = new Marc\Document();
		$document->loadXML($xml);
		
		foreach ( $document->records() as $marc_record )
		{
			$xerxes_record = new Record();
			$xerxes_record->loadMarc($marc_record);
			array_push($records, $xerxes_record);
		}
		
		// echo $xml->saveXML(); print_r($records); exit;
		
		return $records;
	}
	
	/**
	 * Parse library information out of the holdings response
	 *
	 * @param DOMDocument $xml
	 *
	 * @return Library[]
	 */	
	
	protected function extractHoldings(\DOMDocument $xml)
	{
		$libraries = array();
		
		$simple_xml = simplexml_import_dom($xml->documentElement);
		
		foreach ( $simple_xml->holding as $holding )
		{
			$library = new Library();
			
			$library->oclc = (string) $holding->institutionIdentifier->value;
			$library->institution = (string) $holding->physicalLocation;
			$library->address = (string) $holding->physicalAddress->text;
			$library->url = (string) $holding->electronicAddress->text;
			
			$libraries[] = $library;
		}
		
		return $libraries;
	}
	
	/**
	 * No Facets to return
	 *
	 * @return Facets
	 */
	
	public function getAllFacets()
	{
		return new Search\Facets();
	}
	
	/**
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	/**
	 * Solr search query object
	 *
	 * @param Request $request
	 * @return Query
	 */
	
	public function getQuery(Request $request = null)
	{
		if ( ! $this->query instanceof Query )
		{
			$this->query = new Query($request, $this->getConfig());
		}
		
		return $this->query;
	}		
}
