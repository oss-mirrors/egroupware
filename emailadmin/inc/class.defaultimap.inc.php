<?php
	/***************************************************************************\
	* EGroupWare - EMailAdmin                                                   *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	include_once(EGW_SERVER_ROOT."/emailadmin/inc/class.imap_client.inc.php");
	
	define('IMAP_NAMESPACE_PERSONAL', 'personal');
	define('IMAP_NAMESPACE_OTHERS'	, 'others');
	define('IMAP_NAMESPACE_SHARED'	, 'shared');
	define('IMAP_NAMESPACE_ALL'	, 'all');

	class defaultimap
	{
		// password for imap admin account
		var $adminPassword;
		
		// username for imap admin account
		var $adminUsername;
		
		// enable encryption
		var $encryption;
		
		// address/name of the incoming server
		var $host;
		
		// password to login into incoming server
		var $password;
		
		// port of the incoming server
		var $port = 143;

		// username to login into incoming server
		var $username;

		// domainname to use for vmailmgr logins
		var $domainname = false;

		// validate certificate
		var $validatecert;
		
		// mailbox delimiter
		var $mailboxDelimiter = '/';

		// mailbox prefix
		var $mailboxPrefix = '~/mail';

		// is mbstring support available
		var $mbAvailable;
		
		/**
		 * Mailboxes which get automatic created for new accounts (INBOX == '')
		 *
		 * @var array
		 */
		var $createMailboxes = array('','Sent','Trash','Drafts','Junk');
		var $imapLoginType,$defaultDomain;
		
		function defaultimap() {
			if (function_exists('mb_convert_encoding')) $this->mbAvailable = TRUE;
			
			$this->restoreSessionData();
		}
		
		function closeConnection() {
			if(is_resource($this->mbox)) {
				imap_close($this->mbox);
			}
		}
		
		function addAccount($_hookValues)
		{
			return true;
		}

		function updateAccount($_hookValues)
		{
			return true;
		}

		function deleteAccount($_hookValues)
		{
			return true;
		}
		
		function encodeFolderName($_folderName)
		{
			if($this->mbAvailable) {
				return mb_convert_encoding($_folderName, "UTF7-IMAP", $GLOBALS['egw']->translation->charset());
			}

			// if not
			// we can encode only from ISO 8859-1
			return imap_utf7_encode($_folderName);
		}
		
		function getCapabilities() {
			if(!is_array($this->sessionData['capabilities'][$this->host])) {
				return false;
			}
			
			return $this->sessionData['capabilities'][$this->host];
		}
		
		function getDelimiter() {
			return isset($this->sessionData['delimiter'][$this->host]) ? $this->sessionData['delimiter'][$this->host] : $this->mailboxDelimiter;
		}
		
		/**
		 * Create mailbox string from given mailbox-name and user-name
		 *
		 * @param string $_folderName='' 
		 * @return string utf-7 encoded (done in getMailboxName)
		 */
		function getConnectionString() {

			if($this->encryption && $this->validatecert) {
				$connectionString = sprintf("{%s:%s/imap/ssl}",
					$this->host,
					$this->port);
			} elseif($this->encryption) {
				// don't check cert
				$connectionString = sprintf("{%s:%s/imap/ssl/novalidate-cert}",
					$this->host,
					$this->port);
			} else {
				// no tls
				$connectionString = sprintf("{%s:%s/imap/notls}",
					$this->host,
					$this->port);
			}

			return $connectionString;
		}

		/**
		 * Create mailbox string from given mailbox-name and user-name
		 *
		 * @param string $_folderName='' 
		 * @return string utf-7 encoded (done in getMailboxName)
		 */
		function getMailboxName($_username, $_folderName = '') {
			if(!$othersNameSpace = $this->getNameSpace(IMAP_NAMESPACE_OTHERS)) {
				return false;
			}
			
			$mailboxName = $othersNameSpace['name'] . $_username. (!empty($_folderName) ? $this->getDelimiter() . $_folderName : '');

			return $mailboxName;
		}

		/**
		 * Create mailbox string from given mailbox-name and user-name
		 *
		 * @param string $_folderName='' 
		 * @return string utf-7 encoded (done in getMailboxName)
		 */
		function getMailboxString($_folderName = '') {
			$mailboxString = $this->getConnectionString() . $_folderName;

			return $this->encodeFolderName($mailboxString);
		}

		/**
		 * Create mailbox string from given mailbox-name and user-name
		 *
		 * @param string $_folderName='' 
		 * @return string utf-7 encoded (done in getMailboxName)
		 */
		function getUserMailboxString($_username, $_folderName = '') {
			if(!$mailboxName = $this->getUserMailboxName($_username, $_folderName)) {
				return false;
			}
			
			$mailboxString = $this->getConnectionString() . $mailboxName;

			return $this->encodeFolderName($mailboxString);
		}

		function getNameSpace($_nameSpace) {
			if($this->isAdminConnection) {
				$username = $this->adminUsername;
			} else {
				$username = $this->loginName;
			}
			if(!is_array($this->sessionData['nameSpace'][$this->host][$username])) {
				return false;
			}

			switch($_nameSpace) {
				case IMAP_NAMESPACE_OTHERS:
					if(isset($this->sessionData['nameSpace'][$this->host][$username]['othersNameSpace'])) {
						return $this->sessionData['nameSpace'][$this->host][$username]['othersNameSpace'];
					}
					break;

				case IMAP_NAMESPACE_PERSONAL:
					if(isset($this->sessionData['nameSpace'][$this->host][$username]['personalNameSpace'])) {
						return $this->sessionData['nameSpace'][$this->host][$username]['personalNameSpace'];
					}
					break;

				case IMAP_NAMESPACE_SHARED:
					if(isset($this->sessionData['nameSpace'][$this->host][$username]['sharedNameSpace'])) {
						return $this->sessionData['nameSpace'][$this->host][$username]['sharedNameSpace'];
					}
					break;

				default:
					return $this->sessionData['nameSpace'][$this->host][$username];
					break;
			}
			
			// namespace not found
			return false;
		}
		
		function getQuota($_folderName) {
			if(!is_resource($this->mbox)) {
				$this->openConnection();
			}
			
			if(function_exists('imap_get_quotaroot') && $this->supportsCapability('QUOTA')) {
				$quota = @imap_get_quotaroot($this->mbox, $this->encodeFolderName($_folderName));
				if(is_array($quota) && isset($quota['STORAGE'])) {
					return $quota['STORAGE'];
				}
			} 

			return false;
		}
		
		function getQuotaByUser($_username) {
			if(function_exists('imap_get_quotaroot')) {
				if(!$this->isAdminConnection && is_resource($this->mbox)) {
					$this->closeConnection();
				}
			
				if(!is_resource($this->mbox)) {
					// create a admin connection
					$this->openConnection(0, true);
				}
			
				if($this->supportsCapability('QUOTA')) {
					if($othersNameSpace = $this->getNameSpace(IMAP_NAMESPACE_OTHERS)) {
						$quota = @imap_get_quota($this->mbox, $othersNameSpace['name'].$_username);
						
						if(is_array($quota) && isset($quota['STORAGE'])) {
							return $quota['STORAGE'];
						}
					}
				}
			} 

			return false;
		}
		
		function getUserData($_username) {
			$userData = array();

			if($quota = $this->getQuotaByUser($_username)) {
				$userData['quotaLimit'] = $quota['limit'] / 1024;
			}
			
			return $userData;
		}
		
		function openConnection($_options=0, $_adminConnection=false) {
			if(!function_exists('imap_open')) {
				return lang('This PHP has no IMAP support compiled in!!');
			}

			if($_adminConnection) {
				$folderName	= '';
				$username	= $this->adminUsername;
				$password	= $this->adminPassword;
				$options	= '';
				$this->isAdminConnection = true;
			} else {
				$folderName	= $_folderName;
				$username	= $this->loginName;
				$password	= $this->password;
				$options	= $_options;
				$this->isAdminConnection = false;
			}

			$mailboxString = $this->getMailboxString();

			if(!$this->mbox = imap_open ($mailboxString, $username, $password, $options)) {
				return PEAR::raiseError(imap_last_error(), 'horde.error');
			} else {
				if(!isset($this->sessionData['capabilities'][$this->host]) ||
					!isset($this->sessionData['nameSpace'][$this->host][$username]) ||
					!isset($this->sessionData['delimiter'][$this->host])) {

					$imapClient = CreateObject('emailadmin.imap_client',$this->host, $this->port, ($this->encryption ? 'ssl' : ''));
					$imapClient->login($username, $password);
					$this->sessionData['capabilities'][$this->host] = $imapClient->_capability;

					if(isset($this->sessionData['capabilities'][$this->host]['NAMESPACE'])) {
						$nameSpace = $imapClient->namespace();
					
						// try to find the find the delimiter
						foreach($nameSpace as $singleNameSpace) {
							if(isset($singleNameSpace['delimiter'])) {
								$this->sessionData['delimiter'][$this->host] = $singleNameSpace['delimiter'];
							}
							switch($singleNameSpace['type']) {
								case 'personal':
									$this->sessionData['nameSpace'][$this->host][$username]['personalNameSpace'] = $singleNameSpace;
									break;
								case 'others':
									$this->sessionData['nameSpace'][$this->host][$username]['othersNameSpace'] = $singleNameSpace;
									break;
								case 'shared':
									$this->sessionData['nameSpace'][$this->host][$username]['sharedNameSpace'] = $singleNameSpace;
									break;
							}
						}
					}
					
					if(!isset($this->sessionData['nameSpace'][$this->host][$username]['personalNameSpace'])) {
						$this->sessionData['nameSpace'][$this->host][$username]['personalNameSpace'] = array(
							'name'		=> 'INBOX/',
							'delimiter'	=> '/',
							'type'		=> 'personal',
						);
					}
					
					$imapClient->logout();
					$this->saveSessionData();
				}

				return $this->mbox;
			}
			
		}		
		
		function restoreSessionData() {
			$this->sessionData = $GLOBALS['egw']->session->appsession('imap_session_data');
		}
		
		function saveSessionData() {
			$GLOBALS['egw']->session->appsession('imap_session_data','',$this->sessionData);
		}
		
		function setUserData($_username, $_quota) {
			return true;
		}

		function supportsCapability($_capability) {
			return isset($this->sessionData['capabilities'][$this->host][$_capability]) ? true : false;
		}
	}
?>
