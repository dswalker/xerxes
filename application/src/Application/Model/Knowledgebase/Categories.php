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
 * Category List
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Categories
{
	/**
	 * @var array
	 */
	private $values = array();
	
	/**
	 * New Categories
	 * 
	 * @param array $categories
	 */
	
	public function __construct(array $categories)
	{
		foreach ( $categories as $category )
		{
			$this->values[] = $category->toArray();
		}
	}
	
	/**
	 * @return array
	 */
	
	public function toArray()
	{
		return $this->values;
	}
}