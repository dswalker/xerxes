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

use Application\Model\Search\Query;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Xerxes\Mvc\ActionController;
use Xerxes\Mvc\Response;

class CombinedController extends ActionController
{
	/**
	 * Search engine id
	 * @var string
	 */
	
	protected $id = 'combined';
	
	/**
	 * Search home page
	 */
	
	public function indexAction()
	{
		$this->response->setView('search/index.xsl');
	}
	
	/**
	 * Search redirect
	 */
	
	public function searchAction()
	{
		$params = array(
			'controller' => $this->id,
			'action' => 'results',
			'query' => $this->request->getParam('query'),
		);
		
		return $this->redirectTo($params);
	}
	
	/**
	 * The 'main' page of results
	 */
	
	public function resultsAction()
	{
		$this->response = $this->getResults();
	
		return $this->response;
	}	
	
	/**
	 * Only show the results themselves
	 */
	
	public function partialAction()
	{
		$this->response = $this->getResults();
		$this->response->setView('combined/partial.xsl');
	
		return $this->response;
	}
	
	/**
	 * Search results action
	 * 
	 * @return Response
	 */
	
	public function getResults()
	{
		$default_engine = $this->registry->getConfig('COMBINED_DEFAULT_CONTROLLER', false, 'solr');
		$engine = $this->request->getParam('engine', $default_engine);
		$alias = $this->controller_map->getUrlAlias($engine);
		
		// to help the views
		
		$this->response->setVariable('combined_engine', $engine);
		
		// for breadcrumbs and such
		
		$query = new Query($this->request);
		
		$this->response->setVariable('combined_query_params', $this->getSearchQuery($query) );
		$this->request->setSessionData( $this->id . '_' . $query->getHash(), true );
		
		// default to three results, unless specified for all engines or just specific ones
		
		$max = $this->registry->getConfig('COMBINED_MAX_RESULTS', false, 3);
		$max = $this->registry->getConfig("COMBINED_MAX_RESULTS_$engine", false, $max);
		
		// these so the search engine controller thinks it's not a 'combined' request
		
		$this->request->replaceParam('max', $max);
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
		
		$params = $query->getAllSearchParams();
		$params['controller'] = $alias;
		$params['action'] = 'search';
		
		// not authenticated (controller is redirecting to authentication)
		// so tell the user they need to login to see results
		
		if ( $this->response instanceof RedirectResponse )
		{
			$this->response = $this->event->getNewResponse();
			$this->response->setVariable('login_message', 'Login for results');
			$this->response->setVariable('url_more', $this->request->url_for($params));
		}
		else
		{
			$this->response->setVariable('url_more', $this->request->url_for($params));
			
			// make sure the spell check sends us back through combined 
			
			$spelling = $this->response->getVariable('spelling');
			
			if ( $spelling != "")
			{
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
			}
		}
		
		// view
		
		$this->response->setView('combined/results.xsl');
		
		return $this->response;
	}
	
	/**
	 * Search and link 
	 */
	
	public function linkAction()
	{
		$id = $this->request->getParam('id');
		$query = $this->request->getParam('query');
		
		$xml = $this->registry->getXML();
		
		$link = $xml->xpath("//search_and_link[@id='$id']/@url");
		
		if ( count($link) == 1 )
		{
			$url = (string) $link[0];
			$url = str_replace('{query}', $query, $url);
			
			return $this->redirectTo($url);
		}
		else
		{
			throw new \Exception("Could not find search and link option for '$id'");
		}
	}
	
	/**
	 * Extract the search params as a url
	 * @param Query $query
	 */
	
	protected function getSearchQuery(Query $query)
	{
		$url = '';
		
		$params = $query->getAllSearchParams();
		
		foreach ( $params as $key => $value )
		{
			$url .= ';' . $key . '=' . urlencode($value);
		}
		
		return $url;
	}
}
