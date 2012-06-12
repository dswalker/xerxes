<?php

namespace Application\Model\Metalib;

/**
 * Metalib Merged Result Set
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class MergedResultSet
{
	public $set_number;
	public $total;
	
	public function __construct( \DOMDocument $merged_xml)
	{
		$xpath = new \DOMXPath( $merged_xml );
			
		// extract new merge set number and total hits for merge set
			
		if ( $xpath->query("//new_set_number")->item(0) != null )
		{
			$this->set_number = $xpath->query( "//new_set_number")->item(0)->nodeValue;
		}
		
		if ( $xpath->query("//no_of_documents")->item(0) != null )
		{
			$this->total = (int) $xpath->query("//no_of_documents")->item(0)->nodeValue;
		}
	}			
}
