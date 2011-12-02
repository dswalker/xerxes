<?php

namespace Application\Model\Search;

/**
 * Search Limit Term
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class LimitTerm
{
	public $field;
	public $relation;
	public $value;
	public $key;
	
	/**
	 * Constructor
	 * 
	 * @param string $field			field name
	 * @param string $relation		operator ('=', '>', etc.)
	 * @param string $value			value
	 */
	
	public function __construct($field, $relation, $value, $key = null)
	{
		$this->field = $field;
		$this->relation = $relation;
		$this->value = $value;		
		$this->key = $key;
	}
}
