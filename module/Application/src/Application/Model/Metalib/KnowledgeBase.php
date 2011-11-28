<?php

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

class Xerxes_Model_Metalib_KnowledgeBase
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
		$databases = new Xerxes_Model_DataMap_Databases();
		return $databases->getCategories($this->lang);
	}
	
	public function getSubject($subject)
	{
		$databases = new Xerxes_Model_DataMap_Databases();
		return $databases->getSubject($subject, $this->lang);		
	}
	
	public function getDatabases($query = "")
	{
		
	}
	
	public function getDatabase($id)
	{
		
	}
}
