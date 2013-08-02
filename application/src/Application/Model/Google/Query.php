<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Google;

use Application\Model\Search;

/**
 * Ebsco Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * Convert to Google query syntax
	 * 
	 * @return string
	 */
	
	public function toQuery()
	{
		$query = "";
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$query .= ' ' . $term->phrase;
		}
		
		$sites = $this->config->getConfig('site', false);
		
		if ( $sites != null )
		{
			$limit = str_replace(',', ' OR ', $sites);
			
			$query .= "($limit)";
		}
		
		return trim($query);
	}
}
