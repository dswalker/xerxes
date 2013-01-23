<?php

namespace Application\Controller;

use Application\Model\Authentication\AuthenticationFactory,
	Application\Model\Authentication\Scheme,
	Xerxes\Utility\Registry,
	Xerxes\Mvc\ActionController;

class AuthenticateController extends ActionController
{
	protected $authentication = null;
	
	public function init()
	{
		$factory = new AuthenticationFactory();
		$this->authentication = $factory->getAuthenticationObject($this->request);
	}
		
	public function loginAction()
	{
		// values from the request and configuration
	
		$post_back = $this->request->getParam( "postback" );
		$config_https = $this->registry->getConfig( "SECURE_LOGIN", false, false );
	
		// if secure login is required, then force the user back thru https
	
		if ( $config_https == true && $this->request->getScheme() == "http" )
		{
			$url = $this->request->getServerUrl(true) . '/' . $this->request->getUri();
			
			return $this->redirect($url);
		}
	
		### remote authentication
	
		$result = $this->authentication->onLogin();
	
		if ( $result == Scheme::REDIRECT )
		{
			return $this->doRedirect();
		}
	
		### local authentication
		
		// if this is not a 'postback', then the user has not submitted the form, they are arriving
		// for first time so stop the flow and just show the login page with form
	
		if ( $post_back == null )
		{
			return $this->response;
		}
	
		$bolAuth = $this->authentication->onCallBack();
	
		if ( $bolAuth == Scheme::FAILED )
		{
			// failed the login, so present a message to the user
	
			$this->response->setVariable("error", "authentication");
			
			return $this->response;
		}
		else
		{
			return $this->doRedirect();
		}
	}
	
	public function logoutAction()
	{
		// values from the request
	
		$post_back = $this->request->getParam("postback");
	
		// if this is not a 'postback', then the user has not
		// submitted the form, thus confirming logout
	
		if ( $post_back == null ) return 1;
	
		// configuration settings
	
		$configBaseURL = $this->request->getBaseUrl();
		$configLogoutUrl = $this->registry->getConfig("LOGOUT_URL", false, $configBaseURL);
	
		// perform any anuthentication scheme-specific clean-up action
	
		$this->authentication->onLogout();
		$this->request->getSession()->invalidate();
	
		return $this->redirect($configLogoutUrl);
	}
	
	public function validateAction()
	{
		// validate the request
	
		$result = $this->authentication->onCallBack();
		
		if ( $result == Scheme::SUCCESS )
		{
			return $this->doRedirect();
		}
		
	}
	
	public function doRedirect()
	{
		return $this->redirect($this->authentication->getRedirect());
	}
}



