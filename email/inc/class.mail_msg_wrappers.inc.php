<?php
	/**************************************************************************\
	* phpGroupWare - E-Mail Message Processing Functions				*
	* http://www.phpgroupware.org							*
	*/
	/**************************************************************************\
	* phpGroupWare API - E-Mail Message Processing Functions			*
	* This file written by Angelo Tony Puglisi (Angles) <angles@phpgroupware.org>	*
	* Handles specific operations in manipulating email messages			*
	* Copyright (C) 2001 Angelo Tony Puglisi (Angles)					*
	* -------------------------------------------------------------------------			*
	* This library is part of the phpGroupWare API					*
	* http://www.phpgroupware.org/api							* 
	* ------------------------------------------------------------------------ 			*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by 	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.								*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of		*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License 	*
	* along with this library; if not, write to the Free Software Foundation, 		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/
	
	/* $Id$ */
	
	class mail_msg_wrappers extends mail_msg_base
	{
	
	// =====  INTERFACE FUNCTIONS AND/OR  WRAPPER FUNCTIONS =====
		/*!
		@class mail_msg_wrappers
		@abstract  Wrapper functions to be called as "public" functions
		@discussion  Hides the implementation details from the calling process
		Provides most args to the dcom class from variables which class msg processed and set
		Sometimes returns processed data ready to be used for display or information
		Discussion: Why Wrap Here?
		Answer: because once the msg class opens a mailsvr_stream, that will be the only stream
		that instance of the class will have, so WHY keep supplying it as an arg EVERY time?
		Also, same for the "msgnum", unless you are looping thru a message list, you are 
		most likely concerned with only ONE message, and the variable would be the MIME part therein
		*/
	
	
	// ====  Functions For Getting Information About A Message  ====
		/*!
		@function phpgw_fetchstructure
		@abstract wrapper for IMAP_FETSCSTRUCTURE, phpgw supplies the nedessary stream arg
		@param $msgnum : integer
		@result returns the IMAP_FETSCSTRUCTURE data
		@discussion Wrapper supplies the needed mailsvr_stream arg to IMAP_FETSCSTRUCTURE
		The data communications object (class mail_dcom) is supplied by the class
		*/
		function phpgw_fetchstructure($msgball='')
		{
			if (!(isset($msgball))
			|| ((string)$msgball == ''))
			{
				$msgball = $this->get_arg_value('msgball');
			}
			$acctnum = $msgball['acctnum'];
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->fetchstructure($stream, $msgball['msgnum']);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->fetchstructure($stream, $msgball['msgnum']);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		/*!
		@function phpgw_header
		@abstract wrapper for IMAP_HEADER, phpgw supplies the nedessary stream arg and mail_dcom reference
		@param $msgnum : integer
		@result returns the php IMAP_HEADER data
		@discussion Wrapper supplies the needed mailsvr_stream arg to IMAP_HEADER
		The data communications object (class mail_dcom) is supplied by the class
		*/
		function phpgw_header($msgball='')
		{
			if (!(isset($msgball))
			|| ((string)$msgball == ''))
			{
				$msgball = $this->get_arg_value('msgball');
			}
			$acctnum = $msgball['acctnum'];
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			
			// Message Information: THE MESSAGE'S HEADERS RETURNED AS A STRUCTURE
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->header($stream, $msgball['msgnum']);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->header($stream, $msgball['msgnum']);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function phpgw_fetchheader($msgball='')
		{
			if (!(isset($msgball))
			|| ((string)$msgball == ''))
			{
				$msgball = $this->get_arg_value('msgball');
			}
			$acctnum = $msgball['acctnum'];
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
		
			// Message Information: THE MESSAGE'S HEADERS RETURNED RAW (no processing)
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->fetchheader($stream, $msgball['msgnum']);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->fetchheader($stream, $msgball['msgnum']);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
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
				//$tmp_a = $this->a[$this->acctnum];
				//$retval = $tmp_a['dcom']->get_flag($this->get_arg_value('mailsvr_stream'),$this->get_arg_value('["msgball"]["msgnum"]'),$flag);
				$retval = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->get_flag($this->get_arg_value('mailsvr_stream'),$this->get_arg_value('["msgball"]["msgnum"]'),$flag);
				//$this->a[$this->acctnum] = $tmp_a;
				return $retval;
			}
		}
		
		//FIXME: msgball
	// ====  Functions For Getting A Message Or A Part (MIME Part) Of A Message  ====
		function phpgw_body($msgball='')
		{
			if (!(isset($msgball))
			|| ((string)$msgball == ''))
			{
				$msgball = $this->get_arg_value('msgball');
			}
			$acctnum = $msgball['acctnum'];
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->get_body($stream, $msgball['msgnum']);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->get_body($stream, $msgball['msgnum']);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		//FIXME: msgball
		//function phpgw_fetchbody($part_num_mime='', $flags='')
		//{
		//	return $this->a[$this->acctnum]['dcom']->fetchbody($this->get_arg_value('mailsvr_stream'), $this->get_arg_value('msgnum'), $part_num_mime, $flags);
		//}
		function phpgw_fetchbody($msgball='', $flags='')
		{
			//echo 'mail_msg(_wrappers): phpgw_fetchbody: ENTERING, $msgball dump<pre>'; print_r($msgball); echo '</pre>';
			if ( (!isset($msgball))
			|| ($msgball == '') )
			{
				$msgball = $this->get_arg_value('msgball');
			}
			$acctnum = $msgball['acctnum'];
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$msgnum = $msgball['msgnum'];
			$part_no = $msgball['part_no'];
			//echo 'mail_msg(_wrappers): phpgw_fetchbody: processed: $acctnum: '.$acctnum.'; $stream: '.serialize($stream).'; $msgnum: '.$msgnum.'; $part_no: '.$part_no.'<br> * $msgball dump<pre>'; print_r($msgball); echo '</pre>';
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->fetchbody($stream, $msgnum, $part_no, $flags);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->fetchbody($stream, $msgnum, $part_no, $flags);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		/*
		function phpgw_fetchbody($msgball='', $part_num_mime='', $flags='')
		{
			$stream = $this->get_arg_value('mailsvr_stream');
			$msgnum = $msgball['msgnum'];
			$part_no = $msgball['part_no'];
			return $this->a[$this->acctnum]['dcom']->fetchbody($stream, $msgnum, $part_no, $flags);
		}
		*/
		/*
		function phpgw_fetchbody($msgball='', $part_num_mime='', $flags='')
		{
			if (!(isset($msgball))
			|| ((string)$msgball == ''))
			{
				$msgball = $this->get_arg_value('msgball');
			}
			$acctnum = $msgball['acctnum'];
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			
			//return $this->a[$acctnum]['dcom']->fetchbody($stream, $msgball['msgnum'], $part_num_mime, $flags);
			//return $this->a[$this->acctnum]['dcom']->fetchbody($this->get_arg_value('mailsvr_stream'), $this->get_arg_value('msgnum'), $part_num_mime, $flags);
			//return $this->a[$acctnum      ]['dcom']->fetchbody($stream, $msgball['msgnum'], $part_num_mime, $flags);
			return $this->a[$this->acctnum]['dcom']->fetchbody($stream, $msgball['msgnum'], $part_num_mime, $flags);
		}
		*/
		
		
	// =====  Functions For Getting Information About A Folder  =====
		// returns an array of integers which are refer to all the messages in a folder ("INBOX") sorted and ordered
		// any integer in this array can be used to request that specific message from the server
		/*!
		@function get_msgball_list
		@abstract wrapper for IMAP_SORT, sorts a folder in the desired way, then get a list of all message, as integer message numbers
		@param none
		@result returns an array of integers which are message numbers referring to messages in the corrent folder
		@discussion use these message numbers to request mode detailed information for a message, or the message itself.
		Sort and Order is applied by the class, so the calling process does not need to specify sorting here
		The data communications object (class mail_dcom) is supplied by the class
		*/
		function get_msgball_list()
		{
			// try to restore "msgball_list" from saved session data store
			$cached_msgball_list = $this->read_session_cache_item('msgball_list');
			if ($cached_msgball_list)
			{
				return $cached_msgball_list['msgball_list'];
			}
			else
			{
				$server_msgnum_list = array();
				//$tmp_a = $this->a[$this->acctnum];
				//$server_msgnum_list = $tmp_a['dcom']->sort($this->get_arg_value('mailsvr_stream'), $this->get_arg_value('sort'), $this->get_arg_value('order'));
				$server_msgnum_list = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->sort($this->get_arg_value('mailsvr_stream'), $this->get_arg_value('sort'), $this->get_arg_value('order'));
				//$this->a[$this->acctnum] = $tmp_a;
				// put more information about these particular messages into the msgball_list[] structure
				$msgball_list = array();
				$loops = count($server_msgnum_list);
				// folder empty (or an error?), msg_nums_list[] count will be 0, so msgball_list[] will be empty as well
				// because we'll never fill it with anything
				if ($loops > 0)
				{
					$msg_folder = $this->prep_folder_out($this->get_arg_value('folder'));
					$msg_acctnum = $this->get_acctnum();
					for($i=0;$i<$loops;$i++)
					{
						$msgball_list[$i]['msgnum'] = $server_msgnum_list[$i];
						$msgball_list[$i]['folder'] = $msg_folder;
						$msgball_list[$i]['acctnum'] = $msg_acctnum;
						// see php manual page "function.parse-str.html" for explanation of the array'ing of the URI data
						// NOTE: this uri NEVER begins with a "&" here
						// YOU must add the prefix "&" if it's needed
						$msgball_list[$i]['uri'] = 
							 'msgball[msgnum]='.$msgball_list[$i]['msgnum']
							.'&msgball[folder]='.$msgball_list[$i]['folder']
							.'&msgball[acctnum]='.$msgball_list[$i]['acctnum'];
					}
				}
				// save "msgball_list" to session data store
				$this->save_session_cache_item('msgball_list', $msgball_list);
				return $msgball_list;
			}
		}
		
		/*!
		@function get_folder_size
		@abstract uses IMAP_MAILBOXMSGINFO but returns only the size element
		@param none
		@result integer : returns the SIZE element of the php IMAP_MAILBOXMSGINFO data
		@discussion used only if the total size of a folder is desired, which takes time for the server to return
		The other data IMAP_MAILBOXMSGINFO returns (if size is NOT needed) is obtainable
		from "get_folder_status_info" more quickly and wth less load to the IMAP server
		The data communications object (class mail_dcom) and mailsvr_stream are supplied by the class
		*/
		function get_folder_size()
		{
			//$tmp_a = $this->a[$this->acctnum];
			//$mailbox_detail = $tmp_a['dcom']->mailboxmsginfo($this->get_arg_value('mailsvr_stream'));
			$mailbox_detail = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->mailboxmsginfo($this->get_arg_value('mailsvr_stream'));
			//$this->a[$this->acctnum] = $tmp_a;
			return $mailbox_detail->Size;
		}
		
		// ALIAS for get_folder_status_info() , for backward compatibility
		function new_message_check()
		{
			return $this->get_folder_status_info();
		}
		
		/*!
		@function get_folder_status_info
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
		function get_folder_status_info($force_refresh=False)
		{
			if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info: ENTERING, $force_refresh: '.serialize($force_refresh).' <br>'; }
			
			// do we have cached data in L1 cache / class object var, that we can use
			$folder_status_info = $this->get_arg_value('folder_status_info');
			if ((!$force_refresh)
			&& ($folder_status_info)
			&& (count($folder_status_info) > 0)
			&& ($folder_status_info['folder_checked'] == $this->get_arg_value('folder')))
			{
				// this data is cached, L1 cache, temp cache, so it should still be "fresh"
				if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info: LEAVING returning L1/class var cached data<br>'; }
				return $folder_status_info;
			}
			
			// initialize return structure
			$return_data = Array();
			$return_data['is_imap'] = False;
			$return_data['folder_checked'] = $this->get_arg_value('folder');
			$return_data['alert_string'] = '';
			$return_data['number_new'] = 0;
			$return_data['number_all'] = 0;
			// these are used to verify cached msg_list_array data, i.e. is it still any good, or is it stale
			$return_data['uidnext'] = 0;
			$return_data['uidvalidity'] = 0;
			
			$server_str = $this->get_arg_value('mailsvr_callstr');
			//$tmp_a = $this->a[$this->acctnum];
			//$mailbox_status = $tmp_a['dcom']->status($this->get_arg_value('mailsvr_stream'),$server_str.$this->get_arg_value('folder'),SA_ALL);
			$mailbox_status = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->status($this->get_arg_value('mailsvr_stream'),$server_str.$this->get_arg_value('folder'),SA_ALL);
			//$this->a[$this->acctnum] = $tmp_a;
			
			// cache validity data - will be used to cache msg_list_array data, which is good until UID_NEXT changes
			$return_data['uidnext'] = $mailbox_status->uidnext;
			$return_data['uidvalidity'] = $mailbox_status->uidvalidity;
			
			if (($this->get_pref_value('mail_server_type') == 'imap')
			|| ($this->get_pref_value('mail_server_type') == 'imaps'))
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
			// cache data in a class var (L1 Cache)
			$this->set_arg_value('folder_status_info', $return_data);
			if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info: LEAVING returning data obtained from server<br>'; }
			return $return_data;
		}
		
		function phpgw_status($feed_folder_long='')
		{
			$server_str = $this->get_arg_value('mailsvr_callstr');
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->status($this->get_arg_value('mailsvr_stream'),"$server_str"."$feed_folder_long",SA_ALL);
			$retval = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->status($this->get_arg_value('mailsvr_stream'),"$server_str"."$feed_folder_long",SA_ALL);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}

		function phpgw_server_last_error()
		{
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->server_last_error();
			$retval = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->server_last_error();
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function phpgw_ping()
		{
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->noop_ping_test($this->get_arg_value('mailsvr_stream'));
			$retval = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->noop_ping_test($this->get_arg_value('mailsvr_stream'));
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function phpgw_search($criteria,$flags='')
		{
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->i_search($this->get_arg_value('mailsvr_stream'),$criteria,$flags);
			$retval = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->i_search($this->get_arg_value('mailsvr_stream'),$criteria,$flags);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function phpgw_createmailbox($target_fldball)
		{
			$acctnum = (int)$target_fldball['acctnum'];
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$folder = $target_fldball['folder'];
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->createmailbox($stream, $folder);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->createmailbox($stream, $folder);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function phpgw_deletemailbox($target_fldball)
		{
			$acctnum = (int)$target_fldball['acctnum'];
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$folder = $target_fldball['folder'];
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->deletemailbox($stream, $folder);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->deletemailbox($stream, $folder);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function phpgw_renamemailbox($source_fldball,$target_fldball)
		{
			$acctnum = (int)$source_fldball['acctnum'];
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$folder_old = $source_fldball['folder'];
			$folder_new = $target_fldball['folder'];
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->renamemailbox($stream, $folder_old, $folder_new);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->renamemailbox($stream, $folder_old, $folder_new);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function phpgw_append($folder = "Sent", $message, $flags=0)
		{
			//$debug_append = True;
			$debug_append = False;
		
			if ($debug_append) { echo 'append: folder: '.$folder.'<br>'; }
			
			$server_str = $this->get_arg_value('mailsvr_callstr');
			
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
				// delete session msg array data thAt is now stale
				$this->expire_session_cache_item('msgball_list');
				// do the append
				//$tmp_a = $this->a[$this->acctnum];
				//$retval = $tmp_a['dcom']->append($this->get_arg_value('mailsvr_stream'), "$server_str"."$official_folder_long", $message, $flags);
				$retval = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->append($this->get_arg_value('mailsvr_stream'), "$server_str"."$official_folder_long", $message, $flags);
				//$this->a[$this->acctnum] = $tmp_a;
				return $retval;
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
			// delete session msg array data thAt is now stale
			$this->expire_session_cache_item('msgball_list');
			
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->mail_move($this->get_arg_value('mailsvr_stream'), $msg_list, $mailbox);
			$retval = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->mail_move($this->get_arg_value('mailsvr_stream'), $msg_list, $mailbox);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function interacct_mail_move($mov_msgball='', $to_fldball='')
		{
			// this needs A LOT of work!!! do not rely on this yet
			
			// delete session msg array data thAt is now stale
			$this->expire_session_cache_item('msgball_list');
			
			// Note: Only call this function with ONE msgball at a time, i.e. NOT a list of msgballs
			$acctnum = (int)$mov_msgball['acctnum'];
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			//$stream = (int)$this->get_arg_value('mailsvr_stream', $acctnum);
			$stream = $this->get_arg_value('mailsvr_stream');
			$msgnum = (string)$mov_msgball['msgnum'];
			$mailbox = $to_fldball['folder'];
			//echo 'mail_msg(_wrappers): interacct_mail_move: $acctnum: '.$acctnum.' $stream: '.$stream.' $msgnum: '.$msgnum.' $mailsvr_callstr: '.$mailsvr_callstr.' $mailbox: '.$mailbox.'<br>';
			// the acctnum we are moving FROM *may* be different from the acctnum we are moving TO
			// that requires a fetch then an append - FIXME!!!
			
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->mail_move($stream ,$msgnum, $mailbox);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->mail_move($stream ,$msgnum, $mailbox);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		function phpgw_expunge($acctnum='')
		{
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			//$tmp_a = $this->a[$this->acctnum];
			//$retval = $tmp_a['dcom']->expunge($stream);
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->expunge($stream);
			//$this->a[$this->acctnum] = $tmp_a;
			return $retval;
		}
		
		//function phpgw_delete($msg_num,$flags=0, $currentfolder="") 
		function phpgw_delete($msg_num,$flags=0, $currentfolder="", $acctnum='') 
		{
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			// everything from now on MUST specify this $acctnum
			
			// now get the stream that applies to that acctnum
			$stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			
			if (($this->get_isset_pref('use_trash_folder', $acctnum))
			&& ($this->get_pref_value('use_trash_folder', $acctnum)))
			{
				$trash_folder_long = $this->get_folder_long($this->get_pref_value('trash_folder_name', $acctnum));
				$trash_folder_short = $this->get_folder_short($this->get_pref_value('trash_folder_name', $acctnum));
				if ($currentfolder != '')
				{
					$currentfolder_short = $this->get_folder_short($currentfolder);
				}
				// if we are deleting FROM the trash folder, we do a straight delete
				if ($currentfolder_short == $trash_folder_short)
				{
					// delete session msg array data thAt is now stale
					$this->expire_session_cache_item('msgball_list');
					
					//return imap_delete($stream,$msg_num);
					//$tmp_a = $this->a[$this->acctnum];
					//$retval = $tmp_a['dcom']->delete($stream, $msg_num);
					$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->delete($stream, $msg_num);
					//$this->a[$this->acctnum] = $tmp_a;
					return $retval;
				}
				// FIXME: from here on needs that $acctnum and.or $stream NEEDS TO BE SPECIFIED!
				else
				{
					// does the trash folder actually exist ?
					$official_trash_folder_long = $this->folder_lookup('', $this->get_pref_value('trash_folder_name', $acctnum));
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
						$server_str = $this->get_arg_value('mailsvr_callstr');
						//$this->createmailbox($stream,$server_str .$trash_folder_long);
						$this->phpgw_createmailbox("$server_str"."$trash_folder_long");
						// try again to get the real long folder name of the just created trash folder
						$official_trash_folder_long = $this->folder_lookup('', $this->get_pref_value('trash_folder_name'));
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
						// delete session msg array data thAt is now stale
						$this->expire_session_cache_item('msgball_list');
						
						//return imap_mail_move($stream,$msg_num,$official_trash_folder_long);
						return $this->phpgw_mail_move($msg_num,$official_trash_folder_long);
					}
					else
					{
						// delete session msg array data thAt is now stale
						$this->expire_session_cache_item('msgball_list');
						
						// we do not have the trash official folder name, but we have to do something
						// can't just leave the mail sitting there
						// so just straight delete the message
						//return imap_delete($stream,$msg_num);
						//$tmp_a = $this->a[$this->acctnum];
						//$retval = $tmp_a['dcom']->delete($this->get_arg_value('mailsvr_stream'), $msg_num);
						$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->delete($this->get_arg_value('mailsvr_stream'), $msg_num);
						//$this->a[$this->acctnum] = $tmp_a;
						return $retval;
					}
				}
			}
			else
			{
				// delete session msg array data thAt is now stale
				$this->expire_session_cache_item('msgball_list');
				
				//return imap_delete($stream,$msg_num);
				//$tmp_a = $this->a[$this->acctnum];
				//$retval = $tmp_a['dcom']->delete($this->get_arg_value('mailsvr_stream'), $msg_num);
				$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->delete($this->get_arg_value('mailsvr_stream'), $msg_num);
				//$this->a[$this->acctnum] = $tmp_a;
				return $retval;
			}
		}
		
		/*!
		@function decode_fake_uri
		@abstract decodes a URI type "query string" into an associative array
		@param $uri_type_string string in the style of a URI such as "&item=phone&action=dial"
		@result associative array where the $key and $value are exploded from the uri like [item] => "phone"
		@discussion HTML select "combobox"s can only return 1 "value" per item, to break that limitation you 
		can use that 1 item like a "fake URI", meaning you make a single string store structured data 
		by using the standard syntax of a HTTP GET URI, example: 
		< select name="fake_uri_data" > < option value="&item=phone&action=dial&touchtone=1" > ( ... etc ... )
		In an HTTP POST event, this would appear as such:
		$GLOBALS['HTTP_POST_VARS']['fake_uri_data'] => "&item=phone&action=dial&touchtone=1"
		Then you feed that string into this function and you get back an associave array like this
		return["item"] => "phone"
		return["action"] => "dial"
		return["touchtone"] => "1"
		NOTE: this differs from PHP's parse_str() because this function  will NOT attempt to decode the urlencoded values.
		In this way you may store many data elements in a single HTML "option" value=" " tag.
		@author	Angles
		@access	Public
		*/
		function decode_fake_uri($uri_type_string='', $raise_up=False)
		{
			/*
			$fake_url_b = explode('&', $uri_type_string);
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: decode_fake_uri: $fake_url_b = explode("&", '.$uri_type_string.') dump:<pre>'; print_r($fake_url_b); echo '</pre>'; }
			
			$fake_url_b_2 = array();
			while(list($key,$value) = each($fake_url_b))
			{
				$explode_me = trim($fake_url_b[$key]);
				if ((string)$explode_me != '')
				{
					$exploded_parts = explode('=', $explode_me);
					$fake_url_b_2[$exploded_parts[0]] = $exploded_parts[1];
				}
			}
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: decode_fake_uri: $fake_url_b_2 (sub parts exploded and made into an associative array) dump:<pre>'; print_r($fake_url_b_2); echo '</pre>'; }
			return $fake_url_b_2;
			*/
			
			$embeded_data = array();
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: decode_fake_uri: ENTERED $uri_type_string ['.$uri_type_string.'] <br>'; }
			parse_str($uri_type_string, $embeded_data);
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: decode_fake_uri: parse_str('.$uri_type_string.', into $embeded_data dump:<pre>'; print_r($embeded_data); echo '</pre>'; }
			
			// parse_str will "urldecode" the folder string, we need to re-urlencode it, 
			// because "prep_folder_in" is supposed to be where it gets urldecoded
			while(list($key,$value) = each($embeded_data))
			{
				if ((strstr($key, 'folder'))
				&& ((string)$embeded_data[$key] != ''))
				{
					$re_urlencoded_folder = urlencode($embeded_data[$key]);
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: re-urlencode (hopefully) folder element $embeded_data['.$key.'] from ['.$embeded_data[$key].'] into ['.$re_urlencoded_folder.'] <br>'; }
					$embeded_data[$key] = $re_urlencoded_folder;
				}
				elseif ((strstr($key, 'acctnum'))
				&& ((string)$embeded_data[$key] != ''))
				{
					$make_int_acctnum = (int)$embeded_data[$key];
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: $make_int_acctnum (hopefully) acctnum element $embeded_data['.$key.'] from ['.serialize($embeded_data[$key]).'] into ['.serialize($make_int_acctnum).'] <br>'; }
					$embeded_data[$key] = $make_int_acctnum;
				}
			}
			// some embeded uri-faked data needs to be raised up one level from sub-elements to top level
			if ($raise_up)
			{
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: attempt to raise up data one level in the array <br>'; }
				$count_embeded = count($embeded_data);
				if ($count_embeded == 1)
				{
					@reset($embeded_data);
					$new_top_level = array();
					while(list($key,$value) = each($embeded_data))
					{
						$new_top_level = $embeded_data[$key];
						//break;
					}
					// replace result with $new_top_level
					$embeded_data = $new_top_level;
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: raise embeded up to $new_top_level: <pre>'; print_r($new_top_level); echo '</pre>'; }
				}
				else
				{
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: original result had more than one element, can not raise <br>'; }
				}
			}
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: decode_fake_uri: final $embeded_data (sub parts made into an associative array) dump:<pre>'; print_r($embeded_data); echo '</pre>'; }
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: decode_fake_uri: LEAVING <br>'; }
			return $embeded_data;
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
		function grab_class_args_gpc($acctnum='')
		{
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: grab_class_args_gpc: ENTERING, (parm $acctnum=['.serialize($acctnum).'])<br>'; }
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: $GLOBALS[HTTP_POST_VARS] dump:<pre>'; print_r($GLOBALS['HTTP_POST_VARS']); echo '</pre>'; }
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: $GLOBALS[HTTP_GET_VARS] dump:<pre>'; print_r($GLOBALS['HTTP_GET_VARS']); echo '</pre>'; }
			
			// ----  extract any "fake_uri" embedded data from HTTP_POST_VARS  ----
			// note: this happens automatically for HTTP_GET_VARS 
			if (is_array($GLOBALS['HTTP_POST_VARS']))
			{
				while(list($key,$value) = each($GLOBALS['HTTP_POST_VARS']))
				{
					if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: looking for "_fake_uri" token in HTTP_POST_VARS ['.$key.'] = '.$GLOBALS['HTTP_POST_VARS'][$key].'<br>'; }
					if ($key == 'delmov_list')
					{
						if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: FOUND "delmov_list_fake_uri" needs decoding HTTP_POST_VARS['.$key.'] = ['.$GLOBALS['HTTP_POST_VARS'][$key].'] <br>'; }
						$sub_loops = count($GLOBALS['HTTP_POST_VARS'][$key]);				
						for($i=0;$i<$sub_loops;$i++)
						{
							$sub_embedded_data = array();
							// True = attempt to "raise up" embedded data to top level
							$sub_embedded_data = $this->decode_fake_uri($GLOBALS['HTTP_POST_VARS'][$key][$i], True);
							// this array needs to be taken up one level
							$top_of_sub = Array();
							$GLOBALS['HTTP_POST_VARS'][$key][$i] = $sub_embedded_data;
						}
						if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: decoded ARRAY "_fake_uri" data: HTTP_POST_VARS['.$key.'] data dump: <pre>'; print_r($GLOBALS['HTTP_POST_VARS'][$key]); echo '</pre>'; }
					}
					elseif (strstr($key, '_fake_uri'))
					{
						if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: FOUND "_fake_uri" token in HTTP_POST_VARS['.$key.'] = ['.$GLOBALS['HTTP_POST_VARS'][$key].'] <br>'; }
						$embedded_data = array();
						$embedded_data = $this->decode_fake_uri($GLOBALS['HTTP_POST_VARS'][$key]);
						// Strip "_fake_uri" from $key and insert the associative array into HTTP_POST_VARS
						$new_key = str_replace('_fake_uri', '', $key);
						if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: embedded "_fake_uri" data will be inserted into POST VARS with key name: ['.$new_key.'] = ['.$GLOBALS['HTTP_POST_VARS'][$key].'] <br>'; }
						$GLOBALS['HTTP_POST_VARS'][$new_key] = array();
						$GLOBALS['HTTP_POST_VARS'][$new_key] = $embedded_data;
						if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: decoded "_fake_uri" data: HTTP_POST_VARS['.$new_key.'] data dump: <pre>'; print_r($GLOBALS['HTTP_POST_VARS'][$new_key]); echo '</pre>'; }
					}
					/*
					elseif ($key == 'delmov_list')
					{
						if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: FOUND "delmov_list" needs decoding HTTP_POST_VARS['.$key.'] = ['.$GLOBALS['HTTP_POST_VARS'][$key].'] <br>'; }
						$sub_loops = count($GLOBALS['HTTP_POST_VARS'][$key]);				
						for($i=0;$i<$sub_loops;$i++)
						{
							$sub_embedded_data = array();
							$sub_embedded_data = $this->decode_fake_uri($GLOBALS['HTTP_POST_VARS'][$key][$i]);
							$GLOBALS['HTTP_POST_VARS'][$key][$i] = $sub_embedded_data;
						}
						if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: decoded ARRAY "_fake_uri" data: HTTP_POST_VARS['.$key.'] data dump: <pre>'; print_r($GLOBALS['HTTP_POST_VARS'][$key]); echo '</pre>'; }
					}
					*/
				}
			}
			
			$got_args = array();
			
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: about to loop thru $this->known_external_args<br>'; }
			$loops = count($this->known_external_args);
			for($i=0;$i<$loops;$i++)
			{
				$this_arg_name = $this->known_external_args[$i];
				//if ($this->debug_args_input_flow > 2) { echo ' * * (grab pref - external) $this_arg_name: ['.$this_arg_name.']<br>'; }
				if (isset($GLOBALS['HTTP_POST_VARS'][$this_arg_name]))
				{
					if ($this->debug_args_input_flow> 2) { echo ' * * (grab pref - external) $GLOBALS[HTTP_POST_VARS]['.$this_arg_name.'] IS set to ['.$GLOBALS['HTTP_POST_VARS'][$this_arg_name].']<br>'; }
					$got_args[$this_arg_name] = $GLOBALS['HTTP_POST_VARS'][$this_arg_name];
				}
				elseif (isset($GLOBALS['HTTP_GET_VARS'][$this_arg_name]))
				{
					if ($this->debug_args_input_flow > 2) { echo ' * * (grab pref - external) $GLOBALS[HTTP_GET_VARS]['.$this_arg_name.'] IS set to ['.$GLOBALS['HTTP_GET_VARS'][$this_arg_name].']<br>'; }
					$got_args[$this_arg_name] = $GLOBALS['HTTP_GET_VARS'][$this_arg_name];
					
					// ADD "uri" element to incoming "msgball" arg
					// so forms may pass this "msgball" on to the next page view
					if ($this_arg_name == 'msgball')
					{
						// php will automayically urldecode the folder, we don't like this
						$re_urlencoded_folder = $this->prep_folder_out($got_args[$this_arg_name]['folder']);
						$got_args[$this_arg_name]['folder'] = $re_urlencoded_folder;
						$got_args[$this_arg_name]['uri'] = 
							'msgball[msgnum]='.$got_args[$this_arg_name]['msgnum']
							.'&msgball[folder]='.$got_args[$this_arg_name]['folder']
							.'&msgball[acctnum]='.$got_args[$this_arg_name]['acctnum'];
						if ($this->debug_args_input_flow > 2) { echo ' * * (grab pref - external) made msgball URI, added it to msgball[]: <pre>'; print_r($got_args[$this_arg_name]); echo '</pre>'; }
					}
				}
				else
				{
					if ($this->debug_args_input_flow > 2) { echo ' * (grab pref - external) neither POST nor GET vars have this item set ['.$this_arg_name.'] <br>'; }
				}
			}
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: post-loop (external args) $got_args[] dump:<pre>'; print_r($got_args); echo '</pre>'; }
			
			
			// in order to handle internal args, we need to determine what account we are dealing with
			// before we can call "get_isset_arg" or "get_arg_value"
			
			// ---  which email account do are these args intended to apply to  ----
			// ORDER OF PREFERENCE for determining account num
			// 1) force fed acct num
			// 2) gpc fldball['acctnum']
			// 3) gpc msgball['acctnum']
			// 4-) current class value for acct num
			// 4a) use class value $this->acctnum if it exists
			// 4b) get a default value to use (usually = 0)
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": will look here: <br>'
								.' * (1) function arg $acctnum ['.serialize($acctnum).']<br>'
								.' * (2) $got_args[msgball][acctnum] ['.serialize($got_args['msgball']['acctnum']).']<br>'
								.' * (3) $got_args[fldball][acctnum] ['.serialize($got_args['fldball']['acctnum']).']<br>'
								.' * (4) last resort, calls $this->get_acctnum()<br>'
								; }
			
			if ((isset($acctnum))
			&& ((string)$acctnum != ''))
			{
				// do nothing, we'll use this value below
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": will use function param $acctnum=['.serialize($acctnum).']<br>'; }
			}
			elseif ((isset($got_args['msgball']['acctnum']))
			&& ((string)$got_args['msgball']['acctnum'] != ''))
			{
				$acctnum = (int)$got_args['msgball']['acctnum'];
				// make sure this is an integer
				$got_args['msgball']['acctnum'] = $acctnum;
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": will use GPC aquired $got_args[msgball][acctnum] : ['.serialize($got_args['msgball']['acctnum']).']<br>'; }
			}
			elseif ((isset($got_args['fldball']['acctnum']))
			&& ((string)$got_args['fldball']['acctnum'] != ''))
			{
				$acctnum = (int)$got_args['fldball']['acctnum'];
				// make sure this is an integer
				$got_args['fldball']['acctnum'] = $acctnum;
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": will use GPC aquired $got_args[fldball][acctnum] : ['.serialize($got_args['fldball']['acctnum']).']<br>'; }
			}
			elseif ((isset($got_args['source_fldball']['acctnum']))
			&& ((string)$got_args['source_fldball']['acctnum'] != ''))
			{
				$acctnum = (int)$got_args['source_fldball']['acctnum'];
				// make sure this is an integer
				$got_args['source_fldball']['acctnum'] = $acctnum;
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": will use GPC aquired $got_args[source_fldball][acctnum] : ['.serialize($got_args['source_fldball']['acctnum']).']<br>'; }
			}
			elseif ((isset($got_args['target_fldball']['acctnum']))
			&& ((string)$got_args['target_fldball']['acctnum'] != ''))
			{
				$acctnum = (int)$got_args['target_fldball']['acctnum'];
				// make sure this is an integer
				$got_args['target_fldball']['acctnum'] = $acctnum;
				if ($this->debug_args_input_flow > 1)
				{
					echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": will use GPC aquired $got_args[target_fldball][acctnum] : ['.serialize($got_args['target_fldball']['acctnum']).']<br>';
				}
			}
			else
			{
				// ok, we have either a force fed $acctnum or got one from GPC
				// if neither, we grab the class's current value for $this->acctnum
				// $this->get_acctnum(True) will return a default value for us to use if $this->acctnum is not set
				// True means "return a default value, NOT boolean false, if $this->acctnum is not set
				$acctnum = $this->get_acctnum(True);
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": NO *incoming* acctnum specified, called $this->get_acctnum(True), got: ['.serialize($acctnum).']<br>'; }
			}
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: grab_class_args_gpc: * * * *SETTING CLASS ACCTNUM* * * * by calling $this->set_acctnum('.serialize($acctnum).')<br>'; }
			$this->set_acctnum($acctnum);
			
			
			// INTERNALLY CONTROLLED ARGS
			// preserve pre-existing value, for which "acctnum" must be already obtained, so we
			// know what account to check for existing arg values when we use "get_isset_arg" or "get_arg_value"
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: about to loop thru $this->known_internal_args<br>'; }
			$loops = count($this->known_internal_args);
			for($i=0;$i<$loops;$i++)
			{
				$this_arg_name = $this->known_internal_args[$i];
				//if ($this->debug_args_input_flow > 2) { echo ' * * (grab pref - internal) $this_arg_name: '.$this_arg_name.'<br>'; }
				// see if there is a value we can preserve for this arg
				if ($this->get_isset_arg($this_arg_name))
				{
					$preserve_this = $this->get_arg_value($this_arg_name);
					if ($this->debug_args_input_flow> 2) { echo ' * * (grab pref - internal) preserving internal pre-existing arg: ['.$this_arg_name.'] = ['.$preserve_this.']<br>'; }
					$got_args[$this_arg_name] = $preserve_this;
				}
				else
				{
					if ($this->debug_args_input_flow > 2) { echo ' * (grab pref - internal) no pre-existing value for ['.$this_arg_name.'], using initialization default: <br>'; }
					if ($this_arg_name == 'folder_status_info')
					{
						$got_args['folder_status_info'] = array();
					}
					elseif ($this_arg_name == 'folder_list')
					{
						$got_args['folder_list'] = array();
					}
					elseif ($this_arg_name == 'mailsvr_callstr')
					{
						$got_args['mailsvr_callstr'] = '';
					}
					elseif ($this_arg_name == 'mailsvr_namespace')
					{
						$got_args['mailsvr_namespace'] = '';
					}
					elseif ($this_arg_name == 'mailsvr_delimiter')
					{
						$got_args['mailsvr_delimiter'] = '';
					}
					elseif ($this_arg_name == 'mailsvr_stream')
					{
						$got_args['mailsvr_stream'] = '';
					}
					elseif ($this_arg_name == 'mailsvr_account_username')
					{
						$got_args['mailsvr_account_username'] = '';
					}
					/*
					// THESE menuaction args are being DEPRECIATED
					// these are the supported menuaction strings
					elseif ($this_arg_name == 'index_menuaction')
					{
						$got_args['index_menuaction'] = 'menuaction=email.uiindex.index';
						//$got_args['index_menuaction'] .= '&acctnum='.$acctnum;
					}
					elseif ($this_arg_name == 'mlist_menuaction')
					{
						$got_args['mlist_menuaction'] = 'menuaction=email.uiindex.mlist';
						//$got_args['index_menuaction'] .= '&acctnum='.$acctnum;
					}
					elseif ($this_arg_name == 'delmov_menuaction')
					{
						$got_args['delmov_menuaction'] = 'menuaction=email.boaction.delmov';
						//$got_args['index_menuaction'] .= '&acctnum='.$acctnum;
					}
					elseif ($this_arg_name == 'get_attach_menuaction')
					{
						$got_args['get_attach_menuaction'] = 'menuaction=email.boaction.get_attach';
						//$got_args['index_menuaction'] .= '&acctnum='.$acctnum;
					}
					elseif ($this_arg_name == 'view_html_menuaction')
					{
						$got_args['view_html_menuaction'] = 'menuaction=email.boaction.view_html';
						//$got_args['index_menuaction'] .= '&acctnum='.$acctnum;
					}
					elseif ($this_arg_name == 'folder_menuaction')
					{
						$got_args['folder_menuaction'] = 'menuaction=email.uifolder.folder';
						//$got_args['index_menuaction'] .= '&acctnum='.$acctnum;
					}
					elseif ($this_arg_name == 'send_menuaction')
					{
						$got_args['send_menuaction'] = 'menuaction=email.bosend.send';
						//$got_args['index_menuaction'] .= '&acctnum='.$acctnum;
					}
					// use this uri in any auto-refresh request - filled during "fill_sort_order_start_msgnum()"
					elseif ($this_arg_name == 'index_refresh_uri')
					{
						$got_args['index_refresh_uri'] ='';
					}
					*/
					// experimental: Set Flag indicative we've run thru this function
					elseif ($this_arg_name == 'already_grab_class_args_gpc')
					{
						$got_args['already_grab_class_args_gpc'] = True;
					}
				}
			}
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: post-loop (internal args) $got_args[] dump:<pre>'; print_r($got_args); echo '</pre>'; }
			
			
			// clear old args (if any) and set the args we just obtained (or preserved)
			//$this->unset_all_args();
			// set new args, some may require processing (like folder will go thru prep_folder_in() automatically
			//while(list($key,$value) = each($got_args))
			//{
			//	$this->set_arg_value($key, $got_args[$key]);
			//}
			
			// use this one call to do it all
			$this->set_arg_array($got_args);
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: finished, $this->get_all_args() dump:<pre>'; print_r($this->get_all_args()); echo '</pre>'; }
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: grab_class_args_gpc: LEAVING<br>'; }
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
		
		
		/*!
		@cabability appsession TEMPORARY DATA CACHING
		@abstract server-side caching of limited, ephermal data, such as a list of messages from an imap search
		@discussion 
		@author Angles
		*/
		// ---- session-only data cached to appsession  ----
		function expire_session_cache_item($data_name='misc',$acctnum='')
		{		
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ($this->debug_session_caching > 0) { echo 'mail_msg: expire_session_cache_item: ENTERED, $this->session_cache_enabled='.serialize($this->session_cache_enabled).', $data_name to expire=['.$data_name.']<br>'; }
			
			// ---  get rid of any L1 cache folder status info  ---
			if ($this->debug_session_caching > 1) { echo 'mail_msg: expire_session_cache_item: Mandatory clearing of L1 cache/class data "folder_status_info" <br>'; }
			// ALWAYS expire "folder_status_info" because many time this expire function is called because of a message move or delete
			$empty_array = array();
			$this->set_arg_value('folder_status_info', $empty_array);
			if ($this->debug_session_caching > 1) { echo 'mail_msg: expire_session_cache_item: clearing L1 cache/class var $data_name = ['.$data_name.']<br>'; }
			if ($this->get_isset_arg($data_name))
			{
				$old_content = $this->get_arg_value($data_name);
				if ($this->debug_session_caching > 2) { echo 'mail_msg: expire_session_cache_item: L1 cache/class OLD value dump:<pre>'; print_r($old_content); echo '</pre>'; }
				if (gettype($old_content) == 'array')
				{
					$empty_data = array();
				}
				else
				{
					$empty_data = '';
				}
				// set the arg item to this blank value, effectively clearing/expiring it
				$this->set_arg_value($data_name, $empty_data);
			}
			// ---  now get rid of any "$data_name" value saved in the session cache  ---
			// for session cache, we can simple set the value to an empty string to blank it out
			$empty_data = '';
			$this->set_arg_value('folder_status_info', $empty_array);
			// save blank data to session to erase/expire it
			$empty_data = '';
			$this->save_session_cache_item($data_name,$empty_data,$acctnum);
			if ($this->debug_session_caching > 0) { echo 'mail_msg: expire_session_cache_item: LEAVING<br>'; }
		}
		
		function save_session_cache_item($data_name='misc',$data,$acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: ENTERED, $this->session_cache_enabled='.serialize($this->session_cache_enabled).'<br>'; }
			if (($this->session_cache_enabled)
			&& (!$data))
			{
				// empty $data means "EXPIRE the data"
				$location = 'acctnum='.(string)$acct_num.';dataname='.$data_name;
				$app = 'email';
				$meta_data = '';
				if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: saving BLANK data (expiriring) location: ['.$location.'] $app='.$app.'; $meta_data dump:<pre>'; print_r($meta_data); echo '</pre>'; }
				if ($this->session_cache_debug_nosave == False)
				{
					$GLOBALS['phpgw']->session->appsession($location,$app,$meta_data);
				}
				else
				{
					echo 'mail_msg: save_session_cache_item: session_cache_debug_nosave disallows actual saving of data<br>';
				}
				if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: LEAVING, expired data<br>'; }
			}
			elseif ($this->session_cache_enabled)
			{
				// set the data in appsession
				$folder_info = $this->get_folder_status_info();
				
				$meta_data = Array();
				$meta_data[$data_name] = $data;
				$meta_data['validity'] = Array();
				if ($data_name == 'msgball_list')
				{
					$meta_data['validity']['folder_long'] = $this->get_arg_value('folder');
					$meta_data['validity']['sort'] = $this->get_arg_value('sort');
					$meta_data['validity']['order'] = $this->get_arg_value('order');
					$meta_data['validity']['uidnext'] = $folder_info['uidnext'];
					$meta_data['validity']['uidvalidity'] = $folder_info['uidvalidity'];
					$meta_data['validity']['number_all'] = $folder_info['number_all'];
					$meta_data['validity']['get_mailsvr_callstr'] = $this->get_arg_value('mailsvr_callstr');
					$meta_data['validity']['mailsvr_account_username'] = $this->get_arg_value('mailsvr_account_username');
				}
				/*
				$accounts = array();
				$accounts[$acctnum] = array();
				$accounts[$acctnum]['data'] = array();
				$accounts[$acctnum]['data'][$data_name] = $data;
				$accounts[$acctnum]['data']['validity'] = Array();
				if ($data_name == 'msgball_list')
				{
					$accounts[$acctnum]['data']['validity']['folder_long'] = $this->get_arg_value('folder');
					$accounts[$acctnum]['data']['validity']['sort'] = $this->get_arg_value('sort');
					$accounts[$acctnum]['data']['validity']['order'] = $this->get_arg_value('order');
					$accounts[$acctnum]['data']['validity']['uidnext'] = $folder_info['uidnext'];
					$accounts[$acctnum]['data']['validity']['uidvalidity'] = $folder_info['uidvalidity'];
					$accounts[$acctnum]['data']['validity']['get_mailsvr_callstr'] = $this->get_arg_value('mailsvr_callstr');
					$accounts[$acctnum]['data']['validity']['mailsvr_account_username'] = $this->get_arg_value('mailsvr_account_username');
				}
				*/
				$location = 'acctnum='.(string)$acctnum.';dataname='.$data_name;
				$app = 'email';
				if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: location: ['.$location.'] $app='.$app.'; $meta_data dump:<pre>'; print_r($meta_data); echo '</pre>'; }
				if ($this->session_cache_debug_nosave == False)
				{
					$GLOBALS['phpgw']->session->appsession($location,$app,$meta_data);
				}
				else
				{
					echo 'mail_msg: save_session_cache_item: session_cache_debug_nosave disallows actual saving of data<br>';
				}
				if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: LEAVING, did set data<br>'; }
			}
			else
			{
				if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: with error - UNHANDLED if...then conditions<br>'; }
			}
		}
		
		function read_session_cache_item($data_name='misc', $acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: ENTERED, $this->session_cache_enabled='.serialize($this->session_cache_enabled).'<br>'; }
			
			if ($this->session_cache_enabled)
			{
				$folder_info = $this->get_folder_status_info();
				
				$location = 'acctnum='.(string)$acctnum.';dataname='.$data_name;
				$app = 'email';
				// get session data
				$got_data = $GLOBALS['phpgw']->session->appsession($location,$app);
				
				if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: location: ['.$location.'] $app='.$app.'; $got_data dump:<pre>'; print_r($got_data); echo '</pre>'; }
				
				// VERIFY this cached data is still valid
				if (($got_data)
				&& ($data_name == 'msgball_list'))
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: handling $data_name='.$data_name.' session validity and/or relevance<br>'; }
					if (($got_data['validity']['folder_long'] == $this->get_arg_value('folder'))
					&& ($got_data['validity']['sort'] == $this->get_arg_value('sort'))
					&& ($got_data['validity']['order'] == $this->get_arg_value('order'))
					&& ($got_data['validity']['uidnext'] == $folder_info['uidnext'])
					&& ($got_data['validity']['uidvalidity'] == $folder_info['uidvalidity'])
					&& ($got_data['validity']['number_all']  == $folder_info['number_all'])
					&& ($got_data['validity']['get_mailsvr_callstr'] == $this->get_arg_value('mailsvr_callstr'))
					&& ($got_data['validity']['mailsvr_account_username'] == $this->get_arg_value('mailsvr_account_username')))
					{
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, successfully restored valid $data_name='.$data_name.' session data<br>'; }
						return $got_data;
					}
					else
					{
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, returning False, $data_name='.$data_name.' session was stale<br>'; }
						return False;
					}
				}
				elseif ($got_data)
				{
					if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, $got_data exists, BUT no handler for $data_name='.$data_name.', so return session data unchecked<br>'; }
					return $got_data;
				}
				else
				{
					if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, returning False, $got_data does NOT EXIST for $data_name='.$data_name.'<br>'; }
					return False;
				}
			}
		}
		
		
		/*!
		@cabability Pref-Based SEMI-PERMENANT DATA CACHING
		@abstract Folder List server-side caching, for data intended to survive and span individual sessions.
		@discussion Folder List data does not change that often, as opposed to the data cached in appsession,
		which often changes with each page view. Refer to class var array $this->cachable_server_items[] 
		to see supported cachable items. Currently the longer-term data we cache with this Perf-Based methodology are:
		(1) 'get_mailsvr_namespace', and
		(2) 'get_folder_list'
		Those items go hand-in-hand. The data cached is that data which is produced (returned) by the function 
		of the same name (easier to remember this way :), i.e. function "get_mailsvr_namespace()" data is saved to
		an array item with base element called "get_mailsvr_namespace". Ditto for function "get_folder_list()".
		Both cached items are necessary to achieve a longer-lived caching of a list of folders available
		to the user for a particular emil account. This folder list does not change often, thus deserving of a 
		longer-lived caching than the appsession caching methodology. Namespace is also cached because
		it is used to analyse the folder_list data (generating "folder_short" from cached "folder_long" names, 
		so those data items need each other.
		SO: The Storage Object for this data is currently (Dec 26, 2001) the Email Preferences database, for
		this reason: it's the only data store available to the email class for which the data survives and spans
		sessions. Perhaps a dedicated table in the DB may be used in the future,
		@author Angles
		*/
		function get_cached_data($calling_function_name='',$data_type='string')
		{
			if ($this->debug_longterm_caching > 0) { echo 'mail_msg: get_cached_data: ENTERING, called by "'.$calling_function_name.'"<br>';}
			
			$got_data = False;
			
			//// preliminary compare userid and mailsvr callstr to that assicoated with cached data (if any)
			//$account_match = $this->match_cached_account();
			
			if (($calling_function_name == '')
			|| ($this->cache_mailsvr_data == False))
			{
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: get_cached_data: caching not enabled, or $calling_function_name was blank<br>';}
				// we may not use cached data
				// if data IS cached, it should be considered STALE and deleted
				if (($this->get_isset_pref($calling_function_name))
				&& ($this->get_pref_value($calling_function_name) != ''))
				{
					if ($this->debug_longterm_caching > 1) { echo 'mail_msg: get_cached_data: caching not enabled AND removing whatever data was previously cached<br>';}
					//$this->remove_cached_data($calling_function_name);
					// if we do not provide $my_function_name, then we expire all "cachable_server_items"
					// which is probably a good idea, we do not want mismatched cached items
					$this->remove_cached_data('');
				}
				// return a boolean False
				if ($this->debug_longterm_caching > 0) { echo 'mail_msg: get_cached_data: LEAVING, returning False<br>';}
				return False;
			}
			
			// so we may use cached data, do we have any?
			if (($this->get_isset_pref($calling_function_name))
			&& ($this->get_pref_value($calling_function_name) != ''))
			{
				$server = $this->get_arg_value('mailsvr_callstr');
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: get_cached_data: data IS cached, need to compare owner: $this->a['.$this->acctnum.'][mailsvr_account_username].$server: '.$this->get_arg_value('mailsvr_account_username').$server.' to value in $this->a[$this->acctnum][prefs][$calling_function_name."_owner"]<br>';}
				if (($this->get_isset_pref($calling_function_name.'_owner'))
				&& ($this->get_pref_value($calling_function_name.'_owner') != '')
				&& ($this->get_pref_value($calling_function_name.'_owner') == $this->get_arg_value('mailsvr_account_username').$server) )
				{
					if ($this->debug_longterm_caching > 1) { echo 'mail_msg: get_cached_data: *match* on compare owner: '.$this->get_pref_value($calling_function_name.'_owner').'=='.$this->get_arg_value('mailsvr_account_username').$server.'<br>'; }
					$got_data = $this->get_pref_value($calling_function_name);
				}
				else
				{
					if ($this->debug_longterm_caching > 1) { echo 'mail_msg: get_cached_data: owner not ser OR failed match on cached owner: '.$this->get_pref_value($calling_function_name.'_owner').' to user '.$this->get_arg_value('mailsvr_account_username').$server.'<br>'; }
				}
			}
			else
			{
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: get_cached_data: cached data NOT SET for $this->a[$this->acctnum][prefs]['.$calling_function_name.'] <br>';}
				// this boolean False tells the code below that no data was retrieved
				$got_data = False;
			}
			
			if ((isset($got_data))
			&& ($got_data))
			{
				if ($this->debug_longterm_caching > 2) { echo 'mail_msg: get_cached_data: $got_data dump:<pre>'; print_r($got_data); echo '</pre>'; }
				if ($this->debug_longterm_caching > 0) { echo 'mail_msg: get_cached_data: LEAVING, $got_data is set, returning whatever was in the cache<br>';}
				return $got_data;
			}
			else
			{
				if ($this->debug_longterm_caching > 0) { echo 'mail_msg: get_cached_data: LEAVING, returning False, cached data was not set, or was empty, or failed owner match<br>';}
				return False;
			}
		}
		
		function set_cached_data($calling_function_name='',$data_type='string',$data='')
		{
			if ($this->debug_longterm_caching > 0) { echo 'mail_msg: set_cached_data: ENTERING, called by "'.$calling_function_name.'"<br>';}
			
			if (($this->cache_mailsvr_data == False)
			|| ($calling_function_name == '')
			|| (!isset($data))
			|| (!$data))
			{
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: set_cached_data: caching not enabled, or $calling_function_name was blank, or $data was blank<br>';}
				// we may not use cached data
				// if data IS cached, it should be considered STALE and deleted
				if (($this->get_isset_pref($calling_function_name))
				&& ($this->get_pref_value($calling_function_name) != ''))
				{
					if ($this->debug_longterm_caching > 1) { echo 'mail_msg: set_cached_data: caching not available AND removing whatever data was previously cached<br>';}
					//$this->remove_cached_data($calling_function_name);
					// if we do not provide $my_function_name, then we expire all "cachable_server_items"
					// which is probably a good idea, we do not want mismatched cached items
					$this->remove_cached_data('');
				}
				// return a boolean False
				if ($this->debug_longterm_caching > 0) { echo 'mail_msg: set_cached_data: LEAVING, returning False<br>';}
				return False;
			}
			elseif (($this->cache_mailsvr_data == True)
			&& ($calling_function_name != '')
			&& (isset($data))
			&& ($data))
			{
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: set_cached_data: caching IS enabled, AND $calling_function_name AND $data contain data<br>';}
				if ($this->debug_longterm_caching > 2) { echo 'mail_msg: set_cached_data: about to write this to prefs/cache: $data dump:<pre>'; print_r($data); echo '</pre>'; }
				$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name);
				$GLOBALS['phpgw']->preferences->add('email',$calling_function_name,$data);
				// also write comparative data so we can later match this cached data to the correct mailserver account
				$server = $this->get_arg_value('mailsvr_callstr');
				$data_owner = $this->get_arg_value('mailsvr_account_username') .$server;
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: set_cached_data: writting owner data $data_owner: ['.$data_owner.'] in $calling_function_name."_owner"<br>'; }
				$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name.'_owner');
				$GLOBALS['phpgw']->preferences->add('email',$calling_function_name.'_owner',$data_owner);
				// write do DB
				$GLOBALS['phpgw']->preferences->save_repository();
				// save repository *should* not alter our carefully constructed prefs array in $this->a[$this->acctnum]['prefs'][]
				// so we need to put the data there, next session start, when the prefs are initially read, then this data will automatically end up there
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: set_cached_data: until next prefs read (on next session start), we need to manually put the data in our prefs array that is already in memory<br>';}
				$this->set_pref_value($calling_function_name, $data);
				$this->set_pref_value($calling_function_name.'_owner', $data_owner);
				
				if ($this->debug_longterm_caching > 2) { echo 'mail_msg: set_cached_data: POST data write to $this->a[$this->acctnum][prefs]['.$calling_function_name.']  data dump:<pre>'; print_r($this->get_pref_value($calling_function_name)); echo '</pre>'; }
				if ($this->debug_longterm_caching > 0) { echo 'mail_msg: set_cached_data: LEAVING, returning True<br>';}
				return True;
			}
			
			if ($this->debug_longterm_caching > 1) { echo 'mail_msg: set_cached_data: unexpectedly got past caching logic, nothing saved<br>';}
			if ($this->debug_longterm_caching > 0) { echo 'mail_msg: set_cached_data: LEAVING, returning False, unexpected, no action taken<br>'; }
			return False;
		}
		
		function remove_cached_data($calling_function_name='')
		{
			if ($this->debug_longterm_caching > 0) { echo 'mail_msg: remove_cached_data: ENTERING, data set: ['.$calling_function_name.'], if blank will remove all cachable_server_items and *_owner items<br>';}
			if ($this->debug_longterm_caching > 1) { echo 'mail_msg: remove_cached_data: about to remove .... <br>'; }
			if ($calling_function_name == '')
			{
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: remove_cached_data: no calling_function_name was provided, deleting ALL cachable_server_items<br>';}
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: remove_cached_data: add *_owner to each item in $this->cachable_server_items array<br>';}
				$cachables_with_owner = Array();
				for ($i=0; $i<count($this->cachable_server_items);$i++)
				{
					$next_idx = count($cachables_with_owner);
					$cachables_with_owner[$next_idx] = $this->cachable_server_items[$i];
					$next_idx = count($cachables_with_owner);
					$cachables_with_owner[$next_idx] = $this->cachable_server_items[$i].'_owner';
				}
				if ($this->debug_longterm_caching > 2) { echo 'mail_msg: remove_cached_data: $cachables_with_owner data dump:<pre>'; print_r($cachables_with_owner); echo '</pre>'; }
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: remove_cached_data: remove any existing cache elements in the $cachables_with_owner array<br>'; }
				for ($i=0; $i<count($cachables_with_owner);$i++)
				{
					$this_cachable_item_name = $cachables_with_owner[$i];
					$deleting_needed = isset($GLOBALS['phpgw']->preferences->data['email'][$this_cachable_item_name]);
					if ($deleting_needed)
					{
						if ($this->debug_longterm_caching > 2) { echo 'mail_msg: remove_cached_data: preferences object does have data for [email]['.$this_cachable_item_name.'], so deleting...<br>';}
						$GLOBALS['phpgw']->preferences->delete('email',$this_cachable_item_name);
					}
					else
					{
						if ($this->debug_longterm_caching > 2) { echo 'mail_msg: remove_cached_data: preferences object has NO data for [email]['.$this_cachable_item_name.'], no need to selete<br>';}
					}
					$clearing_needed = $this->get_isset_pref($this_cachable_item_name);
					if ($clearing_needed)
					{
						if ($this->debug_longterm_caching > 2) { echo 'mail_msg: remove_cached_data: ['.$this_cachable_item_name.'] until next prefs read (on next session start), we need to manually remove the data in our prefs array that is already in memory<br>';}
						$this->unset_pref($this_cachable_item_name);
					}
					else
					{
						if ($this->debug_longterm_caching > 2) { echo 'mail_msg: remove_cached_data: ['.$this_cachable_item_name.'] prefs array that is already in memory did not have any data to remove<br>';}
					}
				}
				$GLOBALS['phpgw']->preferences->save_repository();
			}
			else
			{
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: remove_cached_data: removing item based on "calling_function_name" arg<br>';}
				$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name);
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: remove_cached_data: removing item based on "calling_function_name"+"_owner" arg<br>'; }
				$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name.'_owner');
				$GLOBALS['phpgw']->preferences->save_repository();
				if ($this->debug_longterm_caching > 1) { echo 'mail_msg: remove_cached_data: until next prefs read (on next session start), we need to manually remove the data in our prefs array that is already in memory<br>';}
				$clearing_needed = ( $this->get_isset_pref($calling_function_name) || $this->get_isset_pref($calling_function_name.'_owner') );
				if ($clearing_needed)
				{
					if ($this->debug_longterm_caching > 2) { echo 'mail_msg: remove_cached_data: manually remove $this->a[$this->acctnum][prefs]['.$calling_function_name.'] from memory<br>';}
					$this->unset_pref($calling_function_name);
					if ($this->debug_longterm_caching > 2) { echo 'mail_msg: remove_cached_data: manually remove $this->a[$this->acctnum][prefs]['.$calling_function_name.'_owner'.'] from memory<br>';}
					$this->unset_pref($calling_function_name.'_owner');
				}
				else
				{
					if ($this->debug_longterm_caching > 1) { echo 'mail_msg: remove_cached_data: prefs array that is already in memory did not have any data to remove<br>';}
				}
			}
			if ($this->debug_longterm_caching > 0) { echo 'mail_msg: remove_cached_data: LEAVING, returning true<br>';}
			return True;
		}
		
		/*!
		@capability OOP-Style Access Methods to Private Object Properties
		@abstract: simple access methods to read and set data, with transparent account number handling
		@discussion When multiple email accounts are enables, they may even be active at the same time,
		thus the properties and preferences for any individual email account must be distinctly accessable 
		for each email account with as little brain damage to the developer as possible. These access methods 
		server two purposes:
		(1) centralize access to all params and oprefs into a common, standardized methodology, and
		(2) these access functions also transparently handly the dirty work of tracking which email account
		the data applies to, takes care of any special handling a param may require, and it's classic OOP style.
		With the exception of a few paramaters/arguments that are not specific to any individual email acount,
		such as for private, internal object core properties, the developer need only use these functions to 
		access object params, arguments, and preferences.
		@author Angles
		*/
		
		/*!
		@function get_acctnum
		@abstract: read which account number the object is currently activated on
		@param $unset_returns_default  boolean  default True. If no acctnum is currently set,
		should this function return a boolean False or a hardcoded "fallback default" account number,
		typically integer 0. Default is to return a fallback default account number.
		@returns (most typically) the internal account number of the currently active email account, 
		but can be set, via the $unset_returns_default param, 
		@discussion When multiple email accounts are enabled, all arg/param and preference access 
		functions "pivot" off of this "object->acctnum" property, it serves essentially as the array key 
		which maps the various access functions to the data of the intended account number.
		DEVELOPERS NOTE: The integer zero returned by this function can sometimes be mistaken
		as "empty" of "false", when using conditionals such as
		if ($my_acctnum) { then do this };
		may incorrectly interper integer 0 as a "false" and this example conditional would not behave 
		as expected, since there is infact a valid acount number of 0 in the variable. The preferred test 
		for that type of condition is:
		if ((string)$my_acctnum != '') { then do this };
		which produces a more desirable result.
		@author Angles
		*/
		function get_acctnum($unset_returns_default=True)
		{
			if ($this->debug_accts > 0) { echo 'mail_msg: get_acctnum: ENTERING, (parm $unset_returns_default=['.serialize($unset_returns_default).'])<br>'; }
			
			if ((isset($this->acctnum))
			&& ((string)$this->acctnum != ''))
			{
				if ($this->debug_accts > 0) { echo 'mail_msg: get_acctnum: LEAVING, $this->acctnum exists, returning it: '.serialize($this->acctnum).'<br>';}
				return $this->acctnum;
			}
			// ok, no useful acctnumber exists, what should we do
			elseif ($unset_returns_default == True)
			{
				
				if ($this->debug_accts > 0) { echo 'mail_msg: get_acctnum: LEAVING, NO $this->acctnum exists, returning $this->fallback_default_acctnum : '.serialize($this->fallback_default_acctnum).'<br>';}
				return $this->fallback_default_acctnum;
			}
			else
			{
				if ($this->debug_accts > 0) { echo 'mail_msg: get_acctnum: LEAVING, NO $this->acctnum exists, returning FALSE<br>';}
				return False;
			}
		}
		
		/*!
		@function set_acctnum
		@abstract: instruct the object which email account is the desired active account for all params,
		args, and preferences should refer to.
		@param $acctnum  integer  
		@returns True if a valid param $acctnum is given and the object->acctnum value is set, False if 
		invalid data is passed in the param.
		@discussion ?
		@author Angles
		*/
		function set_acctnum($acctnum='')
		{
			if ($this->debug_accts > 0) { echo 'mail_msg: set_acctnum: ENTERING, (parm $acctnum=['.serialize($acctnum).'])<br>'; }
			if ((isset($acctnum))
			&& ((string)$acctnum != ''))
			{
				$this->acctnum = $acctnum;
				if ($this->debug_accts > 0) { echo 'mail_msg: set_acctnum: LEAVING, returning True, made $this->acctnum = $acctnum ('.serialize($acctnum).')<br>'; }
				return True;
			}
			else
			{
				if ($this->debug_accts > 0) { echo 'mail_msg: set_acctnum: LEAVING, returning False, value $acctnum not sufficient to set $this->acctnum<br>'; }
				return False;
			}
		}
		
		
		/* * * * * * * * * * * * * * * * * *
		* OOP-Style Access Methods for Preference Values
		* * * * * * * * * * * * * * * * * */
		function get_pref_value($pref_name='',$acctnum='')
		{
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_pref_value: ENTERING, $pref_name: ['.$pref_name.'] $acctnum: ['.$acctnum.']'.'<br>'; }
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
				if ($this->debug_args_oop_access > 1) { echo 'mail_msg(_wrappers): get_pref_value: obtained acctnum from "$this->get_acctnum()", got $acctnum: ['.$acctnum.']'.'<br>'; }
			}
			
			if ((isset($pref_name))
			&& ((string)$pref_name != '')
			&& (isset($this->a[$acctnum]['prefs'][$pref_name])))
			{
				if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_pref_value: LEAVING, returning $this->a['.$acctnum.'][prefs]['.$pref_name.'] : ['.$this->a[$acctnum]['prefs'][$pref_name].'] <br>'; }
				return $this->a[$acctnum]['prefs'][$pref_name];
			}
			else
			{
				// arg not set, or invalid input $arg_name
				if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_pref_value: LEAVING with ERRROR, pref item was not found<br>'; }
				return;
			}
		}
		
		function set_pref_value($pref_name='', $this_value='', $acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ((isset($pref_name))
			&& ((string)$pref_name != ''))
			{
				$this->a[$acctnum]['prefs'][$pref_name] = $this_value;
				// return True to indicate success
				return True;
			}
			else
			{
				// return False to indicate invalid input $arg_name
				return False;
			}
		}
		
		function get_isset_pref($pref_name='',$acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			// error check
			if ((isset($pref_name))
			&& ((string)$pref_name != '')
			&& (isset($this->a[$acctnum]['prefs'][$pref_name])))
			{
				return True;
			}
			else
			{
				// arg not set, or invalid input $arg_name
				return False;
			}
		}
		
		function unset_pref($pref_name='', $acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ((isset($pref_name))
			&& ((string)$pref_name != ''))
			{
				$this->a[$acctnum]['prefs'][$pref_name] = '';
				unset($this->a[$acctnum]['prefs'][$pref_name]);
				// return True to indicate success
				return True;
			}
			else
			{
				// return False to indicate invalid input $pref_name
				return False;
			}
		}
		
		function get_all_prefs($acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if (isset($this->a[$acctnum]['prefs']))
			{
				return $this->a[$acctnum]['prefs'];
			}
			else
			{
				// arg not set, or invalid input $arg_name
				return;
			}
		}
		
		function set_pref_array($pref_array_data='', $acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			$this->a[$acctnum]['prefs'] = array();
			
			if ((isset($pref_array_data))
			&& (count($pref_array_data > 0)))
			{
				$this->a[$acctnum]['prefs'] = $pref_array_data;
				// return True to indicate we filled, not just cleared
				return True;
			}
			else
			{
				// return False to indicate all we did was clear the args, no data was fed
				return False;
			}
		}
		
		
		/* * * * * * * * * * * * * * * * * *
		* OOP-Style Access Methods for Class Params/Args Values
		* * * * * * * * * * * * * * * * * */
		function _get_arg_is_known($arg_name='', $calling_function_name='')
		{
			if ($arg_name == '')
			{
				return False;
			}
			if ($calling_function_name == '')
			{
				$calling_function_name == 'UNSPECIFIED';
			}
			// loop thru known externally controlled args
			$finding = False;
			$report = '';
			for($i=0; $i < count($this->known_external_args); $i++)
			{
				if ($arg_name == $this->known_external_args[$i])
				{
					$finding = True;
					$report = '*is* known (external)';
					break;
				}
			}
			// check internal args
			for($i=0; $i < count($this->known_internal_args); $i++)
			{
				if ($arg_name == $this->known_internal_args[$i])
				{
					$finding = True;
					$report = '*is* known (internal)';
					break;
				}
			}
			if (!$finding)
			{
				$report = '*NOT* KNOWN *NOT* KNOWN *NOT* KNOWN *NOT* KNOWN';
			}
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): _arg_is_known: TEST: '.$report.' ; $arg_name: ['.$arg_name.'] called by $calling_function_name: ['.$calling_function_name.'] '.'<br>'; }
			return $finding;
		}
		
		function get_isset_arg($arg_name='',$acctnum='')
		{
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_isset_arg: ENTERING, $arg_name: ['.$arg_name.'] $acctnum: ['.$acctnum.']'.'<br>'; }
			if ($this->debug_args_oop_access > 1) { $this->_get_arg_is_known($arg_name, 'get_isset_arg'); }
			
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
				if ($this->debug_args_oop_access > 1) { echo 'mail_msg(_wrappers): get_isset_arg: obtained $acctnum from $this->get_acctnum(): ['.$acctnum.']'.'<br>'; }
			}
			/*
			// OBSOLETED
			if ((isset($arg_name))
			&& ((string)$arg_name != '')
			&& (isset($this->a[$acctnum]['args'][$arg_name])))
			{
				return True;
			}
			else
			{
				// arg not set, or invalid input $arg_name
				return False;
			}
			*/
			
			/*
			// OOP VERSION if PROBLEMATIC
			// but it may not give intended answer because
			// "get_arg_value" will handoff processing to specialized functions that WILL fill the value
			// sometimes simply with default values, which would cause this function to return unexpected results
			$test_this = $this->get_arg_value($arg_name, $acctnum);
			if (isset($test_this))
			{
				return True;
			}
			*/
			
			// Best Version at this time, if something is not set, no handoff support function will fill it
			// before we can return false
			
			// $arg_name has sub-levels
			if ((isset($arg_name))
			&& ((string)$arg_name != '')
			&& (strstr($arg_name, '][')))
			{
				// request for $arg_name['sub-element']
				if ($this->debug_args_oop_access > 1) { echo 'mail_msg(_wrappers): get_isset_arg: $arg_name is requesting sub-level array element(s),  use EVAL, $arg_name: '.serialize($arg_name).'<br>'; }
				$evaled = '';
				//$code = '$evaled = $this->a[$acctnum][\'args\']'.$arg_name.';';
				$code = '$evaled = $this->a[$acctnum]["args"]'.$arg_name.';';
				if ($this->debug_args_oop_access > 1) { echo ' * $code: '.$code.'<br>'; }
				eval($code);
				if ($this->debug_args_oop_access > 1) { echo ' * $evaled: '.$evaled.'<br>'; }
				if (isset($evaled))
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_isset_arg: LEAVING returning $evaled: ['.$evaled.'] produced by $code: '.$code.'<br>'; }
					return True;
				}
			}
			// $arg_name has NO sub-levels
			elseif (isset($this->a[$acctnum]['args'][$arg_name]))
			{
				if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_isset_arg: LEAVING returning $this->a[$acctnum('.$acctnum.')][args][$arg_name]: '.$this->a[$acctnum]['args'][$arg_name].'<br>'; }
				return True;
			}
			// if we get here, it was not set
			return False;
		}
		
		function unset_arg($arg_name='', $acctnum='')
		{
			if ($this->debug_args_oop_access > 1) { $this->_get_arg_is_known($arg_name, 'unset_arg'); }
			
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ((isset($arg_name))
			&& ((string)$arg_name != ''))
			{
				$this->a[$acctnum]['args'][$arg_name] = '';
				unset($this->a[$acctnum]['args'][$arg_name]);
				// return True to indicate success
				return True;
			}
			else
			{
				// return False to indicate invalid input $arg_name
				return False;
			}
		}
		
		function get_arg_value($arg_name='',$acctnum='')
		{
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: ENTERING ($arg_name: ['.$arg_name.'], $acctnum: ['.$acctnum.'] )<br>'; }
			if ($this->debug_args_oop_access > 1) { $this->_get_arg_is_known($arg_name, 'get_arg_value'); }
			
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ((isset($arg_name))
			&& ((string)$arg_name != ''))
			{
				// ----  SPECIAL HANDLERS  ----
				if ($arg_name == 'mailsvr_callstr')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_mailsvr_callstr()<br>'; }
					return $this->get_mailsvr_callstr();
				}
				elseif ($arg_name == 'mailsvr_namespace')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_mailsvr_namespace()<br>'; }
					return $this->get_mailsvr_namespace();
				}
				elseif ($arg_name == 'mailsvr_delimiter')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_mailsvr_delimiter()<br>'; }
					return $this->get_mailsvr_delimiter();
				}
				elseif ($arg_name == 'folder_list')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_folder_list()<br>'; }
					return $this->get_folder_list();
				}
				/*
				elseif ($arg_name == 'folder')
				{
					if ($this->debug_args_oop_access > 1) { echo 'mail_msg(_wrappers): get_arg_value: request for backwards compat arg "folder"<br>'; }
					// look for foder in (1) msgball , then (2) fldball , then (3) return default value INBOX
					if ( (isset($this->a[$acctnum]['args']['msgball']['folder']))
					&& ($this->a[$acctnum]['args']['msgball']['folder'] != '') )
					{
						$folder_arg_decision = $this->a[$acctnum]['args']['msgball']['folder'];
						if ($this->debug_args_oop_access > 1) { echo 'mail_msg(_wrappers): get_arg_value: request for "folder" will use value in $this->a['.$acctnum.'][args][msgball][folder] = ['.$folder_arg_decision.']<br>'; }
					}
					elseif ( (isset($this->a[$acctnum]['args']['fldball']['folder']))
					&& ($this->a[$acctnum]['args']['fldball']['folder'] != '') )
					{
						$folder_arg_decision = $this->a[$acctnum]['args']['fldball']['folder'];
						if ($this->debug_args_oop_access > 1) { echo 'mail_msg(_wrappers): get_arg_value: request for "folder" will use value in $this->a['.$acctnum.'][args][fldball][folder] = ['.$folder_arg_decision.']<br>'; }
					}
					else
					{
						if ($this->debug_args_oop_access > 1) { echo 'mail_msg(_wrappers): get_arg_value: request for "folder" using "INBOX", found nothing in [args][msgball][folder] nor [args][fldball][folder]<br>'; }
						$folder_arg_decision = 'INBOX';
					}
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING, returning (backward compat) $folder_arg_decision ['.$folder_arg_decision.']<br>'; }
					return $folder_arg_decision;
				}
				*/
				// ----  STANDARD HANDLER (arg_name has sub-levels) ----
				elseif (strstr($arg_name, ']['))
				{
					// request for $arg_name['sub-element']
					// represents code which typically is an array referencing a system/api property
					if ($this->debug_args_oop_access > 1) { echo 'mail_msg(_wrappers): get_arg_value: $arg_name is requesting sub-level array element(s),  use EVAL, $arg_name: '.serialize($arg_name).'<br>'; }
					$evaled = '';
					//$code = '$evaled = $this->a[$acctnum][\'args\']'.$arg_name.';';
					$code = '$evaled = $this->a[$acctnum]["args"]'.$arg_name.';';
					if ($this->debug_args_oop_access > 1) { echo ' * $code: '.$code.'<br>'; }
					eval($code);
					if ($this->debug_args_oop_access > 1) { echo ' * $evaled: '.$evaled.'<br>'; }
					if (isset($evaled))
					{
						if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING returning $evaled: ['.$evaled.'] produced by $code: '.$code.'<br>'; }
						return $evaled;
					}
				}
				// ----  STANDARD HANDLER (arg_name has sub-levels) ----
				elseif (isset($this->a[$acctnum]['args'][$arg_name]))
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING returning $this->a[$acctnum('.$acctnum.')][args][$arg_name]: '.$this->a[$acctnum]['args'][$arg_name].'<br>'; }
					return $this->a[$acctnum]['args'][$arg_name];
				}
			}
			
			// we ONLY get here if there's no data to return,
			// arg not set, or invalid input $arg_name
			// otherwise, anything that is sucessful returns and exist at that point, never gets to here
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING, returning *nothing*, arg not set of input arg invalid, using naked "return" call<br>'; }
			return;
		}
		
		function _direct_access_arg_value($arg_name='',$acctnum='')
		{
			if ($this->debug_args_oop_access > 1) { $this->_get_arg_is_known($arg_name, '_direct_access_arg_value'); }
			
			// PRIVATE - for use by internal functions
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if (isset($this->a[$acctnum]['args'][$arg_name]))
			{
				return $this->a[$acctnum]['args'][$arg_name];
			}
			else
			{
				// arg not set, or invalid input $arg_name
				return;
			}
		}
		
		function set_arg_value($arg_name='', $this_value='', $acctnum='')
		{
			if ($this->debug_args_oop_access > 1) { $this->_get_arg_is_known($arg_name, 'set_arg_value'); }
			
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ((isset($arg_name))
			&& ((string)$arg_name != ''))
			{
				/*
				// can not do prep_folder_in because it calls "folder_lookup" which requires an active mailsvr stream login
				// ----  SPECIAL HANDLERS  ----
				if ($arg_name == 'folder')
				{
					$processed_value = $this->prep_folder_in($this_value);
					$this_value = $processed_value;
				}
				*/
				// SET it, any special processing should be taken care just above here
				$this->a[$acctnum]['args'][$arg_name] = $this_value;
				// return True to indicate success
				return True;
			}
			else
			{
				// return False to indicate invalid input $arg_name
				return False;
			}
		}
		
		function set_arg_array($arg_array_data='', $acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			$this->a[$acctnum]['args'] = array();
			
			if ((isset($arg_array_data))
			&& (count($arg_array_data > 0)))
			{
				/*
				while(list($key,$value) = each($arg_array_data))
				{
					$this->set_arg_value($key, $arg_array_data[$key]);
				}
				*/
				$this->a[$acctnum]['args'] = $arg_array_data;
				// return True to indicate we filled, not just cleared
				return True;
			}
			else
			{
				// return False to indicate all we did was clear the args, no data was fed
				return False;
			}
		}
		
		function get_all_args($acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if (isset($this->a[$acctnum]['args']))
			{
				return $this->a[$acctnum]['args'];
			}
			else
			{
				// arg not set, or invalid input $arg_name
				return;
			}
		}
		
		function unset_all_args($acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			$this->a[$acctnum]['args'] = array();
		}
		
		
		// depreciated
		//function get_folder($acctnum='')
		//{
		//	return $this->get_arg_value('folder');
		//}
		
		// depreciated
		//function get_msgnum($acctnum='')
		//{
		//	return $this->get_arg_value('["msgball"]["msgnum"]');
		//}
		
		//function get_pref_layout($acctnum='')
		//{
		//	return $this->get_pref_value('layout', $acctnum);
		//}
		
		
	}  // end class mail_msg_wrappers
?>
