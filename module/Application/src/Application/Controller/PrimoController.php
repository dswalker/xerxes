<?php

class Xerxes_Controller_Primo extends Xerxes_Controller_Search
{
	protected $id = "primo";
	
	protected function getEngine()
	{
		return new Xerxes_Model_Primo_Engine();
	}
}
