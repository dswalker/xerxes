<?php

namespace Application\Model\Metalib;

use Application\Model\Search,
	Xerxes\Utility\Registry,
	Xerxes\Utility\Request,
	Zend\Http\Client;

/**
 * Metalib Search Engine
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Engine extends Search\Engine
{
	/**
	 * Create Metalib Search Engine
	 */
	
	public function __construct()
	{
	}
	
	/**
	 * Return the search engine config
	 *
	 * @return Config
	 */
	
	public function getConfig()
	{
		return Config::getInstance();
	}

	/**
	 * Initiate the search
	 * 
	 * @param Search\Query $search
	 */
	
	public function search(Search\Query $search )
	{
		
	}
	
	/**
	 * Return a search query object
	 * 
	 * @return Query
	 */	
	
	public function getQuery(Request $request )
	{
		if ( $this->query instanceof Query )
		{
			return $this->query;
		}
		else
		{
			return new Query($request, $this->getConfig());
		}
	}
}
