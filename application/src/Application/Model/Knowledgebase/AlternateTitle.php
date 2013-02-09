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

/**
 * Alternate Title
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="alternate_titles")
 */

class AlternateTitle 
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;
	
	/**
	 * @Column(type="string") 
	 * @var string
	 */
	protected $name;
	
	/**
	 * @ManyToOne(targetEntity="Database", inversedBy="alternate_titles")
	 * @var Database
	 */
	protected $database;
	
	/**
	 * @return Database
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * @param Database $database
	 */
	public function setDatabase(Database $database) 
	{
		$this->database = $database;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) 
	{
		$this->name = $name;
	}
}