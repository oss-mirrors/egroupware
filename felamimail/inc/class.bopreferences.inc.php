<?php
	/***************************************************************************\
	* phpGroupWare - FeLaMiMail                                                 *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.phpgroupware.org                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class bopreferences
	{
		var $public_functions = array
		(
			'getPreferences'	=> True,
			'none'	=> True
		);
		
		function bopreferences()
		{
			#$this->bocompose	= CreateObject('felamimail.bocompose');
		}
		
		function getPreferences()
		{
/*			while(list($key,$value) = each($GLOBALS['phpgw_info']['server']) )
			{
				print ". $key: $value<br>";
				if (is_array($value))
				{
					while(list($key1,$value1) = each($value) )
					{
						print ".. &nbsp;$mbsp;-$key1: $value1<br>";
					}
				}
			}
*/			

			$config = CreateObject('phpgwapi.config','felamimail');
			$config->read_repository();
			$felamimailConfig = $config->config_data;
			unset($config);
			
			#_debug_array($felamimailConfig);
			#print "<hr>";
			
			// set values to the global values
			$data['imapServerAddress']	= $GLOBALS['phpgw_info']['server']['mail_server'];
			$data['key']			= $GLOBALS['phpgw_info']['user']['passwd'];
			$data['username']		= $GLOBALS['phpgw_info']['user']['userid'];
			$data['imap_server_type']	= strtolower($felamimailConfig["imapServerMode"]);
			$data['realname']		= $GLOBALS['phpgw_info']['user']['fullname'];
			$data['defaultDomainname']	= $GLOBALS['phpgw_info']["server"]["mail_suffix"];

			$data['smtpServerAddress']	= $GLOBALS['phpgw_info']["server"]["smtp_server"];
			$data['smtpPort']		= $GLOBALS['phpgw_info']["server"]["smtp_port"];

			switch($data['imap_server_type'])
			{
				case "imaps-encr-only":
				case "imaps-encr-auth":
					$data['imapPort']	= 993;
					break;
				default:
					$data['imapPort']	= 143;
					break;
			}
			
			// check for felamimail specific settings
			if(!empty($felamimailConfig['imapServer']))
				$data['imapServerAddress']	= $felamimailConfig['imapServer'];

			if(!empty($felamimailConfig['smtpServer']))
				$data['smtpServerAddress']	= $felamimailConfig['smtpServer'];
			
			if(!empty($felamimailConfig['smtpServer']))
				$data['smtpPort']		= $felamimailConfig['smtpPort'];

			if(!empty($felamimailConfig['mailSuffix']))
				$data['defaultDomainname']	= $felamimailConfig['mailSuffix'];

			$data['emailAddress']		= $data['username']."@".$data['defaultDomainname'];

			if($GLOBALS['phpgw_info']['server']['account_repository'] == 'ldap')
			{
				// do a ldap lookup to fetch users email address
				$ldap = $GLOBALS['phpgw']->common->ldapConnect();
				$filter = sprintf("(&(uid=%s)(objectclass=posixAccount))",$GLOBALS['phpgw_info']['user']['userid']);
				
				$sri = @ldap_search($ldap,$GLOBALS['phpgw_info']['server']['ldap_context'],$filter);
				if ($sri)
				{
					$allValues = ldap_get_entries($ldap, $sri);


					if(isset($allValues[0]['emailaddress'][0]))
					{
						$data['emailAddress']		= $allValues[0]['emailaddress'][0];
					}
					elseif(isset($allValues[0]['maillocaladdress'][0]))
					{
						$data['emailAddress']           = $allValues[0]['maillocaladdress'][0];
					}
					elseif(isset($allValues[0]['mail'][0]))
					{
						$data['emailAddress']           = $allValues[0]['mail'][0];
					}
				}
			}
			
			// check for user specific settings
			if ($GLOBALS['phpgw_info']['user']['preferences']['email']['use_custom_settings'] == 'True')
			{
				if(!empty($GLOBALS['phpgw_info']['user']['preferences']['email']['userid']))
					$data['username']		= $GLOBALS['phpgw_info']['user']['preferences']['email']['userid'];

				if(!empty($GLOBALS['phpgw_info']['user']['preferences']['email']['passwd']))
					$data['key']			= $GLOBALS['phpgw_info']['user']['preferences']['email']['passwd'];

				if(!empty($GLOBALS['phpgw_info']['user']['preferences']['email']['address']))
					$data['emailAddress']		= $GLOBALS['phpgw_info']['user']['preferences']['email']['address'];

				if(!empty($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server']))
					$data['imapServerAddress']	= $GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server'];

				if(!empty($GLOBALS['phpgw_info']['user']['preferences']['email']['imap_server_type']))
					$data['imap_server_type']	= strtolower($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type']);
			}
			
			// preferences
			$data['deleteOptions']		= $GLOBALS['phpgw_info']['user']['preferences']['felamimail']['deleteOptions'];
			if(empty($data['deleteOptions']))
			{
				$data['deleteOptions'] = 'remove_immediately';
			}
			
			$data['trash_folder']		= $GLOBALS['phpgw_info']['user']['preferences']['felamimail']['trashFolder'];
			if(empty($data['trash_folder']))
			{
				$data['trash_folder'] = 'INBOX.Trash';
			}

			$data['sent_folder']		= $GLOBALS['phpgw_info']['user']['preferences']['felamimail']['sent_folder'];

			if (empty($data['sent_folder']))
			{
				$data['sent_folder'] = 'INBOX.Sent'; 
			}

			if (!empty($data['trash_folder'])) 
				$data['move_to_trash'] 	= True;
			if (!empty($data['sent_folder'])) 
				$data['move_to_sent'] 	= True;
			$data['signature']		= $GLOBALS['phpgw_info']['user']['preferences']['felamimail']['email_sig'];

		//	_debug_array($data);
			return $data;
		}
}