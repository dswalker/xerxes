<?php

namespace Xerxes\Marc;

/**
 * MARC DatafieldList
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: DataFieldList.php 2045 2011-11-28 14:17:37Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class DataFieldList extends FieldList 
{
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