<?php

namespace Application\Model\Worldcat;

use Application\Model\Search,
	Xerxes\Worldcat,
	Xerxes\Marc,
	Xerxes\Utility\Factory,
	Xerxes\Utility\Request;

/**
 * Worldcat Search Engine
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
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
	 * Search and return results
	 * 
	 * @param Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Results
	 */	
	
	public function searchRetrieve( Search\Query $search, $start = 1, $max = 10, $sort = "")
	{
		return $this->doSearch( $search, $start, $max, $sort);
	}	
	
	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */
	
	public function getRecord( $id )
	{
		$results = $this->doGetRecord( $id );
		
		$record = $results->getRecord(0);
		
		// add holdings
		
		if ($this->group->libraries_include != '' && $record != null )
		{
			$library_codes = $this->group->libraries_include;
			
			$holdings_xml = $this->worldcat_client->getHoldings($id, $library_codes);
			
			$record->holdings = $this->extractHoldings($holdings_xml);
		}
		
		return $results;
	}

	/**
	 * Get record to save
	 * 
	 * @param string	record identifier
	 * @return int		internal saved id
	 */	
	
	public function getRecordForSave( $id )
	{
		return $this->doGetRecord( $id );
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
	 * Do the actual search
	 * 
	 * @param Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Results
	 */		
	
	protected function doSearch( Search\Query $search, $start = 1, $max = 10, $sort = "")
	{ 	
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
	 * @return array of Record's
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
	 * @return array of Library's
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
	 * Return the search engine config
	 *
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	/**
	 * Return the Solr search query object
	 *
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
