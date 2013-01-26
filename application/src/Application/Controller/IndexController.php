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

class IndexController extends ActionController
{
	public function indexAction()
	{
		// get the default controller configured in map
		
		$default_controller = $this->controller_map->getDefaultController();
		
		// update the request
		
		$this->request->replaceParam('controller', $default_controller);
		
		// invoke the controller
		
		$controller = $this->controller_map->getControllerObject($default_controller, $this->event);
		
		return $controller->execute('index');
	}
}
