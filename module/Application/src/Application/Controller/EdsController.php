<?php

class Xerxes_Model_EDS_Config extends Xerxes_Model_Ebsco_Config
{
	protected $config_file = "config/eds";
	private static $instance; // singleton pattern
	
	public static function getInstance()
	{
		if ( empty( self::$instance ) )
		{
			self::$instance = new Xerxes_Model_EDS_Config();
			$object = self::$instance;
			$object->init();
		}
		
		return self::$instance;
	}
}

class Xerxes_Model_EDS_Engine extends Xerxes_Model_Ebsco_Engine 
{
	public function getConfig()
	{
		return Xerxes_Model_EDS_Config::getInstance();
	}
}

class Xerxes_Controller_Eds extends Xerxes_Controller_Search
{
	protected $id = "eds";
	
	protected function getEngine()
	{
		return new Xerxes_Model_EDS_Engine();
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
