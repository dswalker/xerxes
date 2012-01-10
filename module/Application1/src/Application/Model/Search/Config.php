<?php

namespace Application\Model\Search;

use Xerxes\Utility\Registry;

/**
 * Search Config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

abstract class Config extends Registry
{
	private $facets = array();
	private $fields = array();
	
	/**
	 * Initialize the object by picking up and processing the config xml file
	 */	
	
	public function init()
	{
		parent::init();
		
		// facets
		
		$facets = $this->xml->xpath("//config[@name='facet_fields']/facet");
		
		if ( $facets !== false )
		{
			foreach ( $facets as $facet )
			{
				$this->facets[(string) $facet["internal"]] = $facet;
			}
		}
		
		// fields
		
		$fields = $this->xml->xpath("//config[@name='basic_search_fields']/field");
		
		if ( $fields !== false )
		{
			foreach ( $fields as $field )
			{
				$this->fields[(string) $field["internal"]] = (string) $field["public"];
			}
		}
	}
	
	/**
	 * Get the ID for this config
	 */
	
	public function getID()
	{
		$config = explode('/', $this->config_file);
		return array_pop($config);
	}
	
	/**
	 * Get the defined public name of a given facet
	 * 
	 * @param string $internal		facet internal id
	 * @return string 				public name, or null if not defined
	 */
	
	public function getFacetPublicName($internal)
	{
		if ( array_key_exists($internal, $this->facets) )
		{
			$facet = $this->facets[$internal];
			
			return (string) $facet["public"]; 
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the defined public name of a facet value
	 * 
	 * @param string $internal_group		group internal id
	 * @param string $internal_group		internal field id
	 * 
	 * @return string 						public name, or the internal field name supplied if not found
	 */	
	
	public function getValuePublicName($internal_group, $internal_field)
	{
		if ( strstr($internal_field, "'") || strstr($internal_field, " ") )
		{
			return $internal_field;
		}
		
		$query = "//config[@name='facet_fields']/facet[@internal='$internal_group']/value[@internal='$internal_field']";
		
		$values = $this->xml->xpath($query);
		
		if ( count($values) > 0 )
		{
			return (string) $values[0]["public"];
		}
		else
		{
			return $internal_field;
		}
	}

	/**
	 * Get the defined facet type for a given facet
	 * 
	 * @param string $internal			facet internal id
	 * @return string 					type
	 */		
	
	public function getFacetType($internal)
	{
		$facet = $this->getFacet($internal);
		return (string) $facet["type"];
	}
	
	/**
	 * Whether the supplied facet is a date facet
	 * 
	 * @param string $internal			facet internal id
	 * @return bool 					true if date, false if not
	 */		
	
	public function isDateType($internal)
	{
		if ( $this->getFacetType($internal) == "date" )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Return a facet definition from the config file
	 * 
	 * @param string $internal			facet internal id
	 * @return simplexml
	 */			

	public function getFacet($internal)
	{
		if ( array_key_exists($internal, $this->facets) )
		{
			return $this->facets[$internal];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Return all of the facet definitions
	 * 
	 * @return array of simplexml objects
	 */		
	
	public function getFacets()
	{
		return $this->facets;
	}
	
	/**
	 * Return all of the field definitions
	 * 
	 * @return array of simplexml objects
	 */		
	
	public function getFields()
	{
		return $this->fields;
	}
	
	/**
	 * Return a specific attribute from a field definition
	 * 
	 * @param string $internal			facet internal id
	 * @param string $internal			facet internal id
	 * 
	 * @return string if found, null if not
	 */		
	
	public function getFieldAttribute( $field, $attribute )
	{
		$values = $this->xml->xpath("//config[@name='basic_search_fields']/field[@internal='$field']/@$attribute");
		
		if ( count($values) > 0 )
		{
			return (string) $values[0];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Swap the sort id for the internal sort option
	 * 
	 * @param string $id 	public id
	 * @return string 		the internal sort option
	 */
	
	public function swapForInternalSort($id)
	{
		$config = $this->getConfig("sort_options");
		
		if ( $config != null )
		{
			foreach ( $config->option as $option )
			{
				if ( (string) $option["id"] == $id )
				{
					return (string) $option["internal"];
				}
			}			
		}
		
		// if we got this far no mapping, so return original
		
		return $id; 
	}

	/**
	 * Swap the field id for the internal field index
	 * 
	 * @param string $id 	public id
	 * @return string 		the internal field
	 */	
	
	public function swapForInternalField($id)
	{
		$config = $this->getConfig("basic_search_fields");
		
		if ( $config != null )
		{
			foreach ( $config->field as $field )
			{
				$field_id = (string) $field["id"];
				
				if ( $field_id == "")
				{
					continue;
				}
				
				// if $id was blank, then we take the first
				// one in the list, otherwise, we're looking 
				// to match
				
				elseif ( $field_id == $id || $id == "")
				{
					return (string) $field["internal"];
				}
			}			
		}
		
		// if we got this far no mapping, so return original
		
		return $id; 
	}

	/**
	 * The options for the sorting mechanism
	 * 
	 * @return array
	 */
	
	public function sortOptions()
	{
		$options = array();
		
		$config = $this->getConfig("sort_options");
		
		if ( $config != null )
		{
			foreach ( $config->option as $option )
			{
				$options[(string)$option["id"]] = (string) $option["public"];
			}
		}
		
		return $options;
	}	
}
