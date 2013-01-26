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
 * @license 
 * @package Xerxes
 */


class Result extends Search\Result
{
	public $id;
	public $source;
	public $original_id;
	public $timestamp;
	public $username;
}
