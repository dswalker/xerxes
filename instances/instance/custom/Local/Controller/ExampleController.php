<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Local\Controller;

use Xerxes\Mvc\ActionController;


class ExampleController extends ActionController
{
	public function indexAction()
	{
		echo "yes!"; exit;
	}
}
