<?php

namespace Application\Model\Solr;

use Application\Model\Search,
	Xerxes\Utility\Factory;

/**
 * Solr Search Engine
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Solr
 */

class Engine extends Search\Engine 
{
	protected $server; // solr server address
	protected $url; // track the url

	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		parent::__construct();

		// server address
		
		$this->server = $this->config->getConfig('SOLR', true);
		
		if ( substr($this->server,-1,1) != "/" )
		{
			$this->server .= "/";
		}
		
		$this->server .= "select/?version=2.2";
	}
	
	/**
	 * Return the total number of hits for the search
	 * 
	 * @return int
	 */	
	
	public function getHits( Search\Query $search )
	{
		// get the results, just the hit count, no facets
		
		$results = $this->doSearch($search, 0, 0, null, false);
		
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
		// get the results
		
		$results = $this->doSearch($search, $start, $max, $sort, true);
		
		// find any holding we have cached
		
		$results->injectHoldings();
		
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
		// get the record
		
		$results = $this->doGetRecord($id);
		$record = $results->getRecord(0);
		
		$record->fetchHoldings(); // item availability
		$record->addReviews(); // good read reviews
		
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
		$results = $this->doGetRecord($id);
		$record = $results->getRecord(0);
		
		$record->fetchHoldings(); // item availability
		return $results;
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
	 * Do the actual fetch of an individual record
	 * 
	 * @param string	record identifier
	 * @return Results
	 */		
	
	protected function doGetRecord($id)
	{
		$id = str_replace(':', "\\:", $id);
		$results = $this->doSearch("id:$id", 1, 1);
		return $results;
	}
	
	/**
	 * Do the actual search
	 * 
	 * @param mixed $search							search object or string
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * @param bool $include_facets					[optional] whether to include facets or not
	 * 
	 * @return string
	 */		
	
	protected function doSearch( $search, $start, $max = 10, $sort = null, $include_facets = true)
	{
		// start
		
		if ( $start > 0)
		{
			$start--; // solr is 0-based
		}
		
		### parse the query
		
		$query = ""; // query
		$type = ""; // dismax or standard
		
		// passed in a query object, so handle this
		
		if ( $search instanceof Search\Query )
		{
			$terms = $search->getQueryTerms();
			
			// check if a query was supplied
			
			if ( count($terms) == 0 )
			{
				throw new \Exception("No search terms supplied");
			}
			
			// get just the first term for now
			
			$term = $terms[0];
		
			// decide between basic and dismax handler
			
			$trunc_test = $this->config->getFieldAttribute($term->field_internal, "truncate");
			
			// use dismax if this is a simple search, that is:
			// only if there is one phrase (i.e., not advanced), no boolean OR and no wildcard
	
			if ( count($terms) == 1 && 
				! strstr($term->phrase, " OR ") && 
				! strstr($term->phrase, "*") && 
				$trunc_test == null )
			{
				# dismax
				
				$type = "&defType=dismax";
	
				$term = $terms[0];
				
				$phrase = $term->phrase;
				$phrase = strtolower($phrase);
				$phrase = str_replace(" NOT ", " -", $phrase);
				
				if ( $term->field_internal != "" )
				{
					$query .= "&qf=" . urlencode($term->field_internal);
					$query .= "&pf=" . urlencode($term->field_internal);
				}
		
				$query .= "&q=" . urlencode($phrase);
			}
			else
			{
				# standard
				
				$query = "";
				
				foreach ( $terms as $term )
				{
					$phrase = $term->phrase;
					$phrase = strtolower($phrase);
					$phrase = str_replace(':', '', $phrase);
					$phrase = $search->alterQuery($phrase, $term->field_internal, $this->config);
					
					// break up the query into words
					
					$arrQuery = $term->normalizedArray( $phrase );
					
					// we'll now search for this term across multiple fields
					// specified in the config
		
					if ( $term->field_internal != "" )
					{
						// we'll use this to get the phrase as a whole, but minus
						// the boolean operators in order to boost this
						
						$boost_phrase = ""; 
						
						foreach ( $arrQuery as $strPiece )
						{
							// just add the booelan value straight-up
							
							if ( $strPiece == "AND" || $strPiece == "OR" || $strPiece == "NOT" )
							{
								$query .= " $strPiece ";
								continue;			
							}
							
							$boost_phrase .= " " . $strPiece;
							
							// try to mimick dismax query handler as much as possible
							
							$query .= " (";
							$local = array();
							
							// take the fields we're searching on,
							
							foreach ( explode(" ", $term->field_internal) as $field )
							{
								// split them out into index and boost score
							
								$parts = explode("^",$field);
								$field_name = $parts[0];
								$boost = "";
								
								// make sure there really was a  boost score
								
								if ( array_key_exists(1,$parts) )
								{
									$boost = "^" . $parts[1];
								}
								
								// put them together 
								
								array_push($local, $field_name . ":" . $strPiece . $boost);
							}
							
							$query .= implode(" OR ", $local);
								
							$query .= " )";
						}
						
						// $boost_phrase = trim($boost_phrase);
						// $query = "($query) OR \"" . $boost_phrase . '"';
					}
				}
				
				$query = "&q=" . urlencode($query);
			}
			
			// facets selected
			
			foreach ( $search->getLimits(true) as $facet_chosen )
			{
				// put quotes around non-keyed terms
				
				if ( $facet_chosen->key != true )
				{
					$facet_chosen->value = '"' . $facet_chosen->value . '"';
				}
				
				$query .= "&fq=" . urlencode( $facet_chosen->field . ":" . $facet_chosen->value);
			}
			
			// limits set in config
			
			$auto_limit = $this->config->getConfig("LIMIT", false);
			
			if ( $auto_limit != null )
			{
				$query .= "&fq=" . urlencode($auto_limit);
			}
		}
		
		// was just a string, so just take it straight-up
		
		else
		{
			$query = "&q=" . urlencode($search);
		}
		
		
		### now the url
		
		$this->url = $this->server . $type . $query;

		$this->url .= "&start=$start&rows=" . $max . "&sort=" . urlencode($sort);
		
		if ( $include_facets == true )
		{
			$this->url .= "&facet=true&facet.mincount=1";
			
			foreach ( $this->config->getFacets() as $facet => $attributes )
			{
				$sort = (string) $attributes["sort"];
				$max = (string) $attributes["max"];
				
				$this->url .= "&facet.field=$facet";

				if ( $sort != "" )
				{
					$this->url .= "&f.$facet.facet.sort=$sort";
				}				
				
				if ( $max != "" )
				{
					$this->url .= "&f.$facet.facet.limit=$max";
				}					
			}
		}
		
		// make sure we get the score
		
		$this->url .= "&fl=*+score";
		
		
		## get and parse the response

		// get the data
		
		$client = Factory::getHttpClient();
		$client->setUri($this->url);
		$response = $client->send()->getBody();
		
		$xml = simplexml_load_string($response);
		
		if ( $response == null || $xml === false )
		{
			throw new \Exception("Could not connect to search engine.");
		}
		
		// parse the results
		
		$results = new Search\ResultSet($this->config);
		
		// extract total
		
		$results->total = (int) $xml->result["numFound"]; 
		
		// extract records
		
		foreach ( $this->extractRecords($xml) as $record )
		{
			$results->addRecord($record);
		}
		
		// extract facets
		
		$results->setFacets($this->extractFacets($xml));
		
		return $results;
	}
	
	/**
	 * Extract records from the Solr response
	 * 
	 * @param simplexml	$xml	solr response
	 * @return array of Record's
	 */	
	
	protected function extractRecords($xml)
	{
		$records = array();
		$docs = $xml->xpath("//doc");
		
		if ( $docs !== false && count($docs) > 0 )
		{
			foreach ( $docs as $doc )
			{
				$id = null;
				$format = null;
				$score = null;
				$xml_data = "";
				
				foreach ( $doc->str as $str )
				{
					// marc record
											
					if ( (string) $str["name"] == 'fullrecord' )
					{
						$marc = trim( (string) $str );
						
						// marc-xml or marc-y marc -- come on, come on, feel it, feel it!
						
						if ( substr($marc, 0, 5) == '<?xml')
						{
							$xml_data = $marc;
						}
						else
						{
					        $marc = preg_replace('/#31;/', "\x1F", $marc);
					        $marc = preg_replace('/#30;/', "\x1E", $marc);
					        
					        $marc_file = new \File_MARC($marc, \File_MARC::SOURCE_STRING);
					        $marc_record = $marc_file->next();
					        $xml_data = $marc_record->toXML();
						}
					}
					
					// record id
					
					elseif ( (string) $str["name"] == 'id' )
					{
						$id = (string) $str;
					}
				}
				
				// format
				
				foreach ( $doc->arr as $arr )
				{
					if ( $arr["name"] == "format" )
					{
						$format = (string) $arr->str;
					}
				}
				
				// score
				
				foreach ( $doc->float as $float )
				{
					if ( $float["name"] == "score" )
					{
						$score = (string) $float;
					}
				}
				
				
				$record = new Record();
				$record->loadXML($xml_data);
				
				$record->setRecordID($id);
				$record->format()->setFormat($format);
				$record->setScore($score);
				
				array_push($records, $record);
			}
		}
		
		return $records;
	}
	
	/**
	 * Extract facets from the Solr response
	 * 
	 * @param simplexml	$xml	solr response
	 * @return Facets, null if none
	 */
	
	protected function extractFacets($xml)
	{
		$facets = new Search\Facets();
		
		$groups = $xml->xpath("//lst[@name='facet_fields']/lst");
		
		if ( $groups !== false && count($groups) > 0 )
		{
			foreach ( $groups as $facet_group )
			{
				// if only one entry, then all the results have this same facet,
				// so no sense even showing this as a limit
				
				$count = count($facet_group->int);
				
				if ( $count <= 1 )
				{
					continue;
				}
				
				$group_internal_name = (string) $facet_group["name"];
				
				$group = new Search\FacetGroup();
				$group->name = $group_internal_name;
				$group->public = $this->config->getFacetPublicName($group_internal_name);
				
				// put facets into an array
				
				$facet_array = array();
				
				foreach ( $facet_group->int as $int )
				{
					$facet_array[(string)$int["name"]] = (int) $int;
				}

				// date

				$decade_display = array();
				
				$is_date = $this->config->isDateType($group_internal_name);
				
				if ( $is_date == true )
				{
					$date_arrays = $group->luceneDateToDecade($facet_array);
					$decade_display = $date_arrays["display"];
					$facet_array = $date_arrays["decades"];		
				}
				
				foreach ( $facet_array as $key => $value )
				{
					$facet = new Search\Facet();
					$facet->name = $key;
					$facet->count = $value;
					
					// dates are different
					
					if ( $is_date == true )  
					{
						$facet->name = $decade_display[$key];
						$facet->is_date = true;
						$facet->key = $key;
					}

					$group->addFacet($facet);
				}
				
				$facets->addGroup($group);
			}
		}
		
		return $facets;
	}
}
