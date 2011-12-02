<?php

namespace Application\Model\KnowledgeBase;

use Xerxes\Utility\DataValue;

/**
 * Database Value Type
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */


class Type extends DataValue
{
	public $id;
	public $name;
	public $normalized;
	public $databases = array();
}
