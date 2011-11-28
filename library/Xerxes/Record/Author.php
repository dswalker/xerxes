<?php

/**
 * Record Author
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Author.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Xerxes_Record_Author
{
	public $first_name;
	public $last_name;
	public $init;
	public $name;
	public $type;
	public $additional;
	public $display;

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

		if ( $type == "personal" )
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
}