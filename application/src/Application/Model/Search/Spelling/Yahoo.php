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

use Guzzle\Http\Client;
use Guzzle\Plugin\Oauth\OauthPlugin;
use Xerxes\Utility\Registry;

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
		return null;
		
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
			$query = trim($query);
			
			$escaped_query = urlencode(urlencode($query)); // yes, double-escaped
			
			$correction = null;
		
			// get spell suggestion
		
			try
			{
				$response = $client->get('ysearch/spelling?q=' . $escaped_query . ' &format=xml')->send();
				
				// process it
					
				$xml = simplexml_load_string($response->getBody());
					
				// echo header("Content-Type: text/xml"); echo $xml->saveXML(); exit;
					
				$suggestions = $xml->xpath('//result/suggestion');
				
				if ( count($suggestions) > 0 )
				{
					$correction = (string) $suggestions[0];
					$correction = urldecode($correction);
					$correction = htmlspecialchars_decode($correction, ENT_QUOTES);
				}
			}
			catch (\Exception $e)
			{
				trigger_error('Could not process spelling suggestion: ' . $e->getTraceAsString(), E_USER_WARNING);
			}
			
			echo $correction;
		
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
