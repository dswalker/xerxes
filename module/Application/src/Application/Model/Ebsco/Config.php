<?php

/**
 * Summon Config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $$
 * @package Xerxes
 */

class Xerxes_Model_Ebsco_Config extends Xerxes_Model_Search_Config
{
	protected $config_file = "config/ebsco";
	private static $instance; // singleton pattern
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Xerxes_Model_Ebsco_Config();
			$object = self::$instance;
			$object->init();			
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
