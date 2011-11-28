<?php

/**
 * Primo Central Slim
 * 
 * @author David Walker
 * @copyright 2010 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Primo
 */

class Xerxes_Model_Primo_Slim_Engine extends Xerxes_Model_Primo_Engine 
{
	/**
	 * Search and return results
	 * 
	 * @param Xerxes_Model_Search_Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Xerxes_Model_Search_Results
	 */	
	
	public function searchRetrieve( Xerxes_Model_Search_Query $search, $start = 1, $max = 10, $sort = "")
	{
		// get the results
		
		$results = parent::searchRetrieve( $search, $start, $max, $sort);
		
		// take out subject hit counts
		
		foreach ( $results->getFacets() as $facets )
		{
			foreach ( $facets as $group )
			{
				if ( $group->name == "topic" )
				{
					foreach ( $group->getFacets() as $facet )
					{
						$facet->count = "";
					}
				}
			}
		}
		
		return $results;
	}		
	
	/**
	 * Do the actual fetch of an individual record
	 * 
	 * @param string	record identifier
	 * @return Xerxes_Model_Search_Results
	 */		
	
	protected function doGetRecord( $id )
	{
		$results = $this->doSearch("rid:($id)", 1, 1);
		return $results;
	}

	/**
	 * Do the actual search
	 * 
	 * @param mixed $search							string or Xerxes_Model_Search_Query, the search query
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Xerxes_Model_Search_Results
	 */

	protected function doSearch( $search, $start = 1, $max = 10, $sort = "" )
	{
		// parse the query
		
		$query = "";
		
		if ( $search instanceof Xerxes_Model_Search_Query )
		{
			// first term for now
			
			$terms = $search->getQueryTerms();
			$term = $terms[0];
			
			$term->phrase = str_replace(' ', '_', $term->phrase);
			$term->phrase = preg_replace('/\W/', ' ', $term->phrase);
			$term->phrase = str_replace('_', ' ', $term->phrase);			
			
			if ( $term->field_internal != "" )
			{
				 $query .= " " . $term->field_internal . ":(" . $term->phrase . ")";
			}
			else 
			{
				 $query .= " " .  $term->phrase;
			}
			
			$query = trim($query);
			
			// pci query manipulation
			
			$query = "($query) OR title:($query) OR title:($query) NOT (book review)";			
			
			// pseudo-limits
			
			foreach ( $search->getLimits(true) as $limit )
			{
				switch ($limit->field)
				{
					case 'topic':
						$query .= " AND sub:(" . $limit->value . ")";
						break;

					// this doesn't work, really
					/* 					
					case 'creationdate':
						

						$matches = array();
						
						if ( preg_match('/\[([0-9]{4}) TO ([0-9]{4})\]/', $limit->value, $matches) )
						{
							$start = $matches[1];
							$end = $matches[2];
							
							$query .= " AND ( ";
							
							for ( $x = $start; $x <= $end; $x++ )
							{
								if ( $x > $start )
								{
									$query .= " OR ";
								}
								
								$query .= "cdate:($x)";
							}
							
							$query .= " )";
						}

						break;
					*/
				}
			}
		}
		else
		{
			$query = $search;
		}
		
		// create the soap package
		
		// we do it this way using HTTP POST, instead of using a soap client, because the primo central
		// axis web service was giving us problems otherwise, this is how metalib does it too
		
		$soap_request = "<soapenv:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
			xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
			xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" 
			xmlns:api=\"http://api.ws.primo.exlibris.com\">
			<soapenv:Header/>
			<soapenv:Body>
				<api:searchX soapenv:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\">
					<query xsi:type=\"soapenc:string\" >$query</query>
					<sort xsi:type=\"soapenc:string\" >$sort</sort>
					<strDidumean xsi:type=\"soapenc:string\" ></strDidumean>
					<language xsi:type=\"soapenc:string\" >null</language>
					<strFrom xsi:type=\"soapenc:string\" >$start</strFrom>
					<strTake xsi:type=\"soapenc:string\" >$max</strTake>
					<asFull xsi:type=\"xsd:boolean\">false</asFull>
					<institution xsi:type=\"soapenc:string\" >" . $this->institution . "</institution>
					<affiliatedUser xsi:type=\"xsd:boolean\">true</affiliatedUser>
				</api:searchX>
			</soapenv:Body>
			</soapenv:Envelope>";
		
		$this->url = $this->server . "services/JaguarPrimoSearcher?";
		
		// fire it up!
		
		$client = new Zend_Http_Client();
		$client->setUri($this->url);
		
		// make it look like metalib pc adaptor
		
		$client->setHeaders("Accept", 'application/pdf, image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, ' .
			'application/vnd.ms-powerpoint, application/vnd.ms-excel, application/msword, */*');
		$client->setHeaders("Accept-Language", "en-us");
		$client->setHeaders("User-Agent" , "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1");
		$client->setHeaders("SOAPAction", " ");
		$client->setRawData($soap_request, 'text/xml');
		
		// post the soap package
		
		$response = $client->request('POST')->getBody();
		
		if ( $response == "" )
		{
			throw new Exception("Could not connect to Primo Central Server");
		}
		
		// load it
		
		$dom = new DOMDocument();
		$dom->loadXML($response);
		
		if ( $dom->documentElement == null )
		{
			throw new Exception("Primo Central Server returned no response");
		}
		
		// results xml actually lives as a string inside an element, weird
		
		$search_xml = $dom->getElementsByTagName('searchXReturn')->item(0);
		
		if ( $search_xml == null )
		{
			throw new Exception("Primo Central Server returned no results");
		}
		
		// now the actual results as dom
		
		$primo_xml = new DOMDocument();
		$primo_xml->loadXML($search_xml->nodeValue);

		if ( $primo_xml->documentElement == null )
		{
			throw new Exception("Primo Central Server returned no results");
		}
		
		// testing
		// header("Content-type: text/plain"); echo $this->url; echo "<hr/>";	echo $primo_xml->saveXML(); exit;
		
		// parse it
		
		return $this->parseResponse($primo_xml);
	}
}
