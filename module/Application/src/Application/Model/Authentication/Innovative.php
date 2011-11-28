<?php

/**
 * Authenticates users and downloads data from the Innovative Patron API;
 * based on the functions originally developed by John Blyberg
 * 
 * @author David Walker
 * @author John Blyberg
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Authentication_Innovative extends Xerxes_Framework_Authenticate 
{
	protected $server;
	protected $user_data;
	
	public function __construct($objRequest, $objRegistry)
	{
		parent::__construct($objRequest, $objRegistry);
		
		$this->server = $this->registry->getConfig( "INNOVATIVE_PATRON_API", true );
	}
	
	public function onCallBack()
	{
		$strUsername = $this->request->getProperty( "username" );	// barcode
		$strPassword = $this->request->getProperty( "password" );	// pin
		
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
					throw new Xerxes_Exception_AccessDenied("text_authentication_error_not_authorized");
				}
			}
			
			// register the user and stop the flow
			
			$this->user->username = $strUsername;
			
			$this->mapUserData();
			
			$this->register();
			
			return true;
		}
		else
		{
			return false;
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
		
		$url = $this->server . "PATRONAPI/$id/dump";
		$arrData = $this->getContent($url);
		
		// if something went wrong
		
		if ( array_key_exists("ERRMSG", $arrData ) )
		{
			throw new Exception($arrData["ERRMSG"]);	
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
		$url = $this->server . "PATRONAPI/$id/$pin/pintest";
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
		
		$strResponse = Xerxes_Framework_Parser::request($url);
		$strResponse = trim(strip_tags($strResponse));
		
		if ( $strResponse == "" )
		{
			throw new Exception("Could not connect to Innovative Patron API");			
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
				
				$arrLine[0] = preg_replace("/\[[^\]]{1,}\]/", "", $arrLine[0]);
				$arrData[trim($arrLine[0])] = trim( $arrLine[1] );
			}
		}
		
		return $arrData;
	}
}
