<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Availability\Innopac;

use Xerxes\Utility\Registry;

/**
 * Innopac Availability Config
 *
 * @author David Walker <dwalker@calstate.edu>
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
