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

use Application\Model\Primo\Engine;

class PrimoController extends SearchController
{
	protected $id = "primo";
	
	protected function getEngine()
	{
		return new Engine();
	}
}
