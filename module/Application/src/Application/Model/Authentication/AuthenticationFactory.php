<?php

namespace Application\Model\Authentication;

use Xerxes\Utility\Registry,
	Xerxes\Utility\Request,
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
	public function getAuthenticationObject(Request $request)
	{
		$registry = Registry::getInstance();
		
		// if the authentication_source is set in the request, then it takes precedence
		
		$override = $request->getParam("authentication_source");
		
		if ( $override == null )
		{
			// otherwise, see if one has been set in session from a previous login
		
			$session_auth = $request->getSessionData("auth");
		
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
		
		$class_name = 'Application\Model\Authentication' . '\\' . ucfirst($name);
		
		// local custom version
		
		$local_file = "custom/Authentication/$name.php";
		
		if ( file_exists($local_file) )
		{
			require_once($local_file);
			
			$class_name = 'Local\Authentication' . '\\' . ucfirst($name);
			
			if ( ! class_exists($class_name) )
			{
				throw new \Exception("the custom authentication scheme '$name' should have a class called '$class_name'");
			}
		}

		// make it

		$authentication = new $class_name($request);
		
		if ( ! $authentication instanceof Scheme)
		{
			throw new \Exception("class '$class_name' for the '$name' authentication scheme must extend Scheme");
		}
		
		return $authentication;
	}
}