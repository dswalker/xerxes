<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Solr;

use Application\Model\Search;

/**
 * Solr Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * Convert to Solr query syntax
	 * 
	 * Includes the URL parameters &q, &qf, and &pf
	 * @throws \Exception
	 * @return string
	 */
	
	public function toQuery()
	{
		$query = ""; // search query
		$type = ""; // dismax or standard
		
		## search terms
		
		$terms = $this->getQueryTerms();
		
		// check if a query was supplied
		
		if ( count($terms) == 0 )
		{
			throw new \Exception("No search terms supplied");
		}
		
		//@todo: get rid of this as we upgrade to solr 3.x and get e-dismax
		
		// decide between basic and dismax handler
		
		$term = $terms[0]; // get just the first term for now
		
		$trunc_test = $this->config->getFieldAttribute($term->field_internal, "truncate");
		
		// use dismax if this is a simple search, that is:
		// only if there is one phrase (i.e., not advanced), no boolean OR and no wildcard
		
		if ( count($terms) == 1 &&
				! strstr($term->phrase, " OR ") &&
				! strstr($term->phrase, "*") &&
				$trunc_test == null )
		{
			# dismax
				
			$type = "&defType=dismax";
			
			$term = $terms[0];
				
			$phrase = $term->phrase;
			$phrase = strtolower($phrase);
			$phrase = str_replace(" NOT ", " -", $phrase);
				
			if ( $term->field_internal != "" )
			{
				$query .= "&qf=" . urlencode($term->field_internal);
				$query .= "&pf=" . urlencode($term->field_internal);
			}
			
			$query .= "&q=" . urlencode($phrase);
		}
		else
		{
			# standard
				
			$query = "";
				
			foreach ( $terms as $term )
			{
				$phrase = $term->phrase;
				$phrase = strtolower($phrase);
				$phrase = str_replace(':', '', $phrase);
				$phrase = $this->alterQuery($phrase, $term->field_internal, $this->config);
			
				// break up the query into words
			
				$arrQuery = $term->normalizedArray( $phrase );
			
				// we'll now search for this term across multiple fields
				// specified in the config
			
				if ( $term->field_internal != "" )
				{
					// we'll use this to get the phrase as a whole, but minus
					// the boolean operators in order to boost this
						
					$boost_phrase = "";
					
					foreach ( $arrQuery as $strPiece )
					{
						// just add the booelan value straight-up
			
						if ( $strPiece == "AND" || $strPiece == "OR" || $strPiece == "NOT" )
						{
							$query .= " $strPiece ";
							continue;
						}
			
						$boost_phrase .= " " . $strPiece;
			
						// try to mimick dismax query handler as much as possible
			
						$query .= " (";
						$local = array();
		
						// take the fields we're searching on,
		
						foreach ( explode(" ", $term->field_internal) as $field )
						{
							// split them out into index and boost score
			
							$parts = explode("^",$field);
							$field_name = $parts[0];
							$boost = "";
								
							// make sure there really was a  boost score
								
							if ( array_key_exists(1,$parts) )
							{
								$boost = "^" . $parts[1];
							}
							
							// put them together
							
							array_push($local, $field_name . ":" . $strPiece . $boost);
						}
		
						$query .= implode(" OR ", $local);
							
						$query .= " )";
					}
							
					// $boost_phrase = trim($boost_phrase);
					// $query = "($query) OR \"" . $boost_phrase . '"';
				}
			}
							
			$query = "&q=" . urlencode($query);
		}
		
		// facets selected
		
		$start_date = '*';
		$end_date = '*';
		
		foreach ( $this->getLimits(true) as $facet_chosen )
		{
			$value = $facet_chosen->value;
			$field = $facet_chosen->field;
			
			// date field
						
			if ( $field == 'publishDate')
			{
				if ( $value == 'start')
				{
					$start_date = $facet_chosen->display;
				}
				elseif ( $value == 'end')
				{
					$end_date = $facet_chosen->display;
				}
				
				continue;
			}
			
			// regular field
			
			$boolean = 'OR';
			$negative = '';
			
			if ( $facet_chosen->boolean == 'NOT')
			{
				$boolean = 'NOT';
				$negative = 'NOT';
			}			

			// multi-selected
			
			if ( is_array($value) )
			{
				for ( $x = 0; $x < count($value); $x++ )
				{
					// put quotes around non-keyed terms
					
					if ( $facet_chosen->key != true )
					{
						for( $x =0; $x < count($value); $x++)
						{
							$value[$x] = '"' . $value[$x] . '"';
						}
					}
				}
				
				$tag = urlencode( '{!tag=' . $facet_chosen->field . '}');
				
				$composite = $negative . " $field:" . implode(" $boolean $field:", $value);
				
				$query .= '&fq=' . $tag . urlencode($composite);
			}
			else
			{
				// put quotes around non-keyed terms
					
				if ( $facet_chosen->key != true )
				{
					$value = '"' . $value . '"';
				}
				
				$tag = urlencode( '{!tag=' . $facet_chosen->field . '}');
				
				$composite = $negative . " $field:$value";
					
				$query .= '&fq=' . $tag . urlencode($composite);				
			}
		}
		
		if ( $start_date != '*' || $end_date != '*')
		{
			$value = "[$start_date TO $end_date]";
				
			$tag = urlencode( '{!tag=publishDate}');
				
			$query .= '&fq=' . $tag . urlencode( "$field:$value");
		}
		
		// limits set in config
		
		$auto_limit = $this->config->getConfig("LIMIT", false);
		
		if ( $auto_limit != null )
		{
			$query .= "&fq=" . urlencode($auto_limit);
		}
		
		$final = $type . $query;

		return $final;
	}
}
