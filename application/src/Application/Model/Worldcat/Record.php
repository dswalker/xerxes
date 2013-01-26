<?php

namespace Application\Model\Worldcat;

use Xerxes\Record\Bibliographic;

/**
 * Extract bibliographic properties from Worldcat
 * 
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @package Xerxes
 */

class Record extends Bibliographic
{
	protected $source = "worldcat";
	
	public function map()
	{
		parent::map();

		$this->oclc_number = $this->control_number;
		
		// blank all links
		
		$this->links = array();
	}
	
	public function getOpenURL($strResolver, $strReferer = null, $param_delimiter = "&")
	{
		$url = parent::getOpenURL($strResolver, $strReferer, $param_delimiter);
	
		// always ignore dates for journals and books, since worldcat is describing
		// the item as a whole, not any specific issue or part
	
		return $url . "&sfx.ignore_date_threshold=1";
	}	
}
	