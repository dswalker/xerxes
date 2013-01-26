<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

/**
 * Search Facet
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Facet
{
	public $name;
	public $count;
	public $url;
	public $key;
	public $is_excluded;
	public $is_date;
}
