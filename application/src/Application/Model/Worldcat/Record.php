<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Worldcat;

use Xerxes\Record\Bibliographic;

/**
 * Extract bibliographic properties from Worldcat
 * 
 * @author David Walker <dwalker@calstate.edu>
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
	