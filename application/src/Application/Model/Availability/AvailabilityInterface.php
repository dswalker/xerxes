<?php

namespace Application\Model\Availability;

/**
 * Availability interface
 *
 * @author David Walker
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license
 * @package Xerxes
 */

interface AvailabilityInterface
{
	public function getHoldings( $id );
}