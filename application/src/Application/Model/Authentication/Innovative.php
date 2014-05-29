<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Authentication;

use Xerxes\Mvc\Exception\AccessDeniedException;
use Xerxes\Utility\Factory;
use Xerxes\Mvc\Request;

/**
 * Authenticates users and downloads data from the Innovative Patron API
 * 
 * Based on the functions originally developed by John Blyberg
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Innovative extends Scheme 
{
	protected $server;
	protected $user_data;
	
	/*
	 * Create new Innovative authentication
	 */
	
	public function __construct(Request $request)
	{
		parent::__construct($request);
		
		$this->server = $this->registry->getConfig( "INNOVATIVE_PATRON_API", true );
		$this->server = rtrim($this->server, '/');
	}
	
	/**
	 * Authenticate the user against the III Patron API
	 */
	
	public function onCallBack()
	{
		$strUsername = $this->request->getParam( "username" );	// barcode
		$strPassword = $this->request->getParam( "password" );	// pin
		
		$bolAuth = $this->authenticate( $strUsername, $strPassword );
		
		$this->user_data = $this->getUserData($strUsername);

		// print_r($this->user_data); exit;
		
		if ( $bolAuth == true )
		{
			// make sure user is in the list of approved patron types
		
			$configPatronTypes = $this->registry->getConfig( "INNOVATIVE_PATRON_TYPES", false );
			
			if ( $configPatronTypes != null )
			{
				$arrTypes = explode(",", $configPatronTypes);
				
				// make them all integers for consitency
				
				for ( $x = 0; $x < count($arrTypes); $x++ )
				{
					$arrTypes[$x] = (int) $arrTypes[$x];
				}

				if ( ! in_array( (int) $this->user_data["P TYPE"], $arrTypes) )
				{
					throw new AccessDeniedException("User is not authorized to use this service");
				}
			}
			
			// register the user and stop the flow
			
			$this->user->username = $strUsername;
			
			$this->mapUserData();
			
			return $this->register();
		}
		else
		{
			return self::FAILED;
		}
	}
	
	/**
	 * Innovative_Local class defines this
	 */
	
	protected function mapUserData()
	{
		
	}
	
	
	/**
	* Returns patron data from the API as array
	*
	* @param string $id 		barcode
	* @return array 			data returned by the api as associative array
	* @exception 				throws exception when iii patron api reports error
	*/
	
	protected function getUserData( $id )
	{
		// normalize the barcode
		
		$id = str_replace(" ", "", $id);
		
		// fetch data from the api
		
		$url = $this->server . "/PATRONAPI/$id/dump";
		$arrData = $this->getContent($url);
		
		// if something went wrong
		
		if ( array_key_exists("ERRMSG", $arrData ) )
		{
			throw new \Exception($arrData["ERRMSG"]);	
		}

		return $arrData;
	}

	/**
	* Checks tha validity of a barcode / pin combo, essentially a login test
	*
	* @param string $id 	barcode
	* @param string $pin 	the pin to use with $id
	* @return bool			true if valid, false if not
	*/
	
	protected function authenticate ( $id, $pin )
	{
		// normalize the barcode and pin
		
		$id = str_replace(" ", "", $id);
		$pin = str_replace(" ", "", $pin);
		
		// fetch data from the api

		$pin = urlencode($pin);
		$url = $this->server . "/PATRONAPI/$id/$pin/pintest";
		$arrData = $this->getContent($url);
		
		// check pin test for error message, indicating
		// failure
		
		if ( array_key_exists("ERRMSG", $arrData ) )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	* Fetches and normalize the API data
	*
	* @param string $url 	url of patron dump or pint test
	* @return array			patron data
	*/
	
	private function getContent( $url )
	{
		$arrRawData = array();
		$arrData = array();
		
		// get the data and strip out html tags
		
		$client = Factory::getHttpClient();
		
		$strResponse = $client->getUrl($url);
		$strResponse = trim(strip_tags($strResponse));
		
		if ( $strResponse == "" )
		{
			throw new \Exception("Could not connect to Innovative Patron API");			
		}
		else
		{
			// cycle thru each line in the response, splitting each
			// on the equal sign into an associative array
			
			$arrRawData = explode("\n", $strResponse);
			
			foreach ($arrRawData as $strLine)
			{
				$arrLine = explode("=", $strLine);
				
				// strip out the code, leaving just the attribute name
				
				$arrLine[0] = preg_replace('/\[[^\]]{1,}\]/', "", $arrLine[0]);
				$arrData[trim($arrLine[0])] = trim( $arrLine[1] );
			}
		}
		
		return $arrData;
	}
}
