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

	/**
	 * This class holds all information about the imap connection.
	 * This is the base class for all other imap classes.
	 *
	 */
	class defaultimap
	{
		/**
		 * the password to be used for admin connections
		 *
		 * @var string
		 */
		var $adminPassword;
		
		/**
		 * the username to be used for admin connections
		 *
		 * @var string
		 */
		var $adminUsername;
		
		/**
		 * enable encryption
		 *
		 * @var bool
		 */
		var $encryption;
		
		/**
		 * the hostname/ip address of the imap server
		 *
		 * @var string
		 */
		var $host;
		
		/**
		 * the password for the user
		 *
		 * @var string
		 */
		var $password;
		
		/**
		 * the port of the imap server
		 *
		 * @var integer
		 */
		var $port = 143;

		/**
		 * the username
		 *
		 * @var string
		 */
		var $username;

		/**
		 * the domainname to be used for vmailmgr logins
		 *
		 * @var string
		 */
		var $domainname = false;

		/**
		 * validate ssl certificate
		 *
		 * @var bool
		 */
		var $validatecert;
		
		/**
		 * the mailbox delimiter
		 *
		 * @var string
		 */
		var $mailboxDelimiter = '/';

		/**
		 * the mailbox prefix. maybe used by uw-imap only?
		 *
		 * @var string
		 */
		var $mailboxPrefix = '~/mail';

		/**
		 * is the mbstring extension available
		 *
		 * @var unknown_type
		 */
		var $mbAvailable;
		
		/**
		 * Mailboxes which get automatic created for new accounts (INBOX == '')
		 *
		 * @var array
		 */
		var $createMailboxes = array('','Sent','Trash','Drafts','Junk');
		var $imapLoginType;
		var $defaultDomain;
		
		/**
		 * the construtor
		 *
		 * @return void
		 */
		function defaultimap() {
			if (function_exists('mb_convert_encoding')) $this->mbAvailable = TRUE;
			
			$this->restoreSessionData();
		}
		
		/**
		 * closes the current imap connection
		 *
		 */
		function closeConnection() {
			if(is_resource($this->mbox)) {
				imap_close($this->mbox);
			}
		}
		
		/**
		 * adds a account on the imap server
		 *
		 * @param array $_hookValues
		 * @return bool true on success, false on failure
		 */
		function addAccount($_hookValues)
		{
			return true;
		}

		/**
		 * updates a account on the imap server
		 *
		 * @param array $_hookValues
		 * @return bool true on success, false on failure
		 */
		function updateAccount($_hookValues)
		{
			return true;
		}

		/**
		 * deletes a account on the imap server
		 *
		 * @param array $_hookValues
		 * @return bool true on success, false on failure
		 */
		function deleteAccount($_hookValues)
		{
			return true;
		}
		
		/**
		 * converts a foldername from current system charset to UTF7
		 *
		 * @param string $_folderName
		 * @return string the encoded foldername
		 */
		function encodeFolderName($_folderName)
		{
			if($this->mbAvailable) {
				return mb_convert_encoding($_folderName, "UTF7-IMAP", $GLOBALS['egw']->translation->charset());
			}

			// if not
			// we can encode only from ISO 8859-1
			return imap_utf7_encode($_folderName);
		}
		
		/**
		 * returns the supported capabilities of the imap server
		 * return false if the imap server does not support capabilities
		 * 
		 * @return array the supported capabilites
		 */
		function getCapabilities() {
			if(!is_array($this->sessionData['capabilities'][$this->host])) {
				return false;
			}
			
			return $this->sessionData['capabilities'][$this->host];
		}
		
		/**
		 * return the delimiter used by the current imap server
		 *
		 * @return string the delimimiter
		 */
		function getDelimiter() {
			return isset($this->sessionData['delimiter'][$this->host]) ? $this->sessionData['delimiter'][$this->host] : $this->mailboxDelimiter;
		}
		
		/**
		 * Create mailbox string
		 *
		 * @return string the connectionstring
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

		/**
		 * get list of namespaces
		 *
		 * @param integer $_nameSpace
		 * @return array array containing information about namespace
		 */
		function getNameSpace($_nameSpace) {
			// this solves a PHP4 problem
			if(empty($this->sessionData)) {
				$this->restoreSessionData();
			}
			
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
		
		/**
		 * returns the quota for given foldername
		 * gets quota for the current user only
		 *
		 * @param string $_folderName
		 * @return string the current quota for this folder
		 */
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
		
		/**
		 * return the quota for another user
		 * used by admin connections only
		 *
		 * @param string $_username
		 * @return string the quota for specified user
		 */
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
		
		/**
		 * returns information about a user
		 * currently only supported information is the current quota
		 *
		 * @param string $_username
		 * @return array userdata
		 */
		function getUserData($_username) {
			$userData = array();

			if($quota = $this->getQuotaByUser($_username)) {
				$userData['quotaLimit'] = $quota['limit'] / 1024;
			}
			
			return $userData;
		}
		
		/**
		 * opens a connection to a imap server
		 *
		 * @param integer $_options
		 * @param bool $_adminConnection create admin connection if true
		 * @return resource the imap connection
		 */
		function openConnection($_options=0, $_adminConnection=false) {
			if(!function_exists('imap_open')) {
				return lang('This PHP has no IMAP support compiled in!!');
			}

			if($_adminConnection) {
				$username	= $this->adminUsername;
				$password	= $this->adminPassword;
				$options	= '';
				$this->isAdminConnection = true;
			} else {
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
		
		/**
		 * restore session variable
		 *
		 */
		function restoreSessionData() {
			$this->sessionData = $GLOBALS['egw']->session->appsession('imap_session_data');
		}
		
		/**
		 * save session variable
		 *
		 */
		function saveSessionData() {
			$GLOBALS['egw']->session->appsession('imap_session_data','',$this->sessionData);
		}
		
		/**
		 * set userdata
		 *
		 * @param string $_username username of the user
		 * @param int $_quota quota in bytes
		 * @return bool true on success, false on failure
		 */
		function setUserData($_username, $_quota) {
			return true;
		}

		/**
		 * check if imap server supports given capability
		 *
		 * @param string $_capability the capability to check for
		 * @return bool true if capability is supported, false if not
		 */
		function supportsCapability($_capability) {
			return isset($this->sessionData['capabilities'][$this->host][$_capability]) ? true : false;
		}
	}
?>
