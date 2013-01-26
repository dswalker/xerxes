<?php

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