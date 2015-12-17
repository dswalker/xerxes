<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Worldcat;

use Application\Model\Search;

/**
 * Worldcat Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * Convert to Worldcat's SRU query syntax
	 * 
	 * not url encoded
	 * 
	 * @return string
	 */
	
	public function toQuery()
	{
		$query = "";
		
		// prepare the query
		
		// search terms
		
		$x = 1;
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$query .= $this->keyValue($x, $term);
			$x++;
		}
		
		// limits
		
		$limit_array = array();
		
		foreach ( $this->getLimits() as $limit )
		{
			if ( $limit->value == "" )
			{
				continue;
			}
		
			// publication year
		
			if ( $limit->field == "year" )
			{
				$year = $limit->value;
				$year_relation = $limit->relation;
		
				$year_array = explode("-", $year);
		
				// there is a range
		
				if ( count($year_array) > 1 )
				{
					if ( $year_relation == "=" )
					{
						$query .= " and srw.yr >= " . trim($year_array[0]) .
						" and srw.yr <= " . trim($year_array[1]);
					}
		
					// this is probably erroneous, specifying 'before' or 'after' a range;
					// did user really mean this? we'll catch it here just in case
		
					elseif ( $year_relation == ">" )
					{
						array_push($limit_array, " AND srw.yr > " .trim($year_array[1] . " "));
					}
					elseif ( $year_relation == "<" )
					{
						array_push($limit_array, " AND srw.yr < " .trim($year_array[0] . " "));
					}
				}
				else
				{
					// a single year
		
					array_push($limit_array, " AND srw.yr $year_relation $year ");
				}
			}
		
			// language
		
			elseif ( $limit->field == "la")
			{
				array_push($limit_array, " AND srw.la=\"" . $limit->value . "\"");
			}
		
			// material type
		
			elseif ( $limit->field == "mt")
			{
				array_push($limit_array, " AND srw.mt=\"" . $limit->value . "\"");
			}
		}
		
		$limits = implode(" ", $limit_array);
		
		if ( $limits != "" )
		{
			$query = "($query) $limits";
		}
		
		return trim($query);		
	}
	
	/**
	 * Create an SRU boolean/key/value expression in the query, such as:
	 * AND srw.su="xslt"
	 *
	 * @param int $positoin         position in query 
	 * @param QueryTerm $term       term
	 * @param bool $neg				(optional) whether the presence of '-' in $value should indicate a negative expression
	 * 								in which case $boolean gets changed to 'NOT'
	 * @return string				the resulting SRU expresion
	 */
	
	private function keyValue($position = 1, Search\QueryTerm $term, $neg = false)
	{
		// no term, no mas
		
		if ( $term->phrase == "" )
		{
			return "";
		}
		
		// internal field
		
		if ( $term->field_internal == 'undefined' )
		{
			$term->field_internal = 'kw'; // @todo figure out why this hack is necessary on combined
		}
		
		// boolean
		
		$boolean = $term->boolean;
		
		if ( $boolean == "" && $position > 1 )
		{
			$boolean = 'AND';
		}
	
		if ($neg == true && strstr (  $term->phrase, "-" ))
		{
			$boolean = "NOT";
			$term->phrase = str_replace ( "-", "", $term->phrase );
		}
	
		$together = "";
	
		if ( $term->relation == "exact")
		{
			$term->phrase = str_replace ( "\"", "",  $term->phrase );
			$together = " srw." . $term->field_internal . " exact \"  $term->phrase \"";
		}
		else
		{
			$phrase = $term->removeStopWords()->phrase;
			
			$phrase = str_replace(':', '', $phrase);
			
			$parts = $term->normalizedArray($phrase);
			
			foreach ( $parts as $query_part )
			{
				if ( ! preg_match('/[a-zA-Z0-9]{1}/', $query_part))
				{
					continue; // no searchable term, skip
				}
				
				if ($query_part == "AND" || $query_part == "OR" || $query_part == "NOT")
				{
					$together .= " " . $query_part;
				}
				else
				{
					$query_part = str_replace ( '"', '', $query_part );
					$together .= " srw." . $term->field_internal . " = \"  $query_part \"";
				}
			}
		}
	
		return " $boolean ( $together ) ";
	}
}
