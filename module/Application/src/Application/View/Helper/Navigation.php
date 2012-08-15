<?php

namespace Application\View\Helper;

use Xerxes\Utility\Registry,
	Zend\View\ViewEvent;

class Navigation
{
	protected $request; // request
	protected $registry; // reistry
	
	public function __construct( ViewEvent $e )
	{
		$this->request = $e->getRequest();
		$this->registry = Registry::getInstance();
	}
	
	public function getNavbar()
	{
		return array(
			'accessible_link' => $this->accessibleLink(),
			'login_link' => $this->loginLink(),
			'logout_link' => $this->logoutLink(),
			'my_account_link' => $this->myAccountLink()
		);
	}	
	
	public function myAccountLink()
	{
		$params = array(
			'controller' => 'folder',
			'return' => $this->request->server()->get( 'REQUEST_URI' ),
			'userid' => $this->request->getUser()->username // @todo x1 trans hack
		);
		
		return $this->request->url_for($params);
	}
	
	public function loginLink()
	{
		$force_secure_login = false;
		
		if ( $this->registry->getConfig('secure_login', false) == 'true' )
		{
			$force_secure_login = true;
		}		
		
		$params = array(
			'controller' => 'authenticate', 
			'action' => 'login', 
			'return' => $this->request->server()->get('REQUEST_URI') 
		);
		
		return $this->request->url_for($params, true, $force_secure_login);		
	}
	
	public function logoutLink()
	{
		$params = array(
			'controller' => 'authenticate', 
			'action' => 'logout', 
			'return' => $this->request->server()->get('REQUEST_URI')
		); 
		
		return $this->request->url_for($params);			
	}
	
	public function accessibleLink()
	{
		$params = array(
			'controller' => 'databases',
			'action' => 'accessible',
			'return' => $this->request->server()->get('REQUEST_URI')
		);

		return $this->request->url_for($params);	
	}
}

