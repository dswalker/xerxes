<?php

namespace Application\Model\Databases;

use Xerxes\Utility\User,
	Xerxes\Utility\DataValue,
	Xerxes\Utility\Parser,
	Xerxes\Utiltity\Restrict;

/**
 * Database
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @version
 * @package Xerxes
 */

class Database extends DataValue  
{
	public $database_id;
	public $title_full;
	public $title_display;
	public $subscription;
	public $proxy;
	public $active;
	public $creator;
	public $publisher;
	public $description;
	public $coverage;
	public $language;
	public $link;
	public $link_guide;
	public $search_hints;
	
	public $alternate_titles = array();
	public $notes = array();
	public $keywords = array();
}