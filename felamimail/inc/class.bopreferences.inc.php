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
			$this->config = CreateObject('phpgwapi.config','felamimail');
			$this->config->read_repository();
			$this->profileID = $this->config->config_data['profileID'];
			
			$this->boemailadmin = CreateObject('emailadmin.bo');
		}
		
		function getPreferences()
		{
			$imapServerTypes = $this->boemailadmin->getIMAPServerTypes();
			$profileData = $this->boemailadmin->getProfile($this->profileID);
			
			#$imapServerTypes[$profileData['imapType']]['protocol'];
			
			#_debug_array($profileData);
			
			$felamimailUserPrefs = $GLOBALS['phpgw_info']['user']['preferences']['felamimail'];
			
			// set values to the global values
			$data['imapServerAddress']	= $profileData['imapServer'];
			$data['key']			= $GLOBALS['phpgw_info']['user']['passwd'];
			if ($profileData['imapLoginType'] == 'vmailmgr')
				$data['username']		= $GLOBALS['phpgw_info']['user']['userid']."@".$profileData['defaultDomain'];
			else
				$data['username']		= $GLOBALS['phpgw_info']['user']['userid'];
			$data['imap_server_type']	= $imapServerTypes[$profileData['imapType']]['protocol'];
			$data['realname']		= $GLOBALS['phpgw_info']['user']['fullname'];
			$data['defaultDomainname']	= $profileData['defaultDomain'];

			$data['smtpServerAddress']	= $profileData['smtpServer'];
			$data['smtpPort']		= $profileData['smtpPort'];

			if(!empty($profileData['organisationName']))
				$data['organizationName']	= $profileData['organisationName'];

			$data['emailAddress']		= $data['username']."@".$profileData['defaultDomain'];
			$data['smtpAuth']		= $profileData['smtpAuth'];
			$data['imapAdminUsername']	= $profileData['imapAdminUsername'];
			$data['imapAdminPW']		= $profileData['imapAdminPW'];

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
			if ($profileData['userDefinedAccounts'] == 'yes' &&
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
			
			if(($profileData['imapTLSEncryption'] == 'yes' ||
				$profileData['imapTLSEncryption'] == 'yes') &&
				empty($profileData['imapPort']))
			{
				$data['imapPort']	= 993;
			}
			else
			{
				$data['imapPort']	= 143;
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