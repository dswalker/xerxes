<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Controller;

use Application\Model\Authentication\AuthenticationFactory;
use Application\Model\Authentication\Scheme;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Xerxes\Utility\Registry;
use Xerxes\Mvc\ActionController;

/**
 * Authentication Controller
 * 
 * Framework for handling login and logout of the system
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class AuthenticateController extends ActionController
{
	/**
	 * @var Scheme
	 */
	protected $authentication = null; // authentication object
	
	/**
	 * (non-PHPdoc)
	 * @see Xerxes\Mvc.ActionController::init()
	 */
	
	public function init()
	{
		$factory = new AuthenticationFactory();
		$this->authentication = $factory->getAuthenticationObject($this->request);
	}
	
	/**
	 * The action triggered when the user logs in
	 */
		
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
	
		if ( $result === Scheme::FAILED )
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
	
	/**
	 * The action triggered when the user logs out
	 */
	
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
	
		// logout url
	
		$configBaseURL = $this->request->getBaseUrl();
		$configLogoutUrl = $this->registry->getConfig("LOGOUT_URL", false, $configBaseURL);
	
		// perform any anuthentication scheme-specific clean-up action
	
		$this->authentication->onLogout();
		
		// destroy the session
		
		$this->request->getSession()->invalidate();
	
		return $this->redirectTo($configLogoutUrl);
	}
	
	/**
	 * Handle user coming back from SSO service
	 */
	
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
