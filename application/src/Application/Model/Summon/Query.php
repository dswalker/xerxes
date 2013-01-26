<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Summon;

use Application\Model\Search;

/**
 * Summon Search Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * Convert to Summon query syntax
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
			$query .= " " . $term->boolean;

			// is this a fielded search?
			
			if ( $term->field_internal != "" ) // yes
			{
				$query .= " " . $term->field_internal . ':' . $this->escape($term->phrase);

			}
			else // keyword
			{
				$query .= " " . $term->phrase;
			}
		}
		
		return trim($query);
	}
	
	/**
	 * Escape reserved characters
	 * 
	 * @param string $string
	 */
	
	protected function escape($string)
	{
		$chars = str_split(',:\()${}');
		
		foreach ( $chars as $char )
		{
			$string = str_replace($char, "", $string);
		}
		
		return $string;
	}
}
