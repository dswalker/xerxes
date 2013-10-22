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
			$boolean = $term->boolean;
			$value = $this->escapeChars($term->phrase);
			
			if ( $boolean == "")
			{
				$boolean = 'AND';
			}
			
			$query .= '&query-' . $x . '=' . urlencode($boolean . ',');

			if ( $term->field_internal != "")
			{
				$query .= urlencode($term->field_internal . ':');
			}
			
			$query .= urlencode($value);
			
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
			
			$query .= '&facetfilter=' . urlencode($y . ',' . $field . ':' . $this->escapeChars($value) );			
			$y++;
		}
		
		return trim($query);
	}
	
	/**
	 * Escape special characters
	 * 
	 * @param string $string
	 * @return string
	 */
	
	protected function escapeChars($string)
	{
		$string = str_replace(':', '\:', $string);
		$string = str_replace(',', '\,', $string);
		$string = str_replace('(', '\(', $string);
		$string = str_replace(')', '\)', $string);
		
		return $string;
	}
}
