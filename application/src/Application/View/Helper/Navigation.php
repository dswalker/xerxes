<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\View\Helper;

use Xerxes\Utility\Registry;
use Xerxes\Mvc\MvcEvent;

class Navigation
{
	protected $request; // request
	protected $registry; // registry
	
	public function __construct( MvcEvent $e )
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
			'my_account_link' => $this->myAccountLink(),
			'databases_link' => $this->databasesLink(),
			'my_databases_link' => $this->myDatabasesLink()
		);
	}	
	
	public function myAccountLink()
	{
		$params = array(
			'controller' => 'folder',
			'return' => $this->request->server->get( 'REQUEST_URI' )
		);
		
		return $this->request->url_for($params);
	}
	
	public function databasesLink()
	{
		$params = array(
			'controller' => 'databases'
		);
	
		return $this->request->url_for($params);
	}	

	public function myDatabasesLink()
	{
		$params = array(
			'controller' => 'my-databases'
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
			'return' => $this->request->server->get('REQUEST_URI') 
		);
		
		return $this->request->url_for($params, true, $force_secure_login);		
	}
	
	public function logoutLink()
	{
		$params = array(
			'controller' => 'authenticate', 
			'action' => 'logout', 
			'return' => $this->request->server->get('REQUEST_URI')
		); 
		
		return $this->request->url_for($params);			
	}
	
	public function accessibleLink()
	{
		$params = array(
			'controller' => 'navigation',
			'action' => 'accessible',
			'return' => $this->request->server->get('REQUEST_URI')
		);

		return $this->request->url_for($params);	
	}
}

