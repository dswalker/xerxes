<?php

namespace Application\Controller;

use Application\Model\Authentication\AuthenticationFactory;
use Application\Model\Authentication\Scheme;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Xerxes\Utility\Registry;
use Xerxes\Mvc\ActionController;

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
		// if secure login is required, then force the user back thru https
		
		$config_https = $this->registry->getConfig( "SECURE_LOGIN", false, false );
	
		if ( $config_https == true && $this->request->getScheme() == "http" )
		{
			$url = $this->request->getServerUrl(true) . '/' . $this->request->getUri();
			
			return $this->redirectTo($url);
		}
	
		### remote authentication
	
		$result = $this->authentication->onLogin();
	
		if ( $result instanceof RedirectResponse )
		{
			return $result; // redirect to remote auth server
		}
	
		### local authentication
		
		// if this is not a 'postback', then the user has not submitted the form, they are arriving
		// for first time so stop the flow and just show the login page with form

		$post_back = $this->request->getParam('postback');
		
		if ( $post_back == null )
		{
			return $this->response;
		}
	
		$result = $this->authentication->onCallBack();
	
		if ( $result == Scheme::FAILED )
		{
			// failed the login, so present a message to the user
	
			$this->response->setVariable("error", "authentication");
			
			return $this->response;
		}
		elseif ( $result instanceof RedirectResponse )
		{
			return $result; // success, send the user back
		}
		else
		{
			throw new \Exception('onCallBack function must return Scheme::FAILED or register user');
		}
	}
	
	public function logoutAction()
	{
		// values from the request
	
		$post_back = $this->request->getParam("postback");
	
		// if this is not a 'postback', then the user has not
		// submitted the form, thus confirming logout
	
		if ( $post_back == null )
		{
			return $this->response;
		}
	
		// configuration settings
	
		$configBaseURL = $this->request->getBaseUrl();
		$configLogoutUrl = $this->registry->getConfig("LOGOUT_URL", false, $configBaseURL);
	
		// perform any anuthentication scheme-specific clean-up action
	
		$this->authentication->onLogout();
		$this->request->getSession()->invalidate();
	
		return $this->redirectTo($configLogoutUrl);
	}
	
	public function validateAction()
	{
		// validate the request
	
		$result = $this->authentication->onCallBack();
		
		if ( $result instanceof RedirectResponse )
		{
			return $result;
		}
	}
}
