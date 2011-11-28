<?php

/**
 * Authentication factory
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Authentication_Factory
{
	public function getAuthenticationObject($name, Xerxes_Framework_Request $request, Xerxes_Framework_Registry $registry, Xerxes_Framework_Response $response)
	{
		// sanitize
		
		$name = preg_replace('/\W/', '', $name);
		
		// main class
		
		$class_name = "Xerxes_Model_Authentication_" . ucfirst($name);
		
		// local custom version
		
		$local_file = "config/authentication/$name.php";
		
		if ( file_exists($local_file) )
		{
			require_once($local_file);
			
			$class_name = "Xerxes_CustomAuth_" . ucfirst($name);
			
			if ( ! class_exists($class_name) )
			{
				throw new Exception("the custom authentication scheme '$name' should have a class called '$class_name'");
			}
		}

		// make it

		$authentication = new $class_name($request, $registry, $response);
		
		if ( ! $authentication instanceof Xerxes_Model_Authentication_Abstract)
		{
			throw new Exception("class '$class_name' for the '$name' authentication scheme must extend Xerxes_Model_Authentication_Abstract");
		}
		
		return $authentication;
	}
}