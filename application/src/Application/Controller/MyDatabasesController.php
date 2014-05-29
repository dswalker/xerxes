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

/**
 * My Saved Databases Controller
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class MyDatabasesController extends DatabasesEditController
{
	/**
	 * (non-PHPdoc)
	 * @see Xerxes\Mvc.ActionController::init()
	 */
	
	public function init()
	{
		parent::init();
		
		// set owner to the local user (rather than admin!)
		
		$this->knowledgebase->setOwner($this->request->getUser()->username);
		
		// set view on database sub-folder
		
		$action = $this->request->getParam('action', 'index');
		$this->response->setView("databases/saved/$action.xsl");		
	}
	
	protected function enforceLogin()
	{
		// nothing for now
	}
}
