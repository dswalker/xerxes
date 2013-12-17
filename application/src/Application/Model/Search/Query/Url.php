<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search\Query;

/**
 * Search Query Request
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Url
{
	/**
	 * headers
	 * @var array
	 */
	public $headers = array();
	
	/**
	 * url
	 * @var string
	 */
	public $url;
}
