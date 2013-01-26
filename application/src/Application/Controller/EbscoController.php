<?php

namespace Application\Controller;

use Application\Model\Ebsco\Engine;
use Application\View\Helper\Ebsco as SearchHelper;

class EbscoController extends SearchController
{
	protected $id = "ebsco";

	protected function init()
	{
		parent::init();
		
		$this->helper = new SearchHelper($this->event, $this->id, $this->engine);
	}	
	
	protected function getEngine()
	{
		return new Engine();
	}
}
