<?php

class Xerxes_Controller_Ebsco extends Xerxes_Controller_Search
{
	protected $id = "ebsco";
	
	protected function getEngine()
	{
		return new Xerxes_Model_Ebsco_Engine();
	}
}
