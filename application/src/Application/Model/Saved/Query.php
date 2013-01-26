<?php

namespace Application\Model\Saved;

use Application\Model\Search;
use Xerxes\Mvc\Request;

/**
 * Saved Records Query
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Query extends Search\Query
{
	/**
	 * Create a Saved Record Query
	 * 
	 * @param Request $request
	 * @param Config $config
	 */
	
	public function __construct(Request $request = null, Config $config = null )
	{
		$final = parent::__construct($request, $config);
		
		// add the username as the first query term
		
		$term = new Search\QueryTerm();
		$term->phrase = $this->request->getSessionData('username');
		
		array_unshift($this->terms, $term);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Application\Model\Search.Query::checkSpelling()
	 */
	
	public function checkSpelling()
	{
		return null;
	}
}
