<?php

namespace Application\Controller;

use Xerxes\Mvc\ActionController,
	Xerxes\Utility\Labels;

class AssetController extends ActionController
{
	public function labelsAction()
	{
		$lang = $this->request->getParam("lang");
		
		$labels = $this->locator->get('labels');
		$labels->setLanguage($lang);
		
		$model = new ViewModel();
		$model->setVariable('labels', $labels);
		$model->setView('asset/labels.phtml');
		
		return $model;
	}
}