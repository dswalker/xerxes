<?php

namespace Application\Model\Search;

use Application\Model\DataMap\Availability,
	Application\Model\DataMap\Refereed as DataMapRefereed,
	Xerxes\Record,
	Xerxes\Utility\Cache;

/**
 * Search Results
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ResultSet
{
	public $total = 0; // total number of hits
	public $records = array(); // result objects
	public $facets; // facet object
	
	protected $config; // local config
	
	/**
	 * Create Search Result Set
	 * 
	 * @param Config $config
	 */
	
	public function __construct( Config $config )
	{
		$this->config = $config;	
	}
	
	/**
	 * Return an individual search result by position
	 * 
	 * @param int $id		array position
	 * @return Result
	 */	
	
	public function getRecord( $id )
	{
		if ( array_key_exists($id, $this->records) )
		{
			return $this->records[$id];
		}
		else
		{
			throw new \Exception("No such record");
		}
	}
	
	/**
	 * Return all search results
	 * 
	 * @return array of Result objects
	 */

	public function getRecords()
	{
		return $this->records;
	}
	
	/**
	 * Add a Xerxes Record to this result set
	 * 
	 * @param Xerxes_Record $record
	 */

	public function addRecord( Record $record )
	{
		$result = new Result($record, $this->config);
		array_push($this->records, $result);
	}

	/**
	 * Add a Xerxes Search Result to this result set
	 * 
	 * @param Result $result
	 */	
	
	public function addResult( Result $result )
	{
		array_push($this->records, $result);
	}
	
	/**
	 * Get the facets
	 * 
	 * @return Facets
	 */
	
	public function getFacets()
	{
		return $this->facets;
	}
	
	/**
	 * Add facets to the result set
	 * 
	 * @param Facets $facets
	 */
	
	public function setFacets( Facets $facets )
	{
		$this->facets = $facets;
	}
	
	/**
	 * Get the total number of hits for this result set
	 */
	
	public function getTotal()
	{
		return $this->total;	
	}
	
	/**
	 * Add a peer-reviewed indicator for refereed journals
	 */
	
	public function markRefereed()
	{
		// extract all the issns from the available records in one
		// single shot to make this more efficient
		
		$issns = $this->extractISSNs();
		
		if ( count($issns) > 0 )
		{
			// get all from our peer-reviewed list
			
			$data_map = new DataMapRefereed();
			
			$refereed_list = $data_map->getRefereed($issns);
			
			// now mark the records that matched
			
			foreach ( $this->records as $record )
			{
				$xerxes_record = $record->getXerxesRecord();
				
				// check if the issn matched
				
				foreach ( $refereed_list as $refereed )
				{
					if ( in_array($refereed->issn,$xerxes_record->getAllISSN()))
					{
						// not if it is a review
						
						if ( stripos($xerxes_record->format()->getPublicFormat(),'review') === false )
						{
							$xerxes_record->setRefereed(true);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Add a full-text indicator for those records where link resolver indicates it
	 */
	
	public function markFullText()
	{
		// extract all the issns from the available records in one
		// single shot to make this more efficient
		
		$issns = $this->extractISSNs();
			
		if ( count($issns) > 0 )
		{
			$data_map = new Availability();
			
			// execute this in a single query							
			// reduce to just the unique ISSNs
				
			$arrResults = $data_map->getFullText($issns);
			
			// we'll now go back over the results, looking to see 
			// if also the years match, marking records as being in our list
			
			foreach ( $this->records as $result )
			{
				$xerxes_record = $result->getXerxesRecord();
				$this->determineFullText($xerxes_record, $arrResults);

				// do the same for recommendations
				
				foreach ($result->recommendations as $recommend )
				{
					$xerxes_record = $recommend->getXerxesRecord();
					$this->determineFullText($xerxes_record, $arrResults);
				}
			}
		}		
	}
	
	/**
	 * Given the results of a query into our SFX export, based on ISSN,
	 * does the year of the article actually meet the criteria of full-text
	 * 
	 * @param object $xerxes_record		the search result
	 * @param array $arrResults			the array from the sql query 
	 */
	
	protected function determineFullText( &$xerxes_record, $arrResults )
	{
		$strRecordIssn = $xerxes_record->getISSN();
		$strRecordYear = $xerxes_record->getYear();

		foreach ( $arrResults as $objFulltext )
		{
			// convert query issn back to dash

			if ( $strRecordIssn == $objFulltext->issn )
			{
				// in case the database values are null, we'll assign the 
				// initial years as unreachable
					
				$iStart = 9999;
				$iEnd = 0;
						
				if ( $objFulltext->startdate != null )
				{
					$iStart = (int) $objFulltext->startdate;
				}
				if ( $objFulltext->enddate != null )
				{
					$iEnd = (int) $objFulltext->enddate;
				}
				if ( $objFulltext->embargo != null && (int) $objFulltext->embargo != 0 )
				{
					// convert embargo to years, we'll overcompensate here by rounding
					// up, still showing 'check for availability' but no guarantee of full-text
							
					$iEmbargoDays = (int) $objFulltext->embargo;
					$iEmbargoYears = (int) ceil($iEmbargoDays/365);
							
					// embargo of a year or more needs to go back to start of year, so increment
					// date plus an extra year
							
					if ( $iEmbargoYears >= 1 )
					{
						$iEmbargoYears++;
					}
							
					$iEnd = (int) date("Y");
					$iEnd = $iEnd - $iEmbargoYears;
				}
							
				// if it falls within our range, mark the record as having it
				
				if ( $strRecordYear >= $iStart && $strRecordYear <= $iEnd )
				{
					$xerxes_record->setSubscription(true);
				}
			}
		}		
	}

	/**
	 * Extract all the ISSNs from the records, convenience function
	 */

	protected function extractISSNs()
	{
		$issns = array();
		
		// get each result
		
		foreach ( $this->records as $record )
		{
			// issn from the bib
			
			foreach ( $record->getXerxesRecord()->getAllISSN() as $record_issn )
			{
				array_push($issns, $record_issn);
			}
			
			// issn from recommedations
			
			foreach ( $record->recommendations as $recommendation )
			{
				foreach ( $recommendation->getXerxesRecord()->getAllISSN() as $record_issn )
				{
					array_push($issns, $record_issn);
				}
			}
		}
		
		$issns = array_unique($issns);
		
		return $issns;
	}

	/**
	 * Extract all the ISBNs from the records, convenience function
	 */	
	
	protected function extractISBNs()
	{
		$isbns = array();
		
		foreach ( $this->records as $record )
		{
			foreach ( $record->getAllISBN() as $record_isbn )
			{
				array_push($isbns, $record_isbn);
			}
		}
		
		$isbns = array_unique($isbns);
		
		return $isbns;
	}

	/**
	 * Extract all the OCLC numbers from the records, convenience function
	 */	
	
	protected function extractOCLCNumbers()
	{
		$oclc = array();
		
		foreach ( $this->records as $record )
		{
			array_push($oclc, $record->getOCLCNumber() );
		}
		
		$oclc = array_unique($oclc);
		
		return $oclc;
	}

	/**
	 * Extract all the record ids from the records, convenience function
	 */	
	
	protected function extractRecordIDs()
	{
		$id = array();
		
		foreach ( $this->records as $record )
		{
			array_push( $id, $record->getXerxesRecord()->getRecordID() );
		}
		
		$id = array_unique($id);
		
		return $id;
	}

	/**
	 * Look for any holdings data in the cache and add it to results
	 */
	
	public function injectHoldings()
	{
		// get the record ids for all search results

		$ids = $this->extractRecordIDs();
		
		// only if there are actually records here
		
		if ( count($ids) > 0 )
		{
			// prefix the engine id
			
			for ( $x=0; $x < count($ids); $x++ )
			{
				$ids[$x] = $this->config->getID() . "." . $ids[$x];
			}
			
			// look for any of our items
			
			$cache = new Cache();
			
			$cache_array = $cache->get($ids);
			
			foreach ( $cache_array as $id => $data )
			{
				$holdings = unserialize($data);
				
				if ( ! $holdings instanceof Holdings   )
				{
					throw new \Exception("cached item ($id) is not an instance of Holdings");
				}
				
				// now associate this item with its corresponding result
			
				for( $x = 0; $x < count($this->records); $x++ )
				{
					$search_result = $this->records[$x];
					
					if ( $this->config->getID() . "." . $search_result->xerxes_record->getRecordID() == $id )
					{
						$search_result->setHoldings($holdings);
					}
						
					$this->records[$x] = $search_result;
				}
			}
		}
	}		
}
