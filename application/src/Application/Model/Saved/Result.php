<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Saved;

use Application\Model\Search;
use Xerxes\Utility\DataValue;

/**
 * Saved Result
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Result extends Search\Result
{
	public $id;
	public $source;
	public $original_id;
	public $timestamp;
	public $username;
}
