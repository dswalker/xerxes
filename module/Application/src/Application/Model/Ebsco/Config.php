<?php

namespace Application\Model\Ebsco;

use Application\Model\Search;

/**
 * Ebsco Config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
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

	public function getDatabaseName($id)
	{
		foreach ( $this->getConfig("EBSCO_DATABASES") as $database )
		{
			if ($database["id"] == $id )
			{
				return (string) $database["name"];
			}
		}
	}
}
