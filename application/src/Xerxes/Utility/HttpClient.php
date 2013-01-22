<?php

namespace Xerxes\Utility;

use Guzzle\Http\Client;

/**
 * Wrapper on Guzzle Http Client
 * 
 * @author David Walker
 * @copyright 2013 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package
 */ 

class HttpClient extends Client
{
	public function getUrl($url, $timeout = 5)
	{
		$config = array
		(
			'curl.options' => array(CURLOPT_TIMEOUT => $timeout),
		);
		
		$this->setConfig($config);
		
		$request = $this->get($url);
		$request->getQuery()->setAggregateFunction(array($request->getQuery(), 'aggregateUsingDuplicates'));
		$response = $request->send();
		
		return (string) $response->getBody();
	}
}