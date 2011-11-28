<?php

/**
 * Authentication controller
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Controller_Authenticate extends Xerxes_Framework_Controller
{
	protected $authentication = null;
	
	public function init()
	{
		// if the authentication_source is set in the request, then it takes precedence
		
		$override = $this->request->getProperty("authentication_source");
		
		if ( $override == null )
		{
			// otherwise, see if one has been set in session from a previous login
			
			$session_auth = $this->request->getSession("auth");
			
			if ( $session_auth != "" )
			{
				$override = $session_auth;
			}
		}
		
		// make sure it's in our list, or if blank still, we get the default
		
		$configAuth = $this->registry->getAuthenticationSource($override);
		
		// we set this so we can keep track of the authentication type
		// through various requests
		
		$factory = new Xerxes_Model_Authentication_Factory();
		
		$this->authentication = $factory->getAuthenticationObject($configAuth, $this->request, $this->registry, $this->response);
		$this->authentication->id = $configAuth;
	}
	
	public function check()
	{
		$this->authentication->onEveryRequest();
	}	
	
	public function login()
	{
		// values from the request and configuration
	
		$post_back = $this->request->getProperty( "postback" );
		$config_https = $this->registry->getConfig( "SECURE_LOGIN", false, false );
	
		// if secure login is required, then force the user back thru https
	
		if ( $config_https == true && $this->request->getServer("HTTPS") == null )
		{
			$web = $this->registry->getConfig( "SERVER_URL" );
			$web = str_replace("http://", "https://", $web);
	
			$this->request->setRedirect( $web . $_SERVER['REQUEST_URI'] );
			return 1;
		}
	
		### remote authentication
	
		$bolStop = $this->authentication->onLogin();
	
		if ( $bolStop == true )
		{
			return 1;
		}
	
		### local authentication
	
		// if this is not a 'postback', then the user has not submitted the form, they are arriving
		// for first time so stop the flow and just show the login page with form
	
		if ( $post_back == null ) return 1;
	
		$bolAuth = $this->authentication->onCallBack();
	
		if ( $bolAuth == false )
		{
			// failed the login, so present a message to the user
	
			$this->response->add("error", "authentication");
		}
	}
	
	public function logout()
	{
		// values from the request
	
		$post_back = $this->request->getProperty("postback");
	
		// if this is not a 'postback', then the user has not
		// submitted the form, thus confirming logout
	
		if ( $post_back == null ) return 1;
	
		// configuration settings
	
		$configBaseURL = $this->registry->getConfig("BASE_URL", true);
		$configLogoutUrl = $this->registry->getConfig("LOGOUT_URL", false, $configBaseURL);
	
		// perform any anuthentication scheme-specific clean-up action
	
		$this->authentication->onLogout();
	
		// release the data associated with the session
	
		session_destroy();
		session_unset();
	
		// delete cookies
	
		setcookie("PHPSESSID", "", 0, "/");
		setcookie("saves", "", 0, "/");
	
		// redirect to specified logout location
	
		$this->response->setRedirect($configLogoutUrl);
	}
	
	public function validate()
	{
		// validate the request
	
		$this->authentication->onCallBack();
	}	
}



