<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Utility;

/**
 * Utility class
 * 
 * @author David Walker <dwalker@calstate.edu>
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