<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\View\Helper;

use Xerxes\Record;

/**
 * View helper for reading lists
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class ReadingList extends Search
{
	public function getNavigation()
	{
		$final = array();
		
		// previously saved records
		
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'select',
			'course_id' => $this->request->getParam('course_id'),
		);
		
		$final['url_previously_saved'] = $this->request->url_for($params);

		// search for new records

		$params = array(
			'controller' => 'reading',
			'course_id' => $this->request->getParam('course_id'),
		);		
		
		$final['url_search'] = $this->request->url_for($params);
		
		return $final;
	}

	/**
	 * URL for remove action
	 * 
	 * @param Record $record
	 * @return string url
	 */
	
	public function linkSaveRecord( Record $record )
	{
		$params = array(
			'controller' => $this->request->getParam('controller'),
			'action' => 'save',
			'id' => $record->getRecordID(),
				
			// Added course ID
		
			'course_id' => $this->request->getParam('course_id')
		);
		
		return $this->request->url_for($params);
	}
}