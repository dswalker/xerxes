<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Google;

/**
 * Search and retrieve records Google search appliance
 *
 * @author David Walker <dwalker@calstate.edu> 
 */

class Appliance 
{
	private $url = "";
	private $parameters = array();
	
	function __construct($host = "google.calstate.edu")
	{
		$this->url = "http://$host/search?";
	}
	
	function search($query, $limit = 10, $site = "")
	{
		$final = array();
		
		if ( $site != "")
		{
			$query = "$query site:$site";
		}
		
		$this->url .= "&q=" . urlencode($query);
		
		// this seems to send a header or something that in itself
		// causes the google appliance to return as xml?  weird
		
		$xml = simplexml_load_file($this->url);
		
		$x = 0;
		
		$results = $xml->xpath("//RES/R");
		
		if ( $results !== false)
		{
			foreach ( $results as $result )
			{
				if ( $x >= $limit )
				{
					break;
				}
				
				$record = new Record();
				
				$record->mime_type = (string) $result["MIME"];
				$record->url = (string) $result->U;
				$record->title = strip_tags((string) $result->T);
				$record->snippet = strip_tags((string) $result->S);
				
				$final[] = $record;
				
				$x++;
			}
		}
		
		return $final;		
	}
}

class Record 
{
	public $mime_type;
	public $url;
	public $title;
	public $snippet;
}