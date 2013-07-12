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

use Application\Model\Solr\Config;
use Application\Model\Search\Query;
use Xerxes\Google\Appliance;
use Xerxes\Mvc\ActionController;

class CombinedController extends ActionController
{
	protected $id = 'combined';
	
	public function indexAction()
	{
		$this->response->setView('search/index.xsl');
	}
	
	public function searchAction()
	{
		$params = array(
			'controller' => $this->id,
			'action' => 'results',
			'query' => $this->request->getParam('query'),
		);
		
		return $this->redirectTo($params);
	}
	
	public function resultsAction()
	{
		$engine = $this->request->getParam('engine', 'solr');
		$alias = $this->controller_map->getUrlAlias($engine);
		
		// to help the views
		
		$this->response->setVariable('combined_engine', $engine);
		
		// for breadcrumbs and such
		
		$query = new Query($this->request);
		$this->request->setSessionData('combined_' . $query->getHash(), $this->request->getRequestUri());
		
		// these so the search engine controller thinks it's not a 'combined' request
		
		$this->request->replaceParam('max', 3);
		$this->request->replaceParam('controller', $alias);
		$this->request->setParam('include_facets', false);
		
		// if it exists, fire away!
		
		$class_name = 'Application\\Controller\\' . ucfirst($engine) . 'Controller';
		
		if ( class_exists($class_name) )
		{
			$controller = new $class_name($this->event);
			$this->response = $controller->execute('results');
		}
		
		// set controller back
		
		$this->request->replaceParam('controller', $this->id);
		
		// construct more results url
		
		$params = array(
			'controller' => $alias,
			'action' => 'search',
			'query' => $this->request->getParam('query'),
			'field' => $this->request->getParam('field')
		);
		
		$this->response->setVariable('url_more', $this->request->url_for($params));
		
		// make sure the spell check sends us back through combined 
		
		$spelling = $this->response->getVariable('spelling');
		
		if ( $spelling->url != "" )
		{
			$params = array(
				'controller' => $this->id,
				'action' => 'search',
				'query' => $spelling->getTerm(0)->phrase,
			);
			
			$spelling->url = $this->request->url_for($params);
			
			$this->response->setVariable('spelling', $spelling);
		}
		
		// view
		
		$this->response->setView('combined/results.xsl');
		
		return $this->response;
	}
	
	public function partialAction()
	{
		$this->response = $this->resultsAction();
		$this->response->setView('combined/partial.xsl');
	
		return $this->response;
	}	
}
