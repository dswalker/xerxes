<?php

class Xerxes_Controller_Worldcat extends Xerxes_Controller_Search
{
	protected $id = "worldcat";
	
	protected function getEngine()
	{
		$role = $this->request->getSession("role");
		$source = $this->request->getParam("source");
		
		return new Xerxes_Model_Worldcat_Engine($role, $source);
	}
}
