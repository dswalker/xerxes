<?php

/**
 * CAS authentication
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @version $Id: CAS.php 1145 2010-04-30 22:22:23Z dwalker@calstate.edu $
 * @package Xerxes
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class Xerxes_Model_Authentication_CAS extends Xerxes_Model_Authentication_Abstract 
{
	/**
	 * Redirect to the cas login service
	 */
	
	public function onLogin()
	{
		$configCasLogin = $this->registry->getConfig( "CAS_LOGIN", true );
		
		$strUrl = $configCasLogin . "?service=" . urlencode($this->validate_url);
		$this->request->setRedirect( $strUrl );
		
		return true;
	}
	
	public function onCallBack()
	{
		// validate the request
		
		$strUsername = $this->isValid();
		
		if ($strUsername === false )
		{
			throw new Exception("Could not validate user against CAS server");
		}
		else
		{
			$this->user->username = $strUsername;
			$this->register();
		}
	}
	
	/**
	 * Parses a validation response from a CAS server to see if the returning CAS request is valid
	 *
	 * @param string $strResults		xml or plain text response from cas server
	 * @return bool						true if valid, false otherwise
	 * @exception 						throws exception if cannot parse response or invalid version
	 */
	
	private function isValid()
	{
		// values from the request
		
		$strTicket = $this->request->getProperty("ticket");
					
		// configuration settings

		$configCasValidate = $this->registry->getConfig("CAS_VALIDATE", true);

		// figure out which type of response this is based on the service url
		
		$arrURL = explode("/", $configCasValidate);
		$service = array_pop($arrURL);
		
		// now get it!
			
		$strUrl = $configCasValidate . "?ticket=" . $strTicket . "&service=" . urlencode($this->validate_url);
		
		$strResults = Xerxes_Framework_Parser::request( $strUrl );		
		
		// validate is plain text
		
		if ( $service == "validate" )
		{
			$arrMessage = explode("\n", $strResults);
			
			if ( count($arrMessage) >= 2 )
			{
				if ( $arrMessage[0] == "yes")
				{
					return $arrMessage[1];
				}
			}
			else
			{
				throw new Exception("Could not parse CAS validation response.");
			}
		}	
		elseif ( $service == "serviceValidate" || $service == "proxyValidate")
		{
			// these are XML based
			
			$objXml = new DOMDocument();
			$objXml->loadXML($strResults);
			
			$strCasNamespace = "http://www.yale.edu/tp/cas";
			
			$objUser = $objXml->getElementsByTagNameNS($strCasNamespace, "user")->item(0);
			$objFailure = $objXml->getElementsByTagNameNS($strCasNamespace, "authenticationFailure")->item(0);
			
			if ( $objUser != null )
			{
				if ( $objUser->nodeValue != "" )
				{
					return $objUser->nodeValue;
				}
				else
				{
					throw new Exception("CAS validation response missing username value");
				}
			}
			elseif ( $objFailure != null )
			{
				// see if error, rather than failed authentication
				
				if ( $objFailure->getAttribute("code") == "INVALID_REQUEST")
				{
					throw new Exception("Invalid request to CAS server: " . $objFailure->nodeValue);
				}
			}
			else
			{
				throw new Exception("Could not parse CAS validation response.");
			}
		}
		else
		{
			throw new Exception("Unsupported CAS version.");
		}
		
		// if we got this far, the request was invalid
		
		return false;
	}
}
