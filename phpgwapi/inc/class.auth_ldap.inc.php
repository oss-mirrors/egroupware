<?php
	/**************************************************************************\
	* phpGroupWare API - Auth from LDAP                                        *
	* This file written by Lars Kneschke <kneschke@phpgroupware.org>           *
	* and Joseph Engo <jengo@phpgroupware.org>                                 *
	* Authentication based on LDAP Server                                      *
	* Copyright (C) 2000, 2001 Joseph Engo                                     *
	* -------------------------------------------------------------------------*
	* This library is part of the phpGroupWare API                             *
	* http://www.phpgroupware.org/api                                          * 
	* ------------------------------------------------------------------------ *
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

	/* $Id$ */
  
	class auth
	{
		function authenticate($username, $passwd)
		{
			global $phpgw_info, $phpgw;
			//  error_reporting MUST be set to zero, otherwise you'll get nasty LDAP errors with a bad login/pass...
			//  these are just "warnings" and can be ignored.....
			error_reporting(0); 

			$ldap = ldap_connect($phpgw_info['server']['ldap_host']);

			// find the dn for this uid, the uid is not always in the dn
			$sri = ldap_search($ldap, $phpgw_info['server']['ldap_context'], 'uid='.$username);
			$allValues = ldap_get_entries($ldap, $sri);
			if ($allValues['count'] > 0)
			{
				// we only care about the first dn
				$userDN = $allValues[0]['dn'];

				// generate a bogus password to pass if the user doesn't give us one 
				// this gets around systems that are anonymous search enabled 
				if (empty($passwd)) $passwd = crypt(microtime()); 
					// try to bind as the user with user suplied password
					if (ldap_bind($ldap,$userDN, $passwd)) return True;
				}

				// Turn error reporting back to normal
				error_reporting(7);

				// dn not found or password wrong
				return False;
		}

		function change_password($old_passwd, $new_passwd, $_account_id="") 
		{
			global $phpgw_info, $phpgw;

			if ("" == $_account_id)
			{
				$_account_id = $phpgw_info['user']['account_id'];
			}
	
			$ds = $phpgw->common->ldapConnect();
			$sri = ldap_search($ds, $phpgw_info["server"]["ldap_context"], "uidnumber=$_account_id");
			$allValues = ldap_get_entries($ds, $sri);
	
	
			$entry['userpassword'] = $phpgw->common->encrypt_password($new_passwd);
			$dn = $allValues[0]["dn"];
	
			if (!@ldap_modify($ds, $dn, $entry)) 
			{
				return false;
			}
			$phpgw->session->appsession('password','phpgwapi',$new_passwd);
	
			return $encrypted_passwd;
		}

		// This data needs to be updated in LDAP, not SQL (jengo)
		function update_lastlogin($account_id, $ip)
		{
			global $phpgw;
	
			$account_id = get_account_id($account_id);
			$now = time();
	
			$phpgw->db->query("update phpgw_accounts set account_lastloginfrom='"
				. "$ip', account_lastlogin='" . $now
				. "' where account_id='$account_id'",__LINE__,__FILE__);
		}
	}
?>
