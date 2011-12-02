<?php

namespace Application\Controller;

use Xerxes\Utility\Registry;

use Application\View\Helper\Search as SearchHelper,
	Application\Model\Search\Result,
	Application\Model\DataMap\SavedRecords,
	Xerxes\Record,
	Xerxes\Utility\Parser,
	Zend\Mvc\Controller\ActionController,
	Zend\Mvc\MvcEvent;

abstract class SearchController extends ActionController
{
	protected $id = "search";
	
	protected $config; // local config
	protected $query; // query object
	protected $engine; // search engine
	protected $max; // default records per page
	protected $max_allowed; // upper-limit per page
	protected $sort; // default sort
	protected $helper; // search display helper
	protected $response_array = array(); // response data
	
	public function execute(MvcEvent $e)
	{
		$this->init($e);
		parent::execute($e);
	}
	
	protected function init(MvcEvent $e)
	{
		$this->engine = $this->getEngine();
		
		$this->config = $this->engine->getConfig();
		
		$this->registry = Registry::getInstance();
		
		$this->data["config_local"] = $this->config->toXML();
		
		$this->query = $this->engine->getQuery($this->request);
		
		$this->helper = new SearchHelper($e, $this->id, $this->engine);
	}
	
	abstract protected function getEngine();
	
	public function searchAction()
	{
		// set the url params for where we are gong to redirect,
		// usually to the results action, but can be overriden
		
		$base = $this->helper->searchRedirectParams();
		$params = $this->query->getAllSearchParams();
		$params = array_merge($base, $params);
		
		// check spelling
		
		if ( $this->request->getParam("spell") != "none" )
		{
			$spelling = $this->query->checkSpelling();
			
			foreach ( $spelling as $key => $correction )
			{
				$params["spelling_$key"] = $correction;
			}
		}
		
		// construct the actual url and redirect

		$url = $this->helper->url_for($params);
		
		$this->redirect()->toUrl($url);
	}
	
	public function hitsAction()
	{
		// create an identifier for this search
		
		$id = $this->helper->getQueryID();
		
		// see if one exists in session already
		
		$total = $this->request->getSession($id);
		
		// nope
		
		if ( $total == null )
		{
			// so do a search (just the hit count) 
			// and cache the hit total
			
			$total = $this->engine->getHits($this->query);
			$total = Parser::number_format($total);
			$this->request->setSession($id, (string) $total);
		}
		
		// and tell the browser too
		
		$this->data["hits"] = $total;
		return $this->data;
	}
	
	public function resultsAction()
	{
		// defaults
		
		$this->max = $this->registry->getConfig("RECORDS_PER_PAGE", false, 10);
		$this->max = $this->config->getConfig("RECORDS_PER_PAGE", false, $this->max);
		
		$this->max_allowed = $this->registry->getConfig("MAX_RECORDS_PER_PAGE", false, 30);
		$this->max_allowed = $this->config->getConfig("MAX_RECORDS_PER_PAGE", false, $this->max_allowed);
		
		$this->sort = $this->registry->getConfig("SORT_ORDER", false, "relevance");
		$this->sort = $this->config->getConfig("SORT_ORDER", false, $this->sort);
		
		// params
		
		$start = $this->request->getParam('start', 1);
		$max = $this->request->getParam('max', $this->max);
		$sort = $this->request->getParam('sort', $this->sort);
		
		// swap for internal
		
		$internal_sort = $this->config->swapForInternalSort($sort);
		
		// make sure records per page does not exceed upper bound
		
		if ( $max > $this->max_allowed )
		{
			$max = $this->max_allowed;
		}
		
		// search
				
		$results = $this->engine->searchRetrieve($this->query, $start, $max, $internal_sort);
		
		// total
		
		$total = $results->getTotal();
		
		// cache it
		
		$id = $this->helper->getQueryID();
		$this->request->setSession($id, (string) $total);
		
		// add links
		
		$this->helper->addRecordLinks($results);
		$this->helper->addFacetLinks($results);
		$this->helper->addQueryLinks($this->query);
		
		// summary, sort & paging elements
		
		$results->summary = $this->helper->summary($total, $start, $max);
		$results->pager = $this->helper->pager($total, $start, $max);
		$results->sort_display = $this->helper->sortDisplay($sort);
		
		// response
		
		$this->data["query"] = $this->query;
		$this->data["results"] = $results;
		
		return $this->data;
	}
	
	public function recordAction()
	{
		$id = $this->request->getParam('id');

		// get the record

		$results = $this->engine->getRecord($id);
		
		// set links
		
		$this->helper->addRecordLinks($results);
		
		// add to response
		
		$this->data["results"] = $results;
		
		return $this->data;
	}
	
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
		
		$this->data["results"] = $result;
		
		return $this->data;
	}	

	public function saveAction()
	{
		$datamap = new SavedRecords();
		
		$username = "testing"; // $this->request->getSession("username"); // TODO: with authentication framework
		$original_id = $this->request->getParam("id");

		$inserted_id = ""; // internal database id
		
		// delete command
		
		if ( $this->isMarkedSaved( $original_id ) == true )
		{
			$datamap->deleteRecordBySource( $username, $this->id, $original_id );
			$this->unmarkSaved( $original_id );
			$this->data["delete"] = "1";
		}

		// add command
		
		else
		{
			// get record
			
			$record = $this->engine->getRecord($original_id)->getRecord(0)->getXerxesRecord();
			
			// save it
			
			$inserted_id = $datamap->addRecord( $username, $this->id, $original_id, $record );
			
			// record this in session
				
			$this->markSaved( $original_id, $inserted_id );
			
			$this->data["savedRecordID"] = $inserted_id;
		} 
		
		return $this->data;
	}	
	
	
	########################
	#  SAVED RECORD STATE  #
	########################	
	
	
	/**
	 * Store in session the fact this record is saved
	 *
	 * @param string $original_id		original id of the record
	 * @param string $saved_id		the internal id in the database
	 */ 
	
	protected function markSaved( $original_id, $saved_id )
	{
		$_SESSION['resultsSaved'][$original_id]['xerxes_record_id'] = $saved_id;
	}

	/**
	 * Delete from session the fact this record is saved
	 *
	 * @param string $original_id		original id of the record
	 */ 
	
	protected function unmarkSaved( $original_id )
	{
		if ( array_key_exists( "resultsSaved", $_SESSION ) && array_key_exists( $original_id, $_SESSION["resultsSaved"] ) )
		{
			unset( $_SESSION['resultsSaved'][$original_id] );
		}
	}

	/**
	 * Determine whether this record is already saved in session
	 *
	 * @param string $original_id		original id of the record
	 */ 
	
	protected function isMarkedSaved($original_id)
	{
		if ( array_key_exists( "resultsSaved", $_SESSION ) && array_key_exists( $original_id, $_SESSION["resultsSaved"] ) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the number of records saved in session
	 */ 
	
	protected function numMarkedSaved()
	{
		$num = 0;
		
		if ( array_key_exists( "resultsSaved", $_SESSION ) )
		{
			$num = count( $_SESSION["resultsSaved"] );
		}
		
		return $num;
	}
}
