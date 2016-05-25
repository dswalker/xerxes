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
use Xerxes\Utility\Parser;
use Application\Model\Search\Facets;
use Application\Model\Search\FacetGroup;
use Application\Model\Search\Facet;

/**
 * Primo Search Engine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Search\Engine 
{
	/**
	 * Flag for facet assembly process
	 * @var bool
	 */
	private $facet_assembly = false;
	
	/**
	 * Total hits
	 * @var int
	 */
	private $total = 0;
	
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
		
		if ( $this->config->getConfig('mark_fulltext_using_export', false, false ) )
		{
			$results->markFullText(); // sfx data
		}
		
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

		if ( $this->config->getConfig('mark_fulltext_using_export', false, false ) )
		{
			$results->markFullText(); // sfx data
		}		
		
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
		
		if ( $this->query->getRequest()->getParam('XML') != "" )
		{
			header("Content-type:text/xml"); echo $xml->saveXML(); exit;
		}
		
		// check for errors
		
		$error = $xml->getElementsByTagName("ERROR")->item(0);
		
		if ( $error != "" )
		{
			// throw new \Exception($error->getAttribute("MESSAGE"));
		}
		
		// set-up the result set
		
		$result_set = new Search\ResultSet($this->config);		
		
		// total
		
		$docset = $xml->getElementsByTagName("DOCSET")->item(0);
		
		if ( $docset == null )
		{
			throw new \Exception("Could not determine total number of records");
		}
		
		$this->total = $docset->getAttribute("TOTALHITS");
		
		$result_set->total = $this->total;

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
		// header("Content-type: text/plain");	print_r($this->query->getLimits()); exit;
		// print_r($this->query->getLimits());

		
		// parse the facets
		
		$facets = new Search\Facets();

		$groups = $dom->getElementsByTagName("FACET"); // otherwise, grab the response
		
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
					// but it was selected, so that means it's a sole value facet group
					// manually put it back in
					
					$missing_limit = $this->query->getLimit($group_internal_name);
					
					if ( $missing_limit->value != "" )
					{
						$missing_facet = new Facet();
						$missing_facet->name = $missing_limit->value;
						$missing_facet->count = $this->total;
							
						$missing_group = new FacetGroup();
						$missing_group->name = $group_internal_name;
						$missing_group->addFacet($missing_facet);
							
						$facets->addGroup($missing_group);
					}
					
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

				$is_date = $this->config->isDateType($group_internal_name);
				
				if ( $is_date == true )
				{
					ksort($facet_array); // year is in the date key
				}
				elseif ( $group->name != "tlevel") // except for tlevel 
				{	
					arsort($facet_array); // not a date, sort by hit count
				}
				
				// now make them into group facet objects
				
				$topics_found = array();
				
				foreach ( $facet_array as $key => $value )
				{					
					$facet = new Search\Facet();
					$facet->count = $value;
					$facet->name = $key;
					
					// format
					
					if ( $group->name == "pfilter")
					{
						$facet->name = Format::toDisplay($facet->name); // public display
					}
					
					// language
					
					else if ( $group->name == "lang")
					{
						$facet->name = Language::toDisplay($facet->name); // public display
					}
					
					// with our multi-select option, we are querying back to PCI with all other facets selected
					// to 'freeze' the current facets; this can produce a list of topics that doesn't include
					// the selected topic, and so we have to add any selected topics back into the mix
					
					elseif ( $group->name == 'topic' )
					{
						foreach ( $this->query->getLimits() as $limit )
						{
							if ( $limit->field == $group->name  )
							{
								$values = $limit->value;
								
								if ( ! is_array($values) )
								{
									$values = array($values);
								}
								
								foreach ( $values as $value )
								{
									if ( $facet->name == $value )
									{
										$found_limit = clone $limit;
										$found_limit->value = $value;
										$topics_found[] = $found_limit;
									}
								}
							}
						}
					}
					
					// is this an excluded facet?
					
					foreach ( $this->query->getLimits() as $limit )
					{
						if ( $limit->field == $group->name && $limit->value == $key && $limit->boolean == "NOT" )
						{
							$facet->is_excluded = 1;
						}
					}
					
					// dates are different
					
					if ( $is_date == true )  
					{
						$facet->is_date = true;
						$facet->key = $key;
						$facet->timestamp = strtotime("1/1/" . $facet->name) * 1000;
					}					
					
					$group->addFacet($facet);
				}
				
				// for any limit that was selected but is not in the topic facet response (see above)
				// make it the top facet, as this is our selected one
				
				if ( $group->name == 'topic' )
				{
					foreach ( $this->query->getLimits() as $limit )
					{
						if ( $limit->field == 'topic')
						{
							$values  = $limit->value;
							
							if ( ! is_array($values) )
							{
								$values = array($values);
							}
							
							foreach ( $values as $value )
							{
								$topic_in_facets = false;
								
								foreach ( $topics_found as $found_limit )
								{
									if ( $value == $found_limit->value )
									{
										$topic_in_facets = true;
									}
								}
							
								if ( $topic_in_facets == false )
								{
									$facet = new Facet();
									$facet->name = $value;
									$group->prependFacet($facet);
								}
							}
						}
					}
				}
				
				$facets->addGroup($group);
			}
		}
		
		// return quickly when in facet assembly mode as we are using this data internally
		
		if ( $this->facet_assembly == true )
		{
			return $facets;
		}
		
		// facets that have been selected
		
		$limit_track = array();
			
		foreach ($this->query->getLimits() as $limit)
		{
			if ( ! in_array($limit->field, $limit_track) )
			{
				$limit_track[] = $limit->field;
			}
		}
			
		// cache id's
		
		$facetset_id = 'facet:' . $this->query->getQueryAndLimitsHash(); // identify this set of facets
		$query_id = 'facet:' . $this->query->getHash(); // use the query itself as the facet id
		
		$limit_count = count($limit_track);
		
		// in the event the user performs a query (with no facets) and then selects a facet
		// we'll grab the cached set from the previous query, just to speed things up 
		
		// if no limits selected, use the query id
		
		if ( $limit_count == 0 ) 
		{
			$facetset_id = $query_id;
		}
		
		// already cached?
		
		$final_facets = $this->cache->get($facetset_id);
		
		if  ($final_facets != "" ) // yup
		{
			return  $final_facets; 
		}
		
		// facet selected

		if ( $limit_count >= 1 ) 
		{
			$frozen_groups = array();
			
			$this->facet_assembly = true;
				
			// clone the query 
			
			$query = clone $this->query;
			$query->start = 0;
			$query->max = 0;
			
			// keep the limits from the original query
			
			$original_limits = array();
			$original_groups = array();
			
			foreach ( $query->getLimits() as $this_limit )
			{
				$original_limits[] = $this_limit;
				$original_groups[] = $this_limit->field;
			}
			
			$original_groups = array_unique($original_groups);
			
			foreach ( $original_limits as $this_limit )
			{
				$query->limits = array(); // blank 'em for this search
				
				// grab all other limits
				
				$other_limits = array();
				
				foreach ( $original_limits as $limit_available )
				{
					if ( $limit_available->field != $this_limit->field )
					{
						$other_limits[] = $limit_available;
					}
				}
				
				$query->limits = $other_limits;
				
				$results = $this->doSearch($query);
				
				// echo  $query->getQueryUrl()->url;
				
				foreach ( $results->getFacets()->getGroups() as $this_group )
				{
					if ( $this_group->name == $this_limit->field )
					{
						$frozen_groups[$this_limit->field] = $this_group;
					}
				}
			}
			
			$this->facet_assembly = false;
			
			$final_facets = new Facets();
			
			// interpose our frozen groups
			
			foreach ( array_keys($this->config->getFacets()) as $group_internal_name ) // take order from config
			{
				// it's frozen
				
				if ( array_key_exists($group_internal_name, $frozen_groups) )
				{
					$final_facets->addGroup($frozen_groups[$group_internal_name]);
				}
				else
				{
					// is it in the facets returned from PCI?
					
					$found = false;
					
					foreach ( $facets->getGroups() as $group )
					{
						if ( $group->name == $group_internal_name )
						{
							$final_facets->addGroup($group);
							$found = true;
						}
					}
				}
			}
			
			$facets = $final_facets;
		}
		
		// excluded facets

		foreach ( $facets->getGroups() as $group ) // for each facet group
		{
			foreach( $this->query->getLimits() as $limit ) // check the limits selected
			{
				if ( $limit->field == $group->name && $limit->boolean == 'NOT' ) // an excluded facet
				{
					$value = $limit->value;
					
					if ( ! is_array($limit->value) )
					{
						$value = array($limit->value);
					}
					
					foreach ( $value as $limit_value )
					{
						$found = false;
						
						// check the facets to see if our excluded one is in the group
						
						foreach ( $group->getFacets() as $facet )
						{
							if ( $facet->name == $limit_value ) // yup
							{
								$facet->is_excluded = true;
								$found = true;
							}
						}
						
						// nope, so add the excluded facet
						
						if ( $found == false )
						{
							$facet_negative = new Facet();
							$facet_negative->name = $limit_value;
							$facet_negative->is_excluded = true;
					
							$group->addFacet($facet_negative);
						}
					}
				}
			}
		}
		
		
		// cache it for later
		
		$this->cache->set($facetset_id, $facets);
		
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
