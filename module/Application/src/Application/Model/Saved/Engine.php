<?php

namespace Application\Model\Saved;

use Application\Model\Search,
	Application\Model\DataMap\SavedRecords;

/**
 * Saved Records
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Engine extends Search\Engine 
{
	private $datamap; // data map
	
	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		$this->datamap = new SavedRecords();
	}

	/**
	 * Return the total number of saved records
	 * 
	 * @return int
	 */		
	
	public function getHits( Query $search )
	{
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
	
	public function searchRetrieve( Query $search, $start = 1, $max = 10, $sort = "")
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
	}

	/**
	 * Get record to save
	 * 
	 * @param string	record identifier
	 * @return int		internal saved id
	 */	
	
	public function getRecordForSave( $id )
	{
	}
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	protected function doSearch(Query $search, $start = 1, $max = 10, $sort = "")
	{
		$username = $search->getQueryTerm(0)->phrase;
		$label = $search->getLimit("label");
		$format = $search->getLimit("format");
		
		$results = new Search\ResultSet($this->config);
		$results->total = $this->datamap->totalRecords($username, $label, $format);
		
		// just the hit count please
		
		if ( $max == 0 )
		{
			return $results;
		}

		// no we want actual records too
		
		$records = array();
		
		if ( $label != "" )
		{
			$records = $this->datamap->getRecordsByLabel($username, $label, $sort, $start, $max);
		}
		elseif ( $format != "" )
		{
			$records = $this->datamap->getRecordsByFormat($username, $format, $sort, $start, $max);
		}
		else
		{
			$records = $this->datamap->getRecords($username, null, $sort, $start, $max);
		}
		
		foreach ( $records as $record )
		{
			// set the internal id as the record id, not the original
			
			$record->xerxes_record->setRecordID($record->id);
			
			$result = new Search\Result($record->xerxes_record, $this->config);
			
			$results->addResult($result);
		}
		
		return $results;
	}
}
