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

class mail_msg_wrappers extends mail_msg_base
{

  // =====  INTERFACE FUNCTIONS AND/OR  WRAPPER FUNCTIONS =====
	/* * * * * * * * * * * * * * * * *
	* Wrapper functions to be called as "public" functions
	* Hides the implementation details from the calling process
	* Provides most args to the dcom class from variables which class msg processed and set
	* Sometimes returns processed data ready to be used for display or information
	* Discussion: Why Wrap Here?
	* Answer: because once the msg class opens a mailsvr_stream, that will be the only stream
	* that instance of the class will have, so WHY keep supplying it as an arg EVERY time?
	* Also, same for the "msgnum", unless you are looping thru a message list, you are 
	* most likely concerned with only ONE message, and the variable would be the MIME part therein
	* * * * * * * * * * * * * * * * */


// ====  Functions For Getting Information About A Message  ====
	/*!
	@function phpgw_fetchstructure
	@abstract wrapper for IMAP_FETSCSTRUCTURE, phpgw supplies the nedessary stream arg
	@param $msg_number : integer
	@result returns the IMAP_FETSCSTRUCTURE data
	@discussion Wrapper supplies the needed mailsvr_stream arg to IMAP_FETSCSTRUCTURE
	The data communications object (class mail_dcom) is supplied by the class
	*/
	function phpgw_fetchstructure($msg_number='')
	{
		if ($msg_number == '')
		{
			$msg_number = $this->msgnum;
		}

		return $this->dcom->fetchstructure($this->mailsvr_stream, $msg_number);
	}

	/*!
	@function phpgw_header
	@abstract wrapper for IMAP_HEADER, phpgw supplies the nedessary stream arg and mail_dcom reference
	@param $msg_number : integer
	@result returns the php IMAP_HEADER data
	@discussion Wrapper supplies the needed mailsvr_stream arg to IMAP_HEADER
	The data communications object (class mail_dcom) is supplied by the class
	*/
	function phpgw_header($msg_number='')
	{
		if ($msg_number == '')
		{
			$msg_number = $this->msgnum;
		}

		// Message Information: THE MESSAGE'S HEADERS RETURNED AS A STRUCTURE
		return $this->dcom->header($this->mailsvr_stream, $msg_number);
	}

	function phpgw_fetchheader($msg_number='')
	{
		if ($msg_number == '')
		{
			$msg_number = $this->msgnum;
		}

		// Message Information: THE MESSAGE'S HEADERS RETURNED RAW (no processing)
		return $this->dcom->fetchheader($this->mailsvr_stream, $msg_number);
	}

	function phpgw_get_flag($flag='')
	{
		// sanity check
		if ($flag == '')
		{
			return '';
		}
		else
		{
			return $this->dcom->get_flag($this->mailsvr_stream,$this->msgnum,$flag);
		}
	}


// ====  Functions For Getting A Message Or A Part (MIME Part) Of A Message  ====
	function phpgw_body()
	{
		return $this->dcom->get_body($this->mailsvr_stream, $this->msgnum);
	}

	function phpgw_fetchbody($part_num_mime='', $flags='')
	{
		return $this->dcom->fetchbody($this->mailsvr_stream, $this->msgnum, $part_num_mime, $flags);
	}


// =====  Functions For Getting Information About A Folder  =====
	// returns an array of integers which are refer to all the messages in a folder ("INBOX") sorted and ordered
	// any integer in this array can be used to request that specific message from the server
	/*!
	@function get_message_list
	@abstract wrapper for IMAP_SORT, sorts a folder in the desired way, then get a list of all message, as integer message numbers
	@param none
	@result returns an array of integers which are message numbers referring to messages in the corrent folder
	@discussion use these message numbers to request mode detailed information for a message, or the message itself.
	Sort and Order is applied by the class, so the calling process does not need to specify sorting here
	The data communications object (class mail_dcom) is supplied by the class
	*/
	function get_message_list()
	{
		$msg_array = array();
		$msg_array = $this->dcom->sort($this->mailsvr_stream, $this->sort, $this->order);
		return $msg_array;
	}

	/*!
	@function get_folder_size
	@abstract uses IMAP_MAILBOXMSGINFO but returns only the size element
	@param none
	@result integer : returns the SIZE element of the php IMAP_MAILBOXMSGINFO data
	@discussion used only if the total size of a folder is desired, which takes time for the server to return
	The other data IMAP_MAILBOXMSGINFO returns (if size is NOT needed) is obtainable
	from "folder_status_info" more quickly and wth less load to the IMAP server
	The data communications object (class mail_dcom) and mailsvr_stream are supplied by the class
	*/
	function get_folder_size()
	{
		$mailbox_detail = $this->dcom->mailboxmsginfo($this->mailsvr_stream);
		return $mailbox_detail->Size;
	}

	// ALIAS for folder_status_info() , for backward compatibility
	function new_message_check()
	{
		return $this->folder_status_info();
	}

	/*!
	@function folder_status_info
	@abstract wrapper for IMAP_STATUS, get status info for the current folder, with emphesis on reporting to user about new messages
	@param none
	@result returns an associative array  with 5 named elements:
		result['is_imap'] boolean - pop3 server do not know what is "new" or not, IMAP servers do
		result['folder_checked'] string - the folder checked, as processed by the msg class, which may have done a lookup on the folder name
		result['alert_string'] string - lang'd string to show the user about status of new messages in this folder
		result['number_new'] integer - for IMAP: the number "recent" and/or "unseen"messages; for POP3: the total number of messages
		result['number_all'] integer - for IMAP and POP3: the total number messages in the folder
	@discussion gives user friendly "alert_string" element to show the user, info is for what ever folder the msg
		class is currently logged into, you may want to apply PHP function "number_format()" to
		the integers after you have done any math code and befor eyou display them to the user, it adds the thousands comma
	*/
	function folder_status_info()
	{
		// initialize return structure
		$return_data = Array();
		$return_data['is_imap'] = False;
		$return_data['folder_checked'] = $this->folder;
		$return_data['alert_string'] = '';
		$return_data['number_new'] = 0;
		$return_data['number_all'] = 0;

		$server_str = $this->get_mailsvr_callstr();
		$mailbox_status = $this->dcom->status($this->mailsvr_stream,$server_str.$this->folder,SA_ALL);

		if (($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] == 'imap')
		|| ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] == 'imaps'))
		{
			$return_data['is_imap'] = True;
			$return_data['number_new'] = $mailbox_status->unseen;
			$return_data['number_all'] = $mailbox_status->messages;
			if ($mailbox_status->unseen == 1) 
			{
				$return_data['alert_string'] .= lang('You have 1 new message!');
			}
			if ($mailbox_status->unseen > 1) 
			{
				$return_data['alert_string'] .= lang('You have x new messages!',$mailbox_status->unseen);
			}
			if ($mailbox_status->unseen == 0) 
			{
				$return_data['alert_string'] .= lang('You have no new messages');
			}
		}
		else
		{
			$return_data['is_imap'] = False;
			// pop3 does not know what is "new" or not
			$return_data['number_new'] = $mailbox_status->messages;
			$return_data['number_all'] = $mailbox_status->messages;
			if ($mailbox_status->messages > 0) 
			{
				$return_data['alert_string'] .= lang('You have messages!');
			}
			elseif ($mailbox_status->messages == 0)
			{
				$return_data['alert_string'] .= lang('You have no new messages');
			}
			else
			{
				$return_data['alert_string'] .= lang('error');
			}
		}
		return $return_data;
	}


	function phpgw_createmailbox($folder)
	{
		return $this->dcom->createmailbox($this->mailsvr_stream, $folder);
	}

	function phpgw_deletemailbox($folder)
	{
		return $this->dcom->deletemailbox($this->mailsvr_stream, $folder);
	}

	function phpgw_renamemailbox($folder_old,$folder_new)
	{
		return $this->dcom->renamemailbox($GLOBALS['phpgw']->msg->mailsvr_stream, $folder_old, $folder_new);
	}

	function phpgw_append($folder = "Sent", $message, $flags = "")
	{
		//$debug_append = True;
		$debug_append = False;

		if ($debug_append) { echo 'append: folder: '.$folder.'<br>'; }

		$server_str = $this->get_mailsvr_callstr();

		// ---  does the target folder actually exist ?  ---
		// strip {server_str} string if it's there
		$folder = $this->ensure_no_brackets($folder);
		// attempt to find a folder match in the lookup list
		$official_folder_long = $this->folder_lookup('', $folder);
		  if ($debug_append) { echo 'append: official_folder_long: '.$official_folder_long.'<br>'; }
		if ($official_folder_long != '')
		{
			$havefolder = True;
		}
		else
		{
			$havefolder = False;
		}

		if ($havefolder == False)
		{
			// add whatever namespace we believe should exist
			// (remember the lookup failed, so we have to guess here)
			$folder_long = $this->get_folder_long($folder);
			// create the specified target folder so it will exist
			//$this->createmailbox($stream,"$server_str"."$folder_long");
			$this->phpgw_createmailbox("$server_str"."$folder_long");
			// try again to get the real long folder name of the just created trash folder
			$official_folder_long = $this->folder_lookup('', $folder);
			// did the folder get created and do we now have the official full name of that folder?
			if ($official_folder_long != '')
			{
				$havefolder = True;
			}
		}

		// at this point we've tries 2 time to obtain the "server approved" long name for the target folder
		// even tries creating it if necessary
		// if we have the name, append the message to that folder
		if (($havefolder == True)
		&& ($official_folder_long != ''))
		{
			return $this->dcom->append($this->mailsvr_stream, "$server_str"."$official_folder_long", $message, $flags);
		}
		else
		{
			// we do not have the official long folder name for the target folder
			// we can NOT append the message to a folder name we are not SURE is corrent
			// it will fail  HANG the browser for a while
			// so just SKIP IT
			return False;
		}
	}

	function phpgw_mail_move($msg_list,$mailbox)
	{
		return $this->dcom->mail_move($this->mailsvr_stream,$msg_list,$mailbox);
	}

	function phpgw_expunge()
	{
		$this->dcom->expunge($GLOBALS['phpgw']->msg->mailsvr_stream);
	}


	function phpgw_delete($msg_num,$flags="", $currentfolder="") 
	{
		//$this->dcom->delete($GLOBALS['phpgw']->msg->mailsvr_stream, $GLOBALS['phpgw']->msg->args['msglist'][$i],"",$GLOBALS['phpgw']->msg->folder);

		if ((isset($GLOBALS['phpgw_info']['user']['preferences']['email']['use_trash_folder']))
		&& ($GLOBALS['phpgw_info']['user']['preferences']['email']['use_trash_folder']))
		{
			$trash_folder_long = $this->get_folder_long($GLOBALS['phpgw_info']['user']['preferences']['email']['trash_folder_name']);
			$trash_folder_short = $this->get_folder_short($GLOBALS['phpgw_info']['user']['preferences']['email']['trash_folder_name']);
			if ($currentfolder != '')
			{
				$currentfolder_short = $this->get_folder_short($currentfolder);
			}
			// if we are deleting FROM the trash folder, we do a straight delete
			if ($currentfolder_short == $trash_folder_short)
			{
				//return imap_delete($stream,$msg_num);
				return $this->dcom->delete($GLOBALS['phpgw']->msg->mailsvr_stream, $msg_num);
			}
			else
			{
				// does the trash folder actually exist ?
				$official_trash_folder_long = $this->folder_lookup('', $GLOBALS['phpgw_info']['user']['preferences']['email']['trash_folder_name']);
				if ($official_trash_folder_long != '')
				{
					$havefolder = True;
				}
				else
				{
					$havefolder = False;
				}

				if (!$havefolder)
				{
					// create the Trash folder so it will exist (Netscape does this too)
					$server_str = $this->get_mailsvr_callstr();
					//$this->createmailbox($stream,$server_str .$trash_folder_long);
					$this->phpgw_createmailbox("$server_str"."$trash_folder_long");
					// try again to get the real long folder name of the just created trash folder
					$official_trash_folder_long = $this->folder_lookup('', $GLOBALS['phpgw_info']['user']['preferences']['email']['trash_folder_name']);
					// did the folder get created and do we now have the official full name of that folder?
					if ($official_trash_folder_long != '')
					{
						$havefolder = True;
					}
				}

				// at this point we've tries 2 time to obtain the "server approved" long name for the trash folder
				// even tries creating it if necessary
				// if we have the name, do the move to the trash folder
				if ($havefolder)
				{
					//return imap_mail_move($stream,$msg_num,$official_trash_folder_long);
					return $this->phpgw_mail_move($msg_num,$official_trash_folder_long);
				}
				else
				{
					// we do not have the trash official folder name, but we have to do something
					// can't just leave the mail sitting there
					// so just straight delete the message
					//return imap_delete($stream,$msg_num);
					return $this->dcom->delete($GLOBALS['phpgw']->msg->mailsvr_stream, $msg_num);
				}
			}
		}
		else
		{
			//return imap_delete($stream,$msg_num);
			return $this->dcom->delete($GLOBALS['phpgw']->msg->mailsvr_stream, $msg_num);
		}
	}
}  // end class mail_msg_wrappers
?>
