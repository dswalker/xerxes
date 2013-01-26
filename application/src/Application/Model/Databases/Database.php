<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Databases;

/**
 * Database
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Database extends Resource  
{
	public $subscription;
	public $proxy;
	public $creator;
	public $publisher;
	public $coverage;
	public $link_guide;
	public $search_hints;
}