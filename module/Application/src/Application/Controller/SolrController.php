<?php

namespace Application\Controller;

use Application\Model\Solr\Engine;

class SolrController extends SearchController
{
	protected $id = "solr";
	
	protected function getEngine()
	{
		return new Engine();
	}
}
