<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\DataMap;

use Application\Model\Search\Query;
use Application\Model\Search\ResultSet;
use Xerxes\Utility\DataMap;

/**
 * Search Stats
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Stats extends DataMap
{
	public function logSearch($module, Query $query, ResultSet $results)
	{
		$term = $query->getQueryTerm(0);
		
		$params = array();
		
		$params[':ip_address'] = $query->getUser()->getIpAddress();
		$params[':module'] = $module;
		$params[':field'] = $term->field;
		$params[':phrase'] = substr($term->phrase,0,999);
		$params[':hits'] = $results->getTotal();
		
		$sql = 'INSERT INTO xerxes_search_stats '  .
				'( ip_address, stamp, module, field, phrase, hits ) ' .
				'VALUES (:ip_address, NOW(), :module, :field, :phrase, :hits)';
		
		$this->insert($sql, $params);
	}
}
