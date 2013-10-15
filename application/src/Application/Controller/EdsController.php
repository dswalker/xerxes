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

use Application\Model\Ebsco\Discovery\Engine;

class EdsController extends SearchController 
{
	protected $id = "eds";
	
	protected function getEngine()
	{
		$session_id = $this->request->getSessionData('ebsco_session');
		
		$engine = new Engine($session_id);
		
		$this->request->setSessionData('ebsco_session', $session_id);
		
		return $engine;
	}
}
