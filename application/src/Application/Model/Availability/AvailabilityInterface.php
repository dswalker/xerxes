<?php

namespace Application\Model\Availability;

/**
 * Availability interface
 *
 * @author David Walker
 */

interface AvailabilityInterface
{
	public function getHoldings( $id );
}