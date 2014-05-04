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
 * Database Type
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="types")
 */

class Type 
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;
	
	/**
	 * @Column(type="string") 
	 */
	protected $value;
	
	/**
	 * @ManyToMany(targetEntity="Database", mappedBy="types")
	 * @var Database[]
	 */
	protected $databases;

	/**
	 * Create new Type
	 */
	
	public function __construct()
	{
		$this->databases = new ArrayCollection();
	}
	
	/**
	 * Add Database
	 * @param Database $database
	 */
	
	public function addDatabase(Database $database)
	{
		$this->databases[] = $database;
	}
	
	/**
	 * @return Database[]
	 */
	public function getDatabases()
	{
		return $this->databases->toArray();
	}

	/**
	 * Set value
	 * 
	 * @param string $value
	 */
	
	public function setValue($value)
	{
		return $this->value = $value;
	}	
	
	/**
	 * Get value
	 * @return string
	 */
	
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * @return array
	 */
	
	public function toArray()
	{
		$final = array();
	
		foreach ( $this as $key => $value )
		{
			if ( $key != 'databases')
			{
				$final[$key] = $value;
			}
		}
	
		return $final;
	}
}