<?php

namespace Application\Model\Search\Spelling;

use Application\Model\Summon\Config,
	Xerxes\Summon as SummonClient,
	Xerxes\Utility\Factory,
	Xerxes\Utility\Registry;

/**
 * Summon Spell Checker
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Summon
{
	/**
	 * Check spelling
	 * 
	 * @param array of QueryTerms $query_terms
	 */
	
	public function checkSpelling(array $query_terms)
	{
		$config = Config::getInstance();

		$id = $config->getConfig("SUMMON_ID", false);
		$key = $config->getConfig("SUMMON_KEY", false);
		
		$suggestion = new Suggestion();
		
		if ( $id != null && $key != null )
		{
			$client = new SummonClient($id, $key, Factory::getHttpClient());	
				
			// @todo: see if we can't collapse multiple terms into a single spellcheck query
			
			foreach ( $query_terms as $term_original )
			{
				$term = clone $term_original;
				
				$query = $term->phrase;
				$query = urlencode(trim($query));
			
				$correction = null;
			
				// get spell suggestion
			
				try
				{
					$correction = $client->checkSpelling($query);
						
				}
				catch (\Exception $e)
				{
					throw $e; // @todo: remove after testing
						
					trigger_error('Could not process spelling suggestion: ' . $e->getTraceAsString(), E_USER_WARNING);
				}
			
				// got one
			
				if ( $correction != null )
				{
					$term->phrase = $correction;
					
					$suggestion->addTerm($term);
				}
			}
		}
		
		return $suggestion;
	}
}
