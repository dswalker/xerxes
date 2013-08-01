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

use Application\Model\Innovative\Link;
use Application\Model\Search\LinkInterface;
use Application\Model\Search\Query;
use Xerxes\Mvc\ActionController;
use Xerxes\Utility\Parser;

/**
 * Controller for search-and-link search engines
 *
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class LinkController extends ActionController
{
	/**
	 * @var LinkInterface
	 */
	
	protected $catalog;
	
	protected function init()
	{
		$this->catalog = $this->getEngine();
		
		$this->query = new Query( $this->request );
	}
	
	public function hitsAction()
	{
		$total  = $this->catalog->getTotal($this->query);
		
		// format it
		
		$total = Parser::number_format($total);
		
		// and tell the browser too
		
		$this->response->setVariable('hits', $total);
		
		// view template
		
		$this->response->setView('search/hits.xsl');
	
		return $this->response;
	}
	
	public function resultsAction()
	{
		$url  = $this->catalog->getUrl($this->query);
	
		return $this->redirectTo($url);
	}
	
	abstract protected function getEngine();
}
