<?php

namespace Application\Model\Authentication;

use Zend\Mvc\MvcEvent;

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
	public function getAuthenticationObject($name, MvcEvent $e)
	{
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