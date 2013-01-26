<?php

namespace Xerxes\Utility;

/**
 * Utility class
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @package  Xerxes_Framework
 */ 

class Factory
{
	/**
	 * @return HttpClient
	 */
	
	public static function getHttpClient()
	{						
		return new HttpClient();
	}
}