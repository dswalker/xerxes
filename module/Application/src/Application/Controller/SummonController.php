<?php

class Xerxes_Controller_Summon extends Xerxes_Controller_Search
{
	protected $id = "summon";
	
	protected function getEngine()
	{
		return new Xerxes_Model_Summon_Engine();
	}
}
