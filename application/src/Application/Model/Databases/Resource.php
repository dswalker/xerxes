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

use Xerxes\Utility\DataValue;

/**
 * Resource
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Resource extends DataValue  
{
	public $resource_id;
	public $title_full;
	public $title_display;
	public $link;
	public $description;	
	public $active;
	public $language;
	
	public $alternate_titles = array();
	public $notes = array();
	public $keywords = array();
}