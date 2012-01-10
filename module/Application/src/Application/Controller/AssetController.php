<?php

namespace Application\Controller;

use Zend\Mvc\Controller\ActionController,
	Xerxes\Utility\Labels;

class AssetController extends ActionController
{
	public function labelsAction()
	{
		$lang = $this->request->getParam("lang");
		
		$labels = $this->getLocator()->get('labels');
		$labels->setLanguage($lang);
		
		return array("labels" => $labels);
	}
}