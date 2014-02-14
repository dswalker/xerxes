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

use Application\Model\DataMap\Stats;
use Application\Model\Search\Config;
use Application\Model\Search\Engine;
use Application\Model\Search\Facets;
use Application\Model\Search\Query;
use Application\Model\Search\Result;
use Application\Model\Search\Spelling\Suggestion;
use Application\Model\DataMap\SavedRecords;
use Application\View\Helper\Search as SearchHelper;
use Xerxes\Mvc\ActionController;
use Xerxes\Record;
use Xerxes\Utility\Cache;
use Xerxes\Utility\Parser;
use Xerxes\Utility\Registry;

/**
 * Search controller
 * 
 * Defines the basic actions for a search engine
 *
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class SearchController extends ActionController
{
	/**
	 * @var Config
	 */
	protected $config;
	
	/**
	 * @var Query
	 */
	protected $query;
	
	/**
	 * @var Engine
	 */
	protected $engine;
	
	/**
	 * @var Application\View\Helper\Search
	 */
	protected $helper;
	
	/**
	 * inlcude facets
	 * @var bool
	 */
	protected $include_facets = true;	
	
	/**
	 * (non-PHPdoc)
	 * @see Xerxes\Mvc.ActionController::init()
	 */
	
	protected function init()
	{
		// search objects
		
		$this->engine = $this->getEngine();
		
		$this->config = $this->engine->getConfig();
		
		$this->query = $this->engine->getQuery($this->request);
		
		$this->helper = new SearchHelper($this->event, $this->id, $this->engine);
		
		$this->helper->addConfigLabels($this->config);
		
		$this->response->setVariable('config_local', $this->config);

		// disable caching
		
		$this->response->cache = false; // @todo figure out how to cache more
	}
	
	/**
	 * Return the search engine for this system
	 * 
	 * @return Engine
	 */
	
	abstract protected function getEngine();
	
	/**
	 * Search home page
	 */
	
	public function indexAction()
	{
		$this->helper->addQueryLinks($this->query);
		$this->response->setVariable('query', $this->query);
		
		// set view template
		
		$this->response->setView('search/index.xsl');
		
		return $this->response;
	}
	
	/**
	 * Check spelling, reset search refinements and redirect to results
	 * 
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	
	public function searchAction()
	{
		// set the url params for where we are gong to redirect,
		// usually to the results action, but can be overriden
		
		$base = $this->helper->searchRedirectParams();
		$params = $this->query->getAllSearchParams();
		$params = array_merge($base, $params);
		
		// check spelling
		
		$this->checkSpelling();
		
		// remove default values
		
		foreach ( $params as $id => $value )
		{
			if ( strstr($id, 'field') )
			{
				if ( $value == 'keyword')
				{
					unset($params[$id]);
				}
			}
		}
		
		// keep search refinements if user says so
		
		if ( $this->request->getParam('clear-facets') != '' )
		{
			$this->request->setSessionData('clear_facets', $this->request->getParam('clear-facets'));
		}
		
		// redirect
		
		return $this->redirectTo($params);
	}
	
	/**
	 * Return total number of hits for search (usually for ajax)
	 */
	
	public function hitsAction()
	{
		// create an identifier for this search
		
		$id = $this->helper->getQueryID();
		
		// see if one exists in session already
		
		$total = $this->request->getSessionData($id);
		
		// nope
		
		if ( $total == null )
		{
			// so do a search (just the hit count) 
			// and cache the hit total
			
			$total = $this->engine->getHits($this->query);
			$this->request->setSessionData($id, (string) $total);
		}
		
		// format it 
		
		$total = Parser::number_format($total);
		
		// and tell the browser too
		
		$this->response->setVariable('hits', $total);
		
		// view template
		
		$this->response->setView('search/hits.xsl');
		
		return $this->response;
	}
	
	/**
	 * Fetch search results, log it, check spelling (if necessary) 
	 */
	
	public function resultsAction()
	{
		// params
		
		$start = $this->query->start;
		$max = $this->query->max;
		$sort = $this->query->sort;
		$sort_id = $this->query->sort_id;
		$include_facets = $this->request->getParam('include_facets', $this->include_facets);
		
		// keep search refinements, if not set by user already and so configured 
		
		if ( $this->request->getSessionData('clear_facets') == '' && $this->config->getConfig('KEEP_SEARCH_REFINEMENT', false, false) )
		{
			$this->request->setSessionData('clear_facets', 'false');
		}

		// search
		
		$results = $this->engine->searchRetrieve($this->query, $start, $max, $sort, $include_facets);
		
		// total
		
		$total = $results->getTotal();
		
		// display spelling suggestion
		
		if ( $start <= 1 ) // but only on page 1
		{
			$suggestion = $this->checkSpelling();
			
			$this->helper->addSpellingLink($suggestion);
			$this->response->setVariable('spelling', $suggestion);
		}
		
		// track the query
		
		$id = $this->helper->getQueryID();		
		
		if ( $this->request->getSessionData("stat-$id") == "" ) // first time only, please
		{
			// log it
			
			try
			{
				$log = new Stats();
				$log->logSearch($this->id, $this->query, $results);
			}
			catch ( \Exception $e ) // make it a warning so we don't stop the search
			{
				trigger_error('search stats warning: ' . $e->getMessage(), E_USER_WARNING);
			}
			
			// mark we've saved this search log
			
			$this->request->setSessionData("stat-$id", (string) $total);
		}
		
		// include original record
		
		if ( $this->config->getConfig('INCLUDE_ORIGINAL_RECORD_IN_BRIEF_RESULTS', false) )
		{
			foreach ( $results->getRecords() as $record )
			{
				$record->includeOriginalRecord();
			}
		}	
		
		
		// always cache the total based on the last action
			
		$this->request->setSessionData($id, (string) $total);
		
		// add links & labels
		
		$this->helper->addResultsLabels($results);
		$this->helper->addRecordLinks($results);
		$this->helper->addFacetLinks($results);
		$this->helper->addQueryLinks($this->query);
		
		// summary, sort & paging elements
		
		$results->summary = $this->helper->summary($total, $start, $max);
		$results->pager = $this->helper->pager($total, $start, $max);
		$results->sort_display = $this->helper->sortDisplay($sort_id);
		
		// response
		
		$this->response->setVariable('query', $this->query);
		$this->response->setVariable('results',$results);
		
		// view template
		
		$this->response->setView($this->id . '/results.xsl');		
		
		return $this->response;
	}
	
	/**
	 * Individual record
	 */
	
	public function recordAction()
	{
		$id = $this->request->getParam('id');

		// get the record

		$results = $this->engine->getRecord($id);
		
		if ( $this->request->getParam('original') != null || $this->config->getConfig('INCLUDE_ORIGINAL_RECORD', false) )
		{
			$results->getRecord(0)->includeOriginalRecord();
		}
		
		// set lables and links
		
		$this->helper->addResultsLabels($results);
		$this->helper->addRecordLinks($results);
		
		// add to response
		
		$this->response->setVariable('results', $results);
		
		// view template
		
		$this->response->setView($this->id . '/record.xsl');
		
		return $this->response;
	}
	
	/**
	 * Fetch the record (again), create openurl, and redirect
	 * 
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	
	public function openurlAction()
	{
		$id = $this->request->getParam('id');
		
		// get the record
		
		$results = $this->engine->getRecord($id);
		$record = $results->getRecord(0);
		
		if ( $record->url_open == null )
		{
			throw new \Exception("Could not create OpenURL");
		}

		return $this->redirectTo($record->url_open);
	}
	
	/**
	 * Check availability of the item (with ILS)
	 */
	
	public function lookupAction()
	{
		$id = $this->request->getParam("id");
		
		// we essentially create a mock object and add holdings
		
		$xerxes_record = new Record();
		$xerxes_record->setRecordID($id);
		$xerxes_record->setSource($this->id);
		
		$result = new Result($xerxes_record, $this->config);
		$result->fetchHoldings();
		
		// add to response
		
		$this->response->setVariable('results', $result);
		
		// view template
		
		$this->response->setView('search/lookup.xsl');
		
		return $this->response;
	}
	
	/**
	 * Return just the facets
	 */
	
	public function facetAction()
	{
		$this->request->setParam('max', 1);
	
		$model = $this->resultsAction();
	
		$model->setView('search/facet.xsl');
	
		return $model;
	}
	
	/**
	 * Save or delete a record by id
	 */

	public function saveAction()
	{
		$datamap = new SavedRecords();
		
		$username = $this->request->getSessionData("username");
		$original_id = $this->request->getParam("id");

		$inserted_id = ""; // internal database id
		
		// delete command
		
		if ( $this->isMarkedSaved( $original_id ) == true )
		{
			$datamap->deleteRecordBySource( $username, $this->id, $original_id );
			$this->unmarkSaved( $original_id );
			$this->response->setVariable('delete', '1');
		}

		// add command
		
		else
		{
			// get record
			
			$record = $this->engine->getRecordForSave($original_id)->getRecord(0)->getXerxesRecord();
			
			// save it
			
			$inserted_id = $datamap->addRecord( $username, $this->id, $original_id, $record );
			
			// record this in session
				
			$this->markSaved( $original_id, $inserted_id );
			
			// set this for the response
			
			$this->response->setVariable('savedRecordID', $inserted_id);
			$this->response->setVariable('return_url', $this->request->headers->get('referer'));
		} 
		
		// view based on request
		
		if ( $this->request->getParam('format') == 'json')
		{
			$this->response->setView('search/save-ajax.xsl');
		}
		else
		{
			$this->response->setView('search/save.xsl');
		}
		
		return $this->response;
	}
	
	/**
	 * Advanced search screen
	 */
	
	public function advancedAction()
	{
		$this->cache = new Cache();
		
		$id = $this->id . '_facets';
		
		// get a cached copy if we got it
		
		$facets = $this->cache->get($id);
		
		if ( ! $facets instanceof Facets ) // nope
		{
			$facets = $this->engine->getAllFacets();
			$this->cache->set($id, $facets, time() + (7 * 24 * 60 * 60)); // one week cache
		}
		
		$terms_number = count($this->query->getQueryTerms());
		
		// add blank terms to get us to 4 rows
		
		if ( $terms_number < 4 )
		{
			for ( $x = $terms_number + 1; $x <= 4; $x++ )
			{
				$this->query->addTerm($x, null, null, null, null);
			}  
		}
		
		$this->response->setVariable('query', $this->query);
		$this->response->setVariable('limits', $facets);
		
		$this->response->setView('search/advanced.xsl');
		
		return $this->response;
	}

	/**
	 * Check for mispelled terms
	 * 
	 * @param Query $query
	 * @return Suggestion
	 */
	
	protected function checkSpelling()
	{
		// advanced search?  no thanks!
		
		if ( $this->request->getParam('advanced') != null)
		{
			return new Suggestion();
		}

		$id = $this->query->getHash();
			
		// have we checked it already?
		
		$suggestion = $this->request->getSessionData("spelling_$id");
		
		if ( $suggestion == null ) // nope
		{
			$suggestion = $this->query->checkSpelling(); // check it
			
			$this->request->setSessionData("spelling_$id", serialize($suggestion)); // save for later
		}
		else
		{
			$suggestion = unserialize($suggestion); // resurrect it, like shane
		}
		
		return $suggestion;
	}
	
	
	########################
	#  SAVED RECORD STATE  #
	########################
	
	// @todo move these somewhere else!
	
	
	/**
	 * Store in session the fact this record is saved
	 *
	 * @param string $original_id		original id of the record
	 * @param string $saved_id		the internal id in the database
	 */ 
	
	protected function markSaved( $original_id, $saved_id )
	{
		$data = $this->request->getSessionData('resultsSaved');
		
		if ( $data == null )
		{
			$data = array();
		}
		
		$data[$original_id]['xerxes_record_id'] = $saved_id;
		$this->request->setSessionData('resultsSaved', $data);
	}

	/**
	 * Delete from session the fact this record is saved
	 *
	 * @param string $original_id		original id of the record
	 */ 
	
	protected function unmarkSaved( $original_id )
	{
		$results_saved = $this->request->getSessionData('resultsSaved');
		
		if ( is_array($results_saved ) )
		{
			if ( array_key_exists( $original_id, $results_saved ) )
			{
				unset($results_saved[$original_id]);
				$this->request->setSessionData('resultsSaved', $results_saved);
			}
		}
	}

	/**
	 * Determine whether this record is already saved in session
	 *
	 * @param string $original_id		original id of the record
	 */ 
	
	protected function isMarkedSaved($original_id)
	{
		$results_saved = $this->request->getSessionData('resultsSaved');
		
		if ( is_array($results_saved ) )
		{
			if ( array_key_exists( $original_id, $results_saved ) )
			{
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Get the number of records saved in session
	 */ 
	
	protected function numMarkedSaved()
	{
		$num = 0;
		
		$results_saved = $this->request->getSessionData('resultsSaved');
		
		if ( is_array($results_saved ) )
		{
			$num = count($results_saved);
		}
		
		return $num;
	}
}
