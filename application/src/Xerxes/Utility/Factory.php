<?php

namespace Xerxes\Utility;

/**
 * Utility class
 * 
 * @author David Walker
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