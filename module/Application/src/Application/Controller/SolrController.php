<?php

class Xerxes_Controller_Solr extends Xerxes_Controller_Search
{
	protected $id = "solr";
	
	protected function getEngine()
	{
		return new Xerxes_Model_Solr_Engine();
	}
}
