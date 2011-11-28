<?php

class Xerxes_Controller_Navigation extends Xerxes_Framework_Controller
{
	public function labels()
	{
		$lang = $this->request->getParam("lang");
		
		$labels = Xerxes_Framework_Labels::getInstance($lang);
		$this->response->add("labels", $labels);
		
		$this->response->setView("scripts/helper/labels.phtml");
	}
	
	public function navbar()
	{
		$helper = new Xerxes_View_Helper_Navigation();
		
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
