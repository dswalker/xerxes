<?php

/**
 * Authenticate users against an LDAP-enabled directory server
 * 
 * @author David Walker, Ivan Masar
 * @copyright 2011 California State University, 2010 Ivan Masar
 * 
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id$
 * @package Xerxes
 */

class Xerxes_Model_Authentication_LDAP extends Xerxes_Model_Authentication_Abstract 
{
	/**
	* Authenticates the user against the directory server
	*/
	
	public function onCallBack()
	{
		define("LDAP_OPT_ON",   1);
		define("LDAP_OPT_OFF",  0);
		
		$strUsername		= $this->request->getProperty( "username" );
		$strPassword		= $this->request->getProperty( "password" );
		
		$strController		= $this->registry->getConfig( "DIRECTORY_SERVER", true );
		$strDomain		= $this->registry->getConfig( "DOMAIN", false );
		
		// backwards compatibility with now deprecated domain entry
		
		$strDNFormat = "";
		
		if ( $strDomain != "" )
		{
			$strDNFormat = "%s@$strDomain";
		}
		else
		{
			$strDNFormat	= $this->registry->getConfig( "LDAP_DN_FORMAT", true );
		}
		
		$bolDoInitBind		= $this->registry->getConfig( "LDAP_DO_INIT_BIND", false, false );
		$strInitBindDN		= $this->registry->getConfig( "LDAP_INIT_BIND_DN", false );
		$strInitBindPwd		= $this->registry->getConfig( "LDAP_INIT_BIND_PASSWORD", false );
		$strSearchBase		= $this->registry->getConfig( "LDAP_SEARCH_BASE", false );
		$strSearchFilter	= $this->registry->getConfig( "LDAP_SEARCH_FILTER", false );
		$strSearchUid		= $this->registry->getConfig( "LDAP_SEARCH_UID", false );
		$strSearchName		= strtolower($this->registry->getConfig( "LDAP_SEARCH_NAME", false ));
		$strSearchSurname	= strtolower($this->registry->getConfig( "LDAP_SEARCH_SURNAME", false ));
		$strSearchMail		= strtolower($this->registry->getConfig( "LDAP_SEARCH_MAIL", false ));
		$strOptVersion		= $this->registry->getConfig( "LDAP_OPT_PROTOCOL_VERSION", false, 2 );
		$strOptDeref		= $this->registry->getConfig( "LDAP_OPT_DEREF", false, LDAP_DEREF_NEVER );
		$strOptReferrals	= $this->registry->getConfig( "LDAP_OPT_REFERRALS", false, LDAP_OPT_ON );
		$bolOptTLS		= $this->registry->getConfig( "LDAP_OPT_TLS", false, false );
		$strGroupFilter		= $this->registry->getConfig( "LDAP_GROUP_FILTER", false, "" );
		$strGroupFilterMatch	= $this->registry->getConfig( "LDAP_GROUP_FILTER_MATCH", false, 1 );
		
		if ( is_string($strOptVersion) )
			$strOptVersion = intval($strOptVersion);
		if ( is_string($strOptDeref) )
			$strOptDeref = constant($strOptDeref);
		if ( is_string($strOptReferrals) )
			$strOptReferrals = constant($strOptReferrals);
		
		$bolAuth = false;
		
		
		// connect to ldap server
		
		$objConn = ldap_connect($strController);
		
		if ($objConn)
		{
			// set ldap options

			if ( is_int($strOptVersion) )
				ldap_set_option($objConn, LDAP_OPT_PROTOCOL_VERSION, $strOptVersion);
			if ( is_int($strOptDeref) )
				ldap_set_option($objConn, LDAP_OPT_DEREF, $strOptDeref);
			if ( is_int($strOptReferrals) )
				ldap_set_option($objConn, LDAP_OPT_REFERRALS, $strOptReferrals);
			
			if ( $bolOptTLS == true )
				ldap_start_tls($objConn);
			
			if ( $strPassword != null )
			{
				if ( $bolDoInitBind != true )
				{
					// try to bind directly using provided username / password
					
					// construct the user DN from username
					$strBindDN = sprintf($strDNFormat, $strUsername);
				}
				else
				{
					// first, do the initial bind
					// if $strInitBindDN and $strInitBindPwd are both empty, do anonymous bind
					
					$bolInitBind = ldap_bind($objConn, $strInitBindDN, $strInitBindPwd);
					
					if ( $bolInitBind )
					{
						// search for the user DN in the directory tree
						
						$arrFields = array($strSearchUid);
						$strSearchFilter2 = sprintf($strSearchFilter, $strUsername);
						$objResults = @ldap_search($objConn, $strSearchBase, $strSearchFilter2, $arrFields, 0, 3);
						
						if ( $objResults ) 
						{
							$objEntries = ldap_get_entries($objConn, $objResults);
							
							if ($objEntries['count'] == 0)	
							{
								$strBindDN = 'USER_NOT_FOUND';
							}
							else
							{
								$strBindDN = $objEntries[0]['dn'];
							}
						}
					}
				}
				
				// try to bind using user DN
				if ($strBindDN != 'USER_NOT_FOUND')
				{
					$bolAuth = ldap_bind($objConn, $strBindDN, $strPassword);
					
					// search again (in case we didn't do the initial bind) to retrieve name, surname and email
					
					if (!empty($strSearchName) && !empty($strSearchName) and !empty($strSearchName)) 
					{
						$arrFields = array($strSearchUid, $strSearchSurname, $strSearchName);
						$strSearchFilter2 = sprintf($strSearchFilter, $strUsername);
						$objResults = @ldap_search($objConn, $strSearchBase, $strSearchFilter2, $arrFields, 0, 3);
						
						if ( $objResults ) 
						{
							$objEntries = ldap_get_entries($objConn, $objResults);
							
							if (isset($objEntries[0][$strSearchName][0]))
								$this->user->first_name = $objEntries[0][$strSearchName][0];
							if (isset($objEntries[0][$strSearchSurname][0]))
								$this->user->last_name  = $objEntries[0][$strSearchSurname][0];
							if (isset($objEntries[0][$strSearchMail][0]))
								$this->user->email_addr = $objEntries[0][$strSearchMail][0];
						}
					}
				}
				ldap_close($objConn);
			}
			
			if ( $bolAuth == true )
			{
				// register the user and stop the flow
				
				$this->user->username = $strUsername;
				
				if (@preg_match($strGroupFilter, $strBindDN, $matches) > 0)
				{
					$this->user->usergroups = Array($matches[$strGroupFilterMatch]);
				}
				$this->register();
			}
		}
		return $bolAuth;
	}
}
