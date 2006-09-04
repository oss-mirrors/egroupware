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
		
		function defaultimap($imapLoginType=null,$defaultDomain=null) {
			// use the given login-type and domain if specified or the values from the global eGW configuration if not
			$this->imapLoginType = $imapLoginType ? $imapLoginType : $GLOBALS['egw_info']['server']['mail_login_type'];
			$this->defaultDomain = $defaultDomain ? $defaultDomain : $GLOBALS['egw_info']['server']['mail_suffix'];

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
		 * @param string $username='' if given use the global name, eg. 'user.username[@domain]' as prefix
		 * @return string utf-7 encoded (done in getMailboxName)
		 */
		function getMailboxString($_folderName='',$username='') {
			#$mailboxPrefix = ($_folderName == 'INBOX' ? '' : $this->mailboxPrefix.$this->mailboxDelimiter);
			
			#if($_folderName == 'INBOX') {
				$mailboxPrefix = '';
			#} else {
			#	$mailboxPrefix = (!empty($this->mailboxPrefix) ? $this->mailboxPrefix.$this->mailboxDelimiter : '');
			#}
			
			$_folderName = $this->getMailboxName($_folderName,$username);

			if($this->encryption && $this->validatecert) {
				$mailboxString = sprintf("{%s:%s/imap/ssl}%s%s",
					$this->host,
					$this->port,
					$mailboxPrefix,
					$_folderName);
			} elseif($this->encryption) {
				// don't check cert
				$mailboxString = sprintf("{%s:%s/imap/ssl/novalidate-cert}%s%s",
					$this->host,
					$this->port,
					$mailboxPrefix,
					$_folderName);
			} else {
				// no tls
				$mailboxString = sprintf("{%s:%s/imap/notls}%s%s",
					$this->host,
					$this->port,
					$mailboxPrefix,
					$_folderName);
			}

			return $mailboxString;
		}

		/**
		 * Create mailbox name from given mailbox-name and optional user-name
		 * 
		 * Examples:
		 * getMailboxName('','hugo') --> ''
		 * getMailboxName('INBOX','hugo') --> 'user.hugo'
		 * getMailboxName('INBOX.Trash,'hugo') --> 'user.hugo.Trash'
		 * getMailboxName('INBOX') --> 'INBOX'
		 * getMailboxName('INBOX.Trash') --> 'INBOX.Trash'
		 *
		 * @param string $_folderName='' 
		 * @param string $username='' if given use the global name, eg. 'user.username' instead of 'INBOX'
		 * @return string utf-7 encoded(!)
		 */
		function getMailboxName($_folderName='',$username='') {
			if ($username)
			{
				$_folderName = str_replace('INBOX','user'.$this->mailboxDelimiter.$username,$_folderName);
			}
			//echo "<p align=right>getMailboxName('$_folderName','$username')='$folder'</p>\n";
			return $this->encodeFolderName($folder);
		}

		function getNameSpace($_nameSpace) {
			if(!is_array($this->sessionData['nameSpace'][$this->host][$this->username])) {
				return false;
			}

			switch($_nameSpace) {
				case IMAP_NAMESPACE_OTHERS:
				case IMAP_NAMESPACE_PERSONAL:
				case IMAP_NAMESPACE_SHARED:
					foreach($this->sessionData['nameSpace'][$this->host][$this->username] as $singleNameSpace) {
						if($singleNameSpace['type'] == $_nameSpace) {
							return $singleNameSpace;
						}
					}
					break;

				default:
					return $this->sessionData['nameSpace'][$this->host][$this->username];
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
		function getUserData($_uidnumber) {
			$userData = array();
			
			if($quota = $this->getQuota('INBOX')) {
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
			} else {
				$folderName	= $_folderName;
				$username	= $this->username;
				$password	= $this->password;
				$options	= $_options;
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
					$this->sessionData['nameSpace'][$this->host][$username] = $imapClient->namespace();
					
					// try to find the find the delimiter
					foreach($this->sessionData['nameSpace'][$this->host][$username] as $singleNameSpace) {
						if(isset($singleNameSpace['delimiter'])) {
							$this->sessionData['delimiter'][$this->host] = $singleNameSpace['delimiter'];
							break;
						}
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
		
		function setUserData($_uidnumber, $_quota) {
			return true;
		}

		function supportsCapability($_capability) {
			return isset($this->sessionData['capabilities'][$this->host][$_capability]) ? true : false;
		}
	}
?>
