<?php

/**
 * An event-based authentication framework
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

abstract class Xerxes_Model_Authentication_Abstract
{
	protected $user; // user object
	public $id; // the id of this auth scheme, set by the factory method invoking it
	protected $role = "named"; // users role as named or guest
	protected $return; // the return url to get the user back to where they are in Xerxes
	protected $validate_url; // the url to return for a validate request, for external auths
	
	protected $registry; // config object
	protected $request; // request object
	protected $response; // response object	
	
	public function __construct(Xerxes_Framework_Request $request, Xerxes_Framework_Registry $registry, Xerxes_Framework_Response $response)
	{
		$this->request = $request;
		$this->registry = $registry;
		$this->response = $response;
		
		$this->user = new Xerxes_Model_Authentication_User();
		$this->return = $this->request->getProperty("return");
		
		$base = $this->registry->getConfig("BASE_URL", true);
		$server = $this->registry->getConfig("SERVER_URL", true);
		
		// if no return supplied, then send them home!
		
		if ( $this->return == "" )
		{
			$this->return = $base;
		}
		else
		{
			if ( ! strstr($this->return, $server) )
			{
				$this->return = $server . $this->return;
			}
		}

		// we always send the user back on http: since shib and possibly other schemes
		// will drop the user back in xerxes on https:, which is weird
		
		$this->return = str_replace("https://", "http://", $this->return);		
		
		// we're explicitly _not_ using pretty-url here because some CAS servers might only
		// be set-up with a single URL wildcard, while some other funky auth schemes get 
		// tripped-up by the 'sub-folder' path elements that pretty-url creates
		
		$this->validate_url = $base . "/?base=authenticate&action=validate" .
			"&return=" . urlencode($this->return);
	}
	
	/**
	 * This gets called _before_ Xerxes shows the user the login form.
	 * 
	 * SSO schemes, like CAS or OpenSSL, will use this function to redirect the 
	 * user to the external SSO system for login.  'Local' authentication options -- 
	 * that is, those that use the Xerxes login form -- would not (re-)define this.
	 * 
	 */
	
	public function onLogin()
	{
	}
	
	/**
	 * This gets called after the user has _returned_ to Xerxes from the local/remote 
	 * login form.
	 * 
	 * SSO schemes would use this function to validate the login request with the SSO service.  
	 * The local authentication schemes would use this function to actually send the user's 
	 * credential to the authentication system (via LDAP, HTTP, etc.) and decide whether 
	 * the user has supplied the correct credentials.
	 */
	
	public function onCallBack()
	{
		return false;
	}
	
	/**
	 * This gets called after the user has chosen to log out
	 * 
	 * Xerxes will itself destroy the session, so only use this if you need to do any 
	 * clean-up with the external authentication system
	 */
	
	public function onLogout()
	{
	}
	
	/**
	 * This gets called on every request, so logic can be run to time-out a login,
	 * check with SSO system, etc.; beware of performance issues, yo!
	 */
	
	public function onEveryRequest()
	{

	}
	
	/**
	 * Registers the user in session and with the user tables in the database
	 * and then forwards them to the return url
	 */

	protected function register()
	{
		// data map
		
		$datamap_users = new Xerxes_Model_DataMap_Users();
		$datamap_records = new Xerxes_Model_DataMap_SavedRecords();
		
		// if the user was previously active under a local username 
		// then reassign any saved records to the new username
		
		$old_username = $this->request->getSession("username");
		$old_role = $this->request->getSession("role");
		
		if ( $old_role == "local" )
		{
			$datamap_records->reassignRecords( $old_username, $this->user->username );
		}
		
		// add or update user in the database, get any values in the db not
		// specified here.
		 
		$this->user = $datamap_users->touchUser( $this->user );
		
		
		// @todo: just save user in session? move all this to controller?
		
		
		
		// set main properties in session
		
		$this->request->setSession("username", $this->user->username);
		$this->request->setSession("role", $this->role);
		
		$configApplication = $this->registry->getConfig("BASE_WEB_PATH", false, "");
		$this->request->setSession("application", $configApplication);
		
		// store user's additional properties in session, so they can be used by
		// controller, and included in xml for views. 
		
		$this->request->setSession("user_properties", $this->user->properties());
		
		// groups too empty array not null please. 
	
		$this->request->setSession("user_groups", $this->user->usergroups);
		
		// set this object's id in session
		
		$this->request->setSession("auth", $this->id);
		
		// now forward them to the return url
		
		$this->response->setRedirect($this->return);
	}
}