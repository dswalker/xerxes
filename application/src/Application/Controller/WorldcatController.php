<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Controller;

use Application\Model\Worldcat\Engine;
use Application\View\Helper\Worldcat as SearchHelper;

class WorldcatController extends SearchController
{
	protected $id = "worldcat";
	
	protected function init()
	{
		parent::init();
	
		$this->helper = new SearchHelper($this->event, $this->id, $this->engine);
	}	
	
	protected function getEngine()
	{
		$role = $this->request->getSessionData("role");
		$source = $this->request->getParam("source");
		
		return new Engine($role, $source);
	}
	
	/**
	 * Search home page
	 */
	
	public function indexAction()
	{
		$this->response = parent::indexAction();
	
		// set view template
	
		$this->response->setView('worldcat/index.xsl');
	
		return $this->response;
	}
}
