<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Marc;

/**
 * MARC DatafieldList
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class DataFieldList extends FieldList 
{
	/**
	 * Retrieve SubField(s) from the first DataField in the List
	 * 
	 * @param string $code		[optional] single subfield code, or multiple subfield codes listed together,
	 * 							empty value returns all subfields
	 * @param bool 				[optional] return fields in the order specified in $code
	 * 
	 * @return SubField|SubFieldList	latter if $code is set to multiple subfields
	 */
	
	public function subfield($code, $specified_order = false) // convenience method
	{
		if ( count($this->list) == 0 )
		{
			return new SubField(); // return empty subfield object
		}
		else
		{
			if ( strlen($code) == 1)
			{
				// only one subfield specified, so as a convenience to caller
				// return the first (and only the first) subfield of the 
				// first (and only the first) datafield  
				
				$subfield = $this->list[0]->subfield($code,$specified_order)->item(0);
				
				if ( $subfield == null )
				{
					return new SubField(); // return empty subfield object
				}
				else
				{
					return $subfield;
				}
			}
			else
			{
				// multiple subfields specified, so return them all, but 
				// again only from the first occurance of the datafield
				
				return $this->list[0]->subfield($code,$specified_order);
			}
		}
	}
}