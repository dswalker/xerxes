<?php

/**
 * Search Engine
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Engine.php 2020 2011-11-17 14:39:51Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

abstract class Xerxes_Model_Search_Engine
{
	public $id; // identifier of this search engine
	
	protected $url; // url to the search service
	protected $registry; // xerxes application config
	protected $config; // local search engine config
	
	protected $query; // search query
	
	/**
	 * Constructor
	 */
	
	public function __construct()
	{
		// application config
		
		$this->registry = Xerxes_Framework_Registry::getInstance();
		
		// local config
		
		$this->config = $this->getConfig();
		
	}
	
	/**
	 * Return the total number of hits for the search
	 * 
	 * @return int
	 */	
	
	abstract public function getHits( Xerxes_Model_Search_Query $search );
	
	/**
	 * Search and return results
	 * 
	 * @param Xerxes_Model_Search_Query $search		search object
	 * @param int $start							[optional] starting record number
	 * @param int $max								[optional] max records
	 * @param string $sort							[optional] sort order
	 * 
	 * @return Xerxes_Model_Search_Results
	 */	
	
	abstract public function searchRetrieve( Xerxes_Model_Search_Query $search, $start = 1, $max = 10, $sort = "" );
	
	/**
	 * Return an individual record
	 * 
	 * @param string	record identifier
	 * @return Xerxes_Model_Search_Results
	 */
	
	abstract public function getRecord( $id );

	/**
	 * Get record to save
	 * 
	 * @param string	record identifier
	 * @return int		internal saved id
	 */	
	
	abstract public function getRecordForSave( $id );
	
	/**
	 * Return the search engine config
	 * 
	 * @return Xerxes_Model_Search_Config
	 */
	
	abstract public function getConfig();
	
	/**
	 * Return the URL sent ot the web service
	 * 
	 * @return string
	 */
	
	public function getURL()
	{
		return $this->url;
	}
	
	/**
	 * Return a search query object
	 * 
	 * @return Xerxes_Model_Search_Query
	 */	
	
	public function getQuery(Xerxes_Framework_Request $request )
	{
		if ( $this->query instanceof Xerxes_Model_Search_Query )
		{
			return $this->query;
		}
		else
		{
			return new Xerxes_Model_Search_Query($request, $this->getConfig());
		}
	}
}
