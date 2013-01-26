<?php

namespace Application\Model\Bx;

use Xerxes;
use Xerxes\Utility\Factory;
use Xerxes\Utility\Parser;

/**
 * Search Engine
 *
 * @author David Walker
 */

class Engine
{
	protected $token;
	protected $client;
	
	public function __construct($token, $sid, $url = null)
	{
		$this->token = $token;
		$this->sid = $sid;
		
		if ( $url == null )
		{
			$this->url = "http://recommender.service.exlibrisgroup.com/service";
		}
		else
		{
			$this->url = rtrim($url, '/');
		}
	}
	
	public function getRecommendations(Xerxes\Record $xerxes_record, $min_relevance = 0, $max_records = 10)
	{
		$bx_records = array();
		
		// now get the open url
		
		$open_url = $xerxes_record->getOpenURL(null, $this->sid);
		$open_url = str_replace('genre=unknown', 'genre=article', $open_url);
		
		// send it to bx service
		
		$url = $this->url . "/recommender/openurl?token=" . $this->token . "&$open_url" .
			"&res_dat=source=global&threshold=$min_relevance&maxRecords=$max_records";
		
		try 
		{
			$client = Factory::getHttpClient();
			$xml = $client->getUrl($url, 4);
			
			if ( $xml == "" )
			{
				throw new \Exception("No response from bx service");
			}
		}
		catch ( \Exception $e )
		{
			// just issue the exception as a warning
			
			trigger_error("Could not get result from bX service: " . $e->getTraceAsString(), E_USER_WARNING);
			return $bx_records;
		}
		
		// header("Content-type: text/xml"); echo $xml; exit;
		
		$doc = Parser::convertToDOMDocument($xml);
		
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace("ctx", "info:ofi/fmt:xml:xsd:ctx");
		
		$records = $xpath->query("//ctx:context-object");
		
		foreach ( $records as $record )
		{
			$bx_record = new Record();
			$bx_record->loadXML($record);
			array_push($bx_records, $bx_record);
		}
		
		if ( count($bx_records) > 0 ) // and only if there are any records
		{
			// first one is the record we want to find recommendations for
			// so skip it; any others are actual recommendations
			
			array_shift($bx_records);
		}
			
		return $bx_records;
	}		
}
