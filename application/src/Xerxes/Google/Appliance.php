<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xerxes\Google;

use Zend\Http\Client;

/**
 * Search and retrieve records Google search appliance
 *
 * based on code written by Scott Jungling <sjungling@csuchico.edu>
 *
 * @author David Walker <dwalker@calstate.edu> 
 *
 */

class Appliance 
{
	private $url = "";
	private $parameters = array();
	
	function __construct($host = "google.calstate.edu", $client = "csuchico-edu", $site = "csuchico")
	{
		$this->parameters = array(
			"client" => $client, 
			"site" => $site, 
			"output" => 'xml', 
			"oe" => 'UTF-8'
			);
		
		$this->url = "http://$host/search?";
	}
	
	function search($query)
	{
		$this->url .= "&q=" . urlencode($query);
		
		$xml = new \DomDocument();
		$xml->load( $this->url );
		
		return $xml;		
	}
}