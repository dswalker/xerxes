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

/**
 * View helper for databases
 *
 * @author David Walker <dwalker@calstate.edu>
 */

use Application\Model\Knowledgebase\Category;
use Xerxes\Mvc\MvcEvent;
use Xerxes\Mvc\Request;

class Databases
{
	/**
	 * @var Request
	 */
	private $request;
	
	/**
	 * Create new Database Helper
	 *
	 * @param MvcEvent $e
	 */
	
	public function __construct(MvcEvent $e)
	{
		$this->request = $e->getRequest();
	}
	
	/**
	 * Add database navigation links
	 * 
	 * @param array $categories
	 * @return array
	 */
	
	public function getEditLink()
	{
		$controller = $this->request->getParam('controller');
		$switch = 'databases';
		
		if( $controller == 'databases')
		{
			$switch = 'databases-edit';
		}
		
		$params = $this->request->getParams();
		$params['controller'] = $switch;
		
		return $this->request->url_for($params);
	}
}
