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

use Application\Model\Solr\Engine;

use Application\Model\Innovative\Link;

class InnovativeController extends LinkController
{
	protected $server;
	
	protected function getEngine()
	{
		$this->server = $this->registry->getConfig('INNREACH_HOST', false, 'csul.iii.com');
		
		return new Link($this->server);
	}
	
	public function recordAction()
	{
		$library = $this->request->getParam("library");
		$id = $this->request->getParam("id");
		
		$solr = new Engine();
		$results = $solr->getRecord($id);
		
		if ( $results == null )
		{
			throw new \Exception('Could not fetch record');
		}
		
		$title = urlencode($results->getRecord(0)->getXerxesRecord()->getTitle());
		
		$url = 'http://' . $this->server . "/search/z?9$library+$id&title=$title";
		
		return $this->redirectTo($url);
	}
}
