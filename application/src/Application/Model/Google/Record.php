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
	
	public function loadXML($xml)
	{
		parent::loadXML($xml);
	}	
	
	protected function map()
	{
		$xml = simplexml_import_dom($this->document);
		
		$mime_type = (string) $xml["MIME"];
		
		$this->title = strip_tags(html_entity_decode((string) $xml->T));
		$this->snippet = strip_tags((string) $xml->S);
		
		$link = new Link((string) $xml->U, Link::ONLINE);
		$this->links[] = $link;
	}
}
