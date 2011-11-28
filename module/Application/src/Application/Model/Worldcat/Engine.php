<?php

/**
 * Worldcat Search Engine
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Worldcat_Engine extends Xerxes_Model_Search_Engine 
{
	protected $client;
	
	/**
	 * Constructor
	 */
	
	public function __construct($role, $source)
	{
		parent::__construct();

		$config_key = $this->config->getConfig("WORLDCAT_API_KEY", true);
		$config_always_guest = $this->config->getConfig( "WORLDCAT_SEARCH_AS_GUEST", false);
		
		// worldcat search object
		
		$this->client = new Xerxes_WorldCat($config_key);
		
		// if user is a guest, make it open, and return it pronto, since we
		// can't use the limiters below
		
		if ( $role == "guest" || $config_always_guest != null )
		{
			$this->client->setServiceLevel("default");
		}
		
		// extract and set search options that have been configured for this group
		
		elseif ( $source != "" )
		{
			$group = $this->config->getWorldcatGroup($source);
			
			// no workset grouping, please
		
			if ( $group->frbr == "false" )
			{
				$this->client->setWorksetGroupings(false);
			}

			// limit to certain libraries
		
			if ( $group->libraries_include != null )
			{
				$this->client->limitToLibraries($group->libraries_include);
			}
		
			// exclude certain libraries
		
			if ( $group->libraries_exclude != null )
			{
				$this->client->excludeLibraries($group->libraries_exclude);
			}
		
			// limit results to specific document types; a limit entry will
			// take presidence over any format specifically excluded
		
			if ( $group->limit_material_types != null )
			{
				$this->client->limitToMaterialType($group->limit_material_types);
			}
			elseif ( $group->exclude_material_types != null )
			{
				$this->client->excludeMaterialType($group->exclude_material_types);
			}
		}
	}
	
	/**
	 * Return the total number of hits for the search
	 * 
	 * @return int
	 */	
	
	public function getHits( Xerxes_Model_Search_Query $search )
	{
		// get the results
		
		$results = $this->doSearch( $search, 1, 0 );

		// return total
		
		return $results->getTotal();
	}

	/**
	 * Search and return results
	 * 
	 * @param Xerxes_Model_Search_Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Xerxes_Model_Search_Results
	 */	
	
	public function searchRetrieve( Xerxes_Model_Search_Query $search, $start = 1, $max = 10, $sort = "")
	{
		return $this->doSearch( $search, $start, $max, $sort);
	}	
	
	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return Xerxes_Model_Solr_Results
	 */
	
	public function getRecord( $id )
	{
		return $this->doGetRecord( $id );
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
	 * Return the search engine config
	 * 
	 * @return Xerxes_Model_Worldcat_Config
	 */		
	
	public function getConfig()
	{
		return Xerxes_Model_Worldcat_Config::getInstance();
	}
	
	/**
	 * Do the actual fetch of an individual record
	 * 
	 * @param string	record identifier
	 * @return Xerxes_Model_Solr_Results
	 */	
	
	protected function doGetRecord( $id )
	{
		$xml = $this->client->record($id);
		return $this->parseResponse($xml);
	}		
	
	/**
	 * Do the actual search
	 * 
	 * @param Xerxes_Model_Search_Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Xerxes_Model_Search_Results
	 */		
	
	protected function doSearch( Xerxes_Model_Search_Query $search, $start = 1, $max = 10, $sort = "")
	{ 	
		// convert query
		
		$query = $this->convertQuery($search);
		
		// get results from Worldcat
		
		$xml = $this->client->searchRetrieve($query, $start, $max, $sort);
		
		return $this->parseResponse($xml);
	}
	
	protected function parseResponse($xml)
	{
		// create results
		
		$results = new Xerxes_Model_Search_ResultSet($this->config);
		
		// extract total
		
		$results->total = $this->client->getTotal();		
		
		// extract records
		
		foreach ( $this->extractRecords($xml) as $record )
		{
			$results->addRecord($record);
		}
		
		return $results;		
	}
	
	protected function convertQuery( Xerxes_Model_Search_Query $search )
	{
		$query = "";
		
		// prepare the query
		
		// search terms
		
		foreach ( $search->getQueryTerms() as $term )
		{
			$query .= $this->keyValue($term);
		}
		
		// limits
		
		$limit_array = array();
		
		foreach ( $search->getLimits() as $limit )
		{
			if ( $limit->value == "" )
			{
				continue;
			}
		
			// publication year
		
			if ( $limit->field == "year" )
			{
				$year = $limit->value;
				$year_relation = $limit->relation;
		
				$year_array = explode("-", $year);
		
				// there is a range
		
				if ( count($year_array) > 1 )
				{
					if ( $year_relation == "=" )
					{
						$query .= " and srw.yr >= " . trim($year_array[0]) .
						" and srw.yr <= " . trim($year_array[1]);
					}
		
					// this is probably erroneous, specifying 'before' or 'after' a range;
					// did user really mean this? we'll catch it here just in case
		
					elseif ( $year_relation == ">" )
					{
						array_push($limit_array, " AND srw.yr > " .trim($year_array[1] . " "));
					}
					elseif ( $year_relation == "<" )
					{
						array_push($limit_array, " AND srw.yr < " .trim($year_array[0] . " "));
					}
				}
				else
				{
					// a single year
		
					array_push($limit_array, " AND srw.yr $year_relation $year ");
				}
			}
		
			// language
		
			elseif ( $limit->field == "la")
			{
				array_push($limit_array, " AND srw.la=\"" . $limit->value . "\"");
			}
		
			// material type
		
			elseif ( $limit->field == "mt")
			{
				array_push($limit_array, " AND srw.mt=\"" . $limit->value . "\"");
			}
		}
		
		$limits = implode(" ", $limit_array);
		
		if ( $limits != "" )
		{
			$query = "($query) $limits";
		}
		
		return trim($query);		
	}
	
		
	/**
	 * Parse records out of the response
	 *
	 * @param DOMDocument $xml
	 * 
	 * @return array of Xerxes_Model_Worldcat_Record's
	 */
	
	protected function extractRecords(DOMDocument $xml)
	{
		$records = array();
		
		$document = new Xerxes_Marc_Document();
		$document->loadXML($xml);
		
		foreach ( $document->records() as $marc_record )
		{
			$xerxes_record = new Xerxes_Model_Worldcat_Record();
			$xerxes_record->loadMarc($marc_record);
			array_push($records, $xerxes_record);
		}
		
		// echo $xml->saveXML(); print_r($records); exit;
		
		return $records;
	}
	
	/**
	 * Create an SRU boolean/key/value expression in the query, such as:
	 * AND srw.su="xslt"
	 *
	 * @param Xerxes_Model_Search_QueryTerm $term		
	 * @param bool $neg				(optional) whether the presence of '-' in $value should indicate a negative expression
	 * 								in which case $boolean gets changed to 'NOT'
	 * @return string				the resulting SRU expresion
	 */
	
	private function keyValue(Xerxes_Model_Search_QueryTerm $term, $neg = false)
	{
		if ( $term->phrase == "" )
		{
			return "";
		}
	
		if ($neg == true && strstr (  $term->phrase, "-" ))
		{
			$boolean = "NOT";
			$term->phrase = str_replace ( "-", "", $term->phrase );
		}
	
		$together = "";
	
		if ( $term->relation == "exact")
		{
			$term->phrase = str_replace ( "\"", "",  $term->phrase );
			$together = " srw." . $term->field_internal . " exact \"  $term->phrase \"";
		}
		else
		{
			$phrase = $term->removeStopWords()->phrase;
			
			foreach ( $term->normalizedArray($phrase) as $query_part )
			{
				if ($query_part == "AND" || $query_part == "OR" || $query_part == "NOT")
				{
					$together .= " " . $query_part;
				}
				else
				{
					$query_part = str_replace ( '"', '', $query_part );
					$together .= " srw." . $term->field_internal . " = \"  $query_part \"";
				}
			}
		}
	
		return " " . $term->boolean . " ( $together ) ";
	}	
}
