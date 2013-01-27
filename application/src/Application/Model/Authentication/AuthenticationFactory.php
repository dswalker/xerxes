<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Authentication;

use Xerxes\Utility\Registry;
use Xerxes\Mvc\Request;

/**
 * Authentication factory
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class AuthenticationFactory
{
	/**
	 * Creat an authentication object
	 * 
	 * @param Request $request
	 * @return Scheme
	 */
	
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
		
		$local_class_name = 'Custom\Authentication' . '\\' . ucfirst($name);
		
		if ( class_exists($local_class_name) )
		{
			$class_name = $local_class_name;
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