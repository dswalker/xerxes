<?php

namespace Application\Controller;

use Zend\Mvc\Controller\ActionController;

class LinkplusController extends SolrController
{
	public function recordAction()
	{
		$model = parent::recordAction();
		
		$results = $model->results;
		
		$record = $results->getRecord(0);
		
		if ( $record == null )
		{
			throw new \Exception('Could not fetch record');
		}
		
		$library = $this->request->getParam("library");
		$id = $this->request->getParam("id");
		
		$title = urlencode($record->getXerxesRecord()->getTitle());
		
		$url = "http://csul.iii.com/search/z?9$library+$id&title=$title";
		
		return $this->redirect()->toUrl($url);
	}
	
	public function resultsAction()
	{
		$field = $this->request->getParam("field");
		$query = $this->request->getParam("query");
		
		$index = "X";
		
		switch($field)
		{
			case "title": $index = "t"; break;
			case "subject": $index = "d"; break;
			case "author": $index = "a"; break;
		}
		
		
		$url = "http://csul.iii.com/search/$index?SEARCH=" . urlencode($query);
		
		return $this->redirect()->toUrl($url);		
	}
}
