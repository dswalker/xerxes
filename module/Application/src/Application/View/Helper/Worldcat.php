<?php

namespace Application\View\Helper;

use Xerxes\Record;

class Worldcat extends Search
{
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
}