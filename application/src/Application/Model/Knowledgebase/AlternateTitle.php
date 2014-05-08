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
	protected $value;
	
	/**
	 * @ManyToOne(targetEntity="Database", inversedBy="alternate_titles")
	 * @JoinColumn(name="database_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Database
	 */
	protected $database;
	
	/**
	 * Create new Alternate Title
	 */
	
	public function __construct($value)
	{
		$this->value = $value;
	}	
	
	/**
	 * @param Database $database
	 */
	public function setDatabase(Database $database) 
	{
		$this->database = $database;
	}

	/**
	 * Get value
	 * @return string
	 */
	
	public function getValue()
	{
		return $this->value;
	}
}