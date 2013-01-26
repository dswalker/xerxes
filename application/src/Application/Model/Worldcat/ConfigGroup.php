<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Worldcat;

/**
 * Worldcat group config
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class ConfigGroup
{
	public $source;
	public $type;
	public $libraries_include;
	public $libraries_exclude;
	public $lookup_address;
	public $limit_material_types;
	public $exclude_material_types;
	public $show_holdings = false;
	public $query_limit;
	public $frbr;
}
