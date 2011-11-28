<?php

class Xerxes_View_Helper_Navigation
{
	protected $request;
	
	public function __construct()
	{
		$this->request = Xerxes_Framework_Request::getInstance();
		$this->registry = Xerxes_Framework_Registry::getInstance();
	}
	
	public function myAccountLink()
	{
		$params = array(
			'base' => 'folder',
			'return' => $this->request->getServer( 'REQUEST_URI' )
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
			'base' => 'authenticate', 
			'action' => 'login', 
			'return' => $this->request->getServer('REQUEST_URI') 
		);
		
		return $this->request->url_for($params, $force_secure_login);		
	}
	
	public function logoutLink()
	{
		$params = array(
			'base' => 'authenticate', 
			'action' => 'logout', 
			'return' => $this->request->getServer('REQUEST_URI')
		); 
		
		return $this->request->url_for($params);			
	}
	
	public function accessibleLink()
	{
		$params = array(
			'base' => 'databases',
			'action' => 'accessible',
			'return' => $this->request->getServer('REQUEST_URI')
		);

		return $this->request->url_for($params);	
	}

	public function labelsLink()
	{
		$params = array(
			'base' => 'navigation',
			'action' => 'labels',
		);

		return $this->request->url_for($params);	
	}
}

