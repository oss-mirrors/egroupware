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
		return $this->dcom->renamemailbox($this->mailsvr_stream, $folder_old, $folder_new);
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
		$this->dcom->expunge($this->mailsvr_stream);
	}


	function phpgw_delete($msg_num,$flags="", $currentfolder="") 
	{
		//$this->dcom->delete($this->mailsvr_stream, $this->args['msglist'][$i],"",$this->folder);

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
				return $this->dcom->delete($this->mailsvr_stream, $msg_num);
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
					return $this->dcom->delete($this->mailsvr_stream, $msg_num);
				}
			}
		}
		else
		{
			//return imap_delete($stream,$msg_num);
			return $this->dcom->delete($this->mailsvr_stream, $msg_num);
		}
	}

	/*!
	@function grab_class_args_gpc
	@abstract grab data from $GLOBALS['HTTP_POST_VARS'] and $GLOBALS['HTTP_GET_VARS']
	as necessaey, and fill various class arg variables with the available data
	@param none
	@result none, this is an object call
	@discussion to further seperate the mail functionality from php itself, this function will perform
	the variable handling of the traditional php page view Get Post Cookie (no cookie data used here though)
	The same data could be grabbed from any source, XML-RPC for example, insttead of php's GPC vars,
	so this function could (should) have an equivalent XML-RPC "to handle filling these class variables
	from an alternative source.
	@author	Angles
	@access	Public
	*/
	function grab_class_args_gpc()
	{
		// === SORT/ORDER/START === 
		// if sort,order, and start are sometimes passed as GPC's, if not, default prefs are used
		if (isset($GLOBALS['HTTP_POST_VARS']['sort']))
		{
			$this->args['sort'] = $GLOBALS['HTTP_POST_VARS']['sort'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['sort']))
		{
			$this->args['sort'] = $GLOBALS['HTTP_GET_VARS']['sort'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['order']))
		{
			$this->args['order'] = $GLOBALS['HTTP_POST_VARS']['order'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['order']))
		{
			$this->args['order'] = $GLOBALS['HTTP_GET_VARS']['order'];
		}

		if (isset($GLOBALS['HTTP_POST_VARS']['start']))
		{
			$this->args['start'] = $GLOBALS['HTTP_POST_VARS']['start'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['start']))
		{
			$this->args['start'] = $GLOBALS['HTTP_GET_VARS']['start'];
		}

		// this newsmode thing needs to be further worked out
		if (isset($GLOBALS['HTTP_POST_VARS']['newsmode']))
		{
			$this->args['newsmode'] = $GLOBALS['HTTP_POST_VARS']['newsmode'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['newsmode']))
		{
			$this->args['newsmode'] = $GLOBALS['HTTP_GET_VARS']['newsmode'];
		}

		// === REPORT ON MOVES/DELETES ===
		// ----  td, tm: integer  ----
		// ----  tf: string  ----
		// USAGE:
		//	 td = total deleted ; tm = total moved, tm used with tf, folder messages were moved to
		// (outgoing) action.php: when action on a message is taken, report info is passed in these
		// (in) index.php: here the report is diaplayed above the message list, used to give user feedback
		// generally these are in the URI (GET var, not a form POST var)
		if (isset($GLOBALS['HTTP_POST_VARS']['td']))
		{
			$this->args['td'] = $GLOBALS['HTTP_POST_VARS']['td'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['td']))
		{
			$this->args['td'] = $GLOBALS['HTTP_GET_VARS']['td'];
		}

		if (isset($GLOBALS['HTTP_POST_VARS']['tm']))
		{
			$this->args['tm'] = $GLOBALS['HTTP_POST_VARS']['tm'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['tm']))
		{
			$this->args['tm'] = $GLOBALS['HTTP_GET_VARS']['tm'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['tf']))
		{
			$this->args['tf'] = $GLOBALS['HTTP_POST_VARS']['tf'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['tf']))
		{
			$this->args['tf'] = $GLOBALS['HTTP_GET_VARS']['tf'];
		}

		// === MOVE/DELETE MESSAGE INSTRUCTIONS ===
		// ----  what: string ----
		// USAGE: 
		// (outgoing) index.php: "move", "delall"
		//	used with msglist (see below) an array (1 or more) of message numbers to move or delete
		// (outgoing) message.php: "delete" used with msgnum (see below) what individual message to delete
		// (in) action.php: instruction on what action to preform on 1 or more message(s) (move or delete)
		if (isset($GLOBALS['HTTP_POST_VARS']['what']))
		{
			$this->args['what'] = $GLOBALS['HTTP_POST_VARS']['what'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['what']))
		{
			$this->args['what'] = $GLOBALS['HTTP_GET_VARS']['what'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['tofolder']))
		{
			$this->args['tofolder'] = $GLOBALS['HTTP_POST_VARS']['tofolder'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['tofolder']))
		{
			$this->args['tofolder'] = $GLOBALS['HTTP_GET_VARS']['tofolder'];
		}
		
		// (passed from index.php) this may be an array of numbers if many boxes checked and a move or delete is called
		if (isset($GLOBALS['HTTP_POST_VARS']['msglist']))
		{
			$this->args['msglist'] = $GLOBALS['HTTP_POST_VARS']['msglist'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['msglist']))
		{
			$this->args['msglist'] = $GLOBALS['HTTP_GET_VARS']['msglist'];
		}

		// === INSTRUCTIONS FOR ACTION ON A MESSAGE OR FOLDER ===
		// ----  action: string  ----
		// USAGE:
		// (a) (out and in) folder.php: used with "target_folder" and (for renaming) "source_folder"
		//	instructions to add/delete/rename folders: create(_expert), delete(_expert), rename(_expert)
		//	where "X_expert" indicates do not modify the target_folder, the user know about of namespaces and delimiters
		// (b) compose.php: can be "reply" "replyall" "forward"
		//	passed on to send_message.php
		// (c) send_message.php: when set to "forward" and used with "fwd_proc" instructs on how to construct
		//	the SMTP mail
		if (isset($GLOBALS['HTTP_POST_VARS']['action']))
		{
			$this->args['action'] = $GLOBALS['HTTP_POST_VARS']['action'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['action']))
		{
			$this->args['action'] = $GLOBALS['HTTP_GET_VARS']['action'];
		}

		// === MESSAGE NUMBER AND MIME PART REFERENCES ===
		// msgnum: integer 
		// USAGE:
		// (a) action.php, called from from message.php: used with "what=delete" to indicate a single message for deletion
		// (b) compose.php: indicates the referenced message for reply, replyto, and forward handling
		// (c) get_attach.php: the msgnum of the email that contains the desired body part to get
		if (isset($GLOBALS['HTTP_POST_VARS']['msgnum']))
		{
			$this->args['msgnum'] = $GLOBALS['HTTP_POST_VARS']['msgnum'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['msgnum']))
		{
			$this->args['msgnum'] = $GLOBALS['HTTP_GET_VARS']['msgnum'];
		}
		
		// ----  part_no: string  ----
		// representing a specific MIME part number (example "2.1.2") within a multipart message
		// (a) compose.php: used in combination with msgnum
		// (b) get_attach.php: used in combination with msgnum
		if (isset($GLOBALS['HTTP_POST_VARS']['part_no']))
		{
			$this->args['part_no'] = $GLOBALS['HTTP_POST_VARS']['part_no'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['part_no']))
		{
			$this->args['part_no'] = $GLOBALS['HTTP_GET_VARS']['part_no'];
		}
		
		// ----  encoding: string  ----
		// USAGE: "base64" "qprint"
		// (a) compose.php: if replying to, we get the body part to reply to, it may need to be un-qprint'ed
		// (b) get_attach.php: appropriate decoding of the part to feed to the browser 
		if (isset($GLOBALS['HTTP_POST_VARS']['encoding']))
		{
			$this->args['encoding'] = $GLOBALS['HTTP_POST_VARS']['encoding'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['encoding']))
		{
			$this->args['encoding'] = $GLOBALS['HTTP_GET_VARS']['encoding'];
		}
		
		// ----  fwd_proc: string  ----
		// USAGE: "encapsulation", "pushdown (not yet supported 9/01)"
		// (outgoing) message.php much detail is known about the messge, there the forward proc method is determined
		// (a) compose.php: used with action = forward, (outgoing) passed on to send_message.php
		// (b) send_message.php: used with action = forward, instructs on how the SMTP message should be structured
		if (isset($GLOBALS['HTTP_POST_VARS']['fwd_proc']))
		{
			$this->args['fwd_proc'] = $GLOBALS['HTTP_POST_VARS']['fwd_proc'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['fwd_proc']))
		{
			$this->args['fwd_proc'] = $GLOBALS['HTTP_GET_VARS']['fwd_proc'];
		}
		
		// ----  name, type, subtype: string  ----
		// the name, mime type, mime subtype of the attachment
		// this info is passed to the browser to help the browser know what to do with the part
		// (outgoing) message.php: "name" is set in the link to the addressbook,  it's the actual "personal" name part of the email address
		// get_attach.php: the name of the attachment
		if (isset($GLOBALS['HTTP_POST_VARS']['name']))
		{
			$this->args['name'] = $GLOBALS['HTTP_POST_VARS']['name'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['name']))
		{
			$this->args['name'] = $GLOBALS['HTTP_GET_VARS']['name'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['type']))
		{
			$this->args['type'] = $GLOBALS['HTTP_POST_VARS']['type'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['type']))
		{
			$this->args['type'] = $GLOBALS['HTTP_GET_VARS']['type'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['subtype']))
		{
			$this->args['subtype'] = $GLOBALS['HTTP_POST_VARS']['subtype'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['subtype']))
		{
			$this->args['subtype'] = $GLOBALS['HTTP_GET_VARS']['subtype'];
		}

		// === FOLDER ADD/DELETE/RENAME & DISPLAY ===
		// ----  "target_folder" , "source_folder" (source used in renaming only)  ----
		// (outgoing) and (in) folder.php: used with "action" to add/delete/rename a mailbox folder
		// 	where "action" can be: create, delete, rename, create_expert, delete_expert, rename_expert
		if (isset($GLOBALS['HTTP_POST_VARS']['target_folder']))
		{
			$this->args['target_folder'] = $GLOBALS['HTTP_POST_VARS']['target_folder'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['target_folder']))
		{
			$this->args['target_folder'] = $GLOBALS['HTTP_GET_VARS']['target_folder'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['source_folder']))
		{
			$this->args['source_folder'] = $GLOBALS['HTTP_POST_VARS']['source_folder'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['source_folder']))
		{
			$this->args['source_folder'] = $GLOBALS['HTTP_GET_VARS']['source_folder'];
		}
		
		// ----  show_long: unset / true  ----
		// folder.php: set there and sent back to itself
		// if set - indicates to show 'long' folder names with namespace and delimiter NOT stripped off
		if (isset($GLOBALS['HTTP_POST_VARS']['show_long']))
		{
			$this->args['show_long'] = $GLOBALS['HTTP_POST_VARS']['show_long'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['show_long']))
		{
			$this->args['show_long'] = $GLOBALS['HTTP_GET_VARS']['show_long'];
		}

		// === COMPOSE VARS ===
		// as most commonly NOT used with "mailto" then the following applies
		//	(if used with "mailto", less common, then see "mailto" below)
		// USAGE: 
		// ----  to, cc, body, subject: string ----
		// (outgoing) index.php, message.php: any click on a clickable email address in these pages
		//	will call compose.php passing "to" (possibly in rfc long form address)
		// (outgoing) message.php: when reading a message and you click reply, replyall, or forward
		//	calls compose.php with EITHER
		//		(1) a msgnum ref then compose gets all needed info, (more effecient than passing all those GPC args) OR
		//		(2) to,cc,subject,body may be passed
		// (outgoing) compose.php: ALL contents of input items to, cc, subject, body, etc...
		//	are passed as GPC args to send_message.php
		// (in) (a) compose.php: text that should go in to and cc (and maybe subject and body) text boxes
		//	are passed as incoming GPC args
		// (in) (b) send_message.php: (fill me in - I got lazy)
		if (isset($GLOBALS['HTTP_POST_VARS']['to']))
		{
			$this->args['to'] = $GLOBALS['HTTP_POST_VARS']['to'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['to']))
		{
			$this->args['to'] = $GLOBALS['HTTP_GET_VARS']['to'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['cc']))
		{
			$this->args['cc'] = $GLOBALS['HTTP_POST_VARS']['cc'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['cc']))
		{
			$this->args['cc'] = $GLOBALS['HTTP_GET_VARS']['cc'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['body']))
		{
			$this->args['body'] = $GLOBALS['HTTP_POST_VARS']['body'];
			//$body = '';
		}
		// also may be in the URI (EXTREMELY rare)
		elseif (isset($GLOBALS['HTTP_GET_VARS']['body']))
		{
			$this->args['body'] = $GLOBALS['HTTP_GET_VARS']['body'];
			//$body = '';
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['subject']))
		{
			$this->args['subject'] = $GLOBALS['HTTP_POST_VARS']['subject'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['subject']))
		{
			$this->args['subject'] = $GLOBALS['HTTP_GET_VARS']['subject'];
		}
		
		// Less Common Usage:
		// ----  sender : string : set or unset
		// RFC says use header "Sender" ONLY WHEN the sender of the email is NOT the author, this is somewhat rare
		if (isset($GLOBALS['HTTP_POST_VARS']['sender']))
		{
			$this->args['sender'] = $GLOBALS['HTTP_POST_VARS']['sender'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['sender']))
		{
			$this->args['sender'] = $GLOBALS['HTTP_GET_VARS']['sender'];
		}
		
		// ----  attach_sig: set-True/unset  ----
		// USAGE:
		// (outgoing) compose.php: if checkbox attach sig is checked, this is passed as GPC var to sent_message.php
		// (in) send_message.php: indicate if message should have the user's "sig" added to the message
		if (isset($GLOBALS['HTTP_POST_VARS']['attach_sig']))
		{
			$this->args['attach_sig'] = $GLOBALS['HTTP_POST_VARS']['attach_sig'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['attach_sig']))
		{
			$this->args['attach_sig'] = $GLOBALS['HTTP_GET_VARS']['attach_sig'];
		}
		
		// ----  msgtype: string  ----
		// USAGE:
		// flag to tell phpgw to invoke "special" custom processing of the message
		// 	extremely rare, may be obsolete (not sure), most implementation code is commented out
		// (outgoing) currently NO page actually sets this var
		// (a) send_message.php: will add the flag, if present, to the header of outgoing mail
		// (b) message.php: identify the flag and call a custom proc
		if (isset($GLOBALS['HTTP_POST_VARS']['msgtype']))
		{
			$this->args['msgtype'] = $GLOBALS['HTTP_POST_VARS']['msgtype'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['msgtype']))
		{
			$this->args['msgtype'] = $GLOBALS['HTTP_GET_VARS']['msgtype'];
		}

		// === MAILTO URI SUPPORT ===
		// ----  mailto: unset / ?set?  ----
		// USAGE:
		// (in and out) compose.php: support for the standard mailto html document mail app call
		// 	can be used with the typical compose vars (see above)
		//	indicates that to, cc, and subject should be treated as simple MAILTO args
		if (isset($GLOBALS['HTTP_POST_VARS']['mailto']))
		{
			$this->args['mailto'] = $GLOBALS['HTTP_POST_VARS']['mailto'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['mailto']))
		{
			$this->args['mailto'] = $GLOBALS['HTTP_GET_VARS']['mailto'];
		}
		
		if (isset($GLOBALS['HTTP_POST_VARS']['personal']))
		{
			$this->args['personal'] = $GLOBALS['HTTP_POST_VARS']['personal'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['personal']))
		{
			$this->args['personal'] = $GLOBALS['HTTP_GET_VARS']['personal'];
		}

		// === MESSAGE VIEWING MODS ===
		// ----  no_fmt: set-True/unset  ----
		// USAGE:
		// (in and outgoing) message.php: will display plain body parts without any html formatting added
		if (isset($GLOBALS['HTTP_POST_VARS']['no_fmt']))
		{
			$this->args['no_fmt'] = $GLOBALS['HTTP_POST_VARS']['no_fmt'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['no_fmt']))
		{
			$this->args['no_fmt'] = $GLOBALS['HTTP_GET_VARS']['no_fmt'];
		}


		// === VIEW HTML INSTRUCTIONS ===
		if (isset($GLOBALS['HTTP_POST_VARS']['html_part']))
		{
			$this->args['html_part'] = $GLOBALS['HTTP_POST_VARS']['html_part'];
			//$html_part = '';
		}
		// usually ==NOT== in the URI
		if (isset($GLOBALS['HTTP_POST_VARS']['html_reference']))
		{
			$this->args['html_reference'] = $GLOBALS['HTTP_POST_VARS']['html_reference'];
		}

		// === FOLDER STATISTICS - CALCULATE TOTAL FOLDER SIZE
		// as a speed up measure, and to reduce load on the IMAP server
		// there is an option to skip the calculating of the total folder size
		// user may request an override of this for 1 page view
		if (isset($GLOBALS['HTTP_POST_VARS']['force_showsize']))
		{
			$this->args['force_showsize'] = $GLOBALS['HTTP_POST_VARS']['force_showsize'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['force_showsize']))
		{
			$this->args['force_showsize'] = $GLOBALS['HTTP_GET_VARS']['force_showsize'];
		}
		
		// === SEARCH RESULT MESSAGE SET ===
		if (isset($GLOBALS['HTTP_POST_VARS']['mlist_set']))
		{
			$this->args['mlist_set'] = $GLOBALS['HTTP_POST_VARS']['mlist_set'];
		}
		

		// ----  UN-INITIALIZE HTTP_POST_VARS and HTTP_GET_VARS ARRAY  -------
		// we've stored every *known / Expected* GPC HTTP_POST_VARS and/or HTTP_GET_VARS
		// into $this->args[]
		// therefor, there is NO MORE use for it
		//$GLOBALS['HTTP_POST_VARS'] = Array();
		//$GLOBALS['HTTP_GET_VARS'] = Array();
		// Alternatively, clear vars that might be wasting space and are no longer needed
		// don't do this unless it's needed, add an isset check
		//$GLOBALS['HTTP_POST_VARS']['body'] = '';
		// unset($GLOBALS['HTTP_POST_VARS']['body']);
		//$GLOBALS['HTTP_POST_VARS']['html_part'] = '';
		// unset($GLOBALS['HTTP_POST_VARS']['html_part']);
	}

	/*!
	@function grab_class_args_xmlrpc
	@abstract grab data an XML-RPC call and fill various class arg variables with the available data
	@param none
	@result none, this is an object call
	@discussion functional relative to function "grab_class_args_gpc()", except this function grabs the
	data from an alternative, non-php-GPC, source
	NOT YET IMPLEMENTED
	@author	Angles
	@access	Public
	*/
	function grab_class_args_xmlrpc()
	{
		// STUB, for future use
		echo 'call to un-implemented function grab_class_args_xmlrpc';
	}



}  // end class mail_msg_wrappers
?>
