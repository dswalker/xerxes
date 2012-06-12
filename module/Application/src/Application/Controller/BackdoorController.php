<?php

namespace Application\Controller;

use Application\Model\Authentication\AuthenticationFactory,
	Application\Model\Authentication\Scheme,
	Application\Model\Authentication\User,
	Xerxes\Utility\Registry,
	Xerxes\Utility\Request,
	Zend\Mvc\MvcEvent,
	Zend\Mvc\Controller\ActionController;

class BackdoorController extends ActionController
{
	public function registerAction()
	{
		if ( $this->request->getParam("id") != null && $this->request->getParam("return") != null)
		{
			$key = 'ajklaseufdsklsea8932r4';
			
			$id = $this->request->getParam("id");

			$parts = explode('-', $id);
			$encrypted = $parts[0];
			$username = $parts[1];
			
			$return = $this->request->getParam("return");
			
			// check
			
			if ( md5($key . $username) != $encrypted )
			{
				throw new \Exception("Could not validate login");
			}
			
			$this->request->getUser()->username = $username;
			
			$factory = new AuthenticationFactory();
			$authentication = $factory->getAuthenticationObject($this->request);
			$authentication->register();
			
			return $this->redirect()->toUrl($return);
		}
		else
		{
			throw new \Exception("Could not register user for X2");
		}
	}
}
