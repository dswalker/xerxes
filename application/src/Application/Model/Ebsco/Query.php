<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Ebsco;

use Application\Model\Search;

/**
 * Ebsco Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * Convert to Ebsco query syntax
	 * 
	 * not url encoded
	 * 
	 * @return string
	 */
	
	public function toQuery()
	{
		$query = "";
		
		foreach ( $this->getQueryTerms() as $term )
		{
			$term->toLower()
			     ->andAllTerms();
			
			$query .= ' ' . $term->boolean . ' (';

			if ( $term->field_internal != "" )
			{
				$query .= ' ' . $term->field_internal;
			}
			
			$query .= ' ' . $term->phrase;
			
			$query .= ' )';
		}
		
		if ( $this->request->getParam('scholarly') )
		{
			$query = "$query AND PT Academic Journal";
		}
		
		return trim($query);
	}
}
