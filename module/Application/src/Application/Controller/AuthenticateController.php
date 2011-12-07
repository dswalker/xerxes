<?php

namespace Application\Controller;

use Application\Model\Authentication\AuthenticationFactory,
	Xerxes\Utility\Registry,
	Zend\Mvc\MvcEvent,
	Zend\Mvc\Controller\ActionController;

class AuthenticateController extends ActionController
{
	protected $authentication = null;
	protected $registry;
	
	// @todo: figure out a better way to do this
	
	public function execute(MvcEvent $e)
	{
		$this->init($e);
		parent::execute($e);
	}
	
	public function init(MvcEvent $e)
	{
		$this->registry = Registry::getInstance();
		
		$factory = new AuthenticationFactory();
		$this->authentication = $factory->getAuthenticationObject($e);
	}
		
	public function loginAction()
	{
		// values from the request and configuration
	
		$post_back = $this->request->getParam( "postback" );
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
	
			return array("error" => "authentication");
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
		
		$this->request->session()->destroy( array(
        	'send_expire_cookie' => true,
        	'clear_storage'      => true,
    	));
	
	
		$this->redirect()->toUrl($configLogoutUrl);
	}
	
	public function validateAction()
	{
		// validate the request
	
		$this->authentication->onCallBack();
	}	
}



