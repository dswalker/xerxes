<?php

namespace Application\Controller;

use Xerxes\Utility\Labels;

use Zend\Mvc\Controller\ActionController,
	Application\View\Navigation;

class NavigationController extends ActionController
{
	public function labels()
	{
		$lang = $this->request->getParam("lang");
		
		$labels = Labels::getInstance($lang);
		$this->response->add("labels", $labels);
		
		$this->response->setView("scripts/helper/labels.phtml");
	}
}