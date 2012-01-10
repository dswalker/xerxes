<?php

namespace Application\Model\KnowledgeBase;

use Application\Model\DataMap\Databases;

/**
 * Knowledgebase
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class KnowledgeBase
{
	private $lang; // language
	
	protected $datamap; // xerxes datamap
	
	public function __construct($lang = null)
	{
		$this->lang = $lang;
	}
	
	public function getCategories()
	{
		return $this->getDataMap()->getCategories($this->lang);
	}
	
	public function getSubject($subject)
	{
		return $this->getDataMap()->getSubject($subject, $this->lang);		
	}
	
	public function getDatabases(array $databases = null)
	{
		return $this->getDataMap()->getDatabases($databases);
	}
	
	public function getDatabase($id)
	{
		
	}
	
	/**
	 * Lazyload datamap
	 */
	
	protected function getDataMap()
	{
		if ( ! $this->datamap instanceof Databases )
		{
			$this->datamap = new Databases();
		}
	
		return $this->datamap;
	}	
}
