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

use Xerxes\Mvc\ActionController;

class DatabasesController extends ActionController
{
	public function indexAction()
	{
		$this->response->setVariable('hello', 'world');
		
		return $this->response;
	}
}
