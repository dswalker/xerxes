<?php

namespace Application\Model\Ebsco;

use Application\Model\Search;

/**
 * Ebsco Search Query
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
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
