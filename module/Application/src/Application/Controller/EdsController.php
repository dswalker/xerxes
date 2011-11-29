<?php

namespace Application\Controller;

use Application\Model\Ebsco,
	Application\Model\Search;

class EDS_Config extends Search\Config
{
	protected $config_file = "config/eds";
	private static $instance; // singleton pattern
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new EDS_Config();
			$object = self::$instance;
			$object->init();
		}
		
		return self::$instance;
	}
}

class EDS_Engine extends Ebsco\Engine 
{
	public function getConfig()
	{
		return EDS_Engine::getInstance();
	}
}

class EdsController extends SearchController
{
	protected $id = "eds";
	
	protected function getEngine()
	{
		return new EDS_Engine();
	}

	public function results()
	{
		parent::results();
		$this->response->setView("xsl/ebsco/ebsco_results.xsl");
	}

	public function record()
	{
		parent::record();
		$this->response->setView("xsl/ebsco/ebsco_record.xsl");
	}
}
