<?php

namespace Application\Model\KnowledgeBase;

use Xerxes\Utility\DataValue,
	Xerxes\Utility\Parser;

/**
 * Category
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Category extends DataValue
{
	public $category_id;
	public $name;
	public $normalized;
	public $old;
	public $lang;
	public $subcategories = array();
	public $sidebar = array();
	
	/**
	 * Get the name of the category, normalized (lowercase, just alpha and dashes)
	 *
	 * @return string
	 */
	
	public function getId()
	{
		// this is influenced by the setlocale() call with category LC_CTYPE
		
		$normalized = iconv( 'UTF-8', 'ASCII//TRANSLIT', $this->name ); 
		$normalized = Parser::strtolower( $normalized );
		
		$normalized = str_replace( "&amp;", "", $normalized );
		$normalized = str_replace( "'", "", $normalized );
		$normalized = str_replace( "+", "-", $normalized );
		$normalized = str_replace( " ", "-", $normalized );
		
		$normalized = Parser::preg_replace( '/\W/', "-", $normalized );
		
		while ( strstr( $normalized, "--" ) )
		{
			$normalized = str_replace( "--", "-", $normalized );
		}
		
		return $normalized;
	}
}
