<?php

namespace Application\Controller;

use Application\Model\Primo\Engine;

class PrimoController extends SearchController
{
	protected $id = "primo";
	
	protected function getEngine()
	{
		return new Engine();
	}
}
