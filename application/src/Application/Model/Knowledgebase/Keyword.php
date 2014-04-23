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
 * Keyword
 *
 * @author David Walker <dwalker@calstate.edu>
 * 
 * @Entity @Table(name="keywords")
 */

class Keyword 
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $keyword_id;
	
	/**
	 * @Column(type="string") 
	 */
	protected $value;
	
	/**
	 * @ManyToOne(targetEntity="Database", inversedBy="keywords")
	 */
	protected $database;
	
	/**
	 * Create new Keyword
	 */
	
	public function __construct($value)
	{
		$this->value = $value;
	}
	
	/**
	 * Set Database
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