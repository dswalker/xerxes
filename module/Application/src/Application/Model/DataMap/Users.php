<?php

/**
 * Database access mapper for users
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_DataMap_Users extends Xerxes_Framework_DataMap
{
	/**
	 * Update the user table to include the last date of login and any other
	 * specified attributes. Creates new user if neccesary.
	 * If any attributes in Xerxes_Model_Authentication_User are set other than
	 * username, those will also be written to db over-riding anything that may
	 * have been there.  Returns Xerxes_Model_Authentication_User filled out with information matching
	 * db. 
	 *
	 * @param Xerxes_Model_Authentication_User $user
	 * @return Xerxes_Model_Authentication_User $user
	 */
	
	public function touchUser(Xerxes_Model_Authentication_User $user)
	{
		// array to pass to db updating routines. Make an array out of our
		// properties. 

		$update_values = array ( );
		
		foreach ( $user->properties() as $key => $value )
		{
			$update_values[":" . $key] = $value;
		}
		
		// don't use usergroups though. 
		
		unset( $update_values[":usergroups"] );
		
		$update_values[":last_login"] = date( "Y-m-d H:i:s" );
		
		$this->beginTransaction();
		
		$strSQL = "SELECT * FROM xerxes_users WHERE username = :username";
		
		$arrResults = $this->select( $strSQL, array (":username" => $user->username ) );
		
		if ( count( $arrResults ) == 1 )
		{
			// user already exists in database, so update the last_login time and
			// use any data specified in our Xerxes_Model_Authentication_User record to overwrite. Start
			// with what's already there, overwrite with anything provided in
			// the Xerxes_Model_Authentication_User object. 
			
			$db_values = $arrResults[0];
			
			foreach ( $db_values as $key => $value )
			{
				if ( ! (is_null( $value ) || is_numeric( $key )) )
				{
					$dbKey = ":" . $key;
					
					// merge with currently specified values
					

					if ( ! array_key_exists( $dbKey, $update_values ) )
					{
						$update_values[$dbKey] = $value;
						
					//And add it to the user object too
					//$user->$key = $value;
					

					}
				}
			}
			
			$strSQL = "UPDATE xerxes_users " .
				"SET last_login = :last_login, suspended = :suspended, first_name = :first_name, " .
				"last_name = :last_name, email_addr = :email_addr " .
				"WHERE username = :username";
			$status = $this->update( $strSQL, $update_values );
		} 
		else
		{
			// add em otherwise
			

			$strSQL = "INSERT INTO xerxes_users " .
				"( username, last_login, suspended, first_name, last_name, email_addr) " .
				"VALUES (:username, :last_login, :suspended, :first_name, :last_name, :email_addr)";
			$status = $this->insert( $strSQL, $update_values );
		}
		
		// add let's make our group assignments match, unless the group
		// assignments have been marked null which means to keep any existing ones
		// only.

		if ( is_null( $user->usergroups ) )
		{
			// fetch what's in the db and use that please.

			$fetched = $this->select( 
				"SELECT usergroup FROM xerxes_user_usergroups WHERE username = :username", 
				array (":username" => $user->username ) 
			);
			
			if ( count( $fetched ) )
			{
				$user->usergroups = $fetched[0];
			}
			else
			{
				$user->usergroups = array ( );
			}
		} 
		else
		{
			$status = $this->delete( 
				"DELETE FROM xerxes_user_usergroups WHERE username = :username", 
				array (":username" => $user->username ) 
			);
			
			foreach ( $user->usergroups as $usergroup )
			{
				$status = $this->insert( "INSERT INTO xerxes_user_usergroups (username, usergroup) " .
					"VALUES (:username, :usergroup)", 
					array (":username" => $user->username, ":usergroup" => $usergroup ) 
				);
			}
		}
		
		$this->commit();
		
		return $user;
	}
}
