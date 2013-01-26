<?php

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
