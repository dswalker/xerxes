<?php

namespace Application\Controller;

use Application\Model\Ebsco\Engine;

class EbscoController extends SearchController
{
	protected $id = "ebsco";
	
	protected function getEngine()
	{
		return new Engine();
	}
}
