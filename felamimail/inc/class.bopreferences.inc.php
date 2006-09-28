<?php
	/***************************************************************************\
	* eGroupWare - FeLaMiMail                                                   *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	require_once(EGW_INCLUDE_ROOT.'/felamimail/inc/class.sopreferences.inc.php');
	 
	class bopreferences extends sopreferences
	{
		var $public_functions = array
		(
			'getPreferences'	=> True,
		);
		
		// stores the users profile
		var $profileData;
		
		function bopreferences()
		{
			parent::sopreferences();
			$this->boemailadmin =& CreateObject('emailadmin.bo');
		}

		// get user defined accounts		
		function getAccountData(&$_profileData)
		{
			if(!is_a($_profileData, 'ea_preferences'))
				die(__FILE__.': '.__LINE__);
			$accountData = parent::getAccountData($GLOBALS['egw_info']['user']['account_id']);

			// create some usefull defaults
/*			if(count($accountData) == 0)
			{
				$profileData = $this->boemailadmin->getUserProfile('felamimail');
				_debug_array($profileData);exit;
				$accountData = array(
					'0'	=> array(
						'active'		=> false,
						'realname'		=> $GLOBALS['egw_info']['user']['fullname'],
						'organization'		=> $profileData['organisationName'],
						'emailaddress'		=> $GLOBALS['egw_info']['user']['email'],
						'ic_hostname'		=> $profileData['imapServer'],
						'ic_port'		=> ($profileData['imapPort'] ? $profileData['imapPort'] : 143),
						'ic_username'		=> '',
						'ic_password'		=> '',
						'ic_encryption'		=> false,
						'ic_validatecertificate' => false,
						'og_hostname'		=> $profileData['smtpServer'],
						'og_port'		=> ($profileData['smtpPort'] ? $profileData['smtpPort'] : 25),
						'og_smtpauth'		=> false,
						'og_username'		=> '',
						'og_password'		=> '',
					)
				);
			}
*/
			// currently we use only the first profile available
			$accountData = array_shift($accountData);

			$icServer =& CreateObject('emailadmin.defaultimap');
			$icServer->encryption	= (bool)$accountData['ic_encryption'];
			$icServer->host		= $accountData['ic_hostname'];
			$icServer->port 	= $accountData['ic_port'];
			$icServer->validatecert	= (bool)$accountData['ic_validatecertificate'];
			$icServer->username 	= $accountData['ic_username'];
			$icServer->password	= $accountData['ic_password'];

			$ogServer =& CreateObject('emailadmin.defaultsmtp');
			$ogServer->host		= $accountData['og_hostname'];
			$ogServer->port		= $accountData['og_port'];
			$ogServer->smtpAuth	= (bool)$accountData['og_smtpauth'];
			if($ogServer->smtpAuth) {
				$ogServer->username 	= $accountData['og_username'];
				$ogServer->password 	= $accountData['og_password'];
			}

			$identity =& CreateObject('emailadmin.ea_identity');
			$identity->emailAddress	= $accountData['emailaddress'];
			$identity->realName	= $accountData['realname'];
			$identity->default	= true;
			$identity->organization	= $accountData['organization'];

			$isActive = (bool)$accountData['active'];

			return array('icServer' => $icServer, 'ogServer' => $ogServer, 'identity' => $identity, 'active' => $isActive);
		}
		
		function getListOfSignatures() {
			return parent::getListOfSignatures($GLOBALS['egw_info']['user']['account_id']);
		}
		
		function getPreferences()
		{
			if(!is_a($this->profileData,'ea_preferences ')) {

				$imapServerTypes	= $this->boemailadmin->getIMAPServerTypes();
				$profileData		= $this->boemailadmin->getUserProfile('felamimail');

				if(!is_a($profileData, 'ea_preferences') || !is_a($profileData->ic_server[0], 'defaultimap')) {
					return false;
				}

				if($profileData->userDefinedAccounts) {
					// get user defined accounts
					$accountData = $this->getAccountData($profileData);
					
					if($accountData['active']) {
					
						// replace the global defined IMAP Server
						if(is_a($accountData['icServer'],'defaultimap'))
							$profileData->setIncomingServer($accountData['icServer'],0);
					
						// replace the global defined SMTP Server
						if(is_a($accountData['ogServer'],'defaultsmtp'))
							$profileData->setOutgoingServer($accountData['ogServer'],0);
					
						// replace the global defined identity
						if(is_a($accountData['identity'],'ea_identity'))
							$profileData->setIdentity($accountData['identity'],0);
					}
				}
				
				$GLOBALS['egw']->preferences->read_repository();
				$userPrefs = $GLOBALS['egw_info']['user']['preferences']['felamimail'];
				if(empty($userPrefs['deleteOptions']))
					$userPrefs['deleteOptions'] = 'mark_as_deleted';
				
				#$data['trash_folder']		= $userPrefs['felamimail']['trashFolder'];
				if (!empty($userPrefs['trash_folder'])) 
					$userPrefs['move_to_trash'] 	= True;
				if (!empty($userPrefs['sent_folder'])) 
					$userPrefs['move_to_sent'] 	= True;
				$userPrefs['signature']		= $userPrefs['email_sig'];
				
	 			unset($userPrefs['email_sig']);
 			
 				$profileData->setPreferences($userPrefs);

				#_debug_array($profileData);exit;
			
				$this->profileData = $profileData;
				
				#_debug_array($this->profileData);
			}

			return $this->profileData;
		}
		
		function getSignature($_signatureID) {
			return parent::getSignature($GLOBALS['egw_info']['user']['account_id'], $_signatureID);
		}
		
		function deleteSignatures($_signatureID) {
			if(!is_array($_signatureID)) {
				return false;
			}
			return parent::deleteSignatures($GLOBALS['egw_info']['user']['account_id'], $_signatureID);
		}
		
		function saveAccountData($_icServer, $_ogServer, $_identity) {
			parent::saveAccountData($GLOBALS['egw_info']['user']['account_id'], $_icServer, $_ogServer, $_identity);
		}
		
		function saveSignature($_signatureID, $_description, $_signature) {
			return parent::saveSignature($GLOBALS['egw_info']['user']['account_id'], $_signatureID, $_description, $_signature);
		}

		function setProfileActive($_status) {
			parent::setProfileActive($GLOBALS['egw_info']['user']['account_id'], $_status);
		}
	}
?>