<?php

namespace Application\Model\KnowledgeBase;

use Application\Model\Authentication\User,
	Xerxes\Utility\DataValue,
	Xerxes\Utility\Parser,
	Xerxes\Utiltity\Restrict;

/**
 * Database
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Database extends DataValue  
{
	public $metalib_id; // @todo: switch to database_id
	
	public $database_id; // database id
	public $title_display; // database title
	public $type; // database type
	public $data; // other data about the database

	private $config; // database config
	private $xml; // simplexml
	
	/**
	 * Load data from database results array
	 *
	 * @param array $arrResult
	 * @param User $user
	 */
	
	public function load($arrResult)
	{
		parent::load($arrResult);
		$this->database_id = (string) $this->metalib_id; // @todo: remove this when switched to database_id
	}
	
	/**
	 * Serialize
	 */
	
	public function __sleep()
	{
		// don't include config and simplexml elements
		
		return Parser::removeProperties(get_object_vars($this), array('config', 'xml'));
	}
	
	/**
	 * Get value of a field
	 *
	 * @param string $name field name
	 * @return string
	 */
	
	public function __get($name)
	{
		return (string) $this->simplexml()->$name;
	}
	
	/**
	 * Get values of all given fields
	 * 
	 * @param string $field field name
	 * @return array
	 */
	
	public function get($field)
	{
		$values = array();
		
		foreach ($this->simplexml()->$field as $value)
		{
			array_push($values, $value);
		}
		
		return $values;
	}
	
	/**
	 * Determines if the database is searchable by user
	 * 
	 * @return boolean
	 */
	
	public function isSearchableByUser(User $user)
	{
		$allowed = "";
		
		if ( $this->searchable != 1 )
		{
			$allowed = false; //nobody can search it!
		}
		elseif ( $this->guest_access != "" )
		{
			$allowed = true; //anyone can search it!
		}
		elseif ( count($this->group_restrictions) > 0 )
		{
			// user has to be authenticated, and in a group that is included in the restrictions, 
			// or in an ip address associated with a restricted group.
			
			// @todo: setup user groups in user object
			
			$allowed = ($user->isAuthenticated() && array_intersect($user->getUserGroups(), $this->group_restrictions));
			
			if ( ! $allowed )
			{
				// not by virtue of a login, but now check for ip address
				
				$ranges = array();
				
				foreach ( $this->group_restrictions as $group )
				{
					$ranges[] = $this->config()->getGroupLocalIpRanges($group); // @todo: move this to registry?
				}
				
				$allowed = $user->isInLocalIpRange();
			}
		}
		else
		{
			// ordinary generally restricted resource.  they need to be 
			// an authenticated user, or in the local ip range.
			
			if ( $user->isAuthenticated() || $user->isInLocalIpRange() )
			{
				$allowed = true;
			}
		}
		
		return $allowed;
	}
	
	/**
	 * Lazyload Config
	 */
	
	public function config()
	{
		if ( ! $this->config instanceof Config )
		{
			$this->config = Config::getInstance();
		}
	
		return $this->config;
	}
	
	/**
	 * Lazyload SimpleXmlElement
	 *
	 * @throws Exception
	 */
	
	public function simplexml()
	{
		if ( ! $this->xml instanceof \SimpleXMLElement )
		{
			if ( $this->data == "" )
			{
				throw new \Exception("Cannot access data, it has not been loaded");
			}
				
			$this->xml = simplexml_load_string($this->data);
		}
	
		return $this->xml;
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

		$escape_behavior = $this->config()->getConfig("db_description_html", false, "escape"); // 'escape' ; 'allow' ; or 'strip'
		$note_field = str_replace('##', ' ', $note_field);
		
		if ( $escape_behavior == "strip" )
		{
			$allow_tag_list = $this->config()->getConfig("db_description_allow_tags", false, '');
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
			
		if ( count($this->simplexml()->group_restriction) > 0 )
		{
			foreach ( $this->simplexml()->group_restriction as $group_restriction )
			{
				$group_restriction->addAttribute("display_name", $this->config()->getGroupDisplayName((string) $group_restriction));
			}
		}
	
	
		### split note fields into separate entries per language
	
		$multilingual = $this->config()->getConfig("db_description_multilingual", false, ""); // XML object
	
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
				
			foreach ( $this->simplexml()->$note_field_name as $note_field_xml )
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
				$this->simplexml()->$note_field_name = $note_field;
				$this->simplexml()->$note_field_name->addAttribute('lang', 'ALL');
			}
				
			foreach ( $node_queue as $node )
			{
				$descNode = $this->simplexml()->addChild($node['note_field_name'], $node['description']);
				$descNode->addAttribute('lang', $node['code']);
			}
		}
	
		// convert to DOMDocument
	
		$objDom = Parser::convertToDOMDocument($this->simplexml()->asXML());
	
		// add database id
	
		$objDatabase = $objDom->documentElement;
		$objDatabase->setAttribute("database_id", $this->database_id);
	
		// is the particular user allowed to search this?

		$objElement = $objDom->createElement("searchable_by_user", $this->searchable_by_user); // @todo need to change this
		$objDatabase->appendChild($objElement);
	
		return $objDom;
	}
}