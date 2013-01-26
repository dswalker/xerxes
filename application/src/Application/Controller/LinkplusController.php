<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Controller;

use Application\Model\Availability\Innopac\Config;

class LinkplusController extends SolrController
{
	protected $server;
	
	protected function init()
	{
		parent::init();
		
		$this->server = $this->config->getConfig('INNREACH_HOST', false, 'csul.iii.com');
	}
	
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
		
		$url = 'http://' . $this->server . "/search/z?9$library+$id&title=$title";
		
		return $this->redirectTo($url);
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
		
		$url = 'http://' . $this->server . "/search/$index?SEARCH=" . urlencode($query);
		
		return $this->redirectTo($url);		
	}
}
