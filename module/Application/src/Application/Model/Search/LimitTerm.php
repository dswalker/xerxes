<?php

namespace Application\Model\Search;

/**
 * Search Limit Term
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class LimitTerm
{
	public $boolean;
	public $field;
	public $relation;
	public $value;
	public $key;
	
	/**
	 * Constructor
	 * 
	 * @param string $boolean		boolean combine type
	 * @param string $field			field name
	 * @param string $relation		operator ('=', '>', etc.)
	 * @param string $value			value
	 */
	
	public function __construct($boolean = null, $field = null, $relation = null, $value = null, $key = null)
	{
		$this->boolean = $boolean;
		$this->field = $field;
		$this->relation = $relation;
		$this->value = $value;		
		$this->key = $key;
	}
}
