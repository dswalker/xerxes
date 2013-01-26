<?php

/*
 * This file is part of the Xerxes project.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Databases;

/**
 * Librarian
 *
 * @author David Walker <dwalker@calstate.edu>
 */

class Librarian extends Resource  
{
	public $email;
	public $phone;
	public $office;
	public $office_hours;
}