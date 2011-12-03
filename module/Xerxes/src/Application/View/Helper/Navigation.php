<?php

namespace Application\View\Helper;

use Xerxes\Utility\Registry;

class Navigation
{
	protected $request;
	
	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->registry = Registry::getInstance();
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

