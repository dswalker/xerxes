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

use Xerxes\Utility\Parser;

/**
 * Search Item
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Item
{
	/**
	 * The bibliographic record ID
	 * @var string
	 */
	
    public $bib_id;
    
    /**
     * Is this item available for checkout?
     * @var bool
     */
    
    public $availability;
    
    /**
     * Status of the item
     * @var string
     */
    
    public $status;
    
    /**
     * Physical location of the item
     * @var string
     */
    
    public $location;
    
    /**
     * "on reserve" status
     * @var string
     */
    
    public $reserve;
    
    /**
     * the call number of this item
     * @var string
     */
    
    public $callnumber;
    
    /**
     * Due date of checked out item (null if not checked out)
     * @var string
     */
    
    public $duedate;
    
    /**
     * The copy number for this item
     * 
     * Although called 'number', this may actually be a string if individual items are named rather than numbered
     * @var string
     */
    
    public $number;
    
    /**
     * The barcode number for this item
     * @var string
     */
    
    public $barcode;
    
    /**
     * Automated storage or hold request URL
     * @var string
     */
    
    public $request_url;
	
	/**
	 * Serialize to Array
	 * 
	 * @return array
	 */
	
	public function toArray()
	{
		$array = array();
		
		foreach ( $this as $key => $value )
		{
			if ( $value == "")
			{
				continue;
			}
			
			$array[$key] = $value;
		}
		
		return $array;
	}
}
