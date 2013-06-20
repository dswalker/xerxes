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
		return $this->resultsAction();
	}
	
	public function resultsAction()
	{
		$engine = $this->request->getParam('engine', 'solr');
		$alias = $this->controller_map->getUrlAlias($engine);
		
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
		
		// construct more results url
		
		$params = array(
			'controller' => $alias,
			'action' => 'search',
			'query' => $this->request->getParam('query'),
			'field' => $this->request->getParam('field')
		);
		
		$this->response->setVariable('url_more', $this->request->url_for($params));
		
		// switch to combined view
		
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
