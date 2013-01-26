<?php

namespace Application\Model\Availability\Voyager;

use Xerxes\Utility\Registry;

/**
 * Solr Config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @package Xerxes
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
