<?php

namespace Application\Model\DataMap;

use Application\Model\Search\Query,
	Application\Model\Search\ResultSet,
	Xerxes\Utility\DataMap;

/**
 * Search Stats
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
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
