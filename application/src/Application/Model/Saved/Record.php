<?php

namespace Application\Model\Saved;

use Application\Model\Search;
use Xerxes\Utility\DataValue;

/**
 * Saved Result
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Record extends DataValue
{
	public $id;
	public $source;
	public $original_id;
	public $timestamp;
	public $username;
	public $nonsort;
	public $title;
	public $author;
	public $year;
	public $format;
	public $refereed;
	public $marc;
	public $xerxes_record; // not in database
	
	public $tags = array();
}
