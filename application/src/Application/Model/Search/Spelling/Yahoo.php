<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search\Spelling;

use Xerxes\Utility\Factory;
use Xerxes\Utility\Parser;
use Xerxes\Utility\Registry;
use Guzzle\Http\Client;
use Guzzle\Plugin\Oauth\OauthPlugin;

/**
 * Yahoo BOSS Spell Checker
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Yahoo
{
	/**
	 * Check spelling
	 * 
	 * @param QueryTerms[] $query_terms
	 */
	
	public function checkSpelling(array $query_terms)
	{
		$registry = Registry::getInstance();
		$suggestion = new Suggestion();
		
		$consumer_key = $registry->getConfig('YAHOO_BOSS_CONSUMER_KEY', true);
		$consumer_secret = $registry->getConfig('YAHOO_BOSS_CONSUMER_SECRET', true);
		
		
		$client = new Client('http://yboss.yahooapis.com/');
			
		$oauth = new OauthPlugin(array(
			'consumer_key'    => $consumer_key,
			'consumer_secret' => $consumer_secret
		));
			
		$client->addSubscriber($oauth);			
				
		
		// @todo: see if we can't collapse multiple terms into a single spellcheck query
			
		foreach ( $query_terms as $term )
		{
			$query = $term->phrase;
			$query = urlencode(trim($query));
			
			$correction = null;
		
			// get spell suggestion
		
			try
			{
				$response = $client->get('ysearch/spelling?q=' . urlencode($query) . ' &format=xml')->send();
				
				// process it
					
				$xml = simplexml_load_string($response->getBody());
					
				// echo header("Content-Type: text/xml"); echo $xml->saveXML(); exit;
					
				$suggestions = $xml->xpath('//result/suggestion');
				
				if ( count($suggestions) > 0 )
				{
					$correction = (string) $suggestions[0];
					$correction = urldecode($correction);
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
				$term->phrase = $correction;
				
				$suggestion->addTerm($term);
			}
		}
		
		return $suggestion;
	}
}
