<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Availability\Voyager;

use Xerxes\Utility\Registry;

/**
 * Voyager Availability Config
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Config extends Registry
{
	protected $config_file = "config/availability/voyager";
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
	
	/**
	 * Replace status id with public message
	 * 
	 * @param string $id
	 */
	
	public function getPublicStatus($id)
	{
		$results = $this->xml->xpath("//status[@key='$id']");
		
		if ( count($results) == 1 )
		{
			return (string) $results[0];
		}
		else
		{
			return null;
		}
	}
}
