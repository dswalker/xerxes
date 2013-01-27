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

use Composer\Autoload\ClassLoader;

/**
 * Bootstrap
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Bootstrap
{
	/**
	 * @var string
	 */
	
	private $app_dir; // path to application root

	/**
	 * @var ClassLoader
	 */
	
	private $class_loader;
	
	/**
	 * Bootsrap
	 */
	
	public function __construct(ClassLoader $class_loader)
	{
		$this->class_loader = $class_loader;
		
		// app dir
		
		$this->app_dir = dirname(dirname(dirname(__DIR__))); // three dir's up
		
		// working (instance) directory custom dir
		
		$this->class_loader->add('Local', getcwd() . '/custom');
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
	 * Add a local namepspace
	 * 
	 * @param string $namespace
	 * @param string $path
	 */
	
	public function addLocalNamespace($namespace, $path)
	{
		$this->class_loader->add($namespace, $path);
	}
	
	/**
	 * Get the Class Loader
	 * 
	 * @return ClassLoader
	 */
	
	public function getClassLoader()
	{
		return $this->class_loader;
	}
}