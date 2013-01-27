<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Mvc;

/**
 * Bootstrap
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Bootstrap
{
	private $app_dir; // path to application root
	private $namespaces = array(); // local instance defined namespace/path mapping
	
	/**
	 * Bootsrap
	 */
	
	public function __construct()
	{
		// default values by convention
		
		$this->app_dir = dirname(dirname(dirname(__DIR__))); // three dir's up
		$this->namespaces = array('Local' => realpath(getcwd()). '/custom'); // working (instance) directory custom dir
	}
	
	/**
	 * Path to application root
	 * 
	 * @return string
	 */
	
	public function getApplicationDir()
	{
		return $this->app_dir;
	}
	
	/**
	 * Set application root path
	 *
	 * @return string
	 */
	
	public function setApplicationDir($dir)
	{
		return $this->app_dir = $dir;
	}	
	
	/**
	 * Local instance defined namespace/path mapping
	 *
	 * @return array
	 */	
	
	public function getLocalNamespaces()
	{
		return $this->namespaces;
	}
	
	/**
	 * Set all local namespace/path mappings
	 *
	 * @return array
	 */
	
	public function setLocalNamespaces(array $mapping)
	{
		$this->namespaces = $mapping;
	}	
	
	/**
	 * Add a local namepspace
	 * 
	 * @param string $namespace
	 * @param string $path
	 */
	
	public function addLocalNamespace($namespace, $path)
	{
		$this->namespaces[$namespace] = $path;
	}
}