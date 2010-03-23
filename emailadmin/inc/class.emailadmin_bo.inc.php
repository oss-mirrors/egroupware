<?php
	/***************************************************************************\
	* eGroupWare                                                                *
	* http://www.egroupware.org                                                 *
	* http://www.linux-at-work.de                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class emailadmin_bo extends so_sql
	{
		/**
		 * Name of our table
		 */
		const TABLE = 'egw_emailadmin';
		/**
		 * Name of app the table is registered
		 */
		const APP = 'emailadmin';
		/**
		 * Fields that are numeric
		 */
		static $numericfields = array(
			'ea_profile_id',
			'ea_smtp_type',
			'ea_smtp_port',
			'ea_smtp_auth',
			'ea_editforwardingaddress',
			'ea_smtp_ldap_use_default',
			'ea_imap_type',
			'ea_imap_port',
			'ea_imap_login_type',
			'ea_imap_tsl_auth',
			'ea_imap_tsl_encryption',
			'ea_imap_enable_cyrus',
			'ea_imap_enable_sieve',
			'ea_imap_sieve_port',
			'ea_user_defined_identities',
			'ea_user_defined_accounts',
			'ea_imapoldcclient',
			'ea_order',
			'ea_active',
			'ea_group',
			'ea_user',
			'ea_appname',
			'ea_user_defined_signatures',
			);

		static $sessionData = array();
		#var $userSessionData;
		var $LDAPData;

		var $SMTPServerType = array();		// holds a list of config options
		var $IMAPServerType = array();		// holds a list of config options

		var $imapClass;				// holds the imap/pop3 class
		var $smtpClass;				// holds the smtp class


		function __construct($_profileID=-1,$_restoreSesssion=true)
		{

			parent::__construct(self::APP,self::TABLE,null,'',true);

			if (!is_object($GLOBALS['emailadmin_bo']))
			{
				$GLOBALS['emailadmin_bo'] = $this;
			}
			$this->soemailadmin = new emailadmin_so();

			$this->SMTPServerType = array(
				'1' 	=> array(
					'fieldNames'	=> array(
						'smtpServer',
						'smtpPort',
						'smtpAuth',
						'ea_smtp_auth_username',
						'ea_smtp_auth_password',
						'smtpType'
					),
					'description'	=> lang('standard SMTP-Server'),
					'classname'	=> 'defaultsmtp'
				),
				'2' 	=> array(
					'fieldNames'	=> array(
						'smtpServer',
						'smtpPort',
						'smtpAuth',
						'ea_smtp_auth_username',
						'ea_smtp_auth_password',
						'smtpType',
						'editforwardingaddress',
						'smtpLDAPServer',
						'smtpLDAPAdminDN',
						'smtpLDAPAdminPW',
						'smtpLDAPBaseDN',
						'smtpLDAPUseDefault'
					),
					'description'	=> 'Postfix (qmail Schema)',
					'classname'	=> 'postfixldap'
				),
				'3'     => array(
					'fieldNames'    => array(
						'smtpServer',
						'smtpPort',
						'smtpAuth',
						'ea_smtp_auth_username',
						'ea_smtp_auth_password',
						'smtpType',
					),
					'description'   => 'Postfix (inetOrgPerson Schema)',
					'classname'     => 'postfixinetorgperson'
				),
				'4'     => array(
					'fieldNames'    => array(
						'smtpServer',
						'smtpPort',
						'smtpAuth',
						'ea_smtp_auth_username',
						'ea_smtp_auth_password',
						'smtpType',
						'editforwardingaddress',
					),
					'description'   => 'Plesk SMTP-Server (Qmail)',
					'classname'     => 'smtpplesk'
				),
				'5' 	=> array(
					'fieldNames'	=> array(
						'smtpServer',
						'smtpPort',
						'smtpAuth',
						'ea_smtp_auth_username',
						'ea_smtp_auth_password',
						'smtpType',
						'editforwardingaddress',
						'smtpLDAPServer',
						'smtpLDAPAdminDN',
						'smtpLDAPAdminPW',
						'smtpLDAPBaseDN',
						'smtpLDAPUseDefault'
					),
					'description'   => 'Postfix (dbmail Schema)',
					'classname'     => 'postfixdbmailuser'
				),
			);

			$this->IMAPServerType = array(
/*				'1' 	=> array(
					'fieldNames'	=> array(
						'imapServer',
						'imapPort',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient'
					),
					'description'	=> 'standard POP3 server',
					'protocol'	=> 'pop3',
					'classname'	=> 'defaultpop'
				),*/
				'2' 	=> array(
					'fieldNames'	=> array(
						'imapServer',
						'imapPort',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient',
						'imapAuthUsername',
						'imapAuthPassword'
					),
					'description'	=> 'standard IMAP server',
					'protocol'	=> 'imap',
					'classname'	=> 'defaultimap'
				),
				'3' 	=> array(
					'fieldNames'	=> array(
						'imapServer',
						'imapPort',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient',
						'imapEnableCyrusAdmin',
						'imapAdminUsername',
						'imapAdminPW',
						'imapEnableSieve',
						'imapSieveServer',
						'imapSievePort',
						'imapAuthUsername',
						'imapAuthPassword'
					),
					'description'	=> 'Cyrus IMAP Server',
					'protocol'	=> 'imap',
					'classname'	=> 'cyrusimap'
				),
				'4' 	=> array(
					'fieldNames'	=> array(
						'imapServer',
						'imapPort',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient',
						'imapEnableSieve',
						'imapSieveServer',
						'imapSievePort',
						'imapAuthUsername',
						'imapAuthPassword',
					),
					'description'	=> 'DBMail (qmailUser schema)',
					'protocol'	=> 'imap',
					'classname'	=> 'dbmailqmailuser'
				),
				'5'     => array(
					'fieldNames'    => array(
						'imapServer',
						'imapPort',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient',
						'imapAuthUsername',
						'imapAuthPassword'
					),
					'description'   => 'Plesk IMAP Server (Courier)',
					'protocol'      => 'imap',
					'classname'     => 'pleskimap'
				),
				'6' 	=> array(
					'fieldNames'	=> array(
						'imapServer',
						'imapPort',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient',
						'imapEnableSieve',
						'imapSieveServer',
						'imapSievePort',
						'imapAuthUsername',
						'imapAuthPassword'
					),
					'description'	=> 'DBMail (dbmailUser schema)',
					'protocol'	=> 'imap',
					'classname'	=> 'dbmaildbmailuser'
				),
			); 
			if ($_restoreSesssion) // &&  !(is_array(self::$sessionData) && (count(self::$sessionData)>0))  ) 
			{
				$this->restoreSessionData();
			}
			if ($_restoreSesssion===false) // && (is_array(self::$sessionData) && (count(self::$sessionData)>0))  )
			{
				// make sure session data will be created new
				self::$sessionData = array();
				self::saveSessionData();
			}
			#_debug_array(self::$sessionData);	
			if($_profileID >= 0)
			{
				$this->profileID	= $_profileID;

				$this->profileData	= $this->getProfile($_profileID);

				// try autoloading class, if that fails include it from emailadmin
				if (!class_exists($class = $this->IMAPServerType[$this->profileData['imapType']]['classname']))
				{
					include_once(EGW_INCLUDE_ROOT.'/emailadmin/inc/class.'.$class.'.inc.php');
				}
				$this->imapClass	= new $class;

				if (!class_exists($class = $this->SMTPServerType[$this->profileData['smtpType']]['classname']))
				{
					include_once(EGW_INCLUDE_ROOT.'/emailadmin/inc/class.'.$class.'.inc.php');
				}
				$this->smtpClass	= new $class;
			}
		}

		function addAccount($_hookValues)
		{
			if (is_object($this->imapClass))
			{
				#ExecMethod("emailadmin.".$this->imapClass.".addAccount",$_hookValues,3,$this->profileData);
				$this->imapClass->addAccount($_hookValues);
			}

			if (is_object($this->smtpClass))
			{
				#ExecMethod("emailadmin.".$this->smtpClass.".addAccount",$_hookValues,3,$this->profileData);
				$this->smtpClass->addAccount($_hookValues);
			}
			self::$sessionData =array();
			$this->saveSessionData();
		}

		function deleteAccount($_hookValues)
		{
			if (is_object($this->imapClass))
			{
				#ExecMethod("emailadmin.".$this->imapClass.".deleteAccount",$_hookValues,3,$this->profileData);
				$this->imapClass->deleteAccount($_hookValues);
			}

			if (is_object($this->smtpClass))
			{
				#ExecMethod("emailadmin.".$this->smtpClass.".deleteAccount",$_hookValues,3,$this->profileData);
				$this->smtpClass->deleteAccount($_hookValues);
			}
			self::$sessionData = array();
			$this->saveSessionData();
		}

		function getAccountEmailAddress($_accountName, $_profileID)
		{
			$profileData	= $this->getProfile($_profileID);

			#$smtpClass	= $this->SMTPServerType[$profileData['smtpType']]['classname'];
			$smtpClass	= CreateObject('emailadmin.'.$this->SMTPServerType[$profileData['smtpType']]['classname']);

			#return empty($smtpClass) ? False : ExecMethod("emailadmin.$smtpClass.getAccountEmailAddress",$_accountName,3,$profileData);
			return is_object($smtpClass) ?  $smtpClass->getAccountEmailAddress($_accountName) : False;
		}

		function getFieldNames($_serverTypeID, $_class)
		{
			switch($_class)
			{
				case 'imap':
					return $this->IMAPServerType[$_serverTypeID]['fieldNames'];
					break;
				case 'smtp':
					return $this->SMTPServerType[$_serverTypeID]['fieldNames'];
					break;
			}
		}

		function getIMAPServerTypes($extended=true) 
		{
			foreach($this->IMAPServerType as $key => $value) {
				if ($extended)
				{
					$retData[$key]['description']	= $value['description'];
					$retData[$key]['protocol']	= $value['protocol'];
				}
				else
				{
					$retData[$key]	= $value['description'];
				}
			}

			return $retData;
		}

		function getLDAPStorageData($_serverid)
		{
			$storageData = $this->soemailadmin->getLDAPStorageData($_serverid);
			return $storageData;
		}

		function getMailboxString($_folderName)
		{
			if (is_object($this->imapClass))
			{
				return ExecMethod("emailadmin.".$this->imapClass.".getMailboxString",$_folderName,3,$this->profileData);
				return $this->imapClass->getMailboxString($_folderName);
			}
			else
			{
				return false;
			}
		}

		function getProfile($_profileID)
		{
			if (!(is_array(self::$sessionData) && (count(self::$sessionData)>0))) $this->restoreSessionData();
			if (is_array(self::$sessionData) && (count(self::$sessionData)>0) && self::$sessionData['profile'][$_profileID]) {
				#error_log("sessionData Restored for Profile $_profileID <br>");
				return self::$sessionData['profile'][$_profileID]; 
			} 
			$profileData = $this->soemailadmin->getProfileList($_profileID);
			$found = false;
			if (is_array($profileData) && count($profileData))
			{
				foreach($profileData as $n => $data)
				{
					if ($data['ProfileID'] == $_profileID)
					{
						$found = $n;
						break;
					}
				}
			}
			if ($found === false)		// no existing profile selected
			{
				if (is_array($profileData) && count($profileData)) {	// if we have a profile use that
					reset($profileData);
					list($found,$data) = each($profileData);
					$this->profileID = $_profileID = $data['profileID'];
				} elseif ($GLOBALS['egw_info']['server']['smtp_server']) { // create a default profile, from the data in the api config
					$this->profileID = $_profileID = $this->soemailadmin->addProfile(array(
						'description' => $GLOBALS['egw_info']['server']['smtp_server'],
						'defaultDomain' => $GLOBALS['egw_info']['server']['mail_suffix'],
						'organisationName' => '',
						'userDefinedAccounts' => '',
						'userDefinedIdentities' => '',
					),array(
						'smtpServer' => $GLOBALS['egw_info']['server']['smtp_server'],
						'smtpPort' => $GLOBALS['egw_info']['server']['smtp_port'],
						'smtpAuth' => '',
						'smtpType' => '1',
					),array(
						'imapServer' => $GLOBALS['egw_info']['server']['mail_server'] ?
							$GLOBALS['egw_info']['server']['mail_server'] : $GLOBALS['egw_info']['server']['smtp_server'],
						'imapPort' => '143',
						'imapType' => '2',	// imap
						'imapLoginType' => $GLOBALS['egw_info']['server']['mail_login_type'] ?
							$GLOBALS['egw_info']['server']['mail_login_type'] : 'standard',
						'imapTLSEncryption' => '0',
						'imapTLSAuthentication' => '',
						'imapoldcclient' => '',
					));
					$profileData[$found = 0] = array(
						'smtpType' => '1',
						'imapType' => '2',
					);
				}
			}
			$fieldNames = array();
			if (isset($profileData[$found]))
			{
				$fieldNames = array_merge($this->SMTPServerType[$profileData[$found]['smtpType']]['fieldNames'],
					$this->IMAPServerType[$profileData[$found]['imapType']]['fieldNames']);
			}
			$fieldNames[] = 'description';
			$fieldNames[] = 'defaultDomain';
			$fieldNames[] = 'profileID';
			$fieldNames[] = 'organisationName';
			$fieldNames[] = 'userDefinedAccounts';
			$fieldNames[] = 'userDefinedIdentities';
			$fieldNames[] = 'ea_appname';
			$fieldNames[] = 'ea_group';
			$fieldNames[] = 'ea_user';
			$fieldNames[] = 'ea_active';
			$fieldNames[] = 'ea_user_defined_signatures';
			$fieldNames[] = 'ea_default_signature';
			$fieldNames[] = 'ea_stationery_active_templates';

			$profileData = $this->soemailadmin->getProfile($_profileID, $fieldNames);
			$profileData['imapTLSEncryption'] = ($profileData['imapTLSEncryption'] == 'yes' ? 1 : (int)$profileData['imapTLSEncryption']);
			if(strlen($profileData['ea_stationery_active_templates']) > 0)
			{
				$profileData['ea_stationery_active_templates'] = unserialize($profileData['ea_stationery_active_templates']);
			}
			self::$sessionData['profile'][$_profileID] = $profileData;
			$this->saveSessionData();
			return $profileData;
		}

		function getProfileList($_profileID='',$_appName=false,$_groupID=false,$_accountID=false)
		{
			if ($_appName!==false ||$_groupID!==false ||$_accountID!==false) {
				return $this->soemailadmin->getProfileList($_profileID,false,$_appName,$_groupID,$_accountID);
			} else {
				return $this->soemailadmin->getProfileList($_profileID);
			}
		}

		function getSMTPServerTypes()
		{
			foreach($this->SMTPServerType as $key => $value)
			{
				$retData[$key] = $value['description'];
			}
			return $retData;
		}

		function getUserProfile($_appName='', $_groups='')
		{
			if (!(is_array(self::$sessionData) && (count(self::$sessionData)>0))) $this->restoreSessionData();
			if (is_array(self::$sessionData) && count(self::$sessionData)>0 && self::$sessionData['ea_preferences']) 
			{
				//error_log("sessionData Restored for UserProfile<br>");
				return self::$sessionData['ea_preferences']; 
			}
			$appName	= ($_appName != '' ? $_appName : $GLOBALS['egw_info']['flags']['currentapp']);
			if(!is_array($_groups)) {
				// initialize with 0 => means no group id
				$groups = array(0);
				// set the second entry to the users primary group
				$groups[] = $GLOBALS['egw_info']['user']['account_primary_group'];
				$userGroups = $GLOBALS['egw']->accounts->membership($GLOBALS['egw_info']['user']['account_id']);
				foreach((array)$userGroups as $groupInfo) {
					$groups[] = $groupInfo['account_id'];
				}
			} else {
				$groups = $_groups;
			}

			if($data = $this->soemailadmin->getUserProfile($appName, $groups,$GLOBALS['egw_info']['user']['account_id'])) {

				$eaPreferences = CreateObject('emailadmin.ea_preferences');

				// fetch the IMAP / incomming server data
				$icClass = isset($this->IMAPServerType[$data['imapType']]) ? $this->IMAPServerType[$data['imapType']]['classname'] : 'defaultimap';

				$icServer = CreateObject('emailadmin.'.$icClass);
				$icServer->encryption	= ($data['imapTLSEncryption'] == 'yes' ? 1 : (int)$data['imapTLSEncryption']);
				$icServer->host		= $data['imapServer'];
				$icServer->port 	= $data['imapPort'];
				$icServer->validatecert	= $data['imapTLSAuthentication'] == 'yes';
				$icServer->username 	= $GLOBALS['egw_info']['user']['account_lid'];
				$icServer->password	= $GLOBALS['egw_info']['user']['passwd'];
				// restore the default loginType and check if there are forced/predefined user access Data ($imapAuthType may be set to admin)
				list($data['imapLoginType'],$imapAuthType) = explode('#',$data['imapLoginType'],2);
				$icServer->loginType	= $data['imapLoginType'];
				$icServer->domainName	= $data['defaultDomain'];
				$icServer->loginName 	= $data['imapLoginType'] == 'standard' ? $GLOBALS['egw_info']['user']['account_lid'] : $GLOBALS['egw_info']['user']['account_lid'].'@'.$data['defaultDomain'];
				$icServer->enableCyrusAdmin = ($data['imapEnableCyrusAdmin'] == 'yes');
				$icServer->adminUsername = $data['imapAdminUsername'];
				$icServer->adminPassword = $data['imapAdminPW'];
				$icServer->enableSieve	= ($data['imapEnableSieve'] == 'yes');
				$icServer->sievePort	= $data['imapSievePort'];
				if ($imapAuthType == 'admin') {
					if (!empty($data['imapAuthUsername'])) $icServer->username = $icServer->loginName = $data['imapAuthUsername'];
					if (!empty($data['imapAuthPassword'])) $icServer->password = $data['imapAuthPassword'];
				}
				if ($imapAuthType == 'email' || $icServer->loginType == 'email') {
					$icServer->username = $icServer->loginName = $GLOBALS['egw_info']['user']['account_email'];
				}
				$eaPreferences->setIncomingServer($icServer);

				// fetch the SMTP / outgoing server data
				$ogClass = isset($this->SMTPServerType[$data['smtpType']]) ? $this->SMTPServerType[$data['smtpType']]['classname'] : 'defaultsmtp';
				if (!class_exists($ogClass))
				{
					include_once(EGW_INCLUDE_ROOT.'/emailadmin/inc/class.'.$ogClass.'.inc.php');
				}
				$ogServer = new $ogClass($icServer->domainName);
				$ogServer->host		= $data['smtpServer'];
				$ogServer->port		= $data['smtpPort'];
				$ogServer->editForwardingAddress = ($data['editforwardingaddress'] == 'yes');
				$ogServer->smtpAuth	= $data['smtpAuth'] == 'yes';
				if($ogServer->smtpAuth) {
					if(!empty($data['ea_smtp_auth_username'])) {
						$ogServer->username 	= $data['ea_smtp_auth_username'];
					} else {
						// if we use special logintypes for IMAP, we assume this to be used for SMTP too
						if ($imapAuthType == 'email' || $icServer->loginType == 'email') {
							$ogServer->username     = $GLOBALS['egw_info']['user']['account_email'];
						} elseif ($icServer->loginType == 'vmailmgr') {
							$ogServer->username     = $GLOBALS['egw_info']['user']['account_lid'].'@'.$icServer->domainName;
						} else {
							$ogServer->username 	= $GLOBALS['egw_info']['user']['account_lid'];
						}
					}
					if(!empty($data['ea_smtp_auth_password'])) {
						$ogServer->password     = $data['ea_smtp_auth_password'];
					} else {
						$ogServer->password     = $GLOBALS['egw_info']['user']['passwd'];
					}
				}

				$eaPreferences->setOutgoingServer($ogServer);

				foreach($ogServer->getAccountEmailAddress($GLOBALS['egw_info']['user']['account_lid']) as $emailAddresses)
				{
					$identity = CreateObject('emailadmin.ea_identity');
					$identity->emailAddress	= $emailAddresses['address'];
					$identity->realName	= $emailAddresses['name'];
					$identity->default	= ($emailAddresses['type'] == 'default');
					$identity->organization	= $data['organisationName'];

					$eaPreferences->setIdentity($identity);
				}

				$eaPreferences->userDefinedAccounts		= ($data['userDefinedAccounts'] == 'yes');
				$eaPreferences->userDefinedIdentities     = ($data['userDefinedIdentities'] == 'yes');
				$eaPreferences->ea_user_defined_signatures	= ($data['ea_user_defined_signatures'] == 'yes');
				$eaPreferences->ea_default_signature		= $data['ea_default_signature'];
				if(strlen($data['ea_stationery_active_templates']) > 0)
				{
					$eaPreferences->ea_stationery_active_templates = unserialize($data['ea_stationery_active_templates']);
				}
				self::$sessionData['ea_preferences'] = $eaPreferences;
				$this->saveSessionData();
				return $eaPreferences;
			}

			return false;
		}

		function getUserData($_accountID)
		{

			if($userProfile = $this->getUserProfile('felamimail')) {
				$icServer = $userProfile->getIncomingServer(0);
				if(is_a($icServer, 'defaultimap') && $username = $GLOBALS['egw']->accounts->id2name($_accountID)) {
					$icUserData = $icServer->getUserData($username);
				}

				$ogServer = $userProfile->getOutgoingServer(0);
				if(is_a($ogServer, 'defaultsmtp')) {
					$ogUserData = $ogServer->getUserData($_accountID);
				}

				return $icUserData + $ogUserData;

			}

			return false;
		}

		function restoreSessionData()
		{
			$GLOBALS['egw_info']['flags']['autoload'] = array(__CLASS__,'autoload');

			//echo function_backtrace()."<br>";
			//unserializing the sessiondata, since they are serialized for objects sake
			self::$sessionData = (array) unserialize($GLOBALS['egw']->session->appsession('session_data','emailadmin'));
		}

		/**
		* Autoload classes from emailadmin, 'til they get autoloading conform names
		*
		* @param string $class
		*/
		static function autoload($class)
		{
			if (file_exists($file=EGW_INCLUDE_ROOT.'/emailadmin/inc/class.'.$class.'.inc.php'))
			{
				include_once($file);
			}
		}

		function saveSMTPForwarding($_accountID, $_forwardingAddress, $_keepLocalCopy)
		{
			if (is_object($this->smtpClass))
			{
				#$smtpClass = CreateObject('emailadmin.'.$this->smtpClass,$this->profileID);
				#$smtpClass->saveSMTPForwarding($_accountID, $_forwardingAddress, $_keepLocalCopy);
				$this->smtpClass->saveSMTPForwarding($_accountID, $_forwardingAddress, $_keepLocalCopy);
			}

		}

		/**
		 * called by the validation hook in setup
		 *
		 * @param array $settings following keys: mail_server, mail_server_type {IMAP|IMAPS|POP-3|POP-3S},
		 *	mail_login_type {standard|vmailmgr}, mail_suffix (domain), smtp_server, smpt_port, smtp_auth_user, smtp_auth_passwd
		 */
		function setDefaultProfile($settings)
		{
			if (($profiles = $this->soemailadmin->getProfileList(0,true)))
			{
				$profile = array_shift($profiles);
			}
			else
			{
				$profile = array(
					'smtpType' => 1,
					'description' => 'default profile (created by setup)',
					'ea_appname' => '',
					'ea_group' => 0,
					'ea_user' => 0,
					'ea_active' => 1,
				);

                if (empty($settings['mail_server'])) $profile['userDefinedAccounts'] = 'yes';
				if (empty($settings['mail_server'])) $profile['userDefinedIdentities'] == 'yes';
                if (empty($settings['mail_server'])) $profile['ea_user_defined_signatures'] == 'yes';

			}
			foreach($to_parse = array(
				'mail_server' => 'imapServer',
				'mail_server_type' => array(
					'imap' => array(
						'imapType' => 2,
						'imapPort' => 143,
						'imapTLSEncryption' => 0,
					),
					'imaps' => array(
						'imapType' => 2,
						'imapPort' => 993,
						'imapTLSEncryption' => '3',
					),
/*					'pop3' => array(
						'imapType' => 1,
						'imapPort' => 110,
						'imapTLSEncryption' => 0,
					),
					'pop3s' => array(
						'imapType' => 1,
						'imapPort' => 995,
						'imapTLSEncryption' => '1',
					),*/
				),
				'mail_login_type' => 'imapLoginType',
				'mail_suffix' => 'defaultDomain',
				'smtp_server' => 'smtpServer',
				'smpt_port' => 'smtpPort',
				'smtp_auth_user' => 'ea_smtp_auth_username',
				'smtp_auth_passwd' => 'ea_smtp_auth_password',
			) as $setup_name => $ea_name_data)
			{
				if (!is_array($ea_name_data))
				{
					$profile[$ea_name_data] = $settings[$setup_name];
					if ($setup_name == 'smtp_auth_user') $profile['stmpAuth'] = !empty($settings['smtp_auth_user']);
				}
				else
				{
					foreach($ea_name_data as $setup_val => $ea_data)
					{
						if ($setup_val == $settings[$setup_name])
						{
							foreach($ea_data as $var => $val)
							{
								if ($var != 'imapType' || $val != 2 || $profile[$var] < 3)	// dont kill special imap server types
								{
									$profile[$var] = $val;
								}
							}
							break;
						}
					}
				}
			}
			// merge the other not processed values unchanged
			$profile = array_merge($profile,array_diff_assoc($settings,$to_parse));

			$this->soemailadmin->updateProfile($profile);
			self::$sessionData['profile'] = array();
			$this->saveSessionData();
			//echo "<p>EMailAdmin profile update: ".print_r($profile,true)."</p>\n"; exit;
		}

		function saveProfile($_globalSettings, $_smtpSettings, $_imapSettings)
		{
			if(!isset($_imapSettings['imapTLSAuthentication'])) {
				$_imapSettings['imapTLSAuthentication'] = true;
			}

			if(is_array($_globalSettings['ea_stationery_active_templates']) && count($_globalSettings['ea_stationery_active_templates']) > 0)
			{
				$_globalSettings['ea_stationery_active_templates'] = serialize($_globalSettings['ea_stationery_active_templates']);
			}
			else
			{
				$_globalSettings['ea_stationery_active_templates'] = null;
			}

			if(!isset($_globalSettings['profileID'])) {
				$_globalSettings['ea_order'] = count($this->getProfileList()) + 1;
				$this->soemailadmin->addProfile($_globalSettings, $_smtpSettings, $_imapSettings);
			} else {
				$this->soemailadmin->updateProfile($_globalSettings, $_smtpSettings, $_imapSettings);
			}
			$all = $_globalSettings+$_smtpSettings+$_imapSettings;
			if (!$all['ea_user'] && !$all['ea_group'] && !$all['ea_application'])	// standard profile update eGW config
			{
				$new_config = array();
				foreach(array(
					'imapServer'    => 'mail_server',
					'imapType'      => 'mail_server_type',
					'imapLoginType' => 'mail_login_type',
					'defaultDomain' => 'mail_suffix',
					'smtpServer'    => 'smtp_server',
					'smtpPort'      => 'smpt_port',
				)+($all['smtpAuth'] ? array(
					'ea_smtp_auth_username' => 'smtp_auth_user',
					'ea_smtp_auth_password' => 'smtp_auth_passwd',
				) : array()) as $ea_name => $config_name)
				{
					if (isset($all[$ea_name]))
					{
						if ($ea_name != 'imapType')
						{
							$new_config[$config_name] = $all[$ea_name];
						}
						else	// imap type
						{
							$new_config[$config_name] = ($all['imapType'] == 1 ? 'pop3' : 'imap').($all['imapTLSEncryption'] ? 's' : '');
						}
					}
				}
				if (count($new_config))
				{
					$config = CreateObject('phpgwapi.config','phpgwapi');

					foreach($new_config as $name => $value)
					{
						$config->save_value($name,$value,'phpgwapi');
					}
					//echo "<p>eGW configuration update: ".print_r($new_config,true)."</p>\n";
				}
			}
			self::$sessionData = array();
			$this->saveSessionData();
		}

		function saveSessionData()
		{
			// serializing the session data, for the sake of objects
			$GLOBALS['egw']->session->appsession('session_data','emailadmin',serialize(self::$sessionData));
			#$GLOBALS['egw']->session->appsession('user_session_data','',$this->userSessionData);
		}

		function saveUserData($_accountID, $_formData) {

			if($userProfile = $this->getUserProfile('felamimail')) {
				$ogServer = $userProfile->getOutgoingServer(0);
				if(is_a($ogServer, 'defaultsmtp')) {
					$ogServer->setUserData($_accountID,
						(array)$_formData['mailAlternateAddress'],
						(array)$_formData['mailForwardingAddress'],
						$_formData['deliveryMode'],
						$_formData['accountStatus'],
						$_formData['mailLocalAddress']
					);
				}

				$icServer = $userProfile->getIncomingServer(0);
				if(is_a($icServer, 'defaultimap') && $username = $GLOBALS['egw']->accounts->id2name($_accountID)) {
					$icServer->setUserData($username, $_formData['quotaLimit']);
				}

				// calling a hook to allow other apps to monitor the changes
				$_formData['account_id'] = $_accountID;
				$_formData['location'] = 'editaccountemail';
				$GLOBALS['egw']->hooks->process($_formData);

				return true;
				self::$sessionData = array();
				$this->saveSessionData();
			}

			return false;
		}

		function setOrder($_order) {
			if(is_array($_order)) {
				$this->soemailadmin->setOrder($_order);
			}
			self::$sessionData = array();
			$this->saveSessionData();
		}

		function updateAccount($_hookValues) {
			if (is_object($this->imapClass)) {
				#ExecMethod("emailadmin.".$this->imapClass.".updateAccount",$_hookValues,3,$this->profileData);
				$this->imapClass->updateAccount($_hookValues);
			}

			if (is_object($this->smtpClass)) {
				#ExecMethod("emailadmin.".$this->smtpClass.".updateAccount",$_hookValues,3,$this->profileData);
				$this->smtpClass->updateAccount($_hookValues);
			}
			self::$sessionData = array();
			$this->saveSessionData();
		}
	}
