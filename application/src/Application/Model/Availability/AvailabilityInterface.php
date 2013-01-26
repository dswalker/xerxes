<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Availability;

/**
 * Availability interface
 *
 * @author David Walker <dwalker@calstate.edu>
 */

interface AvailabilityInterface
{
	public function getHoldings( $id );
}