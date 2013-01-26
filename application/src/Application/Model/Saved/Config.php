<?php

namespace Application\Model\Saved;

use Application\Model\Search;

/**
 * Primo Config
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 */

class Config extends Search\Config
{
	protected $config_file = "config/folder";
	private static $instance; // singleton pattern
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Config();
			$object = self::$instance;
			$object->init();			
		}
		
		return self::$instance;
	}
}
