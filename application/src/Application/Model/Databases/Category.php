<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Databases;

use Doctrine\Common\Collections\ArrayCollection;
use Xerxes\Utility\Parser;

/**
 * Category
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="categories")
 */

class Category
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;
	
	/**
	 * @Column(type="string")
	 */
	protected $name;
	
	/**
	 * @Column(type="string")
	 */
	protected $normalized;
	
	/**
	 * @OneToMany(targetEntity="Subcategory", mappedBy="category")
	 * @var Subcategory[]
	 */	
	protected $subcategories;
	
	/**
	 * Create new Category
	 */
	
	public function __construct()
	{
		$this->subcategories = new ArrayCollection();
	}	
	
	/**
	 * Create a normalize category name (lowercase, just alpha and dashes) 
	 * from supplied name
	 * 
	 * @param string $name
	 */
	
	public function setNormalizedFromName($name)
	{
		// convert accented character and the like to just ascii equivalent
		// this is influenced by the setlocale() call with category LC_CTYPE
		
		$this->normalized = iconv( 'UTF-8', 'ASCII//TRANSLIT', $name ); 
		$this->normalized = Parser::strtolower( $this->normalized );
		
		// strip out weird characters
		
		$this->normalized = str_replace( "&amp;", "", $this->normalized );
		$this->normalized = str_replace( "'", "", $this->normalized );
		
		// convert these to dashes
		
		$this->normalized = str_replace( "+", "-", $this->normalized );
		$this->normalized = str_replace( " ", "-", $this->normalized );
		
		// now any other non-word character to a dash
		
		$this->normalized = Parser::preg_replace( '/\W/', "-", $this->normalized );
		
		// pair multiple dashes down to one
		
		while ( strstr( $this->normalized, "--" ) )
		{
			$this->normalized = str_replace( "--", "-", $this->normalized );
		}
	}
}
