<?php

namespace Application\Model\Solr;

use Application\Model\Search;

/**
 * Solr Config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Config.php 1715 2011-02-25 18:43:47Z dwalker@calstate.edu $
 * @package Xerxes
 */

class Config extends Search\Config
{
	protected $config_file = "config/solr";
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
