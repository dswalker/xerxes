<?php

namespace Application\Model\Search;

use Xerxes\Utility\DataValue;

/**
 * Database Value Full Text
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @package Xerxes
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
