<?php

namespace Application\Model\Databases;

use Xerxes\Utility\Parser;

/**
 * Databases Config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Config.php 1709 2011-02-25 15:54:04Z dwalker@calstate.edu $
 * @package Xerxes
 */

class Config extends Config
{
	protected $config_file = "config/databases";
	private $usergroups = array(); // user groups
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
	
	/**
	 * Initialize the object by picking up and processing the config xml file
	 * 
	 * @exception 	will throw exception if no configuration file can be found
	 */	
	
	public function init()
	{
		parent::init();
					
		// get group information out of config.xml too
		// we just store actual simplexml elements in the 
		// $this->usergroups array.
				
		$groups = $this->xml->configuration->groups->group;
				
		if ( $groups != false )
		{
			foreach ( $groups as $group )
			{
				$id = ( string ) $group["id"];
				$this->usergroups[Parser::strtoupper($id)] = $group; //case insensitive
			}
		}		
	}
	
	/**
	 * Return identifiers for all groups
	 * 
	 * @return array of ids, null if none
	 */

	public function userGroups()
	{
		if ( $this->usergroups != null )
		{
			return array_keys( $this->usergroups );
		} 
		else
		{
			return null;
		}
	}
	
	/**
	 * Get the display name of the group
	 * 
	 * @param string $id	group id
	 * @return steing 		display name, or id if none found
	 */
	
	public function getGroupDisplayName($id)
	{	  
		$id = Parser::strtoupper($id); //case insensitive
		
		if ( array_key_exists( $id, $this->usergroups ) )
		{
			$group = $this->usergroups[$id];
			return ( string ) $group->display_name;
		} 
		else
		{
			return $id;
		}
	}
	
	/**
	 * Return the IP range for the group
	 * 
	 * @param string $id	group id
	 * @return string 		ip range for the group (or ID if not found?)
	 */
	
	public function getGroupLocalIpRanges($id)
	{
		if ( array_key_exists( $id, $this->usergroups ) )
		{
			$group = $this->usergroups[$id];
			return ( string ) $group->local_ip_range;
		} 
		else
		{
			return $id;
		}
	}
	
	/*
	 * Return the simplexml object for a group
	 * 
	 * @param string $id	group id
	 * @return simplexml 	or null if not found
	 */

	public function getGroupXml($id)
	{
		if ( array_key_exists( $id, $this->usergroups ) )
		{
			return $this->usergroups[$id];
		} 
		else
		{
			return null;
		}
	}
}
