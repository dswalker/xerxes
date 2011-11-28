<?php

/**
 * Authentication User
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Authentication_User extends Xerxes_Framework_DataValue
{
	public $username;
	public $last_login;
	public $suspended;
	public $first_name;
	public $last_name;
	public $email_addr;
	public $usergroups = array();
	
	function __construct($username = null)
	{
		$this->username = $username;
	}
}