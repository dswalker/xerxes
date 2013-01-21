<?php

namespace Xerxes\Record;

/**
 * Record Author
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Author
{
	public $first_name; // first name
	public $last_name; // surname
	public $init; // middle initial
	public $name; // full form of name (esp for corporate author)
	
	public $type; // personal or otherwise
	public $additional; // additional (i.e., not main) author
	
	public $title; // title (of work) for this author (from MARC standards, yuck!)
	public $search_string; // searchable form of the name
	public $display; // full display of the name
	
	const PERSONAL = 'personal';
	
	/**
	 * Create a Record Author
	 * 
	 * @param string $author			[optional] author name
	 * @param string $author_display	[optional] autor display name
	 * @param string $type				[optional] type of author
	 * @param bool $additional			[optional] whether this author is an additional author
	 */

	public function __construct($author = null, $author_display = null, $type = null, $additional = false)
	{
		$this->type = $type;
		$this->additional = $additional;
		
		$comma = strpos( $author, "," );
		$last_space = strripos( $author, " " );
		
		// for personal authors:

		// if there is a comma, we will assume the names are in 'last, first' order
		// otherwise in 'first last' order -- the second one here obviously being
		// something of a guess, assuming the person has a single word for last name
		// rather than 'van der Kamp', but better than the alternative?

		if ( $type == self::PERSONAL )
		{
			$match_array = array();
			$last = "";
			$first = "";
			$initial = "";
			
			if ( $comma !== false )
			{
				$last = trim( substr( $author, 0, $comma ) );
				$first = trim( substr( $author, $comma + 1 ) );
			} 

			// some databases like CINAHL put names as 'last first' but first 
			// is just initials 'Walker DS' so we can catch this scenario?
			
			elseif ( preg_match( "/ ([A-Z]{1,3})$/", $author, $match_array ) != 0 )
			{
				$first = $match_array[1];
				$last = str_replace( $match_array[0], "", $author );
			} 
			else
			{
				$last = trim( substr( $author, $last_space ) );
				$first = trim( substr( $author, 0, $last_space ) );
			}
			
			if ( preg_match( '/ ([a-zA-Z]{1})\.$/', $first, $match_array ) != 0 )
			{
				$initial = $match_array[1];
				$first = str_replace( $match_array[0], "", $first );
			}
			
			$this->last_name = $last;
			$this->first_name = $first;
			$this->init = $initial;
		
		} 
		else
		{
			$this->name = trim( $author );
		}
		
		// display is different
		
		if ( $author_display != "" )
		{
			$this->display = $author_display;
		}
	}
	
	/**
	 * Get formatted author name
	 * 
	 * @param bool $bolReverse     [optional] reverse personal names as last, first
	 * @return string
	 */
	
	public function getName($bolReverse = false)
	{
		// corporate name
		
		if ( $this->name != "" )
		{
			return $this->name;
		}
		
		else // personal name
		{
			$name = ""; // author name formatted
			
			// last, first init
			
			if ( $bolReverse == true )
			{
				$name = $this->last_name;

				if ( $this->first_name != '' )
				{
					$name .= ", " . $this->first_name;
				}
				
				if ( $this->init != '' )
				{
					$name .= " " . $this->init;
				}
			}
			else // first init last
			{
				$name = $this->first_name . " ";
		
				if ( $this->init != "" )
				{
					$name .= $this->init . " ";
				}
		
				$name .= $this->last_name;
			}
			
			return trim($name);
		}
	}
	
	/**
	 * Get all fields
	 */
	
	public function getAllFields()
	{
		$values = "";
		
		foreach ( $this as $key => $value )
		{
			if ( $key == "additional" || $key == "display")
			{
				continue;
			}
			
			$values .= $value . " ";
		}
		
		return trim($values);
	}
	
	/**
	 * Serialize object to array 
	 */
	
	public function toArray()
	{
		$convert = array(
			'last_name' => 'aulast',
			'first_name' => 'aufirst',
			'init' => 'auinit',
			'name' => 'aucorp',
			'display' => 'display'
		);
		
		$final = array();
		
		// take each property above, but convert the name to the converted (OpenURL-ish) name
		
		foreach ($this as $key => $value)
		{
			if ( $value == '')
			{
				continue;
			}
			
			if ( array_key_exists($key, $convert))
			{
				$final[$convert[$key]] = $value;
			}
			else
			{
				$final[$key] = $value;
			}
		}
		
		return $final;
	}
	
	/**
	 * Serialize to string
	 */
	
	public function __toString()
	{
		return $this->getAllFields();
	}
}