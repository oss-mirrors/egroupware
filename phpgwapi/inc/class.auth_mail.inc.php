<?php
  /**************************************************************************\
  * phpGroupWare API - Auth from Mail server                                 *
  * This file written by Dan Kuykendall <seek3r@phpgroupware.org>            *
  * Authentication based on mail server                                      *
  * Copyright (C) 2000, 2001 Dan Kuykendall                                  *
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
		var $previous_login = -1;

		function authenticate($username, $passwd)
		{
			global $phpgw_info, $phpgw;
			error_reporting(error_reporting() - 2);

			if ($phpgw_info['server']['mail_login_type'] == 'vmailmgr')
			{
				$username = $username . '@' . $phpgw_info['server']['mail_suffix'];
			}
			if ($phpgw_info['server']['mail_server_type']=='imap')
			{
				$phpgw_info['server']['mail_port'] = '143';
			}
			elseif ($phpgw_info['server']['mail_server_type']=='pop3')
			{
				$phpgw_info['server']['mail_port'] = '110';
			}

			if( $phpgw_info['server']['mail_server_type']=='pop3')
			{
				$mailauth = imap_open('{'.$phpgw_info['server']['mail_server'].'/pop3'
					.':'.$phpgw_info['server']['mail_port'].'}INBOX', $username , $passwd);
			}
			else
			{ //assume imap 
				$mailauth = imap_open('{'.$phpgw_info['server']['mail_server']
					.':'.$phpgw_info['server']['mail_port'].'}INBOX', $username , $passwd);
			}

			error_reporting(error_reporting() + 2);
			if ($mailauth == False) {
				return False;
			} else {
				imap_close($mailauth);
				return True;
			}
		}

		function change_password($old_passwd, $new_passwd) {
			global $phpgw_info, $phpgw;
			return False;
		}

		// Since there account data will still be stored in SQL, this should be safe to do. (jengo)
		function update_lastlogin($account_id, $ip)
		{
			global $phpgw;

			$phpgw->db->query("select account_lastlogin from phpgw_accounts where account_id='$account_id'",__LINE__,__FILE__);
			$phpgw->db->next_record();
			$this->previous_login = $phpgw->db->f('account_lastlogin');

			$phpgw->db->query("update phpgw_accounts set account_lastloginfrom='"
				. "$ip', account_lastlogin='" . time()
				. "' where account_id='$account_id'",__LINE__,__FILE__);
		}

	}
?>
