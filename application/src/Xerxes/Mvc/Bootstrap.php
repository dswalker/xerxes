<?php

namespace Xerxes\Mvc;

/**
 * Bootstrap
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @version
 * @package  Xerxes
 * @link
 * @license
 */

class Bootstrap
{
	private $config;
	
	public function __construct(array $config)
	{
		$this->config = $config;
	}
	
	public function get($name, $required = false)
	{
		if ( array_key_exists($name, $this->config))
		{
			return $this->config[$name];
		}
		elseif ( $required == true )
		{
			throw new \Exception("Could not find '$name' in application config");
		}
		else
		{
			return null;
		}
	}
}
