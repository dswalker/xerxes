<?php

namespace Application\Model\Search\Spelling;

use Xerxes\Utility\Factory,
	Xerxes\Utility\Parser,
	Xerxes\Utility\Registry;

/**
 * Bing Spell Checker
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Bing
{
	/**
	 * Check spelling
	 * 
	 * @param array of QueryTerms $query_terms
	 */
	
	public function checkSpelling(array $query_terms)
	{
		$registry = Registry::getInstance();
		$app_id = $registry->getConfig('BING_ID');
		
		$suggestion = new Suggestion();
		
		if ( $app_id != null )
		{
			$client = Factory::getHttpClient();
				
			// @todo: see if we can't collapse multiple terms into a single spellcheck query
			
			foreach ( $query_terms as $term )
			{
				$query = $term->phrase;
				$query = urlencode(trim($query));
			
				$correction = null;
			
				// get spell suggestion
			
				try
				{
					$url = "http://api.search.live.net/xml.aspx?Appid=$app_id&sources=spell&query=$query";
			
					$client->setUri($url);
					$response = $client->send()->getBody();
			
					// process it
						
					$xml = Parser::convertToDOMDocument($response);
						
					// echo header("Content-Type: text/xml"); echo $xml->saveXML(); exit;
						
					$suggestion_node = $xml->getElementsByTagName('Value')->item(0);
						
					if ( $suggestion_node != null )
					{
						$correction = $suggestion_node->nodeValue;
					}
						
				}
				catch (\Exception $e)
				{
					throw $e; // @todo: remove after testing
						
					trigger_error('Could not process spelling suggestion: ' . $e->getTraceAsString(), E_USER_WARNING);
				}
			
				// got one
			
				if ( $correction != null )
				{
					$term->phrase = $suggestion_node->nodeValue;
					
					$suggestion->addTerm($term);
				}
			}
		}
		
		return $suggestion;
	}
}
