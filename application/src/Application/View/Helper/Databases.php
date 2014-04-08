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
	 * Add links to categories
	 * 
	 * @param array $categories
	 * @return array
	 */
	
	public function addCategoryLinks(array $categories )
	{
		$final = array();
		
		foreach ( $categories as $category )
		{
			$normalized = $category['normalized'];
			
			$params = array(
				'controller' => 'databases',
				'action' => 'subject',
				'subject' => $normalized
			);
			
			$category['url'] = $this->request->url_for($params);
			
			$final[] = $category;
		}
		
		return $final;
	}
}
