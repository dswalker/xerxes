<?php

/**
 * HTTP Request
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @version $Id: HTTP.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package  Xerxes_Framework
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Xerxes_Framework_HTTP
{
	public static function request($url, $timeout = null, $data = null, $headers = null, $bolEncode = true)
	{
		$registry = Xerxes_Framework_Registry::getInstance();
		
		$proxy = $registry->getConfig("HTTP_PROXY_SERVER", false);
		$curl = $registry->getConfig("HTTP_USE_CURL", false, false);
		
		### GET REQUEST (NON-PROXY)
		
		if ( $data == null && $proxy == null && $curl == null && $headers == null )
		{
			$ctx = null;
			
			if ( $timeout != null )
			{
				$ctx = stream_context_create(array(
				    'http' => array(
				        'timeout' => $timeout
				        )
				    )
				);
			}
		
			return file_get_contents($url, 0, $ctx);
		}
		
		// these for POST requests
		
		$host = ""; // just the server host name
		$port = 80; // just the port number
		$path = ""; // just the uri path

		if ( $data != null )
		{
			// split the host from the path
			
			$arrMatches = array();
			
			if ( preg_match('/http:\/\/([^\/]*)(\/.*)/', $url, $arrMatches) != false )
			{
				$host = $arrMatches[1];
				$path = $arrMatches[2];
			}
			
			// extract the port number, if present
			
			if ( strstr($host, ":") )
			{
				$port = (int) self::removeLeft($host, ":");
				$host = self::removeRight($host, ":");
			}
			
			// regular POST requests will need to have the data urlencoded, but some special 
			// POST requests, like 'text/xml' to Solr, should not, so client code should 
			// set to false
			
			if ( $bolEncode == true )
			{
				$data = urlencode($data);
			}				
		}

		### POST OR GET USING CURL
		
		if ( $proxy != null || $curl != null || $headers != null)
		{				
			$response = ""; // the response
			$ch = curl_init(); // curl object
			$header = array();
			
			// basic curl settings
			
			curl_setopt($ch, CURLOPT_URL, $url); // the url we're sending the request to
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // this returns the response to a variable		
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this tells curl to follow 'location:' headers
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // but don't follow more than 10 'location:' redirects
			
			if ( $timeout != null )
			{
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // wait and then timeout 
			}
			
			// this is a post request
			
			if ( $data != null )
			{
				// we do it this way, as opposed to a more typical curl post,
				// in case this is a custom HTTP POST request

				$header[] = "Host: $host\r\n";
				$header[] = "Content-type: application/x-www-form-urlencoded\r\n";
				$header[] = "Content-length: " . strlen($data) . "\r\n";
				$header[] = $data;
					
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
			}
			
			// headers
			
			if ( count($header) > 0 )
			{
				curl_setopt( $ch, CURLOPT_HTTPHEADER, $header ); 
			}
			
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $header ); 			
			
			// proxy settings
			
			if ( $proxy != null )
			{
				curl_setopt($ch, CURLOPT_PROXY, $proxy);

				// proxy username and password, if necessary
				
				$username = $registry->getConfig("HTTP_PROXY_USERNAME", false);
				$password = $registry->getConfig("HTTP_PROXY_PASSWORD", false);				
				
				if ( $username != null && $password != null )
				{
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$username:$password");
				}
			}
			
			// return the response

			$response = curl_exec($ch);
			$responseInfo = curl_getinfo($ch);
			curl_close($ch);

			if ( $response === false || $responseInfo["http_code"] != 200 )
			{
				throw new Exception("Error in response, " . $responseInfo["http_code"] . " " . $response );
			}
			
			return $response;
		}

		### POST REQUEST NOT USING CURL
		
		else
		{
			$buf = ""; // the response
			$fp = fsockopen($host, $port); // file pointer object
			
			if ( ! $fp )
			{
				throw new Exception("could not connect to server");
			}
			
			fputs($fp, "POST $path HTTP/1.1\r\n");
			fputs($fp, "Host: $host\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($data) . "\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $data);
			
			while (!feof($fp))
			{
				$buf .= fgets($fp,128);
			}
			
			fclose($fp);
			
			if ( ! strstr($buf, "200 OK") )
			{
				throw new Exception("Error in response, $buf");
			}
			
			return $buf;					
		}
	}
}