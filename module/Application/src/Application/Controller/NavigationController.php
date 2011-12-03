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
	
	public function navbar()
	{
		$helper = new Navigation();
		
		$navbar = array(
			'accessible_link' => $helper->accessibleLink(),
			'login_link' => $helper->loginLink(),
			'logout_link' => $helper->logoutLink(),
			'my_account_link' => $helper->myAccountLink(),
			'labels_link' => $helper->labelsLink()	
		);
		
		$this->response->add("navbar", $navbar);
	}
}
