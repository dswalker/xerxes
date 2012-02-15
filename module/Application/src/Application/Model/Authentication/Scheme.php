<?php

namespace Application\Model\Authentication;

use Application\Model\Authentication\User,
	Application\Model\DataMap\Users, 
	Application\Model\DataMap\SavedRecords,
	Xerxes\Utility\Request,
	Xerxes\Utility\Registry,
	Zend\Mvc\MvcEvent;

/**
 * An event-based authentication framework
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

abstract class Scheme
{
	public $id; // the id of this auth scheme, set by the factory method invoking it
	
	protected $user; // user object
	protected $role = "named"; // users role as named or guest
	protected $return_url; // the return url to get the user back to where they are in Xerxes
	protected $validate_url; // the url to return for a validate request, for external auths
	protected $redirect;
	
	protected $registry; // config object
	protected $request; // request object
	protected $response; // response object	
	
	const FAILED = 0;
	const SUCCESS = 1;
	const REDIRECT = 3;
	
	/**
	 * Create Authentication Scheme
	 * 
	 * @param Request $request
	 */
	
	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->registry = Registry::getInstance();
		
		// get the user from the request
		
		$this->user = $this->request->getUser();
		
		// send them back here when they are done
		
		$this->return_url = $this->request->getParam("return");
		
		// flesh out our return url
		
		$base = $this->request->getBaseUrl();
		$server = $this->request->getServerUrl();
		
		if ( $this->return_url == "" ) // no return supplied
		{
			$this->return_url = $base; // so send them home!
		}
		else
		{
			if ( ! strstr($this->return_url, $server) ) // not a full url
			{
				$this->return_url = $server . $this->return_url; // make it so  @todo: why?
			}
		}
		
		// we always send the user back on http: since shib and possibly other schemes
		// will drop the user back in xerxes on https:, which is weird
		
		$this->return_url = str_replace("https://", "http://", $this->return_url);		
		
		// @todo find out if some CAS servers are still tripping up on this
		
		$params = array (
			'controller' => 'authenticate',
			'action' => 'validate',
			'return' => $this->return_url 
		);
		
		$this->validate_url = $this->request->url_for($params, true);
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

	public function register()
	{
		// data map
		
		$datamap_users = new Users();
		$datamap_records = new SavedRecords();
		
		// if the user was previously active under a local username 
		// then reassign any saved records to the new username
		
		$old_username = $this->request->getSessionData("username");
		$old_role = $this->request->getSessionData("role");
		
		if ( $old_role == "local" )
		{
			$datamap_records->reassignRecords( $old_username, $this->user->username );
		}
		
		// add or update user in the database
		// get any values in the db not specified here and populates user
		 
		$this->user = $datamap_users->touchUser( $this->user );
		
		
		// @todo: should we just save user object in session?
		
		// set main properties in session
		
		$this->request->setSessionData("username", $this->user->username);
		$this->request->setSessionData("role", $this->role);
		
		// store user's additional properties in session, so they can be used by
		// controller, and included in xml for views. 
		
		$this->request->setSessionData("user_properties", $this->user->properties());
		
		// groups too empty array not null please. 
	
		$this->request->setSessionData("user_groups", $this->user->usergroups);
		
		// set this object's id in session
		
		$this->request->setSessionData("auth", $this->id);
		
		// now forward them to the return url
		
		$this->setRedirect($this->return_url);
		
		return self::SUCCESS;
	}
	
	public function setRedirect($url)
	{
		$this->redirect = $url;
	}
	
	public function getRedirect()
	{
		if ( $this->redirect != "" )
		{
			return $this->redirect;
		}
		else
		{
			return $this->return_url;
		}
	}
}