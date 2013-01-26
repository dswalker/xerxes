<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Authentication;

/**
 * Guest Authentication
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Guest extends Scheme 
{
	/**
	 * Just register the user with a role of guest
	 */
	
	public function onLogin() 
	{
		$this->user->username = User::genRandomUsername(User::GUEST);
		$this->user->role = User::GUEST;
		
		return $this->register();
	}
}
