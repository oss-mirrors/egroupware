<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail Message Processing Functions                             *
  * http://www.phpgroupware.org                                              *
  */
  /**************************************************************************\
  * phpGroupWare API - E-Mail Message Processing Functions                         *
  * This file written by Angelo Tony Puglisi (Angles) <angles@phpgroupware.org>      *
  * Handles specific operations in manipulating email messages                         *
  * Copyright (C) 2001 Angelo Tony Puglisi (Angles)                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  /* $Id$ */

  class mail_msg extends mail_msg_base
  {

	function all_folders_listbox($mailbox,$pre_select="",$skip="",$indicate_new=false)
	{
		global $phpgw, $phpgw_info;

		// init some important variables
		$outstr = '';
		//$unseen_prefix = ' &lt;';
		//$unseen_suffix = ' new&gt;';	
		//$unseen_prefix = ' &#091;';
		//$unseen_suffix = ' new&#093;';
		//$unseen_prefix = ' &#040;';
		//$unseen_suffix = ' new&#041;';
		//$unseen_prefix = ' &#045; ';
		//$unseen_suffix = ' new';
		//$unseen_prefix = ' &#045;';
		//$unseen_suffix = '&#045;';	
		//$unseen_prefix = '&nbsp;&nbsp;&#040;';
		//$unseen_suffix = ' new&#041;';
		//$unseen_prefix = '&nbsp;&nbsp;&#091;';
		//$unseen_suffix = ' new&#093;';
		$unseen_prefix = '&nbsp;&nbsp;&#060;';
		$unseen_suffix = ' new&#062;';

		if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"])
		{
			while($pref = each($phpgw_info["user"]["preferences"]["nntp"]))
			{
				$phpgw->db->query("SELECT name FROM newsgroups WHERE con=".$pref[0]);
				while($phpgw->db->next_record())
				{
					$outstr = $outstr .'<option value="' . urlencode($phpgw->db->f("name")) . '">' . $phpgw->db->f("name")
					  . '</option>';
				}
			}
		}
		elseif (($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'pop3')
		    && ($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'pop3s'))
		{
			// Establish Email Server Connectivity Conventions
			$server_str = $this->get_mailsvr_callstr();
			$name_space = $this->get_mailsvr_namespace();
			$delimiter = $this->get_mailsvr_delimiter();
			if ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
			{
				$mailboxes = $phpgw->dcom->listmailbox($mailbox, $server_str, "$name_space" ."$delimiter" ."*");
			}
			else
			{
				$mailboxes = $phpgw->dcom->listmailbox($mailbox, $server_str, "$name_space" ."*");
			}

			// sort folder names 
			if (gettype($mailboxes) == 'array')
			{
				sort($mailboxes);
			}

			if($mailboxes)
			{
				$num_boxes = count($mailboxes);
				if ($name_space != 'INBOX')
				{
					// UWash for example, we must FORCE it to look at the INBOX 
					$outstr = $outstr .'<option value="INBOX">INBOX';
					if ($indicate_new)
					{
						$mailbox_status = $phpgw->dcom->status($mailbox,$server_str . 'INBOX',SA_UNSEEN);
						if ($mailbox_status->unseen > 0)
						{
							$outstr = $outstr . $unseen_prefix . $mailbox_status->unseen . $unseen_suffix;
						}
					}
					$outstr = $outstr . "</option>\r\n"; 
				}
				for ($i=0; $i<$num_boxes;$i++)
				{
					/*
					if (($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
					&& (strstr($mailboxes[$i],"/.")) )
					{
						// {serverstring}~/. indicates this is a hidden file in the users home directory
						// $server_str."/."
						// actually, ANY pattern matching "/." for UWash is NOT an MBOX
						// DO NOTHING - this is not an MBOX file
					}
					else
					*/
					if ($this->is_imap_folder($mailboxes[$i]))
					{
						$folder_short = $this->get_folder_short($mailboxes[$i]);
						if ($folder_short == $pre_select)
						{
							$sel = ' selected';
						}
						else
						{
							$sel = '';
						}
						if ($folder_short != $skip)
						{
							$outstr = $outstr .'<option value="' .urlencode($folder_short) .'"'.$sel.'>' .$folder_short;
							// do we show the number of new (unseen) messages for this folder
							if (($indicate_new)
							&& ($this->care_about_unseen($folder_short)))
							{
								$mailbox_status = $phpgw->dcom->status($mailbox,$mailboxes[$i],SA_UNSEEN);
								if ($mailbox_status->unseen > 0)
								{
									$outstr = $outstr . $unseen_prefix . $mailbox_status->unseen . $unseen_suffix;
								}
							}
							$outstr = $outstr . "</option>\r\n";
						}
					}
				}
			}
			else
			{
				$outstr = $outstr .'<option value="INBOX">INBOX</option>';
			}
		}
		return $outstr;
	}


  }
  // end class mail_msg
?>