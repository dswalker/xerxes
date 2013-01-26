<?php

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
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$query .= $this->keyValue($term);
		}
		
		// limits
		
		$limit_array = array();
		
		foreach ( $this->getLimits(true) as $limit )
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
	 * @param QueryTerm $term
	 * @param bool $neg				(optional) whether the presence of '-' in $value should indicate a negative expression
	 * 								in which case $boolean gets changed to 'NOT'
	 * @return string				the resulting SRU expresion
	 */
	
	private function keyValue(Search\QueryTerm $term, $neg = false)
	{
		if ( $term->phrase == "" )
		{
			return "";
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
				
			foreach ( $term->normalizedArray($phrase) as $query_part )
			{
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
	
		return " " . $term->boolean . " ( $together ) ";
	}
}
