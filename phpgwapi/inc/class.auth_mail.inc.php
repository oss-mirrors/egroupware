<?php
  /**************************************************************************\
  * eGroupWare API - Auth from Mail server                                   *
  * This file written by Dan Kuykendall <seek3r@phpgroupware.org>            *
  * Authentication based on mail server                                      *
  * Copyright (C) 2000, 2001 Dan Kuykendall                                  *
  * ------------------------------------------------------------------------ *
  * This library is part of the eGroupWare API                               *
  * http://www.egroupware.org/api                                            *
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

	class auth_
	{
		var $previous_login = -1;

		function authenticate($username, $passwd)
		{
			error_reporting(error_reporting() - 2);

			if ($GLOBALS['egw_info']['server']['mail_login_type'] == 'vmailmgr')
			{
				$username = $username . '@' . $GLOBALS['egw_info']['server']['mail_suffix'];
			}
			if ($GLOBALS['egw_info']['server']['mail_server_type']=='imap')
			{
				$GLOBALS['egw_info']['server']['mail_port'] = '143';
			}
			elseif ($GLOBALS['egw_info']['server']['mail_server_type']=='pop3')
			{
				$GLOBALS['egw_info']['server']['mail_port'] = '110';
			}
			elseif ($GLOBALS['egw_info']['server']['mail_server_type']=='imaps')
			{
				$GLOBALS['egw_info']['server']['mail_port'] = '993';
			}
			elseif ($GLOBALS['egw_info']['server']['mail_server_type']=='pop3s')
			{
				$GLOBALS['egw_info']['server']['mail_port'] = '995';
			}

			if( $GLOBALS['egw_info']['server']['mail_server_type']=='pop3')
			{
				$mailauth = imap_open('{'.$GLOBALS['egw_info']['server']['mail_server'].'/pop3'
					.':'.$GLOBALS['egw_info']['server']['mail_port'].'}INBOX', $username , $passwd);
			}
			elseif ( $GLOBALS['egw_info']['server']['mail_server_type']=='imaps' )
			{
				// IMAPS support:
				$mailauth = imap_open('{'.$GLOBALS['egw_info']['server']['mail_server']."/ssl/novalidate-cert"
					.':993}INBOX', $username , $passwd);
			}
			elseif ( $GLOBALS['egw_info']['server']['mail_server_type']=='pop3s' )
			{
				// POP3S support:
				$mailauth = imap_open('{'.$GLOBALS['egw_info']['server']['mail_server']."/ssl/novalidate-cert"
					.':995}INBOX', $username , $passwd);
			}
			else
			{
				/* assume imap */
				$mailauth = imap_open('{'.$GLOBALS['egw_info']['server']['mail_server']
					.':'.$GLOBALS['egw_info']['server']['mail_port'].'}INBOX', $username , $passwd);
			}

			error_reporting(error_reporting() + 2);
			if ($mailauth == False)
			{
				return False;
			}
			else
			{
				imap_close($mailauth);
				return True;
			}
		}

		function change_password($old_passwd, $new_passwd)
		{
			return False;
		}

		// Since there account data will still be stored in SQL, this should be safe to do. (jengo)
		function update_lastlogin($account_id, $ip)
		{
			$GLOBALS['egw']->db->query("SELECT account_lastlogin FROM phpgw_accounts WHERE account_id=" . (int)$account_id,__LINE__,__FILE__);
			$GLOBALS['egw']->db->next_record();
			$this->previous_login = $GLOBALS['egw']->db->f('account_lastlogin');

			$GLOBALS['egw']->db->query("UPDATE phpgw_accounts SET account_lastloginfrom='"
				. "$ip', account_lastlogin='" . time()
				. "' WHERE account_id=" . (int)$account_id,__LINE__,__FILE__);
		}
	}
?>
