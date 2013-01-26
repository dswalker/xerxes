<?php

namespace Xerxes\Utility;

use Guzzle\Http\Client;

/**
 * Wrapper on Guzzle Http Client
 * 
 * @author David Walker
 */ 

class HttpClient extends Client
{
	/**
	 * Simple method to get and return content from a ur;
	 * @param string $url
	 * @param int $timeout
	 * @param array $headers
	 * 
	 * @return string
	 */
	
	public function getUrl($url, $timeout = 5, $headers = array())
	{
		$config = array
		(
			'curl.options' => array(CURLOPT_TIMEOUT => $timeout),
		);
		
		$this->setConfig($config);
		
		$request = $this->get($url, $headers);
		$request->getQuery()->setAggregateFunction(array($request->getQuery(), 'aggregateUsingDuplicates'));
		$response = $request->send();
		
		return (string) $response->getBody();
	}
}