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
use Application\Model\Search\Query\Url;
use Xerxes\Mvc\Request;

/**
 * Solr Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * solr server address
	 * @var string
	 */
	protected $server;
	
	/**
	 * Create a Solr Query
	 *
	 * @param Request $request
	 * @param Config $config
	 */
	
	public function __construct(Request $request = null, Config $config = null )
	{
		parent::__construct($request, $config);
		
		if ( $this->config != null )
		{
			// server address
			
			$this->server = $this->config->getConfig('SOLR', true);
			$this->server = rtrim($this->server, '/');
			$this->server .= "/select/?version=2.2";
			
			// limits set in config
			
			$auto_limit = $this->config->getConfig("LIMIT", false);
			
			if ( $auto_limit != null )
			{
				$this->server .= "&fq=" . urlencode($auto_limit);
			}
		}
	}
	
	/**
	 * Convert to Solr individual record syntax
	 *
	 * @param string $id 
	 * @return Url
	 */	
	
	public function getRecordUrl($id)
	{
		$id = str_replace(':', "\\:", $id);
		
		$url = new Url($this->server . "&q=" . urlencode("id:$id"));
		return $url;
	}
	
	/**
	 * Convert to Solr query syntax
	 * 
	 * @throws \Exception
	 * @return Url
	 */
	
	public function getQueryUrl()
	{
		$url = ""; // final url
		$query = ""; // search query
		$type = ""; // dismax or standard
		
		## search terms
		
		$terms = $this->getQueryTerms();
		
		// check if a query was supplied
		
		if ( count($terms) == 0 )
		{
			throw new \Exception("No search terms supplied");
		}
		
		$term = $terms[0]; // get just the first term for now
		
		// isbn
		
		if ( preg_match('/(?:-?\d){10,13}/', $term->phrase ) )
		{
			$term->phrase = str_replace('-', '', $term->phrase);
		}
		
		// decide between basic and dismax handler
		
		//@todo: get rid of this as we upgrade to solr > 3.x and get e-dismax
		
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
				
				if ( $term->boolean != "" )
				{
					$query .= $term->boolean . ' ';
				}
				
				if ( $term->field != "keyword" )
				{
					$field_no_boost = explode(' ', preg_replace('/[\^0-9\.]/', '', $term->field_internal));
					
					$query .= '( ';
					$x = 1;
						
					foreach ( $field_no_boost as $field_straight )
					{
						if ( $x != 1 )
						{
							$query .= 'OR';
						}
						
						$query .= " $field_straight: $phrase ";
						
						$x++;
					}
						
					$query .= ') ';
				}
				else
				{
					$query .= $phrase . ' ';
				}
			}
			
			// echo "<p>$query</p>";
			
			$query = "&q=" . urlencode(trim($query));
		}
		
		// facets selected
		
		$start_date = '*';
		$end_date = '*';
		
		foreach ( $this->getLimits() as $facet_chosen )
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

		// start
		
		if ( $this->start > 0)
		{
			$this->start--; // solr is 0-based
		}
		
		// now the url
		
		$url = $this->server . $final;

		$url .= '&start=' . $this->start . '&rows=' . $this->max . '&sort=' . urlencode($this->sort);
		
		if ( $this->facets == true )
		{
			$url .= "&facet=true&facet.mincount=1";
			
			foreach ( $this->config->getFacets() as $facet => $attributes )
			{
				$sort = (string) $attributes["sort"];
				$max = (string) $attributes["max"];
				$type = (string) $attributes["type"];
				
				if ( $type == 'date' )
				{
					$sort = 'index';
				}
				
				$url .= "&facet.field=" . urlencode("{!ex=$facet}$facet");

				if ( $sort != "" )
				{
					$url .= "&f.$facet.facet.sort=$sort";
				}				
				
				if ( $max != "" )
				{
					$url .= "&f.$facet.facet.limit=$max";
				}					
			}
		}
		
		// make sure we get the score
		
		$url .= "&fl=*+score";
		
		// echo $url;
		
		return new Url($url);
	}
}
