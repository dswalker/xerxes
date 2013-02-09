<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Knowledgebase;

use Xerxes\Utility\Registry;

/**
 * Databases Config
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Config extends Registry
{
	protected $config_file = "config/databases";
	private static $instance; // singleton pattern
	
	/**
	 * Get an instance of the file; Singleton to ensure correct data
	 *
	 * @return Config
	 */	
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Config();
		}
		
		return self::$instance;
	}
}
