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
			$userPassword	= $_hookValues['new_passwd'];

			$this->closeConnection();
			
			// we need a admin connection
			$this->openConnection(0,true);
			
			$folderNames = array(
				'user'. $this->mailboxDelimiter .$username,
				'user'. $this->mailboxDelimiter .$username . $this->mailboxDelimiter .'Trash',
				'user'. $this->mailboxDelimiter .$username . $this->mailboxDelimiter .'Sent',
			);
			
			// create the mailbox
			if(is_resource($this->mbox)) {
				// create the users folders
				foreach($folderNames as $mailboxName) {
					$mailboxString = $this->getMailboxString($mailboxName);
					$mailboxName = $this->encodeFolderName($mailboxName);
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
				imap_subscribe($mbox,$this->getMailboxString('INBOX'));
				imap_subscribe($mbox,$this->getMailboxString('INBOX'. $this->mailboxDelimiter .'Trash'));
				imap_subscribe($mbox,$this->getMailboxString('INBOX'. $this->mailboxDelimiter .'Sent'));
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

			$username		= $_hookValues['account_lid'];
		
			if(is_resource($this->mbox)) {
				$mailboxString = $this->getMailboxString('user'. $this->mailboxDelimiter. $username);
				$mailboxName = $this->encodeFolderName('user'. $this->mailboxDelimiter. $username);
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

				$mailboxname = 'user'. $this->mailboxDelimiter. $username;

				if((int)$_quota > 0) {
					// enable quota
					$quota_value = imap_set_quota($this->mbox, $mailboxname, $_quota*1024);
				} else {
					// disable quota
					$quota_value = imap_set_quota($this->mbox, $mailboxname, -1);
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
			#_debug_array($_hookValues);
			$username 	= $_hookValues['account_lid'];
			if(isset($_hookValues['new_passwd'])) {
				$userPassword	= $_hookValues['new_passwd'];
			}

			$this->closeConnection();
			
			// we need a admin connection
			$this->openConnection(0,true);

			$folderNames = array(
				'user'. $this->mailboxDelimiter .$username,
				'user'. $this->mailboxDelimiter .$username . $this->mailboxDelimiter .'Trash',
				'user'. $this->mailboxDelimiter .$username . $this->mailboxDelimiter .'Sent',
			);
			
			// create the mailbox
			if(is_resource($this->mbox)) {
				// create the users folders
				foreach($folderNames as $mailboxName) {
					$mailboxName = $this->getMailboxString($mailboxName);
					if(imap_createmailbox($mbox, $mailboxName)) {
						if(!imap_setacl($mbox, $mailboxName, $username, "lrswipcd")) {
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
					imap_subscribe($mbox,$this->getMailboxString('INBOX'));
					imap_subscribe($mbox,$this->getMailboxString('INBOX'. $this->mailboxDelimiter .'Trash'));
					imap_subscribe($mbox,$this->getMailboxString('INBOX'. $this->mailboxDelimiter .'Sent'));
					imap_close($mbox);
				} else {
					# log error message
				}
			}
		}
	}
?>
