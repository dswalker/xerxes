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

use Xerxes\Mvc\Exception\AccessDeniedException;
/**
 * My Saved Databases Controller
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class MyDatabasesPublicController extends DatabasesController
{
	/**
	 * Ensure the subject is public
	 * 
	 * (non-PHPdoc)
	 * @see \Application\Controller\DatabasesController::subjectAction()
	 */
	
	public function subjectAction()
	{
		$response = parent::subjectAction();
		
		$category = $response->getVariable('category');
		
		if ( $category->isPublic() == false )
		{
			throw new AccessDeniedException('This list is not public');
		}
		
		return $response;
	}

	/**
	 * (non-PHPdoc)
	 * @see Application\Controller.DatabasesController::getKnowledgebase()
	 */
	
	protected function getKnowledgebase()
	{
		$username = $this->request->getParam('username');
		
		$knowledgebase = parent::getKnowledgebase();
	
		$knowledgebase->setOwner($username);
		
		return $knowledgebase;
	}
}
