<?php

namespace Application\Model\Metalib;

use Application\Model\Search,
	Xerxes\Utility\Request;

/**
 * Search Query
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Query extends Search\Query
{
	public function getDatabases()
	{
		return $this->request->getParam('database', null, true);
	}
	
	public function getSubject()
	{
		return $this->request->getParam('subject');
	}
	
	public function getLanguage()
	{
		return $this->request->getParam('lang');
	}
}
