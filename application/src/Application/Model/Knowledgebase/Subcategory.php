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

/**
 * Subcategory
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="subcategories")
 */

class Subcategory
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $sequence;
	
	/**
	 * @ManyToOne(targetEntity="Category", inversedBy="subcategories")
	 * @JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
	 * @var Category
	 */
	protected $category;
	
	/**
	 * @ManyToMany(targetEntity="Database", inversedBy="subcategory", cascade={"persist"})
     * @JoinTable(name="databases_subcategories")
	 * @var Database[]
	 */	
	protected $databases;
	
	/**
	 * Create new Subcategory
	 */
	
	public function __construct()
	{
		$this->databases = new ArrayCollection();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
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
	}

	/**
	 * @return int
	 */
	public function getSequence()
	{
		return $this->sequence;
	}

	/**
	 * @param int $sequence
	 */
	public function setSequence($sequence)
	{
		$this->sequence = $sequence;
	}

	/**
	 * @param Category $category
	 */
	public function setCategory(Category $category) 
	{
		$this->category = $category;
	}

	/**
	 * @return Database[]
	 */
	public function getDatabases()
	{
		return $this->databases->getValues();
	}

	/**
	 * @param Database $databases
	 */
	public function addDatabase(Database $database)
	{
		$database->addSubcategory($this);
		$this->databases[] = $database;
	}
	
	/**
	 * @return array
	 */
	
	public function toArray()
	{
		$final = array();
	
		foreach ( $this as $key => $value )
		{
			if ( $key == 'category')
			{
				continue;
			}
			
			if ( $key == 'databases')
			{
				$final[$key] = $value->toArray();
			}
			else
			{
				$final[$key] = $value;
			}
		}
	
		return $final;
	}
}