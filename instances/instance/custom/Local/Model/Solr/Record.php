<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Local\Model\Solr;

use Application\Model\Solr\Record;
use Xerxes\Marc\Record as MarcRecord;

/**
 * Extract properties for books, articles, and dissertations from SolrMarc implementation
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Record
{
	public function map(Record $record, MarcRecord $marc)
	{
	}
}
