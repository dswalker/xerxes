<?php

/**
 * Summon Config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Config.php 1715 2011-02-25 18:43:47Z dwalker@calstate.edu $
 * @package Xerxes
 */

class Xerxes_Model_Summon_Config extends Xerxes_Model_Search_Config
{
	protected $config_file = "config/summon";
	private static $instance; // singleton pattern
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Xerxes_Model_Summon_Config();
			$object = self::$instance;
			$object->init();			
		}
		
		return self::$instance;
	}
}
