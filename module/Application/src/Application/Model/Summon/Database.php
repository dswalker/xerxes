<?php

namespace Application\Model\Summon;

use Xerxes,
	Xerxes\Record\Format,
	Xerxes\Utility\Parser;

/**
 * Summon Database recommendation
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Database
{
	public $title;
	public $description;
	public $link;
	
	public function __construct(array $database_array)
	{
		$this->title = $database_array['title'];
		$this->description = $database_array['description'];
		$this->link = $database_array['link'];
	}
}