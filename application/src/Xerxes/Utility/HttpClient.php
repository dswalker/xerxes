<?php

namespace Xerxes\Utility;

use Guzzle\Http\Client;

/**
 * Utility class
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package  Xerxes_Framework
 */ 

class HttpClient
{
	private $client;
	
	public function __construct()
	{
		$this->client = new Client();
	}
	
	public function get($url)
	{
		$request = $this->client->get($url);
		$request->getQuery()->setAggregateFunction(array($request->getQuery(), 'aggregateUsingDuplicates'));
		$response = $request->send();
		
		return (string) $response->getBody();
	}
}