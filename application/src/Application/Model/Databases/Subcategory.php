<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Databases;

use Xerxes\Utility\DataValue;
use Xerxes\Utility\Parser;

/**
 * Metalib SubCategory
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Subcategory extends DataValue
{
	public $subcategory_id;
	public $name;
	public $sequence;
	public $category_id;
	public $databases = array();
}