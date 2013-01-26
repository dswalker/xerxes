<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Controller;

use Application\Model\Summon\Engine;

class SummonController extends SearchController
{
	protected $id = "summon";
	
	protected function getEngine()
	{
		return new Engine();
	}
	
	public function indexAction()
	{
		$response = parent::indexAction();
		
		// set view template
	
		$response->setView('summon/index.xsl');
	
		return $response;
	}	
}
