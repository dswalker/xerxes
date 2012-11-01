<?php

namespace Application\Model\Search;

use Xerxes\Utility\Parser;

/**
 * Search Item
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Item
{
	public $bib_id; // the bibliographic record ID
    public $availability; // boolean: is this item available for checkout?
    public $status; // string describing the status of the item
    public $location; // string describing the physical location of the item
    public $reserve; // string indicating “on reserve” status – legal values: 'Y' or 'N'
    public $callnumber; // the call number of this item
    public $duedate; // string showing due date of checked out item (null if not checked out)
    public $number; 	// the copy number for this item (note: although called “number”, 
    					// this may actually be a string if individual items are named rather than numbered)
    public $barcode; // the barcode number for this item
    public $request_url; // automated storage or hold request
	
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
