<?php

namespace Application\Model\Search\Spelling;

use Application\Model\Search;

/**
 * Spelling Suggestion
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Suggestion
{
	private $terms = array();
	private $display;
	
	public function addTerm( Search\QueryTerm $term )
	{
		$this->terms[] = $term;
		$this->display .= " " . $term->phrase;
		$this->display = trim($this->display);
	}
	
	public function hasSuggestions()
	{
		if ( count($this->terms) > 0 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function toArray()
	{
		$final = array();
		
		foreach ( $this->terms as $term )
		{
			$final = array_merge($final, $term->toArray() );
		}
		
		return $final;
	}
}
