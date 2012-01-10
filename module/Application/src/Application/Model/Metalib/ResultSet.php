<?php

namespace Application\Model\Metalib;

use Application\Model\KnowledgeBase\Database,
	Application\Model\Search;

/**
 * Metalib Search Results
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ResultSet extends Search\ResultSet
{
	public $database;
	public $set_number;
	public $find_status;
	
	/**
	 * Create Metalib Search Result Set
	 * 
	 * @param Config $config
	 */
	
	public function __construct( Config $config, Database $database )
	{
		parent::__construct( $config );

		$this->database = $database;
	} 
}
