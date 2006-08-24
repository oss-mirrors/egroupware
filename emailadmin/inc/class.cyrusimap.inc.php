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
	
	include_once(EGW_SERVER_ROOT."/emailadmin/inc/class.defaultimap.inc.php");
	
	class cyrusimap extends defaultimap
	{
		// mailbox delimiter
		var $mailboxDelimiter = '.';

		// mailbox prefix
		var $mailboxPrefix = '';

		var $enableCyrusAdmin = false;
		
		var $cyrusAdminUsername;
		
		var $cyrusAdminPassword;
		
		var $enableSieve = false;
		
		var $sieveHost;
		
		var $sievePort;
		
		function addAccount($_hookValues) {
			if(!$this->enableCyrusAdmin) {
				return false;
			}
			#_debug_array($_hookValues);
			$username 	= $_hookValues['account_lid'];
			if ($this->imapLoginType == 'vmailmgr' && $this->defaultDomain) $username .= '@' . $this->defaultDomain;
			$userPassword	= $_hookValues['new_passwd'];

			$this->closeConnection();
			
			// we need a admin connection
			$this->openConnection(0,true);
			
			// create the mailbox
			if(is_resource($this->mbox)) {
				// create the users folders
				foreach($this->createMailboxes as $mailboxName) {
					$mailboxName = 'INBOX' . ($mailboxName ? $this->mailboxDelimiter .$mailboxName : '');
					$mailboxString = $this->getMailboxString($mailboxName,$username);
					$mailboxName = $this->getMailboxName($mailboxName,$username);
					if(imap_createmailbox($this->mbox, $mailboxString)) {
						if(!imap_setacl($this->mbox, $mailboxName, $username, "lrswipcda")) {
							# log error message
						}
					}
				}
				$this->closeConnection();
			} else {
				#_debug_array(imap_errors());
				return false;
			}
			
			// subscribe to the folders
			if($mbox = @imap_open($this->getMailboxString(), $username, $userPassword)) {
				foreach($this->createMailboxes as $mailboxName) {
					$mailboxName = 'INBOX' . ($mailboxName ? $this->mailboxDelimiter .$mailboxName : '');
					imap_subscribe($mbox,$this->getMailboxString($mailboxName));
				}
				imap_close($mbox);
			} else {
				# log error message
			}
		}
		
		function deleteAccount($_hookValues)
		{
			if(!$this->enableCyrusAdmin) {
				return false;
			}

			$this->closeConnection();
			
			// we need a admin connection
			$this->openConnection(0,true);

			$username = $_hookValues['account_lid'];
		
			if(is_resource($this->mbox)) {
				$mailboxName = 'INBOX' . ($mailboxName ? $this->mailboxDelimiter .$mailboxName : '');
				$mailboxString = $this->getMailboxString('',$username);
				$mailboxName = $this->getMailboxName('',$username);
				// give the admin account the rights to delete this mailbox
				if(imap_setacl($this->mbox, $mailboxName, $this->adminUsername, 'lrswipcda')) {
					if(imap_deletemailbox($this->mbox, $mailboxString)) {
						$this->closeConnection();
						return true;
					}
				}
			}
			
			$this->closeConnection();
			// imap open failed
			return false;
		}

		function setUserData($_uidnumber, $_quota) {
			if(!$this->enableCyrusAdmin) {
				return false;
			}
			
			$this->closeConnection();
			
			// we need a admin connection
			$this->openConnection(0,true);

			if($username = $GLOBALS['egw']->accounts->id2name($_uidnumber)) {

				$mailboxName = $this->getMailboxName('',$username);

				if((int)$_quota > 0) {
					// enable quota
					$quota_value = imap_set_quota($this->mbox, $mailboxName, $_quota*1024);
				} else {
					// disable quota
					$quota_value = imap_set_quota($this->mbox, $mailboxName, -1);
				}
				$this->closeConnection();
				return true;
			}
			$this->closeConnection();
			return false;
		}

		function updateAccount($_hookValues) {
			if(!$this->enableCyrusAdmin) {
				return false;
			}
			//_debug_array($_hookValues);
			$username 	= $_hookValues['account_lid'];
			if ($this->imapLoginType == 'vmailmgr' && $this->defaultDomain) $username .= '@' . $this->defaultDomain;
			if(isset($_hookValues['new_passwd'])) {
				$userPassword	= $_hookValues['new_passwd'];
			}

			$this->closeConnection();
			
			// we need a admin connection
			$this->openConnection(0,true);

			// create the mailbox
			if(is_resource($this->mbox)) {
				// create the users folders
				foreach($this->createMailboxes as $mailboxName) {
					$mailboxName = 'INBOX' . ($mailboxName ? $this->mailboxDelimiter .$mailboxName : '');
					$mailboxString = $this->getMailboxString($mailboxName,$username);
					$mailboxName = $this->getMailboxName($mailboxName,$username);
					if(imap_createmailbox($this->mbox, $mailboxString)) {
						if(!imap_setacl($this->mbox, $mailboxName, $username, "lrswipcda")) {
							# log error message
						}
					}
				}
				$this->closeConnection();
			} else {
				return false;
			}
			// we can only subscribe to the folders, if we have the users password
			if(isset($_hookValues['new_passwd'])) {
				// subscribe to the folders
				if($mbox = @imap_open($this->getMailboxString(), $username, $userPassword)) {
					foreach($this->createMailboxes as $mailboxName) {
						$mailboxName = 'INBOX' . ($mailboxName ? $this->mailboxDelimiter .$mailboxName : '');
						imap_subscribe($mbox,$this->getMailboxString($mailboxName));
					}
					imap_close($mbox);
				} else {
					# log error message
				}
			}
		}

		/**
		 * Create mailbox name from given mailbox-name and optional user-name
		 * 
		 * Reimplemented to deal with the wired way cyrus 2.2 with virtual domains specifies 
		 * 
		 * Examples:
		 * getMailboxName('','hugo') --> ''
		 * getMailboxName('INBOX','hugo') --> 'user.hugo'
		 * getMailboxName('INBOX.Trash','hugo@domain.com') --> 'user.hugo.Trash@domain.com'
		 * getMailboxName('INBOX') --> 'INBOX'
		 * getMailboxName('INBOX.Trash') --> 'INBOX.Trash'
		 *
		 * @param string $_folderName='' 
		 * @param string $username='' if given use the global name, eg. 'user.username' instead of 'INBOX'
		 * @return string utf-7 encoded(!)
		 */
		function getMailboxName($_folderName='',$username='') {
			$domain = '';
			if ($username)
			{
				if ($this->imapLoginType == 'vmailmgr')
				{
					list($username,$domain) = explode('@',$username);
					if (!$domain) $domain = $this->defaultDomain;
				}
				$_folderName = str_replace('INBOX','user'.$this->mailboxDelimiter.$username,$_folderName);
			}
			// domain has to be behind the regular mailbox name
			if ($domain) $_folderName .= '@'.$domain;

			//echo "<p align=right>getMailboxName('$_folderName','$username')='$folder'</p>\n";
			return $this->encodeFolderName($_folderName);
		}
	}
?>
