<?php

namespace Application\Model\Authentication;

use Xerxes\Utility\Registry,
	Zend\Mvc\MvcEvent;

/**
 * Authentication factory
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class AuthenticationFactory
{
	public function getAuthenticationObject(MvcEvent $e)
	{
		$registry = Registry::getInstance();
		$request = $e->getRequest();
		
		// if the authentication_source is set in the request, then it takes precedence
		
		$override = $request->getParam("authentication_source");
		
		if ( $override == null )
		{
			// otherwise, see if one has been set in session from a previous login
		
			$session_auth = $request->getSession("auth");
		
			if ( $session_auth != "" )
			{
				$override = $session_auth;
			}
		}
		
		// make sure it's in our list, or if blank still, we get the default
		
		$name = $registry->getAuthenticationSource($override);
		
		// sanitize
		
		$name = preg_replace('/\W/', '', $name);
		
		// main class
		
		$class_name = ucfirst($name);
		
		// local custom version
		
		$local_file = "config/authentication/$name.php";
		
		if ( file_exists($local_file) )
		{
			require_once($local_file);
			
			$class_name = "Xerxes_CustomAuth_" . ucfirst($name);
			
			if ( ! class_exists($class_name) )
			{
				throw new \Exception("the custom authentication scheme '$name' should have a class called '$class_name'");
			}
		}

		// make it

		$authentication = new $class_name($e);
		
		if ( ! $authentication instanceof AbstractAuthentication)
		{
			throw new \Exception("class '$class_name' for the '$name' authentication scheme must extend AbstractAuthentication");
		}
		
		return $authentication;
	}
}