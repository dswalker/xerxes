<?php

namespace Application\Controller;

use Application\Model\Search\Availability\Innopac\Config;
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
	
	public function requestAction()
	{
		$id = $this->request->getParam("id");
		
		$config = Config::getInstance();
		
		$server = $config->getConfig('SERVER', true);
		$server = rtrim($server, '/');
	
		$url = "$server/record=$id";
		
		$html = file_get_contents($url);
	
		$arrMatch = array();
		$redirect = "";
	
		$pattern = '/href="(.*asrsreq[^"]*)"/';
	
		if ( preg_match_all($pattern, $html, $arrMatch) != 1 )
		{
			return $this->redirect()->toUrl($url);
		}
		elseif ( preg_match($pattern, $html, $arrMatch) )
		{
			$redirect = $server . $arrMatch[1];
			return $this->redirect()->toUrl($redirect);
		}
	}
}
