<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

/**
 * Search Limit Term
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class LimitTerm
{
	public $param;
	public $boolean;
	public $field;
	public $relation;
	public $value;
	public $key;
}
