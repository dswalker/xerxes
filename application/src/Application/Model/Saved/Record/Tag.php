<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Saved\Record;

use Xerxes\Utility\DataValue;

/**
 * Database Value Record Tag
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Tag extends DataValue
{
	public $label;
	public $total;
}
