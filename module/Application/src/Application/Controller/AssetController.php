<?php

namespace Application\Controller;

use Zend\Mvc\Controller\ActionController,
	Xerxes\Utility\Labels;

class AssetController extends ActionController
{
	public function labelsAction()
	{
		$lang = $this->request->getParam("lang");
		
		$labels = Labels::getInstance($lang);
		
		return array("labels" => $labels);
	}
}