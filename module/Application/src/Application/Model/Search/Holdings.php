<?php

namespace Application\Model\Search;

/**
 * Result Holdings
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Items.php 1717 2011-02-25 21:47:55Z dwalker@calstate.edu $
 * @package Xerxes
 */

class Holdings
{
	public $none;
	public $items = array();
	public $holdings = array();
	
	/**
	 * Add an item to this group of items
	 * 
	 * @param Item $item
	 */
	
	public function addItem(Item $item)
	{
		array_push($this->items, $item);
	}

	/**
	 * Add (journal) holdings record to this group of items
	 * 
	 * @param Holding $holdings
	 */
	
	public function addHolding(Holding $holdings)
	{
		array_push($this->holdings, $holdings);
	}
	
	/**
	 * Get all items
	 */
	
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Get all holdings
	 */
	
	public function getHoldingRecords()
	{
		return $this->holdings;
	}	
	
	/**
	 * The number of items
	 */
	
	public function length()
	{
		return count($this->items);
	}
}
