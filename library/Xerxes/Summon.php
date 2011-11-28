<?php

class Xerxes_Summon
{
	protected $client;
	protected $host;
	protected $api_key;
	protected $app_id;
	protected $session_id;
	protected $debug = false;
	
	/**
	 * Constructor
	 */
	
	function __construct($app_id, $api_key, Zend_Http_Client $client = null)
	{
		$this->host = 'http://api.summon.serialssolutions.com';
		$this->app_id = $app_id;
		$this->api_key = $api_key;
		
		if ( $client != null )
		{
			$this->client = $client;
		}
		else 
		{
			$this->client = new Zend_Http_Client();
		}
	}
	
	public function setDebugging($value)
	{
		$this->debug = (bool) $vaue;
	}
	
	/**
	 * Retrieves a document specified by the ID.
	 *
	 * @param   string  $id         The document to retrieve from the Summon API
	 * @return  string              The requested resource
	 */
	
	public function getRecord($id)
	{
		if ( $this->debug )
		{
			echo "<pre>Get Record: $id</pre>\n";
		}
		
		$options = array('s.q' => "id:$id");
		return $this->call($options);
	}
	
	/**
	 * Execute a search.
	 *
	 * @param   string  $query      The search query
	 * @param   array   $filter     The fields and values to filter results on
	 * @param   int  $page       	The page to start with
	 * @param   int  $limit      	The amount of records to return
	 * @param   string  $sortBy     The value to be used by for sorting
	 * @param   array  $facets      An array of facets to return.  Default list is used if null.
	 * @access  public
	 * @return  array               An array of query results
	 */
	
	public function query($query, $filter = array(), $page = 1, $limit = 20, $sortBy = null, $facets = array())
	{
		if ( $this->debug )
		{
			echo '<pre>Query: ';
			
			print_r($query);
			
			if ( $filter )
			{
				echo "\nFilterQuery: ";
				
				foreach ( $filter as $filterItem )
				{
					echo " $filterItem";
				}
			}
			
			echo "</pre>\n";
		}
		
		$options = array();
		
		// Query String Parameters
		
		// Define search query
		
		if ( $query != '' )
		{
			$options['s.q'] = $query;
		}
		
		// Define facets to be polled
		
		if ( count($facets) == 0 )
		{
			// Set Default Facets
			
			$facets = array(
				'IsScholarly,or,1,2' , 
				'ContentType,or,1,30' , 
				'SubjectTerms,or,1,30'
			);
			
			$options['s.ff'] = $facets;
		}
		
		// add filters to be applied
		
		if ( count($filter) > 0 )
		{
			$options['s.fvf'] = $filter;
		}
		
		// Define which sorting to use
		
		if ( $sortBy != "" )
		{
			$options['s.sort'] = $sortBy;
		}
		
		// Define Paging Parameters
		
		$options['s.ps'] = $limit;
		$options['s.pn'] = $page;
		
		// Define Visibility 
		
		// $options['s.ho'] = 'true';
		
		return $this->call($options);
	}
	
	/**
	 * Submit request
	 *
	 * @param   array       $params     An array of parameters for the request
	 * @param   string      $service    The API Service to call
	 * 
	 * @return  string                  The response from the Summon API
	 */
	
	private function call($params = array(), $service = 'search')
	{
		// Build Query String
		
		$query = array();
		
		foreach ( $params as $function => $value )
		{
			if ( is_array($value) )
			{
				foreach ( $value as $additional )
				{
					$additional = urlencode($additional);
					$query[] = "$function=$additional";
				}
			}
			else
			{
				$value = urlencode($value);
				$query[] = "$function=$value";
			}
		}
		
		asort($query);
		$queryString = implode('&', $query);

		$this->client->setUri($this->host . "/$service?" . $queryString);		
		
		if ( $this->debug )
		{
			echo "<pre>";
			print_r($this->host . "/$service?" . $queryString);
			echo "</pre>\n";
		}
		
		// Build Authorization Headers
		
		$headers = array(
			'Accept' => 'application/json' , 
			'x-summon-date' => date('D, d M Y H:i:s T') , 
			'Host' => 'api.summon.serialssolutions.com'
		);
		
		$data = implode($headers, "\n") . "\n/$service\n" . urldecode($queryString) . "\n";
		$hmacHash = $this->hmacsha1($this->api_key, $data);
		
		foreach ( $headers as $key => $value )
		{
			$this->client->setHeaders($key, $value);
		}

		$this->client->setHeaders("Authorization: Summon $this->app_id;$hmacHash");
		
		if ( $this->session_id )
		{
			$this->client->setHeaders('x-summon-session-id', $this->session_id);
		}
		
		// Send Request
		
		$response = $this->client->request()->getBody();
		return json_decode($response, true);
	}
	
	private function hmacsha1($key, $data)
	{
		$blocksize = 64;
		$hashfunc = 'sha1';
		if ( strlen($key) > $blocksize )
		{
			$key = pack('H*', $hashfunc($key));
		}
		$key = str_pad($key, $blocksize, chr(0x00));
		$ipad = str_repeat(chr(0x36), $blocksize);
		$opad = str_repeat(chr(0x5c), $blocksize);
		$hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $data))));
		return base64_encode($hmac);
	}
}
