<?php

namespace Application\Model\Databases;

use Xerxes\Utility\DataValue;

/**
 * Resource
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @link http://xerxes.calstate.edu
 * @license
 */

class Resource extends DataValue  
{
	public $database_id;
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