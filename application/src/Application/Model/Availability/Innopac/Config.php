<?php

namespace Application\Model\Availability\Innopac;

use Xerxes\Utility\Registry;

/**
 * Solr Config
 *
 * @author David Walker
 */

class Config extends Registry
{
	protected $config_file = "config/availability/innopac";
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
