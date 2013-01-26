<?php

namespace Application\Model\Databases;

use Xerxes\Utility\DataValue;

/**
 * Resource
 *
 * @author David Walker
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