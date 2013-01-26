<?php

namespace Xerxes\Mvc;

/**
 * Bootstrap
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @package  Xerxes
 * @link http://xerxes.calstate.edu
 * @license
 */

class Bootstrap
{
	static protected $config;
	private static $instance; // singleton pattern
	
	protected function __construct()
	{
	}
	
	public static function setConfig( array $config )
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Bootstrap();
			self::$config = $config;
		}
		
		return self::$instance;
	}
	
	public static function get($name, $required = false)
	{
		if ( array_key_exists($name, self::$config))
		{
			return self::$config[$name];
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
