<?php

namespace Application\Controller;

use Application\Model\Solr\Booklist\Engine;

class BooklistController extends SearchController
{
	protected $id = "booklist";
	
	protected function getEngine()
	{
		return new Engine();
	}
}
