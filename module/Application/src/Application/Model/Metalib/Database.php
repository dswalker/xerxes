<?php

/**
 * Metalib Database
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Metalib_Database extends Xerxes_Framework_DataValue  
{
	public $xml;
	
	public $metalib_id; // metalib id
	public $title_display; // database title
	public $type; 
	public $data;
	
	private $searchable_by_user; // is resource searchable by user
	private $config; // metalib config
	
	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		$this->config = Xerxes_Model_Metalib_Config::getInstance();
	}
	
	/**
	 * Load data from database resutls array
	 *
	 * @param array $arrResult
	 * @param Xerxes_Model_Metalib_User $user
	 */
	
	public function load($arrResult, $user = null)
	{
		parent::load($arrResult);
		
		if ( $this->data != "" )
		{
			$this->xml = simplexml_load_string($this->data);
		}

		if ( $user != null )
		{
			$this->searchable_by_user = $this->isSearchableByUser($user);
		}
	}
	
	public function __get($name)
	{
		if ( $this->xml instanceof SimpleXMLElement )
		{
			return (string) $this->xml->$name;
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Get value of a field
	 * 
	 * @param string $field field name
	 * @return string
	 */
	
	public function get($field)
	{
		$values = array();
		
		if ( $this->xml instanceof SimpleXMLElement )
		{
			foreach ($this->xml->$field as $value)
			{
				array_push($values, $value);
			}
		}
		
		return $values;
	}
	
	/**
	 * Serialize to XML
	 * 
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
		// data is already in xml, we just use this opportunity to
		// enhance it with a few bits of data we don't already have
		
		
		### display name for group restrictions
			
		if ( count($this->xml->group_restriction) > 0 )
		{
			foreach ( $this->xml->group_restriction as $group_restriction )
			{
				$group_restriction->addAttribute("display_name", $this->config->getGroupDisplayName((string) $group_restriction));
			}
		}
		
		
		### split note fields into separate entries per language
		
		$multilingual = $this->config->getConfig("db_description_multilingual", false, ""); // XML object

		// build a list of configured description languages
		
		$db_languages_code = array();
		$db_languages_order = array();
		
		if ( $multilingual != "" )
		{
			$order = 0;
			
			foreach ( $multilingual->language as $language )
			{
				$order++;
				$code = NULL;
				
				foreach ( $language->attributes() as $name => $val )
				{
					if ( $name == "code" )
					{
						$code = (string) $val;
					}
				}
				
				$db_languages_order[$code] = $order;
				$db_languages_code[$order] = $code;
			}
		}
		
		$notes = array("description" , "search_hints");
		
		foreach ( $notes as $note_field_name )
		{
			$node_queue = array(); // nodes to add when done looping to prevent looping over nodes added inside the loop
			
			foreach ( $this->xml->$note_field_name as $note_field_xml )
			{
				$note_field = (string) $note_field_xml;
				
				$pos = strpos($note_field, '######');
				
				if ( $multilingual == false || $pos === false )
				{
					$note_field = str_replace('######', '\n\n\n', $note_field);
				}
				else
				{
					$descriptions = explode('######', $note_field);
					$i = 1;
					
					foreach ( $descriptions as $description )
					{
						$description = $this->embedNoteField($description);
						
						$node_queue[] = array(
							'note_field_name' => $note_field_name , 
							'description' => $description , 
							'code' => $db_languages_code[$i ++]
						);
					}
				}
				
				$note_field = $this->embedNoteField($note_field);
				$this->xml->$note_field_name = $note_field;
				$this->xml->$note_field_name->addAttribute('lang', 'ALL');
			}
			
			foreach ( $node_queue as $node )
			{
				$descNode = $this->xml->addChild($node['note_field_name'], $node['description']);
				$descNode->addAttribute('lang', $node['code']);
			}
		}
		
		// convert to DOMDocument
		
		$objDom = new DOMDocument();
		$objDom->loadXML($this->xml->asXML());
		
		// add metalib id
		
		$objDatabase = $objDom->documentElement;
		$objDatabase->setAttribute("metalib_id", $this->metalib_id);
		
		// is the particular user allowed to search this?
		
		$objElement = $objDom->createElement("searchable_by_user", $this->searchable_by_user);
		$objDatabase->appendChild($objElement);

		return $objDom;
	}
	
	/**
	 * Determines if the database is searchable by user
	 * 
	 * @return boolean
	 */
	
	private function isSearchableByUser(Xerxes_Model_Metalib_User $user)
	{
		$allowed = "";
		
		if ( $this->searchable != 1 )
		{
			//nobody can search it!
			$allowed = false;
		}
		elseif ( $this->guest_access != "" )
		{
			//anyone can search it!
			$allowed = true;
		}
		elseif ( count($this->group_restrictions) > 0 )
		{
			// they have to be authenticated, and in a group that is included
			// in the restrictions, or in an ip address associated with a
			// restricted group.
			
			$allowed = ($user->isAuthenticatedUser() && array_intersect($user->getUserGroups(), $this->group_restrictions));
			
			if ( ! $allowed )
			{
				// not by virtue of a login, but now check for ip address
				
				$ranges = array();
				
				foreach ( $this->group_restrictions as $group )
				{
					$ranges[] = $this->config->getGroupLocalIpRanges($group);
				}
				
				$allowed = Xerxes_Framework_Restrict::isIpAddrInRanges($user->getIpAddress(),implode(",", $ranges));
			}
		}
		else
		{
			// ordinary generally restricted resource.  they need to be 
			// an authenticated user, or in the local ip range.
			
			if ( $user->isAuthenticatedUser() || $user->isCampusUser() )
			{
				$allowed = true;
			}
		}
		
		return $allowed;
	}
	
	/**
	 * Handling of note field escaping
	 * 
	 * @param string $note_field
	 * @return string
	 */

	private function embedNoteField($note_field)
	{
		// description we handle special for escaping setting. Note that we
		// handle html escpaing here in controller for description, view
		// should use disable-output-escaping="yes" on value-of of description.

		$escape_behavior = $this->config->getConfig("db_description_html", false, "escape"); // 'escape' ; 'allow' ; or 'strip'
		$note_field = str_replace('##', ' ', $note_field);
		
		if ( $escape_behavior == "strip" )
		{
			$allow_tag_list = $this->config->getConfig("db_description_allow_tags", false, '');
			$arr_allow_tags = explode(',', $allow_tag_list);
			$param_allow_tags = '';
			
			foreach ( $arr_allow_tags as $tag )
			{
				$param_allow_tags .= "<$tag>";
			}
			$note_field = strip_tags($note_field, $param_allow_tags);
		}
		
		if ( $escape_behavior == "escape" )
		{
			$note_field = htmlspecialchars($note_field);
		}
		
		return $note_field;
	}
}