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
	public $url;
	private $terms = array();
	private $display;
	
	/**
	 * Add a Query Term
	 * 
	 * @param Search\QueryTerm $term
	 */
	
	public function addTerm( Search\QueryTerm $term )
	{
		$this->terms[] = $term;
		$this->display .= " " . $term->phrase;
		$this->display = trim($this->display);
	}
	
	/**
	 * Does this suggestion have any QueryTerms
	 */
	
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
	
	/**
	 * Get Query term by index
	 * 
	 * @param int $index
	 */
	
	public function getTerm($index)
	{
		if ( array_key_exists($index, $this->terms) )
		{
			return $this->terms[$index];
		}
		else
		{
			return new Search\QueryTerm();
		}
	}
	
	/**
	 * Serialize to array
	 */
	
	public function toArray()
	{
		$final = array('url' => $this->url);
		
		foreach ( $this->terms as $term )
		{
			$final = array_merge($final, $term->toArray() );
		}
		
		return $final;
	}
}
