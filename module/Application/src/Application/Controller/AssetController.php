<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;

use Zend\Mvc\Controller\ActionController,
	Xerxes\Utility\Labels;

class AssetController extends ActionController
{
	public function labelsAction()
	{
		$lang = $this->request->getParam("lang");
		
		$labels = $this->locator->get('labels');
		$labels->setLanguage($lang);
		
		return array("labels" => $labels);
	}
	
	public function testAction()
	{
		$test = array('hello' => 'world');
		$json = json_encode($test);
		
		header("Content-type: application/json"); echo $json; exit;
		
		$this->request->setParam("format", "json");
		
		$model = new ViewModel();
		$model->setVariable('json', $json);
		
		$this->request->getControllerMap()->setNoView();
		
		return $model;
	}
}