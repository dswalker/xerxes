<?php

namespace Xerxes\Mvc;

/**
 * Bootstrap
 *
 * @author David Walker
 * @copyright 2013 California State University
 * @version
 * @package  Xerxes
 * @link
 * @license
 */

class Bootstrap extends \ArrayIterator
{
	private $config;
	
	public function __construct(array $config)
	{
		$this->config = $config;
	}
}
