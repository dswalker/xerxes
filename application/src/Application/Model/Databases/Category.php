<?php

namespace Application\Model\Databases;

use Xerxes\Utility\DataValue,
	Xerxes\Utility\Parser;

/**
 * Category
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @link http://xerxes.calstate.edu
 * @license
 */

class Category extends DataValue
{
	public $category_id;
	public $name;
	public $normalized;
	public $subcategories = array();
	public $related_resources = array();
	
	/**
	 * Normalize the category name (lowercase, just alpha and dashes)
	 * 
	 * @param string $name
	 * @return string
	 */
	
	public function getId($name)
	{
		// this is influenced by the setlocale() call with category LC_CTYPE
		
		$normalized = iconv( 'UTF-8', 'ASCII//TRANSLIT', $name ); 
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
