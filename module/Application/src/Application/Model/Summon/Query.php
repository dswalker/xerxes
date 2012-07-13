<?php

namespace Application\Model\Summon;

use Application\Model\Search;

/**
 * Summon Search Query
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Query extends Search\Query
{
	/*
	 * Search limited to full-text
	 */
	
	public function isHoldingsOnly()
	{
		if ( $this->request->getParam("holdings") != null )
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
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
