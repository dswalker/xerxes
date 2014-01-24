<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Ebsco;

use Application\Model\Search;

/**
 * Ebsco Config
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Config extends Search\Config
{
	protected $config_file = "config/ebsco";
	private static $instance; // singleton pattern
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Config();
			$object = self::$instance;
			$object->init();
			
			// all ebsco links should be proxied
			
			$object->setConfig('SHOULD_PROXY', true);
		}
		
		return self::$instance;
	}
}
