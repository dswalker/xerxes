<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Saved\ReadingList;

use Application\Model\Saved;
use Application\Model\Search;
use Application\Model\DataMap\ReadingList;
use Xerxes\Lti\Basic;

/**
 * Reading List
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Engine extends Saved\Engine 
{
	protected $context_id; // basic lti context id
	protected $reading_list; // readinglist datamap
	
	/**
	 * New reading list search engine
	 * 
	 * @param Basic $basic_lti
	 */
	
	public function __construct(Basic $basic_lti)
	{
		$this->context_id = $basic_lti->getID();
		$this->reading_list = new ReadingList($this->context_id);
		
		parent::__construct();
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
		$records = $this->reading_list->getRecords();
		
		$results = new Search\ResultSet($this->config);
		$results->total = count($records);
		
		// convert them into our model
		
		foreach ( $records as $record )
		{
			$result = $this->createSearchResult($record);
			$results->addResult($result);
		}
		
		return $results;
	}
}
