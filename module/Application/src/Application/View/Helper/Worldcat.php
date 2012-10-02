<?php

namespace Application\View\Helper;

use Xerxes\Record;

class Worldcat extends Search
{
	/**
	 * Parameters to construct the url on the search redirect
	 * Accounts for worldcat 'source' identifier
	 * @return array
	 */
	
	public function searchRedirectParams()
	{
		$params = parent::searchRedirectParams();
		$params['source'] = $this->request->getParam('source');
	
		return $params;
	}	
	
	/**
	 * Query ID
	 * Accounts for worldcat 'source' identifier
	 * 
	 * @return string
	 */
	
	public function getQueryID()
	{
		$source = $this->request->getParam('source');
		
		return $this->id . '_' . $source . '_' . $this->query->getHash();
	}
	
	/**
	 * URL for the full record display
	 * Accounts for worldcat 'source' identifier
	 *
	 * @param $result Record object
	 * @return string
	 */
	
	public function linkFullRecord( Record $result )
	{
		$arrParams = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'record',
			'source' =>  $this->request->getParam('source'),
			'id' => $result->getRecordID()
		);
	
		return $this->request->url_for($arrParams);
	}
	
	/**
	 * lateral link base
	 * Accounts for worldcat 'source' identifier
	 * 
	 * @return array
	 */
	
	public function lateralLink()
	{
		return array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'search',
			'source' => $this->request->getParam('source')
		);
	}
	
}