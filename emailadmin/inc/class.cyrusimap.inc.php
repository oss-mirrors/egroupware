<?php
	/***************************************************************************\
	* EGroupWare - EMailAdmin                                                   *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; version 2 of the License.                       *
	\***************************************************************************/
	/* $Id$ */
	
	include_once(EGW_SERVER_ROOT."/emailadmin/inc/class.defaultimap.inc.php");
	
	class cyrusimap extends defaultimap
	{
		#function cyrusimap()
		#{
		#}
		
		function addAccount($_hookValues)
		{
			if($this->profileData['imapEnableCyrusAdmin'] != 'yes' ||
			   empty($this->profileData['imapAdminUsername']) || 
			   empty($this->profileData['imapAdminPW']) ) {
				return false;
			}
			
			#_debug_array($_hookValues);
			$username 	= $_hookValues['account_lid'];
			$userPassword	= $_hookValues['new_passwd'];
			
			#_debug_array($this->profileData);
			$imapAdminUsername	= $this->profileData['imapAdminUsername'];
			$imapAdminPW		= $this->profileData['imapAdminPW'];

			
			// create the mailbox
			if($mbox = @imap_open ($this->getMailboxString(), $imapAdminUsername, $imapAdminPW))
			{
				$list = imap_getmailboxes($mbox, $this->getMailboxString(), "INBOX");
				$delimiter = isset($list[0]->delimiter) ? $list[0]->delimiter : '.';
				// create the users folders

				$folderNames = array(
					'user'.$delimiter.$username ,
					'user'.$delimiter.$username.$delimiter.'Trash' ,
					'user'.$delimiter.$username.$delimiter.'Sent'
				);
	
				foreach($folderNames as $mailBoxName)
				{
					if(imap_createmailbox($mbox,imap_utf7_encode("{".$this->profileData['imapServer']."}$mailBoxName")))
					{
						if(!imap_setacl($mbox, $mailBoxName, $username, "lrswipcda"))
						{
							# log error message
						}
					}
				}
				imap_close($mbox);
			}
			else
			{
				#_debug_array(imap_errors());
				return false;
			}
			
			// subscribe to the folders
			if($mbox = @imap_open($this->getMailboxString(), $username, $userPassword))
			{
				imap_subscribe($mbox,$this->getMailboxString('INBOX'));
				imap_subscribe($mbox,$this->getMailboxString('INBOX.Sent'));
				imap_subscribe($mbox,$this->getMailboxString('INBOX.Trash'));
				imap_close($mbox);
			}
			else
			{
				# log error message
			}
		}
		
		function deleteAccount($_hookValues)
		{
			if($this->profileData['imapEnableCyrusAdmin'] != 'yes' ||
			   empty($this->profileData['imapAdminUsername']) || 
			   empty($this->profileData['imapAdminPW']) ) {
				return false;
			}
			
			$username		= $_hookValues['account_lid'];
		
			$imapAdminUsername	= $this->profileData['imapAdminUsername'];
			$imapAdminPW		= $this->profileData['imapAdminPW'];

			if($mbox = @imap_open($this->getMailboxString(), $imapAdminUsername, $imapAdminPW))
			{
				$list = imap_getmailboxes($mbox, $this->getMailboxString(), "INBOX");
				$delimiter = isset($list[0]->delimiter) ? $list[0]->delimiter : '.';
				
				$mailBoxName = 'user'.$delimiter.$username;
				// give the admin account the rights to delete this mailbox
				if(imap_setacl($mbox, $mailBoxName, $imapAdminUsername, "lrswipcda"))
				{
					if(imap_deletemailbox($mbox,
						imap_utf7_encode("{".$this->profileData['imapServer']."}$mailBoxName")))
					{
						return true;
					}
					else
					{
						// not able to delete mailbox
						return false;
					}
				}
				else
				{
					// not able to set acl
					return false;
				}
			}
			else
			{
				// imap open failed
				return false;
			}
		}

		function updateAccount($_hookValues)
		{
			if($this->profileData['imapEnableCyrusAdmin'] != 'yes' ||
			   empty($this->profileData['imapAdminUsername']) || 
			   empty($this->profileData['imapAdminPW']) ) {
				return false;
			}
			
			$username 	= $_hookValues['account_lid'];
			if(isset($_hookValues['new_passwd']))
				$userPassword	= $_hookValues['new_passwd'];
			
			#_debug_array($this->profileData);
			$imapAdminUsername	= $this->profileData['imapAdminUsername'];
			$imapAdminPW		= $this->profileData['imapAdminPW'];

			// create the mailbox
			if($mbox = @imap_open ($this->getMailboxString(), $imapAdminUsername, $imapAdminPW))
			{
				$list = imap_getmailboxes($mbox, $this->getMailboxString(), "INBOX");
				$delimiter = isset($list[0]->delimiter) ? $list[0]->delimiter : '.';
				// create the users folders
				
				if($_hookValues['account_lid'] != $_hookValues['old_loginid']){
					@imap_renamemailbox($mbox, $this->getMailboxString('user'.$delimiter.$_hookValues['old_loginid']), $this->getMailboxString('user'.$delimiter.$username));

					if(strpos($_hookValues['account_lid'],'.') && function_exists('imap_getacl')) {
						// this is a hack for some broken cyrus imap server versions
						// after the account rename to l.kneschke for example, the acl got renamed to l^kneschke
						// which is wrong! also the acl need to be renamed to l.kneschke too
						// l^kneschke is only the name for the folder in the filesystem
						// we search for broken acl entries and replace them with the correct ones
						$list = imap_list($mbox, $this->getMailboxString('user'.$delimiter.$username), '*');
						foreach($list as $longMailboxName) {
							$shortMailboxName = preg_replace("/{.*}/",'',$longMailboxName);
							$currentACL = imap_getacl ($mbox, $shortMailboxName);
							foreach((array)$currentACL as $accountName => $acl) {
								$pos = strpos($accountName, '^');
								if($pos !== false) {
									imap_setacl ($mbox, $shortMailboxName, $accountName, "");
									imap_setacl ($mbox, $shortMailboxName, $_hookValues['account_lid'], $acl);
								}
							}
						}
					}
				}

				$folderNames = array(
					'user'.$delimiter.$username ,
					'user'.$delimiter.$username.$delimiter.'Trash' ,
					'user'.$delimiter.$username.$delimiter.'Sent'
				);
	
				// create the users folders
				foreach($folderNames as $mailBoxName)
				{
					if(imap_createmailbox($mbox,imap_utf7_encode("{".$this->profileData['imapServer']."}$mailBoxName")))
					{
						if(!imap_setacl($mbox, $mailBoxName, $username, "lrswipcda"))
						{
							# log error message
						}
					}
				}
				imap_close($mbox);
			}
			else
			{
				return false;
			}
			
			// we can only subscribe to the folders, if we have the users password
			if(isset($_hookValues['new_passwd']))
			{
				if($mbox = @imap_open($this->getMailboxString(), $username, $userPassword))
				{
					imap_subscribe($mbox,$this->getMailboxString('INBOX'));
					imap_subscribe($mbox,$this->getMailboxString('INBOX' .$delimiter. 'Sent'));
					imap_subscribe($mbox,$this->getMailboxString('INBOX' .$delimiter. 'Trash'));
					imap_close($mbox);
				}
				else
				{
					# log error message
				}
			}
		}
	}
?>
