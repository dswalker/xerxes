<?php

namespace Application\Model\Saved;

use Application\Model\Search;
use Xerxes\Utility\DataValue;

/**
 * Saved Result
 *
 * @author David Walker
 */

class Result extends Search\Result
{
	public $id;
	public $source;
	public $original_id;
	public $timestamp;
	public $username;
}
