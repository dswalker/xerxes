<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Google;

use Xerxes;
use Xerxes\Record\Link;

class Record extends Xerxes\Record
{
	protected $source = 'Google';
	protected $result_xml;
	
	public function loadXML($xml)
	{
		$this->result_xml = $xml;
		parent::loadXML($xml);
	}	
	
	protected function map()
	{
		$mime_type = (string) $this->result_xml["MIME"];
		
		$this->title = strip_tags(html_entity_decode((string) $this->result_xml->T));
		$this->snippet = strip_tags((string) $this->result_xml->S);
		
		$link = new Link((string) $this->result_xml->U, Link::ONLINE);
		$this->links[] = $link;
	}
}
