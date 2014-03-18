<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Knowledgebase;

use Doctrine\Common\Collections\ArrayCollection;
use Xerxes\Utility\Parser;

/**
 * Category
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="categories",uniqueConstraints={@UniqueConstraint(name="category_unique_idx", columns={"normalized"})})
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
	 * @OneToMany(targetEntity="Subcategory", mappedBy="category", cascade={"persist"})
	 * @var Subcategory[]
	 */	
	protected $subcategories;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $owner;
	
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
	 * @param string $name  [optional] will otherwise use name property
	 */
	
	public function setNormalizedFromName($name = null)
	{
		if ( $name == null )
		{
			$name = $this->name;
		}
		
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
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		
		$this->setNormalizedFromName();
	}

	/**
	 * @return string
	 */
	public function getNormalized() 
	{
		return $this->normalized;
	}

	/**
	 * @param string $normalized
	 */
	public function setNormalized($normalized) 
	{
		$this->normalized = $normalized;
	}

	/**
	 * @return Subcategory[]
	 */
	public function getSubcategories() 
	{
		return $this->subcategories->getValues();
	}

	/**
	 * @param Subcategory $subcategory
	 */
	public function addSubcategory($subcategory) 
	{
		$subcategory->setCategory($this);
		$this->subcategories[] = $subcategory;
	}
	/**
	 * @return string
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * @param string $owner
	 */
	public function setOwner($owner)
	{
		$this->owner = $owner;
	}
}