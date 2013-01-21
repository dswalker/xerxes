<?php

namespace Application\Model\Search;

use Xerxes\Utility\Parser;

/**
 * Search Facet Group
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class FacetGroup
{
	public $name; // internal name
	public $public; // public facing name
	public $display; // to display
	public $facets = array();

	/**
	 * Add a facet
	 * 
	 * @param Facet $facets
	 */
	
	public function addFacet(Facet $facet)
	{
		$facet->count = Parser::number_format($facet->count);
		
		array_push($this->facets, $facet);
	}
	
	/**
	 * Get the facets
	 * 
	 * @return array of Facet's
	 */	
	
	public function getFacets()
	{
		return $this->facets;
	}
	
	/**
	 * Sort facets by name
	 * 
	 * @param string $order		'desc' or 'asc'
	 */
	
	public function sortByName($order)
	{
		$names = array();
		
		// extract the names
		
		foreach ( $this->facets as $facet )
		{
			array_push($names, (string) $facet->name);
		}
		
		// now sort them, keeping the key associations
		
		if ( $order == "desc")
		{
			arsort($names);
		}
		elseif ( $order == "asc")
		{
			asort($names);
		}
		else
		{
			throw new \Exception("sort order must be 'desc' or 'asc'");
		}
		
		// now unset and re-add the facets based on those keys
		
		$facets = $this->facets;
		$this->facets = array();
		
		foreach ( array_keys($names) as $key )
		{
			array_push($this->facets, $facets[$key]);			
		}
	}

	/**
	 * Convert date facets based on Lucene types into decade groupings
	 * 
	 * @param array $facet_array	array of facets
	 * @param int $bottom_decade	default is 1900 
	 * @return array				associative array of facets and display info
	 */
	
	public function luceneDateToDecade($facet_array, $bottom_decade = 1900)
	{
		// ksort($facet_array); print_r($facet_array);
		
		$bottom_year = $bottom_decade - 1; // the year before the bottom decade
		
		$decades = array();
		$decade_display = array();
		
		$top = date("Y"); // keep track of top most year
		$bottom = $bottom_year; // and the bottom most year
		$top_of_bottom = 0; // the top most of the bottom group
		
		foreach ( $facet_array as $year => $value)
		{
			// set a new top 
			
			if ( $year > $top )
			{
				$top = $year;			
			}
			
			// strip the end year, getting just century and decade
			
			$dec = substr($year,0,3);
			
			// if the end date in this decade is beyond the current year, then
			// we are in the current decade
			
			$dec_end = (int) $dec . "9";
							
			if ( $dec_end > date("Y") )
			{
				$display = $dec . "0-present";
			}
			else
			{
				// otherwise we're going DDD0-D9
				
				$display = $dec . "0-" . substr($dec,2,1) . "9";
			}
			
			// but the actual query is the dates themselves
							
			$query = "[" . $dec . "0 TO " . $dec . "9]";
			
			// for the old stuff, just group it together
			
			$bottom_decade = $bottom_decade - 1;
			
			if ( $year <= $bottom_year )
			{
				// set a new bottom for display purposes
				
				if ( $year < $bottom )
				{
					$bottom = $year;
				}

				// and the top of the bottom group
				
				if ( $year > $top_of_bottom )
				{
					$top_of_bottom = $year;
				}				
				
				$query = "[-999999999 TO $bottom_year]";
				$display = "before-$bottom_year";
			}
			
			$decade = array();
			$decade["display"] = $display;
			$decade["query"] = $query;
		
			$query = $decade["query"];
						
			$decade_display[$query] = $decade["display"];
					
			if ( array_key_exists($query, $decades) )
			{
				$decades[$query] += (int) $value; 
			}
			else
			{
				$decades[$query] = (int) $value; 
			}
		}
		
		// now replace the 'present' and 'bottom' place holders 
		// with actual top and bottom year values
		
		foreach ( $decade_display as $key => $value )
		{
			if ( strstr($value,"present") )
			{
				$decade_display[$key] = str_replace("present", $top, $value);
			}
			if ( strstr($value,"before") )
			{
				$decade_display[$key] = str_replace("before", $bottom, $value);
			}
			
			// now eliminate same year scenario
			
			$date = explode("-", $decade_display[$key]);
			
			if ( $date[0] == $date[1] )
			{
				$decade_display[$key] = $date[0];
			}
		}
		
		// sort em in date order
		
		krsort($decades);
		
		$final = array();
		$final["decades"] = $decades;
		$final["display"] = $decade_display;
		
		return $final;
	}
}
