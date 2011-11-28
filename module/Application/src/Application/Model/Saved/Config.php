<?php

/**
 * Primo Config
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Saved_Config extends Xerxes_Model_Search_Config
{
	protected $config_file = "config/folder";
	private static $instance; // singleton pattern
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Xerxes_Model_Saved_Config();
			$object = self::$instance;
			$object->init();			
		}
		
		return self::$instance;
	}
}
