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
			#_debug_array($felamimailConfig);
			unset($config);
			
			$felamimailUserPrefs = $GLOBALS['phpgw_info']['user']['preferences']['felamimail'];
			
			#_debug_array($GLOBALS['phpgw_info']['user']);
			#print "<hr>";
			
			// set values to the global values
			$data['imapServerAddress']	= $GLOBALS['phpgw_info']['server']['mail_server'];
			$data['key']			= $GLOBALS['phpgw_info']['user']['passwd'];
			if ($felamimailConfig["mailLoginType"] == 'vmailmgr')
				$data['username']		= $GLOBALS['phpgw_info']['user']['userid']."@".$felamimailConfig["mailSuffix"];
			else
				$data['username']		= $GLOBALS['phpgw_info']['user']['userid'];
			$data['imap_server_type']	= strtolower($felamimailConfig["imapServerMode"]);
			$data['realname']		= $GLOBALS['phpgw_info']['user']['fullname'];
			$data['defaultDomainname']	= $GLOBALS['phpgw_info']["server"]["mail_suffix"];

			$data['smtpServerAddress']	= $GLOBALS['phpgw_info']["server"]["smtp_server"];
			$data['smtpPort']		= $GLOBALS['phpgw_info']["server"]["smtp_port"];

			// check for felamimail specific settings
			if(!empty($felamimailConfig['imapServer']))
				$data['imapServerAddress']	= $felamimailConfig['imapServer'];

			if(!empty($felamimailConfig['smtpServer']))
				$data['smtpServerAddress']	= $felamimailConfig['smtpServer'];
			
			if(!empty($felamimailConfig['smtpServer']))
				$data['smtpPort']		= $felamimailConfig['smtpPort'];

			if(!empty($felamimailConfig['mailSuffix']))
				$data['defaultDomainname']	= $felamimailConfig['mailSuffix'];

			if(!empty($felamimailConfig['organizationName']))
				$data['organizationName']	= $felamimailConfig['organizationName'];

			$data['emailAddress']		= $data['username']."@".$data['defaultDomainname'];
			$data['smtpAuth']		= $felamimailConfig['smtpAuth'];

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
			#_debug_array($felamimailUserPrefs);
			if ($felamimailConfig['userDefinedAccounts'] == 'yes' &&
				$felamimailUserPrefs['use_custom_settings'] == 'yes')
			{
				if(!empty($felamimailUserPrefs['username']))
					$data['username']		= $felamimailUserPrefs['username'];

				if(!empty($felamimailUserPrefs['key']))
					$data['key']			= $felamimailUserPrefs['key'];

				if(!empty($felamimailUserPrefs['emailAddress']))
					$data['emailAddress']		= $felamimailUserPrefs['emailAddress'];

				if(!empty($felamimailUserPrefs['imapServerAddress']))
					$data['imapServerAddress']	= $felamimailUserPrefs['imapServerAddress'];

				if(!empty($felamimailUserPrefs['imap_server_type']))
					$data['imap_server_type']	= strtolower($felamimailUserPrefs['imap_server_type']);
			}
			
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
			
			#_debug_array($data);
			
			$GLOBALS['phpgw']->preferences->read_repository();
			$userPrefs = $GLOBALS['phpgw_info']['user']['preferences'];
			
			// how to handle deleted messages
			if(isset($userPrefs['felamimail']['deleteOptions']))
			{
				$data['deleteOptions'] = $userPrefs['felamimail']['deleteOptions'];
			}
			else
			{
				$data['deleteOptions'] = 'mark_as_deleted';
			}
			
			$data['htmlOptions'] = $userPrefs['felamimail']['htmlOptions'];
			
			// where is the trash folder
			$data['trash_folder']		= $userPrefs['felamimail']['trashFolder'];
			if(!empty($userPrefs['felamimail']['sentFolder']))
			{
				$data['sent_folder']		= $userPrefs['felamimail']['sentFolder'];
				$data['sentFolder']		= $userPrefs['felamimail']['sentFolder'];
			}
			$data['refreshTime'] 		= $userPrefs['felamimail']['refreshTime'];

			if (!empty($data['trash_folder'])) 
				$data['move_to_trash'] 	= True;
			if (!empty($data['sent_folder'])) 
				$data['move_to_sent'] 	= True;
			$data['signature']		= $userPrefs['felamimail']['email_sig'];

			#_debug_array($data);
			return $data;
		}
}