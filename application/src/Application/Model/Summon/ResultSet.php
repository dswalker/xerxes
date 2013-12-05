<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Summon;

use Application\Model\Search;

/**
 * Search Results
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class ResultSet extends Search\ResultSet
{
	public $database_recommendations;
	public $best_bets;
	public $query_expansion = array();
	
	public function addRecommendation(Resource $resource)
	{
		if ( $resource instanceof Database )
		{
			$this->database_recommendations[] = $resource;
		}
		else
		{
			$this->best_bets[] = $resource;
		}
	}
	
	public function addQueryExpansion(array $terms)
	{
		$this->query_expansion = $terms;
	}
}
