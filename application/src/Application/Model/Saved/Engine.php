<?php

namespace Application\Model\Saved;

use Application\Model\Search;
use Application\Model\DataMap\SavedRecords;
use Xerxes\Mvc\Request;

/**
 * Saved Records
 * 
 * @author David Walker
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
	
	public function getHits( Search\Query $search )
	{
		return $this->doSearch( $search, 0, 0 ); 
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
	 * @return ResultSet
	 */
	
	public function getRecord( $id )
	{
		$results = new Search\ResultSet($this->config);
		
		$record = $this->datamap->getRecordByID($id);
		
		// no record found?
		
		if ( $record == null )
		{
			$results->total = 0;
			return $results;
		}
		
		// got one
		
		$results->total = 1;
		
		// add it to the results
		
		$result = $this->createSearchResult($record);
		$results->addResult($result);
		
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
	}
	
	public function getConfig()
	{
		return Config::getInstance();
	}
	
	protected function doSearch(Query $search, $start = 1, $max = 10, $sort = "")
	{
		$username = $search->getQueryTerm(0)->phrase;
		
		$label = $search->getLimit("facet.label");
		$format = $search->getLimit("facet.format");
		
		$results = new Search\ResultSet($this->config);
		$results->total = $this->datamap->totalRecords($username, $label->value, $format->value);
		
		// just the hit count please
		
		if ( $max == 0 )
		{
			return $results;
		}

		// no we want actual records too
		
		$records = array();
		
		if ( $label->value != "" ) // tag
		{
			$records = $this->datamap->getRecordsByLabel($username, $label->value, $sort, $start, $max);
		}
		elseif ( $format->value != "" ) // format facet
		{
			$records = $this->datamap->getRecordsByFormat($username,  $format->value, $sort, $start, $max);
		}
		else // just the regular results
		{
			$records = $this->datamap->getRecords($username, null, $sort, $start, $max);
		}
		
		// convert them into our model
		
		foreach ( $records as $record )
		{
			$result = $this->createSearchResult($record);
			$results->addResult($result);
		}
		
		// facets
		
		$facets = new Search\Facets();
		
		// formats
		
		$formats = $this->datamap->getFormats($username);
		
		if ( count($formats) > 0 )
		{
			$group = new Search\FacetGroup();
			$group->name = "format";
			$group->public = "Formats"; // @todo: i18n this?
			
			foreach ( $formats as $format )
			{
				$facet = new Search\Facet();
				$facet->name = $format->format;
				$facet->count = $format->total;
				
				$group->addFacet($facet);				
			}
			
			$facets->addGroup($group);
		}

		// labels
		
		$tags = $this->datamap->getTags($username);
		
		if ( count($tags) > 0 )
		{
			$group = new Search\FacetGroup();
			$group->name = "label";
			$group->public = "Label"; // @todo: i18n this?
				
			foreach ( $tags as $tag )
			{
				$facet = new Search\Facet();
				$facet->name = $tag->label;
				$facet->count = $tag->total;
		
				$group->addFacet($facet);
			}
			
			$facets->addGroup($group);
		}		
		
		$results->setFacets($facets);
		
		return $results;
	}
	
	/**
	 * Create a Result from the suppled Xerxes Record
	 * @param Record $record
	 */
	
	protected function createSearchResult(Record $record)
	{
		// set the internal id as the record id, not the original
		
		$record->xerxes_record->setRecordID($record->id);
		
		$result = new Result($record->xerxes_record, $this->config);
		$result->id = $record->id;
		$result->username = $record->username;
		$result->source = $record->source;
		$result->original_id = $record->original_id;
		$result->timestamp = $record->timestamp;
		
		return $result;		
	}
	
	/**
	 * Return the Saved Records query object
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
