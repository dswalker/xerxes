<?php

namespace Xerxes\Utility;

use Zend\Http\Client;

/**
 * Utility class for basic parsing functions
 * 
 * @author David Walker
 * @copyright 2008 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package  Xerxes_Framework
 */ 

class Factory
{
	public static function getHttpClient()
	{						
		return new Client();
	}
}