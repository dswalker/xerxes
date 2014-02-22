<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Saved;

use Application\Model\Search;
use Application\Model\Search\Exception\NotFoundException;
use Application\Model\Search\ResultSet;
use Application\Model\Solr;
use Application\Model\DataMap\SavedRecords;
use Xerxes;
use Xerxes\Mvc\Request;
use Xerxes\Utility\Parser;

/**
 * Saved Records
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Search\Engine 
{
	private $datamap; // data map
	
	/**
	 * New Savd Records Engine
	 */
	
	public function __construct()
	{
		parent::__construct();
		
		$this->datamap = new SavedRecords();
	}

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
	
	public function searchRetrieve( Query $search, $start = 1, $max = 10, $sort = "", $facets = true)
	{
		// never cache!
	
		return $this->doSearch( $search, $start, $max, $sort, $facets);
	}	
	
	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return ResultSet
	 */
	
	protected function doGetRecord( $id )
	{
		$results = new Search\ResultSet($this->config);
		
		// get the record from the database
		
		$record = $this->datamap->getRecordByID($id);
		
		// no record found?
		
		if ( $record == null )
		{
			$results->total = 0;
			return $results;
		}
		
		// got one
		
		$results->total = 1;
		
		$result = $this->createSearchResult($record);
		
		// corrupted record, look out
		
		if ( $result->corrupted == true )
		{
			$fixed = false;
				
			// go back to the original search engine and fetch it again
				
			$class_name = 'Application\\Model\\' . ucfirst($result->source) . '\\Engine';
				
			if ( class_exists($class_name) )
			{
				try 
				{
					$engine = new $class_name();
			
					$new_results = $engine->getRecord($result->original_id);
			
					if ( $new_results->total > 0 )
					{
						$result = $new_results->getRecord(0);
						$fixed = true;
					}
				}
				catch (NotFoundException $e)
				{
					$data = $record->marc;
					
					if ( strstr($data, 'Xerxes_TransRecord') )
					{
						$data = '<?xml version="1.0"?>' . Parser::removeLeft($data, '<?xml version="1.0"?>');
						$data = Parser::removeRight($data, '</xerxes_record>') . '</xerxes_record>';
					}
					else
					{
					}
				}
			}
				
			if ( $fixed == false )
			{
				throw new \Exception('Sorry, this record has been corrupted');
			}
		}		
		
		// if a catalog record, fetch holdings
		
		if ( $record->xerxes_record instanceof Solr\Record )
		{
			try
			{
				$engine = new Solr\Engine();
		
				$solr_results = $engine->getRecord($result->original_id);
				$holdings = $solr_results->getRecord(0)->getHoldings();
				$result->setHoldings($holdings);
			}
			catch ( \Exception $e )
			{
				trigger_error('saved records holdings lookup: ' . $e->getMessage(), E_USER_WARNING);
			}
		}

		$results->addResult($result);
		
		return $results;
	}
	
	/**
	 * Get multiple records by id
	 * 
	 * @param array $ids
	 * @return Search\ResultSet
	 */
	
	public function getRecords(array $ids)
	{
		$results = new Search\ResultSet($this->config);
		$records = $this->datamap->getRecordsByID($ids);
		
		$results->total = count($records);
		
		foreach ( $records as $record )
		{
			$result = $this->createSearchResult($record);
			$results->addResult($result);
		}
		
		return $results;
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
			$group->public = "Labels"; // @todo: i18n this?
				
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
	 * Create a Result from the supplied Xerxes Record
	 * @param Record $record
	 */
	
	protected function createSearchResult(Record $record)
	{
		$result = new Result($record, $this->config);
		
		// set the internal id as the record id, not the original
		
		$result->xerxes_record->setRecordID($record->id);
		
		$result->id = $record->id;
		$result->username = $record->username;
		$result->source = $record->source;
		$result->original_id = $record->original_id;
		$result->timestamp = $record->timestamp;
		$result->tags = $record->tags;
		
		return $result;		
	}

	/**
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}	
	
	/**
	 * Return the Saved Records query object
	 *
	 * @param Request $request
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
