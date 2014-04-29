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
 * Database Subcategory Join
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="databases_subcategories")
 */

class DatabaseSequence
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;
	
	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $sequence = 999;
	
	/**
	 * @ManyToOne(targetEntity="Database", inversedBy="database_sequence", cascade={"persist"})
	 * @JoinColumn(name="database_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Database[]
	 */	
	protected $database;
	
	/**
	 * @ManyToOne(targetEntity="Subcategory", inversedBy="database_sequence")
	 * @JoinColumn(name="subcategory_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Subcategory
	 */
	protected $subcategory;
	
	/**
	 * Create new Database Sequence
	 */
	
	public function __construct()
	{
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
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
	 * @return Database
	 */
	public function getDatabases()
	{
		return $this->databases;
	}

	/**
	 * @param Database $databases
	 */
	public function setDatabase(Database $database)
	{
		$this->database = $database;
	}

	/**
	 * @param Subcategory $subcategory
	 */
	public function setSubcategory($subcategory)
	{
		$this->subcategory = $subcategory;
	}

	/**
	 * @return Subcategory
	 */
	public function getSubcategory()
	{
		return $this->subcategory;
	}	
	
	/**
	 * @return array
	 */
	
	public function toArray()
	{
		$final = array();
	
		foreach ( $this as $key => $value )
		{
			if ( $key == 'subcategory')
			{
				continue;
			}
			
			if ( $key == 'database')
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