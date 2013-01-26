<?php

namespace Application\Model\Search;

use Xerxes\Utility\DataValue;

/**
 * Database Value Full Text
 *
 * @author David Walker
 */


class Fulltext extends DataValue
{
	public $issn;
	public $title;
	public $startdate;
	public $enddate;
	public $embargo;
	public $updated;
	public $live;
}
