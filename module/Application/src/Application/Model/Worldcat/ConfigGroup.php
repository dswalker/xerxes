<?php

namespace Application\Model\Worldcat;

/**
 * Worldcat group config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
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
