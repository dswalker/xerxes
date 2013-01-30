<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

/**
 * Result Holdings
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Holdings
{
	/**
	 *  bibliographic id
	 * 
	 * @var string
	 */
	
	public $id;
	
	/**
	 * @var unknown_type
	 */
	protected $bibliographicRecord;
	
	/**
	 * aray of Item's
	 * 
	 * @var array
	 */
	
	public $items = array();
	
	/**
	 * array of Holding's
	 *
	 * @var array
	 */	
	
	public $holdings = array();
	
	/**
	 * array of ElectronicResource's
	 * 
	 * @var array
	 */
	
	public $electronicResources = array();
	
	/**
	 * Placeholder to show there are no holdings
	 * 
	 * @var string
	 */
	
	public $none;
	
	/**
	 * Link to place a hold
	 * 
	 * @var string
	 */
	
	public $hold_url;
	
	/**
	 * Get bibliographic record
	 */
	
	public function getBibliographicRecord()
	{
		return $this->bibliographicRecord;
	}
	
	/**
	 * Set bibliographic record
	 */
	
	public function setBibliographicRecord($record)
	{
		$this->bibliographicRecord = $record;
	}	
	
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
	 * Add (journal) holdings record to this group of items
	 *
	 * @param Holding $holdings
	 */
	
	public function addElectronicResource(ElectronicResource $electronic)
	{
		array_push($this->electronicResources, $electronic);
	}	
	
	/**
	 * Get all items or item at specified position
	 * 
	 * @param int [optional] item position
	 */
	
	public function getItems($position = null)
	{
		if ( $position === null )
		{
			return $this->items;
		}
		elseif ( array_key_exists($position, $this->items) )
		{
			return $this->items[$position];
		}
		else
		{
			throw new \DomainException("no item at position '$position'");
		}
	}

	/**
	 * Get all holdings
	 */
	
	public function getHoldingRecords()
	{
		return $this->holdings;
	}	
	
	/**
	 * Get all electronic resources
	 */
	
	public function getElectronicResources()
	{
		return $this->electronicResources;
	}
	
	/**
	 * The number of items
	 */
	
	public function length()
	{
		return count($this->items);
	}
}
