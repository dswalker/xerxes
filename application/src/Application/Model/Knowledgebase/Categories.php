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

class Categories extends \ArrayIterator
{
	/**
	 * New Categories
	 * 
	 * @param array $categories
	 */
	
	public function __construct(array $categories)
	{
		foreach ( $categories as $category )
		{
			$this[] = $category;
		}
	}
	
	/**
	 * @return array
	 * Shallow copy
	 */
	
	public function toArray($deep = true)
	{
		$final = array();
		
		foreach ( $this as $category )
		{
			$final[] = $category->toArray($deep); // shallow copy
		}
		
		return $final;
	}
}