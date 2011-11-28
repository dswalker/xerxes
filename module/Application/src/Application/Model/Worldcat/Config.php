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

class Xerxes_Model_Worldcat_Config extends Xerxes_Model_Search_Config
{
	protected $config_file = "config/worldcat";
	private static $instance; // singleton pattern
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Xerxes_Model_Worldcat_Config();
			$object = self::$instance;
			$object->init();			
		}
		
		return self::$instance;
	}
	
	public function getWorldcatGroup($source)
	{
		$group_object = new Xerxes_Model_Worldcat_ConfigGroup();
		
		$groups = $this->xml->xpath("//worldcat_groups/group");
		
		if ( $groups != false )
		{
			foreach ( $groups as $group )
			{
				if ( $group["id"] == $source )
				{
					$group_object->libraries_include = (string) $group->libraries;
					$group_object->limit_material_types = (string) $group->limit_material_types;
					$group_object->exclude_material_types = (string) $group->exclude_material_types;
					$group_object->frbr = (string) $group->frbr;
	
					if ( (string) $group->show_holdings == "true" )
					{
						$group_object->show_holdings = true;
					}
	
					// exclude certain libraries?
	
					$id = (string) $group->exclude;
					$group_object->query_limit = (string) $group->query_limit;
	
					if ( $id != "" )
					{
						$arrID = explode(",", $id);
	
						foreach ( $arrID as $strID )
						{
							foreach ( $this->xml->xpath("//worldcat_groups/group[@id='$strID']/libraries") as $exclude )
							{
								if ( $group_object->libraries_exclude != null )
								{
									$group_object->libraries_exclude .= "," . (string) $exclude;
								}
								else
								{
									$group_object->libraries_exclude = (string) $exclude;
								}
							}
						}
					}
					
					break; // we got our object, so quite of out the foreach loop
				}
			}
		}
		
		return $group_object;
	}	
}
