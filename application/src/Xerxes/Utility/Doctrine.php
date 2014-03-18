<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Utility;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Tools\Setup;

/**
 * Doctrine
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class Doctrine extends DatabaseConnection
{
	/**
	 * Lazy load the EntityManager
	 * 
	 * @param array $paths    to entities
	 * @return EntityManager
	 */
	
	protected function getEntityManager(array $paths)
	{
		$params = array(
			'pdo' => $this->pdo()
		);
		
		$config = Setup::createAnnotationMetadataConfiguration($paths, true);
		return EntityManager::create($params, $config);		
	}
	
	/**
	 * Convert Doctrine objects to array 
	 * 
	 * @param mixed $object  any Doctrine object
	 * @param bool $deep     [optional] the entire object
	 */
	
	public function convertToArray( $object, $deep = false )
	{
		$final = array();
		
		if ( is_array($object) )
		{
			foreach ( $object as $key => $value )
			{
				$final[$key] = $this->convertToArray($value);
			}
			
			return $final;
		}

		$reflect = new \ReflectionClass($object);
		$methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);
			
		foreach ( $methods as $method )
		{
			$method_name = $method->getName();
			
			if ( substr($method_name, 0, 3) == 'get' )
			{
				// everything after get and convert camel case to underscore
				
				$name = substr($method_name, 3);
				$name = preg_replace('/([A-Z])/', '_$1', $name);
				
				// object's value
				
				$value = $object->$method_name();
				
				// if this is an object or array it needs to be converted to arrays too
				
				if ( is_object($value) || is_array($value) )
				{
					if ( $deep == true ) // but only if we want more than a shallow array
					{
						$final[$name] = $this->convertToArray($value);
					}
				}
				else
				{
					$final[$name] = $value;
				}
			}
		}
		
		return $final;
	}
}
