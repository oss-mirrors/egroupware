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
		@param $msg_number : integer
		@result returns the IMAP_FETSCSTRUCTURE data
		@discussion Wrapper supplies the needed mailsvr_stream arg to IMAP_FETSCSTRUCTURE
		The data communications object (class mail_dcom) is supplied by the class
		*/
		function phpgw_fetchstructure($msg_number='')
		{
			//if ((string)$msg_number == '')
			//if ($msg_number == '')
			if (empty($msg_number))
			{
				$msg_number = $this->get_arg_value('msgnum');
			}
			
			return $this->a[$this->acctnum]['dcom']->fetchstructure($this->get_arg_value('mailsvr_stream'), $msg_number);
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
			//if ($msg_number == '')
			if (empty($msg_number))
			{
				$msg_number = $this->get_arg_value('msgnum');
			}	
			
			// Message Information: THE MESSAGE'S HEADERS RETURNED AS A STRUCTURE
			return $this->a[$this->acctnum]['dcom']->header($this->get_arg_value('mailsvr_stream'), $msg_number);
		}
		
		function phpgw_fetchheader($msg_number='')
		{
			//if ($msg_number == '')
			if (empty($msg_number))
			{
				$msg_number = $this->get_arg_value('msgnum');
			}
		
			// Message Information: THE MESSAGE'S HEADERS RETURNED RAW (no processing)
			return $this->a[$this->acctnum]['dcom']->fetchheader($this->get_arg_value('mailsvr_stream'), $msg_number);
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
				return $this->a[$this->acctnum]['dcom']->get_flag($this->get_arg_value('mailsvr_stream'),$this->get_arg_value('msgnum'),$flag);
			}
		}
		
		
	// ====  Functions For Getting A Message Or A Part (MIME Part) Of A Message  ====
		function phpgw_body()
		{
			return $this->a[$this->acctnum]['dcom']->get_body($this->get_arg_value('mailsvr_stream'), $this->get_arg_value('msgnum'));
		}
		
		function phpgw_fetchbody($part_num_mime='', $flags='')
		{
			return $this->a[$this->acctnum]['dcom']->fetchbody($this->get_arg_value('mailsvr_stream'), $this->get_arg_value('msgnum'), $part_num_mime, $flags);
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
			// try to restore "msg_array" from saved session data store
			$cached_msg_array = $this->read_session_cache_item('msg_array');
			if ($cached_msg_array)
			{
				return $cached_msg_array['msg_array'];
			}
			else
			{
				$msg_array = array();
				$msg_array = $this->a[$this->acctnum]['dcom']->sort($this->get_arg_value('mailsvr_stream'), $this->get_arg_value('sort'), $this->get_arg_value('order'));
				// save "msg_array" to session data store
				$this->save_session_cache_item('msg_array', $msg_array);
				return $msg_array;
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
			$mailbox_detail = $this->a[$this->acctnum]['dcom']->mailboxmsginfo($this->get_arg_value('mailsvr_stream'));
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
			$mailbox_status = $this->a[$this->acctnum]['dcom']->status($this->get_arg_value('mailsvr_stream'),$server_str.$this->get_arg_value('folder'),SA_ALL);
			
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
			return $this->a[$this->acctnum]['dcom']->status($this->get_arg_value('mailsvr_stream'),"$server_str"."$feed_folder_long",SA_ALL);
		}
		
		function phpgw_server_last_error()
		{
			return $this->a[$this->acctnum]['dcom']->server_last_error();
		}
		
		function phpgw_ping()
		{
			return $this->a[$this->acctnum]['dcom']->noop_ping_test($this->get_arg_value('mailsvr_stream'));
		}
		
		function phpgw_search($criteria,$flags='')
		{
			return $this->a[$this->acctnum]['dcom']->i_search($this->get_arg_value('mailsvr_stream'),$criteria,$flags);
		}
		
		function phpgw_createmailbox($folder)
		{
			return $this->a[$this->acctnum]['dcom']->createmailbox($this->get_arg_value('mailsvr_stream'), $folder);
		}
		
		function phpgw_deletemailbox($folder)
		{
			return $this->a[$this->acctnum]['dcom']->deletemailbox($this->get_arg_value('mailsvr_stream'), $folder);
		}
		
		function phpgw_renamemailbox($folder_old,$folder_new)
		{
			return $this->a[$this->acctnum]['dcom']->renamemailbox($this->get_arg_value('mailsvr_stream'), $folder_old, $folder_new);
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
				$this->expire_session_cache_item('msg_array');
				// do the append
				return $this->a[$this->acctnum]['dcom']->append($this->get_arg_value('mailsvr_stream'), "$server_str"."$official_folder_long", $message, $flags);
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
			$this->expire_session_cache_item('msg_array');
			
			return $this->a[$this->acctnum]['dcom']->mail_move($this->get_arg_value('mailsvr_stream'),$msg_list,$mailbox);
		}
		
		function phpgw_expunge()
		{
			$this->a[$this->acctnum]['dcom']->expunge($this->get_arg_value('mailsvr_stream'));
		}
		
		function phpgw_delete($msg_num,$flags=0, $currentfolder="") 
		{
			//$this->a[$this->acctnum]['dcom']->delete($this->get_arg_value('mailsvr_stream'), $this->get_arg_value('msglist'][$i],"",$this->get_arg_value('folder'));
			
			if (($this->get_isset_pref('use_trash_folder'))
			&& ($this->get_pref_value('use_trash_folder')))
			{
				$trash_folder_long = $this->get_folder_long($this->get_pref_value('trash_folder_name'));
				$trash_folder_short = $this->get_folder_short($this->get_pref_value('trash_folder_name'));
				if ($currentfolder != '')
				{
					$currentfolder_short = $this->get_folder_short($currentfolder);
				}
				// if we are deleting FROM the trash folder, we do a straight delete
				if ($currentfolder_short == $trash_folder_short)
				{
					// delete session msg array data thAt is now stale
					$this->expire_session_cache_item('msg_array');
					
					//return imap_delete($stream,$msg_num);
					return $this->a[$this->acctnum]['dcom']->delete($this->get_arg_value('mailsvr_stream'), $msg_num);
				}
				else
				{
					// does the trash folder actually exist ?
					$official_trash_folder_long = $this->folder_lookup('', $this->get_pref_value('trash_folder_name'));
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
						$this->expire_session_cache_item('msg_array');
						
						//return imap_mail_move($stream,$msg_num,$official_trash_folder_long);
						return $this->phpgw_mail_move($msg_num,$official_trash_folder_long);
					}
					else
					{
						// delete session msg array data thAt is now stale
						$this->expire_session_cache_item('msg_array');
						
						// we do not have the trash official folder name, but we have to do something
						// can't just leave the mail sitting there
						// so just straight delete the message
						//return imap_delete($stream,$msg_num);
						return $this->a[$this->acctnum]['dcom']->delete($this->get_arg_value('mailsvr_stream'), $msg_num);
					}
				}
			}
			else
			{
				// delete session msg array data thAt is now stale
				$this->expire_session_cache_item('msg_array');
				
				//return imap_delete($stream,$msg_num);
				return $this->a[$this->acctnum]['dcom']->delete($this->get_arg_value('mailsvr_stream'), $msg_num);
			}
		}
		
		/*!
		@function explode_fake_uri
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
		function explode_fake_uri($uri_type_string='')
		{
			$fake_url_b = explode('&', $uri_type_string);
			if ($this->debug_args_flow > 2) { echo 'mail_msg: explode_fake_uri: $fake_url_b = explode("&", '.$uri_type_string.') dump:<pre>'; print_r($fake_url_b); echo '</pre>'; }
			
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
			if ($this->debug_args_flow > 2) { echo 'mail_msg: explode_fake_uri: $fake_url_b_2 (sub parts exploded and made into an associative array) dump:<pre>'; print_r($fake_url_b_2); echo '</pre>'; }
			return $fake_url_b_2;
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
			if ($this->debug_args_flow > 0) { echo 'mail_msg: grab_class_args_gpc: ENTERING, (parm $acctnum=['.serialize($acctnum).'])<br>'; }
			if ($this->debug_args_flow > 2) { echo 'mail_msg: grab_class_args_gpc: $GLOBALS[HTTP_POST_VARS] dump:<pre>'; print_r($GLOBALS['HTTP_POST_VARS']); echo '</pre>'; }
			if ($this->debug_args_flow > 2) { echo 'mail_msg: grab_class_args_gpc: $GLOBALS[HTTP_GET_VARS] dump:<pre>'; print_r($GLOBALS['HTTP_GET_VARS']); echo '</pre>'; }
			
			$embedded_data = array();
			// ----  extract any "fake_uri" embedded data from HTTP_POST_VARS  ----
			while(list($key,$value) = each($GLOBALS['HTTP_POST_VARS']))
			{
				if ($this->debug_args_flow > 2) { echo 'mail_msg: grab_class_args_gpc: looking for "_fake_uri" token in HTTP_POST_VARS ['.$key.'] = '.$GLOBALS['HTTP_POST_VARS'][$key].'<br>'; }
				if (strstr($key, '_fake_uri'))
				{
					$embedded_data = $this->explode_fake_uri($GLOBALS['HTTP_POST_VARS'][$key]);
					if ($this->debug_args_flow > 2) { echo 'mail_msg: grab_class_args_gpc: FOUND "_fake_uri" token in HTTP_POST_VARS ['.$GLOBALS['HTTP_POST_VARS'][$key].'] , $embedded_data dump:<pre>'; print_r($embedded_data); echo '</pre>'; }
				}
			}
			
			// put any "_fake_uri" embedded data BACK into HTTP_POST_VARS as if they were values 
			// posted like any other form data
			if (count($embedded_data) > 0)
			{
				while(list($key,$value) = each($embedded_data))
				{
					$GLOBALS['HTTP_POST_VARS'][$key] = $embedded_data[$key];
					if ($this->debug_args_flow > 1) { echo 'mail_msg: grab_class_args_gpc: inserted $embedded_data ['.$key.']=['.$value.'] into $GLOBALS[HTTP_POST_VARS] <br>'; }
				}
			}
			
			$got_args = array();
			
			if ($this->debug_args_flow > 1) { echo 'mail_msg: grab_class_args_gpc: about to loop thru $this->known_external_args<br>'; }
			$loops = count($this->known_external_args);				
			for($i=0;$i<$loops;$i++)
			{
				$this_arg_name = $this->known_external_args[$i];
				//if ($this->debug_args_flow > 2) { echo ' * * (grab pref - external) $this_arg_name: ['.$this_arg_name.']<br>'; }
				if (isset($GLOBALS['HTTP_POST_VARS'][$this_arg_name]))
				{
					if ($this->debug_args_flow> 2) { echo ' * * (grab pref - external) $GLOBALS[HTTP_POST_VARS]['.$this_arg_name.'] IS set to ['.$GLOBALS['HTTP_POST_VARS'][$this_arg_name].']<br>'; }
					$got_args[$this_arg_name] = $GLOBALS['HTTP_POST_VARS'][$this_arg_name];
				}
				elseif (isset($GLOBALS['HTTP_GET_VARS'][$this_arg_name]))
				{
					if ($this->debug_args_flow > 2) { echo ' * * (grab pref - external) $GLOBALS[HTTP_GET_VARS]['.$this_arg_name.'] IS set to ['.$GLOBALS['HTTP_GET_VARS'][$this_arg_name].']<br>'; }
					$got_args[$this_arg_name] = $GLOBALS['HTTP_GET_VARS'][$this_arg_name];
				}
				else
				{
					if ($this->debug_args_flow > 2) { echo ' * (grab pref - external) neither POST nor GET vars have this item set ['.$this_arg_name.'] <br>'; }
				}
			}
			if ($this->debug_args_flow > 2) { echo 'mail_msg: grab_class_args_gpc: post-loop (external args) $got_args[] dump:<pre>'; print_r($got_args); echo '</pre>'; }
			
			
			// in order to handle internal args, we need to determine what account we are dealing with
			// before we can call "get_isset_arg" or "get_arg_value"
			
			// ---  which email account do are these args intended to apply to  ----
			// ORDER OF PREFERENCE for determining account num
			// 1) force fed acct num
			// 2) gpc acctnum (first POST, then GET)
			// 3) current class value for acct num
			// 4a) use class value $this->acctnum if it exists
			// 4b) get a default value to use (usually = 0)
			if ((isset($acctnum))
			&& ((string)$acctnum != ''))
			{
				// do nothing, we'll use this value below
				if ($this->debug_args_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": will use function param $acctnum=['.serialize($acctnum).']<br>'; }
			}
			elseif ((isset($got_args['acctnum']))
			&& ((string)$got_args['acctnum'] != ''))
			{
				$acctnum = (int)$got_args['acctnum'];
				// make sure this is an integer
				$got_args['acctnum'] = $acctnum;
				if ($this->debug_args_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": will use GPC aquired $got_args[acctnum] : ['.serialize($got_args['acctnum']).']<br>'; }
			}
			else
			{
				// ok, we have either a force fed $acctnum or got one from GPC
				// if neither, we grab the class's current value for $this->acctnum
				// $this->get_acctnum(True) will return a default value for us to use if $this->acctnum is not set
				// True means "return a default value, NOT boolean false, if $this->acctnum is not set
				$acctnum = $this->get_acctnum(True);
				if ($this->debug_args_flow > 1) { echo 'mail_msg: grab_class_args_gpc: "what acctnum to use": NO *incoming* acctnum specified, called $this->get_acctnum(True), got: ['.serialize($acctnum).']<br>'; }
			}
			if ($this->debug_args_flow > 1) { echo 'mail_msg: grab_class_args_gpc: setting class acctnum by calling $this->set_acctnum('.serialize($acctnum).')<br>'; }
			$this->set_acctnum($acctnum);
			
			
			// INTERNALLY CONTROLLED ARGS
			// preserve pre-existing value, for which "acctnum" must be already obtained, so we
			// know what account to check for existing arg values when we use "get_isset_arg" or "get_arg_value"
			if ($this->debug_args_flow > 1) { echo 'mail_msg: grab_class_args_gpc: about to loop thru $this->known_internal_args<br>'; }
			$loops = count($this->known_internal_args);
			for($i=0;$i<$loops;$i++)
			{
				$this_arg_name = $this->known_internal_args[$i];
				//if ($this->debug_args_flow > 2) { echo ' * * (grab pref - internal) $this_arg_name: '.$this_arg_name.'<br>'; }
				// see if there is a value we can preserve for this arg
				if ($this->get_isset_arg($this_arg_name))
				{
					$preserve_this = $this->get_arg_value($this_arg_name);
					if ($this->debug_args_flow> 2) { echo ' * * (grab pref - internal) preserving internal pre-existing arg: ['.$this_arg_name.'] = ['.$preserve_this.']<br>'; }
					$got_args[$this_arg_name] = $preserve_this;
				}
				else
				{
					if ($this->debug_args_flow > 2) { echo ' * (grab pref - internal) no pre-existing value for ['.$this_arg_name.'], using initialization default: <br>'; }
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
					// experimental: Set Flag indicative we've run thru this function
					elseif ($this_arg_name == 'already_grab_class_args_gpc')
					{
						$got_args['already_grab_class_args_gpc'] = True;
					}
				}
			}
			if ($this->debug_args_flow > 2) { echo 'mail_msg: grab_class_args_gpc: post-loop (internal args) $got_args[] dump:<pre>'; print_r($got_args); echo '</pre>'; }
			
			// clear old args (if any) and set the args we just obtained (or preserved)
			//$this->unset_all_args();
			// set new args, some may require processing (like folder will go thru prep_folder_in() automatically
			//while(list($key,$value) = each($got_args))
			//{
			//	$this->set_arg_value($key, $got_args[$key]);
			//}
			
			// use this one call to do it all
			$this->set_arg_array($got_args);
			if ($this->debug_args_flow > 2) { echo 'mail_msg: grab_class_args_gpc: finished, $this->get_all_args() dump:<pre>'; print_r($this->get_all_args()); echo '</pre>'; }
			if ($this->debug_args_flow > 0) { echo 'mail_msg: grab_class_args_gpc: LEAVING<br>'; }
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
				if ($data_name == 'msg_array')
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
				if ($data_name == 'msg_array')
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
				&& ($data_name == 'msg_array'))
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
			if ($this->debug_caching > 0) { echo 'mail_msg: get_cached_data: ENTERING, called by "'.$calling_function_name.'"<br>';}
			
			$got_data = False;
			
			//// preliminary compare userid and mailsvr callstr to that assicoated with cached data (if any)
			//$account_match = $this->match_cached_account();
			
			if (($calling_function_name == '')
			|| ($this->cache_mailsvr_data == False))
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: caching not enabled, or $calling_function_name was blank<br>';}
				// we may not use cached data
				// if data IS cached, it should be considered STALE and deleted
				if (($this->get_isset_pref($calling_function_name))
				&& ($this->get_pref_value($calling_function_name) != ''))
				{
					if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: caching not enabled AND removing whatever data was previously cached<br>';}
					//$this->remove_cached_data($calling_function_name);
					// if we do not provide $my_function_name, then we expire all "cachable_server_items"
					// which is probably a good idea, we do not want mismatched cached items
					$this->remove_cached_data('');
				}
				// return a boolean False
				if ($this->debug_caching > 0) { echo 'mail_msg: get_cached_data: LEAVING, returning False<br>';}
				return False;
			}
			
			// so we may use cached data, do we have any?
			if (($this->get_isset_pref($calling_function_name))
			&& ($this->get_pref_value($calling_function_name) != ''))
			{
				$server = $this->get_arg_value('mailsvr_callstr');
				if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: data IS cached, need to compare owner: $this->a['.$this->acctnum.'][mailsvr_account_username].$server: '.$this->get_arg_value('mailsvr_account_username').$server.' to value in $this->a[$this->acctnum][prefs][$calling_function_name."_owner"]<br>';}
				if (($this->get_isset_pref($calling_function_name.'_owner'))
				&& ($this->get_pref_value($calling_function_name.'_owner') != '')
				&& ($this->get_pref_value($calling_function_name.'_owner') == $this->get_arg_value('mailsvr_account_username').$server) )
				{
					if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: *match* on compare owner: '.$this->get_pref_value($calling_function_name.'_owner').'=='.$this->get_arg_value('mailsvr_account_username').$server.'<br>'; }
					$got_data = $this->get_pref_value($calling_function_name);
				}
				else
				{
					if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: owner not ser OR failed match on cached owner: '.$this->get_pref_value($calling_function_name.'_owner').' to user '.$this->get_arg_value('mailsvr_account_username').$server.'<br>'; }
				}
			}
			else
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: cached data NOT SET for $this->a[$this->acctnum][prefs]['.$calling_function_name.'] <br>';}
				// this boolean False tells the code below that no data was retrieved
				$got_data = False;
			}
			
			if ((isset($got_data))
			&& ($got_data))
			{
				if ($this->debug_caching > 2) { echo 'mail_msg: get_cached_data: $got_data dump:<pre>'; print_r($got_data); echo '</pre>'; }
				if ($this->debug_caching > 0) { echo 'mail_msg: get_cached_data: LEAVING, $got_data is set, returning whatever was in the cache<br>';}
				return $got_data;
			}
			else
			{
				if ($this->debug_caching > 0) { echo 'mail_msg: get_cached_data: LEAVING, returning False, cached data was not set, or was empty, or failed owner match<br>';}
				return False;
			}
		}
		
		function set_cached_data($calling_function_name='',$data_type='string',$data='')
		{
			if ($this->debug_caching > 0) { echo 'mail_msg: set_cached_data: ENTERING, called by "'.$calling_function_name.'"<br>';}
			
			if (($this->cache_mailsvr_data == False)
			|| ($calling_function_name == '')
			|| (!isset($data))
			|| (!$data))
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: caching not enabled, or $calling_function_name was blank, or $data was blank<br>';}
				// we may not use cached data
				// if data IS cached, it should be considered STALE and deleted
				if (($this->get_isset_pref($calling_function_name))
				&& ($this->get_pref_value($calling_function_name) != ''))
				{
					if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: caching not available AND removing whatever data was previously cached<br>';}
					//$this->remove_cached_data($calling_function_name);
					// if we do not provide $my_function_name, then we expire all "cachable_server_items"
					// which is probably a good idea, we do not want mismatched cached items
					$this->remove_cached_data('');
				}
				// return a boolean False
				if ($this->debug_caching > 0) { echo 'mail_msg: set_cached_data: LEAVING, returning False<br>';}
				return False;
			}
			elseif (($this->cache_mailsvr_data == True)
			&& ($calling_function_name != '')
			&& (isset($data))
			&& ($data))
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: caching IS enabled, AND $calling_function_name AND $data contain data<br>';}
				if ($this->debug_caching > 2) { echo 'mail_msg: set_cached_data: about to write this to prefs/cache: $data dump:<pre>'; print_r($data); echo '</pre>'; }
				$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name);
				$GLOBALS['phpgw']->preferences->add('email',$calling_function_name,$data);
				// also write comparative data so we can later match this cached data to the correct mailserver account
				$server = $this->get_arg_value('mailsvr_callstr');
				$data_owner = $this->get_arg_value('mailsvr_account_username') .$server;
				if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: writting owner data $data_owner: ['.$data_owner.'] in $calling_function_name."_owner"<br>'; }
				$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name.'_owner');
				$GLOBALS['phpgw']->preferences->add('email',$calling_function_name.'_owner',$data_owner);
				// write do DB
				$GLOBALS['phpgw']->preferences->save_repository();
				// save repository *should* not alter our carefully constructed prefs array in $this->a[$this->acctnum]['prefs'][]
				// so we need to put the data there, next session start, when the prefs are initially read, then this data will automatically end up there
				if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: until next prefs read (on next session start), we need to manually put the data in our prefs array that is already in memory<br>';}
				$this->set_pref_value($calling_function_name, $data);
				$this->set_pref_value($calling_function_name.'_owner', $data_owner);
				
				if ($this->debug_caching > 2) { echo 'mail_msg: set_cached_data: POST data write to $this->a[$this->acctnum][prefs]['.$calling_function_name.']  data dump:<pre>'; print_r($this->get_pref_value($calling_function_name)); echo '</pre>'; }
				if ($this->debug_caching > 0) { echo 'mail_msg: set_cached_data: LEAVING, returning True<br>';}
				return True;
			}
			
			if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: unexpectedly got past caching logic, nothing saved<br>';}
			if ($this->debug_caching > 0) { echo 'mail_msg: set_cached_data: LEAVING, returning False, unexpected, no action taken<br>'; }
			return False;
		}
		
		function remove_cached_data($calling_function_name='')
		{
			if ($this->debug_caching > 0) { echo 'mail_msg: remove_cached_data: ENTERING, data set: ['.$calling_function_name.'], if blank will remove all cachable_server_items and *_owner items<br>';}
			if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: about to remove .... <br>'; }
			if ($calling_function_name == '')
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: no calling_function_name was provided, deleting ALL cachable_server_items<br>';}
				if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: add *_owner to each item in $this->cachable_server_items array<br>';}
				$cachables_with_owner = Array();
				for ($i=0; $i<count($this->cachable_server_items);$i++)
				{
					$next_idx = count($cachables_with_owner);
					$cachables_with_owner[$next_idx] = $this->cachable_server_items[$i];
					$next_idx = count($cachables_with_owner);
					$cachables_with_owner[$next_idx] = $this->cachable_server_items[$i].'_owner';
				}
				if ($this->debug_caching > 2) { echo 'mail_msg: remove_cached_data: $cachables_with_owner data dump:<pre>'; print_r($cachables_with_owner); echo '</pre>'; }
				if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: remove any existing cache elements in the $cachables_with_owner array<br>'; }
				for ($i=0; $i<count($cachables_with_owner);$i++)
				{
					$this_cachable_item_name = $cachables_with_owner[$i];
					$deleting_needed = isset($GLOBALS['phpgw']->preferences->data['email'][$this_cachable_item_name]);
					if ($deleting_needed)
					{
						if ($this->debug_caching > 2) { echo 'mail_msg: remove_cached_data: preferences object does have data for [email]['.$this_cachable_item_name.'], so deleting...<br>';}
						$GLOBALS['phpgw']->preferences->delete('email',$this_cachable_item_name);
					}
					else
					{
						if ($this->debug_caching > 2) { echo 'mail_msg: remove_cached_data: preferences object has NO data for [email]['.$this_cachable_item_name.'], no need to selete<br>';}
					}
					$clearing_needed = $this->get_isset_pref($this_cachable_item_name);
					if ($clearing_needed)
					{
						if ($this->debug_caching > 2) { echo 'mail_msg: remove_cached_data: ['.$this_cachable_item_name.'] until next prefs read (on next session start), we need to manually remove the data in our prefs array that is already in memory<br>';}
						$this->unset_pref($this_cachable_item_name);
					}
					else
					{
						if ($this->debug_caching > 2) { echo 'mail_msg: remove_cached_data: ['.$this_cachable_item_name.'] prefs array that is already in memory did not have any data to remove<br>';}
					}
				}
				$GLOBALS['phpgw']->preferences->save_repository();
			}
			else
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: removing item based on "calling_function_name" arg<br>';}
				$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name);
				if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: removing item based on "calling_function_name"+"_owner" arg<br>'; }
				$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name.'_owner');
				$GLOBALS['phpgw']->preferences->save_repository();
				if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: until next prefs read (on next session start), we need to manually remove the data in our prefs array that is already in memory<br>';}
				$clearing_needed = ( $this->get_isset_pref($calling_function_name) || $this->get_isset_pref($calling_function_name.'_owner') );
				if ($clearing_needed)
				{
					if ($this->debug_caching > 2) { echo 'mail_msg: remove_cached_data: manually remove $this->a[$this->acctnum][prefs]['.$calling_function_name.'] from memory<br>';}
					$this->unset_pref($calling_function_name);
					if ($this->debug_caching > 2) { echo 'mail_msg: remove_cached_data: manually remove $this->a[$this->acctnum][prefs]['.$calling_function_name.'_owner'.'] from memory<br>';}
					$this->unset_pref($calling_function_name.'_owner');
				}
				else
				{
					if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: prefs array that is already in memory did not have any data to remove<br>';}
				}
			}
			if ($this->debug_caching > 0) { echo 'mail_msg: remove_cached_data: LEAVING, returning true<br>';}
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
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ((isset($pref_name))
			&& ((string)$pref_name != '')
			&& (isset($this->a[$acctnum]['prefs'][$pref_name])))
			{
				return $this->a[$acctnum]['prefs'][$pref_name];
			}
			else
			{
				// arg not set, or invalid input $arg_name
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
		function get_isset_arg($arg_name='',$acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			// error check
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
		}
		
		function unset_arg($arg_name='', $acctnum='')
		{
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
					return $this->get_mailsvr_callstr();
				}
				elseif ($arg_name == 'mailsvr_namespace')
				{
					return $this->get_mailsvr_namespace();
				}
				elseif ($arg_name == 'mailsvr_delimiter')
				{
					return $this->get_mailsvr_delimiter();
				}
				elseif ($arg_name == 'folder_list')
				{
					return $this->get_folder_list();
				}
				// ----  STANDARD HANDLER  ----
				elseif (isset($this->a[$acctnum]['args'][$arg_name]))
				{
					return $this->a[$acctnum]['args'][$arg_name];
				}
				else
				{
					// arg not set
					return;
				}
			}
			else
			{
				// arg not set, or invalid input $arg_name
				return;
			}
		}
		
		function _direct_access_arg_value($arg_name='',$acctnum='')
		{
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
		//	return $this->get_arg_value('msgnum');
		//}
		
		//function get_pref_layout($acctnum='')
		//{
		//	return $this->get_pref_value('layout', $acctnum);
		//}
		
		
	}  // end class mail_msg_wrappers
?>
