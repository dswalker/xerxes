<?php

namespace Application\Controller;

use Xerxes\Mvc\ActionController,
	Xerxes\Utility\Labels;

class AssetController extends ActionController
{
	public function labelsAction()
	{
		$lang = $this->request->getParam("lang");
		
		$labels = new Labels();
		$labels->setLanguage($lang);
		
		$this->response->setVariable('labels', $labels);
		
		
		$this->response->headers->set('Content-type', 'application/javascript');
		$this->response->setView('asset/labels.phtml');
		
		return $this->response;
	}
}