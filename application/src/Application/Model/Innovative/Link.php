<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Innovative;

use Application\Model\Search\LinkInterface;
use Application\Model\Search\Query;
use Xerxes\Utility\HttpClient;

/**
 * Innovative link search engine
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Link implements LinkInterface
{
	protected $server;
	
	public function __construct($server)
	{
		$this->server = "http://$server/"; 
	}
	
	public function getTotal( Query $query )
	{
		$total = 0;
		$url = $this->getUrl($query);
		
		$client = new HttpClient();
		$response = $client->getUrl($url);
		
		// extract the total number of hits in the results page;
		
		$arrMatches = array();
		
		if ( preg_match( '/\(1-[0-9]{1,2} of ([0-9]{1,10})\)/', $response, $arrMatches ) != 0 )
		{
			$total = (int) $arrMatches[1];
		}
		elseif ( ! stristr( $response, "No matches found" ) && ! stristr( $response, "NO ENTRIES FOUND" ) )
		{
			$total =  1; // only found one response, catalog jumped right to full display
		}
		
		return $total;
	}
	
	public function getUrl( Query $query )
	{
		$term = $query->getQueryTerm(0);
		$phrase = $term->phrase;
		
		switch( $term->field )
		{
			case "title": 
				$phrase = "t:($phrase)"; 
				break;
				
			case "subject": 
				$phrase = "s:($phrase)"; 
				break;
				
			case "author": 
				$phrase = "a:($phrase)"; 
				break;
		}
		
		$url = $this->server . "search/?searchtype=X&searcharg=" . urlencode($phrase);
		
		return $url;
	}
}