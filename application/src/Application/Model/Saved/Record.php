<?php

namespace Application\Model\Saved;

use Application\Model\Search,
	Xerxes\Utility\DataValue;

/**
 * Saved Result
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
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
