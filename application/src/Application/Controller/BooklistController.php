<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Controller;

use Application\Model\Solr\Booklist\Engine;

class BooklistController extends SearchController
{
	protected $id = "booklist";
	
	protected function getEngine()
	{
		return new Engine();
	}
}
