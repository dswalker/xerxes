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

use Application\View\Helper\Navigation;
use Xerxes\Mvc\ActionController;

class NavigationController extends ActionController
{
	public function menuAction()
	{
		$nav = new Navigation($this->event);
		$this->response->setVariable("navbar", $nav->getNavbar());
	}
}
