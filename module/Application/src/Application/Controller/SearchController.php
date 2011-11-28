<?php

abstract class Xerxes_Controller_Search extends Xerxes_Framework_Controller
{
	protected $id = "search";
	
	protected $config; // local config
	protected $query; // query object
	protected $engine; // search engine
	protected $max; // default records per page
	protected $max_allowed; // upper-limit per page
	protected $sort; // default sort
	
	protected $helper; // search display helper
	
	protected function init()
	{
		$this->engine = $this->getEngine();
		
		$this->config = $this->engine->getConfig();
		
		$this->response->add("config_local", $this->config->toXML());
		
		$this->query = $this->engine->getQuery($this->request);
		
		$this->helper = new Xerxes_View_Helper_Search($this->id, $this->engine);
	}
	
	abstract protected function getEngine();
	
	public function index()
	{
		$this->response->setView("xsl/search/index.xsl");
	}
	
	public function search()
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

		$url = $this->request->url_for($params);
		$this->response->setRedirect($url);
	}
	
	public function hits()
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
			$total = Xerxes_Framework_Parser::number_format($total);
			$this->request->setSession($id, (string) $total);
		}
		
		// and tell the browser too
		
		$this->response->add("hits", $total);
		$this->response->setView("xsl/search/hits.xsl");
	}
	
	public function results()
	{
		// defaults
		
		$this->max = $this->registry->getConfig("RECORDS_PER_PAGE", false, 10);
		$this->max = $this->config->getConfig("RECORDS_PER_PAGE", false, $this->max);
		
		$this->max_allowed = $this->registry->getConfig("MAX_RECORDS_PER_PAGE", false, 30);
		$this->max_allowed = $this->config->getConfig("MAX_RECORDS_PER_PAGE", false, $this->max_allowed);
		
		$this->sort = $this->registry->getConfig("SORT_ORDER", false, "relevance");
		$this->sort = $this->config->getConfig("SORT_ORDER", false, $this->sort);
		
		// params
		
		$start = $this->request->getParam('start', false, 1);
		$max = $this->request->getParam('max', false, $this->max);
		$sort = $this->request->getParam('sort', false, $this->sort);
		
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
		
		$this->response->add("query", $this->query);
		$this->response->add("results", $results);
		
		// set view
		
		$this->response->setView("xsl/" . $this->id . "/results.xsl");
	}
	
	public function record()
	{
		$id = $this->request->getParam('id');

		// get the record

		$results = $this->engine->getRecord($id);
		
		// set links
		
		$this->helper->addRecordLinks($results);
		
		// add to response
		
		$this->response->add("results", $results);

		// set view
		
		$this->response->setView("xsl/" . $this->id . "/record.xsl");	
	}
	
	public function lookup()
	{
		$id = $this->request->getParam("id");
		
		// we essentially create a mock object and add holdings
		
		$xerxes_record = new Xerxes_Record();
		$xerxes_record->setRecordID($id);
		$xerxes_record->setSource($this->id);
		
		$result = new Xerxes_Model_Search_Result($xerxes_record, $this->config);
		$result->fetchHoldings();
		
		// add to response
		
		$this->response->add("results", $result);
		
		// set view
		
		$this->response->setView('xsl/search/lookup.xsl');
	}	

	public function save()
	{
		$datamap = new Xerxes_Model_DataMap_SavedRecords();
		
		$username = "testing"; // $this->request->getSession("username"); // TODO: with authentication framework
		$original_id = $this->request->getParam("id");

		$inserted_id = ""; // internal database id
		
		// delete command
		
		if ( $this->isMarkedSaved( $original_id ) == true )
		{
			$datamap->deleteRecordBySource( $username, $this->id, $original_id );
			$this->unmarkSaved( $original_id );
			$this->response->add("delete", "1");
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
			
			$this->response->add("savedRecordID", $inserted_id);
		} 
		
		// view
		
		$this->response->setView('xsl/search/save.xsl');
		$this->response->setView('xsl/search/save-ajax.xsl', 'json');
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
