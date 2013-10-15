<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Ebsco\Discovery;

use Application\Model\Search;

/**
 * Ebsco Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * Convert to Ebsco query syntax
	 * 
	 * not url encoded
	 * 
	 * @return string
	 */
	
	public function toQuery()
	{
		$query = "";
		
		// search terms

		$x = 1;
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$query .= '&query-' . $x . '=' . $term->boolean . ',';

			if ( $term->field_internal != "")
			{
				$query .= urlencode($term->field_internal . ':');
			}
			
			$query .= urlencode($term->phrase);
			
			$x++;
		}
		
		// limits
		
		$y = 1;
		
		foreach ( $this->getLimits(true) as $limit )
		{
			$field = $limit->field;
			$value = $limit->value;
			
			if ( is_array($value) )
			{
				$value = implode(',', $value);
			}
			
			$query .= '&facetfilter=' . $y . ',' . $field . ':' . urlencode($value);
			
			$y++;
		}
		
		return trim($query);
	}
}
