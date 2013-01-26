<?php

namespace Application\Model\Summon;

use Xerxes;
use Xerxes\Record\Format;
use Xerxes\Utility\Parser;

// @todo get rid of this for new kb model 

class Resource
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