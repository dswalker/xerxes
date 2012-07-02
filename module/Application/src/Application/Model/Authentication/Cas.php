<?php

namespace Application\Model\Authentication;

use Xerxes\Utility\Factory,
	Xerxes\Utility\Parser;

/**
 * CAS authentication
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @version
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Cas extends Scheme 
{
	/**
	 * Redirect to the cas login service
	 */
	
	public function onLogin()
	{
		$configCasLogin = $this->registry->getConfig( "CAS_LOGIN", true );
		
		$configCasLogin = rtrim($configCasLogin, '/');
		
		$url = $configCasLogin . "?service=" . urlencode($this->validate_url);
		
		$this->setRedirect( $url );
		
		return self::REDIRECT;
	}
	
	public function onCallBack()
	{
		// validate the request
		
		$username = $this->isValid();
		
		if ($username === false )
		{
			throw new \Exception("Could not validate user against CAS server");
		}
		else
		{
			$this->user->username = $username;
			return $this->register();
		}
	}
	
	/**
	 * Parses a validation response from a CAS server to see if the returning CAS request is valid
	 *
	 * @param string $results		xml or plain text response from cas server
	 * @return bool						true if valid, false otherwise
	 * @exception 						throws exception if cannot parse response or invalid version
	 */
	
	private function isValid()
	{
		// values from the request
		
		$ticket = $this->request->getParam("ticket");
					
		// configuration settings

		$configCasValidate = $this->registry->getConfig("CAS_VALIDATE", true);
		$configCasValidate = rtrim($configCasValidate, '/');

		// figure out which type of response this is based on the service url
		
		$arrURL = explode("/", $configCasValidate);
		$service = array_pop($arrURL);
		
		// now get it!
			
		$url = $configCasValidate . "?ticket=" . $ticket . "&service=" . urlencode($this->validate_url);
		
		$http_client = Factory::getHttpClient();
		$http_client->setUri($url);
		$results = $http_client->send()->getBody();
			
		
		// validate is plain text
		
		if ( $service == "validate" )
		{
			$message_array = explode("\n", $results);
			
			if ( count($message_array) >= 2 )
			{
				if ( $message_array[0] == "yes")
				{
					return $message_array[1];
				}
			}
			else
			{
				throw new \Exception("Could not parse CAS validation response.");
			}
		}	
		elseif ( $service == "serviceValidate" || $service == "proxyValidate")
		{
			// these are XML based
			
			$xml = Parser::convertToDOMDocument($results);
			
			$cas_namespace = "http://www.yale.edu/tp/cas";
			
			$user = $xml->getElementsByTagNameNS($cas_namespace, "user")->item(0);
			$failure = $xml->getElementsByTagNameNS($cas_namespace, "authenticationFailure")->item(0);
			
			if ( $user != null )
			{
				if ( $user->nodeValue != "" )
				{
					return $user->nodeValue;
				}
				else
				{
					throw new \Exception("CAS validation response missing username value");
				}
			}
			elseif ( $failure != null )
			{
				// see if error, rather than failed authentication
				
				if ( $failure->getAttribute("code") == "INVALID_REQUEST")
				{
					throw new \Exception("Invalid request to CAS server: " . $failure->nodeValue);
				}
			}
			else
			{
				throw new \Exception("Could not parse CAS validation response.");
			}
		}
		else
		{
			throw new \Exception("Unsupported CAS version.");
		}
		
		// if we got this far, the request was invalid
		
		return false;
	}
}
