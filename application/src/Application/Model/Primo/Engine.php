<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Primo;

use Application\Model\Search;
use Xerxes\Mvc\Request;
use Xerxes\Utility\Factory;
use Xerxes\Utility\Parser;

/**
 * Primo Search Engine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Search\Engine 
{
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
		// get the results
		
		$results = parent::searchRetrieve( $search, $start, $max, $sort, $facets);
		
		// enhance
		
		$results->markFullText(); // sfx data
		
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
		$results = parent::getRecord($id);
		
		// enhance
		
		$results->getRecord(0)->addRecommendations(); // bx
		$results->markFullText(); // sfx data
		$results->markRefereed(); // refereed
		
		return $results;
	}

	/**
	 * Parse the primo response
	 *
	 * @param string $response
	 * @return ResultSet
	 */	
	
	public function parseResponse($response)
	{
		// load it
		
		$xml = Parser::convertToDOMDocument($response);
		
		// check for errors
		
		$error = $xml->getElementsByTagName("ERROR")->item(0);
		
		if ( $error != "" )
		{
			throw new \Exception($error->getAttribute("MESSAGE"));
		}
		
		// set-up the result set
		
		$result_set = new Search\ResultSet($this->config);		
		
		// total
		
		$docset = $xml->getElementsByTagName("DOCSET")->item(0);
		
		if ( $docset == null )
		{
			throw new \Exception("Could not determine total number of records");
		}
		
		$total = $docset->getAttribute("TOTALHITS");
		
		$result_set->total = $total;

		// extract records
		
		foreach ( $this->extractRecords($xml) as $xerxes_record )
		{
			$result_set->addRecord($xerxes_record);
		}		
		
		// facets
		
		$facets = $this->extractFacets($xml);
		$result_set->setFacets($facets);
		
		return $result_set; 
	}

	/**
	 * Parse records out of the response
	 *
	 * @param DOMDocument $dom 	Primo XML
	 * @return Record[]
	 */	
	
	protected function extractRecords(\DOMDocument $dom)
	{
		$final = array();
		
		$xpath = new \DOMXPath($dom);
		$xpath->registerNamespace("sear", "http://www.exlibrisgroup.com/xsd/jaguar/search");
		
		$records = $xpath->query("//sear:DOC");
		
		foreach ( $records as $record )
		{
			$xerxes_record = new Record();
			$xerxes_record->loadXML($record);
			array_push($final, $xerxes_record);
		}
		
		return $final;
	}
	
	/**
	 * Parse facets out of the response
	 *
	 * @param DOMDocument $dom 	Primo XML
	 * @return Facets
	 */	
		
	protected function extractFacets(\DOMDocument $dom)
	{
		$facets = new Search\Facets();
		
		// echo $dom->saveXML();
		
		$groups = $dom->getElementsByTagName("FACET");
		
		if ( $groups->length > 0 )
		{
			// we'll pass the facets into an array so we can control both which
			// ones appear and in what order in the Xerxes config
			
			$facet_group_array = array();
			
			foreach ( $groups as $facet_group )
			{
				$facet_values = $facet_group->getElementsByTagName("FACET_VALUES");
				
				// if only one entry, then all the results have this same facet,
				// so no sense even showing this as a limit
				
				if ( $facet_values->length <= 1 )
				{
					continue;
				}
				
				$group_internal_name = $facet_group->getAttribute("NAME");
				
				$facet_group_array[$group_internal_name] = $facet_values;
			}
			
			// now take the order of the facets as defined in xerxes config
			
			foreach ( $this->config->getFacets() as $group_internal_name => $facet_values )
			{
				// we defined it, but it's not in the primo response
				
				if ( ! array_key_exists($group_internal_name, $facet_group_array) )
				{
					continue;
				}
				
				$group = new Search\FacetGroup();
				$group->name = $group_internal_name;
				
				// get the actual facets out of the array above
				
				$facet_values = $facet_group_array[$group_internal_name];

				// and put them in their own array so we can mess with them
				
				$facet_array = array();
				
				foreach ( $facet_values as $facet_value )
				{
					$key = $facet_value->getAttribute("KEY");
					$value = $facet_value->getAttribute("VALUE");
					$facet_array[$key] = $value;
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
				else 
				{	
					// not a date, sort by hit count
					
					arsort($facet_array);
				}
				
				// now make them into group facet objects
				
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

	/**
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	/**
	 * Primo search query object
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
