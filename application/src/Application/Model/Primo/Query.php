<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Primo;

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
	 * primo server address
	 * @var string
	 */
	protected $server;
	
	/**
	 * primo institution id
	 * @var string
	 */
	protected $institution;
	
	/**
	 * not sure what this is, 'vendor' id?
	 * @var string
	 */
	protected $vid;
	
	/**
	 * scope value(s)
	 * @var array
	 */
	protected $loc = array();

	/**
	 * on campus or not
	 * @var bool
	 */
	protected $on_campus = true;
	
	/**
	 * Create a Primo Query
	 *
	 * @param Request $request
	 * @param Config $config
	 */
	
	public function __construct(Request $request = null, Config $config = null )
	{
		parent::__construct($request, $config);
		
		// server info
		
		$this->server = $this->config->getConfig('PRIMO_ADDRESS', true);
		$this->server = rtrim($this->server, '/');
		
		// institutional id's
		
		$this->institution = $this->config->getConfig('INSTITUTION', true);
		$this->vid = $this->config->getConfig('VID', false);
		
		// scope
		
		$loc = $this->config->getConfig('LOC', false, $this->request->getParam('scope'));
		
		if ( $loc != "" )
		{
			$this->loc = explode(";", $loc);
		}
	}
	
	/**
	 * Whether user is on-campus or not
	 * 
	 * @param unknown_type $bool
	 */
	
	public function setOnCampus($bool)
	{
		$this->on_campus = $bool;
	}
	
	/**
	 * Convert to Primo individual record syntax
	 *
	 * @param string $id
	 * @param string $type  'exact' or 'contains'
	 * @return Url
	 */
	
	public function getRecordUrl($id, $type = 'exact')
	{
		$id = urlencode($id);
		
		$url = $this->server . '/xservice/search/brief?' .
			"&query=rid,$type,$id" .
			'&indx=1&bulkSize=1&pcAvailability=true';
		
		$url = $this->addLocationParams($url);
		
		// echo $url;		
		
		return new Url($url);
	}
	
	/**
	 * Convert to Primo query syntax
	 * 
	 * @return Url
	 */
	
	public function getQueryUrl()
	{
		$query = ""; // query
		$discipline = ""; // disciplines
		$start_date = ""; // pub start date
		$end_date = ""; // pub end date
		
		$search_terms = "";
		$x = 1;
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$query .= "&query=" . $term->field_internal . ",contains," . urlencode($term->phrase);
		}
		
		// limit to local holdings unless told otherwise
		
		$this->holdings_only = $this->config->getConfig('LIMIT_TO_HOLDINGS', false, true);
		
		
		#### limits
		
		// always exclude these
		
		$formats = $this->config->getConfig("EXCLUDE_FORMATS");
		
		if ( $formats != "" )
		{
			foreach ( explode(',', $formats) as $format )
			{
				$query .= "&query_exc=facet_pfilter,exact," . urlencode($format);
			}
		}
		
		foreach ( $this->getLimits() as $limit )
		{
			$value = $limit->value;
			
			// make all values in array for simplicity of handling
			
			if ( ! is_array($value) )
			{
				$value = array($value);
			}
			
			// these fields need to be translated back into internal values
			// we do it this way, as opposed to keys, to support legacy url's
			
			if ( $limit->field == 'pfilter' )
			{
				for ( $x = 0; $x < count($value); $x++)
				{
					$value[$x] = Format::fromDisplay($value[$x]);
				}
			}
			
			if ( $limit->field == 'lang' )
			{
				for ( $x = 0; $x < count($value); $x++)
				{
					$value[$x] = Language::fromDisplay($value[$x]);
				}
			}
			
			// legacy support for summon disciplines
				
			if ( $limit->field == 'Discipline')
			{
				// only grab the summon disciplines that can be mapped to those in primo 
				
				$value = Discipline::convertToPrimoDisciplines($value);
				$value = implode(';', $value);
				
				if ( count($value) > 0 )
				{
					$discipline = "&pyrCategories=" . urlencode($value);
				}
				
				continue;
			}
			
			// combined multiple values into a single line
			// we use a pipe here so we can nix commas from the values themselves below
				
			$value = implode('|', $value);
			
			// now separate the actual values by comma
			
			$value = str_replace(',', ' ', $value);
			$value = str_replace('|', ',', $value); 
			
			// full-text
			
			if ( $limit->field == 'IsFullText')
			{
				if ( $limit->value == 'false' )
				{
					$this->holdings_only = false;
				}
				elseif ( $limit->value == 'true' )
				{
					$this->holdings_only = true;
				}
			}
			
			// scholarly
			
			elseif ( $limit->field == "IsPeerReviewed" || $limit->field == 'IsScholarly')
			{
				if ($value == "true")
				{
					$query .= "&query_inc=facet_tlevel,exact,peer_reviewed";
				}
			}
			
			// dates
			
			elseif ($limit->field == 'creationdate')
			{
				if ( $limit->value == 'start')
				{
					$start_date = $limit->display;
				}
				elseif ( $limit->value == 'end')
				{
					$end_date = $limit->display;
				}
			}
			else // regular field
			{
				$type = 'query_inc';
				
				if ( $limit->boolean == "NOT" )
				{
					$type = 'query_exc';
				}
				
				$query .= "&$type=facet_" . $limit->field . ",exact," . urlencode($value);
			}
		}
		
		if ( $start_date != "" || $end_date != "" )
		{
			if ( $start_date == "" )
			{
				$start_date = '1000';
			}
			
			if ( $end_date == "" )
			{
				$end_date = '9999';	
			}
			
			$query .= '&query_inc=facet_searchcreationdate,exact,' . "[$start_date TO $end_date]";
		}
		
		// on campus as string
		
		$on_campus = "true";
		
		if ( $this->on_campus == false )
		{
			$on_campus = "false";
		}
		
		// create the url
		
		$url = $this->server . '/xservice/search/brief?' .
			$query . 
			$discipline . 
			'&indx=' . $this->start .
			'&bulkSize=' . $this->max;

		// full-text
		
		if ($this->holdings_only == false)
		{
			$url .= "&pcAvailability=true"; // this seems backwards but is correct
		}
		else
		{
			$url .= "&pcAvailability=false"; // this seems backwards but is correct
		}
		
		// institutional params
		
		$url = $this->addLocationParams($url);
			
		if ( $this->sort != "" )
		{
			$url .= '&sortField=' . $this->sort;
		}
		
		echo $url . "<br>\n";
		
		return new Url($url);
	}
	
	protected function addLocationParams($url)
	{
		$url .= '&lang=eng&institution=' . $this->institution;
		
		$on_campus = "true";
		
		if ( $this->on_campus == false )
		{
			$on_campus = "false";
		}
		
		$url .= '&onCampus=' . $this->on_campus;
		
		if ( $this->vid != "" )
		{
			$url .= "&vid=" . $this->vid;
		}
		
		foreach ( $this->loc as $loc )
		{
			$url .= "&loc=" . $loc;
		}
		
		return $url;
	}
}