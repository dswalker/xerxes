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

use Application\View\Helper\Navigation;
use Xerxes\Mvc\ActionController;

/**
 * Navigation controller
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class NavigationController extends ActionController
{
	/**
	 * Add the navigation menu to the response
	 */
	
	public function menuAction()
	{
		$nav = new Navigation($this->event);
		$this->response->setVariable("navbar", $nav->getNavbar());
	}
}
