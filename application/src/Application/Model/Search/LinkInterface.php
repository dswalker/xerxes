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

use Application\Model\Search\Query;

/**
 * Linking interface
 *
 * @author David Walker <dwalker@calstate.edu>
 */

interface LinkInterface
{
	public function getTotal( Query $query );
	
	public function getUrl( Query $query );
}