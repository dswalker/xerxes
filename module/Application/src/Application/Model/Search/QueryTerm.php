<?php

namespace Application\Model\Search;

use Xerxes\Utility\Parser;

/**
 * Search Query Term
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class QueryTerm
{
	public $id;
	public $boolean;
	public $field;
	public $field_internal;
	public $relation;
	public $phrase;
	
	/**
	 * Create Query Term
	 * 
	 * @param string $id				a unique identifier for this term
	 * @param string $boolen			a boolean operator (AND, OR, NOT) that joins this term to the query
	 * @param string $field				field id?
	 * @param string $field_internal	internal field name
	 * @param string $relation			relation operator (=, >, <)  
	 * @param string $phrase			value
	 */
	
	public function __construct($id, $boolean, $field, $field_internal, $relation, $phrase)
	{
		$this->id = $id;
		$this->boolean = $boolean;
		$this->field = $field;
		$this->field_internal = $field_internal;
		$this->relation = $relation;
		$this->phrase = $phrase;		
	}

	/**
	 * Strip out stop words
	 * 
	 * @param string $stop_words			[optional] stop words to remove
	 */

	public function removeStopWords($stop_words = "")
	{
		if ( $stop_words != "" )
		{
			 $stop_words = "a,an,and,are,as,at,be,but,by,for,from,had,have,he,her,his" .
				"in,is,it,not,of,on,or,that,the,this,to,was,which,with,you,&";
		}
		
		$stop_words = explode(',', $stop_words);
		
		$final = "";
			
		$arrTerms = explode ( " ", $this->phrase );
			
		foreach ( $arrTerms as $chunk )
		{
			if ($chunk == "AND" || $chunk == "OR" || $chunk == "NOT")
			{
				$final .= " " . $chunk;
			} 
			else
			{
				$normal = strtolower ( $chunk );
				
				if (! in_array ( $normal, $stop_words ))
				{
					$final .= " " . $chunk;
				}
			}
		}
		
		$this->phrase =  trim($final);
		
		return $this;
	}
	
	/**
	 * Lower-case the phrase
	 */
	
	public function toLower()
	{
		$this->phrase = Parser::strtolower($this->phrase);
		return $this;
	}
	
	/**
	 * Boolean AND all terms in the phrase, while preserving boolean operators
	 * and quoted phrases
	 */	
	
	public function andAllTerms()
	{
		$arrFinal = $this->normalizedArray($this->phrase);
		$this->phrase = implode(" ", $arrFinal);
		return $this;
	}
	
	/**
	 * Return an array of the phrase with all terms AND'd, while 
	 * preserving boolean operators and quoted phrases
	 * 
	 * @return array
	 */
		
	public function normalizedArray($phrase = "")
	{
		if ( $phrase == "" )
		{
			$phrase = $this->phrase;
		}
		
		$bolQuote = false; // flags the start and end of a quoted phrase
		$arrWords = array(); // the query broken into a word array
		$arrFinal = array(); // final array of words
		$strQuote = ""; // quoted phrase
		
		// strip extra spaces
		
		while ( strstr($this->phrase, "  ") )
		{
			$phrase = str_replace("  ", " ", $phrase);
		}
		
		// split words into an array			
		
		$arrWords = explode(" ", $phrase);
		
		// cycle thru each word in the query
		
		for ( $x = 0; $x < count($arrWords); $x ++ )
		{
			if ( $bolQuote == true )
			{
				// we are inside of a quoted phrase
				
				$strQuote .= " " . $arrWords[$x];
				if ( strpos($arrWords[$x], "\"") !== false )
				{
					// the end of a quoted phrase
					
					$bolQuote = false;
					
					if ( $x + 1 < count($arrWords) )
					{
						if ( $arrWords[$x + 1] != "and" && $arrWords[$x + 1] != "or" && $arrWords[$x + 1] != "not" )
						{
							// the next word is not a boolean operator,
							// so AND the current one
							
							array_push($arrFinal, $strQuote);
							array_push($arrFinal, "AND");
						}
						else
						{
							array_push($arrFinal, $strQuote);
						}
					}
					else
					{
						array_push($arrFinal, $strQuote);
					}
					
					$strQuote = "";
				}
			}
			elseif ( $bolQuote == false && strpos($arrWords[$x], "\"") !== false )
			{
				// this is the start of a quoted phrase
				
				$strQuote .= " " . $arrWords[$x];
				$bolQuote = true;
			}
			elseif ( $arrWords[$x] == "and" || $arrWords[$x] == "or" || $arrWords[$x] == "not" )
			{
				// the current word is a boolean operator
				
				array_push($arrFinal, Parser::strtoupper($arrWords[$x]));
			}
			else
			{
				if ( $x + 1 < count($arrWords) )
				{
					if ( $arrWords[$x + 1] != "and" && $arrWords[$x + 1] != "or" && $arrWords[$x + 1] != "not" )
					{
						// the next word is not a boolean operator,
						// so AND the current one
						
						array_push($arrFinal, $arrWords[$x]);
						array_push($arrFinal, "AND");
					}
					else
					{
						array_push($arrFinal, $arrWords[$x]);
					}
				}
				else
				{
					array_push($arrFinal, $arrWords[$x]);
				}
			}
		}
		
		// single quoted phrase
		
		if ( count($arrFinal) == 0 && $strQuote != "" )
		{
			array_push($arrFinal, $strQuote);
		}
		
		return $arrFinal;
	}
	
	/**
	 * Serialize the object to array
	 */
	
	public function toArray()
	{
		$term = array();
		
		$id = $this->id;
		
		if ( $this->boolean != "") $term["boolean$id"] = $this->boolean;
		if ( $this->field != "") $term["field$id"] = $this->field;
		if ( $this->relation != "") $term["relation$id"] = $this->relation;
		if ( $this->phrase != "") $term["query$id"] = $this->phrase;
		
		return $term;
	}
}
