<?php

namespace Application\Model\KnowledgeBase;

use Application\Model\DataMap\Databases;

/**
 * Metalib KB
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class KnowledgeBase
{
	private $user;
	private $lang;
	
	public function __construct($user, $lang)
	{
		$this->user = $user;
		$this->lang = $lang;
	}
	
	public function getCategories()
	{
		$databases = new Databases();
		return $databases->getCategories($this->lang);
	}
	
	public function getSubject($subject)
	{
		$databases = new Databases();
		return $databases->getSubject($subject, $this->lang);		
	}
	
	public function getDatabases($query = "")
	{
		
	}
	
	public function getDatabase($id)
	{
		
	}
}
