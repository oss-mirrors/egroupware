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
	
	include_once(PHPGW_SERVER_ROOT."/emailadmin/inc/class.imapBaseClass.inc.php");
	
	class cyrusimap extends imapBaseClass
	{
		#function cyrusimap()
		#{
		#}
		
		function addAccount($_username, $_password)
		{
			#_debug_array($this->profileData);
			$imapAdminUsername	= $this->profileData['imapAdminUsername'];
			$imapAdminPW		= $this->profileData['imapAdminPW'];

			$folderNames = array(
				"user.$_username",
				"user.$_username.Trash",
				"user.$_username.Sent"
			);
			
			// create the mailbox
			if($mbox = imap_open ($this->getMailboxString(), $imapAdminUsername, $imapAdminPW))
			{
				// create the users folders
				foreach($folderNames as $mailBoxName)
				{
					if(imap_createmailbox($mbox,imap_utf7_encode("{".$this->profileData['imapServer']."}$mailBoxName")))
					{
						if(!imap_setacl($mbox, $mailBoxName, $_username, "lrswipcd"))
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
			
			// subscribe to the folders
			if($mbox = @imap_open($this->getMailboxString(), $_username, $_password))
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
		
		function deleteAccount($_username)
		{
			$imapAdminUsername	= $this->profileData['imapAdminUsername'];
			$imapAdminPW		= $this->profileData['imapAdminPW'];

			if($mbox = @imap_open($this->getMailboxString(), $imapAdminUsername, $imapAdminPW))
			{
				$mailBoxName = "user.$_username";
				// give the admin account the rights to delete this mailbox
				if(imap_setacl($mbox, $mailBoxName, $imapAdminUsername, "lrswipcda"))
				{
				}
				if(imap_deletemailbox($mbox,imap_utf7_encode("{127.0.0.1}$mailBoxName")))
				{
				}
			}
			else
			{
				return false;
			}
		}

		function updateAccount($_username)
		{
			$imapAdminUsername	= $this->profileData['imapAdminUsername'];
			$imapAdminPW		= $this->profileData['imapAdminPW'];

			$folderNames = array(
				"user.$_username",
				"user.$_username.Trash",
				"user.$_username.Sent"
			);
			
			// create the mailbox
			if($mbox = imap_open ($this->getMailboxString(), $imapAdminUsername, $imapAdminPW))
			{
				// create the users folders
				foreach($folderNames as $mailBoxName)
				{
					if(imap_createmailbox($mbox,imap_utf7_encode("{".$this->profileData['imapServer']."}$mailBoxName")))
					{
						if(!imap_setacl($mbox, $mailBoxName, $_username, "lrswipcd"))
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
			
		}
	}
?>
