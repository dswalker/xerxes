<?php

namespace Application\View\Helper;

class Worldcat extends Search
{
	public function getQueryID()
	{
		$source = $this->request->getParam('source');
		
		return $this->id . '_' . $source . '_' . $this->query->getHash();
	}	
}