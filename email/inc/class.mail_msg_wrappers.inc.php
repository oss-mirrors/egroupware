<?php
	/**************************************************************************\
	* AngleMail - E-Mail Message Processing Functions				*
	* http://www.anglemail.org									*
	* http://www.phpgroupware.org									*
	*/
	/**************************************************************************\
	* AngleMail - E-Mail Message Processing Functions					*
	* This file written by Angelo Puglisi (Angles) <angles@aminvestments.com>	*
	* Handles specific operations in manipulating email messages			*
	* Copyright (C) 2001, 2002 Angelo Tony Puglisi (Angles)				*
	* ------------------------------------------------------------------------ 		*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by 	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.								*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of	*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License 	*
	* along with this library; if not, write to the Free Software Foundation, 		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/
	
	/* $Id$ */
	
	// =====  INTERFACE FUNCTIONS AND/OR  WRAPPER FUNCTIONS =====
	
	/*!
	@class mail_msg_wrappers
	@abstract  Wrapper functions to be called as "public" functions
	@discussion  Hides the implementation details from the calling process
	Provides most args to the dcom class from variables which class msg processed and set
	Sometimes returns processed data ready to be used for display or information
	MORE DISCUSSION - Why Wrap Here?
	Answer: because once the msg class opens a mailsvr_stream, that will be the only stream
	that instance of the class will have, so WHY keep supplying it as an arg EVERY time?
	Also, same for the "msgnum", unless you are looping thru a message list, you are 
	most likely concerned with only ONE message, and the variable would be the MIME part therein
	*/
	class mail_msg_wrappers extends mail_msg_base
	{
	
		// ====  Functions For Getting Information About A Message  ====
		
		/*!
		@function phpgw_fetchstructure
		@abstract wrapper for IMAP_FETSCSTRUCTURE, phpgw supplies the nedessary stream arg
		@param $msgnum   integer
		@result returns the IMAP_FETSCSTRUCTURE data
		@discussion Wrapper supplies the needed mailsvr_stream arg to IMAP_FETSCSTRUCTURE
		The data communications object (class mail_dcom) is supplied by the class. NOTE: this data 
		CAN ONLY BE OBTAINED FOR A MSG IN THE CURRENTLY SELECTED FOLDER. 
		This means we automatically know which folder this data applies to because it can ONLY be 
		the currently selected folder, and only one folder can be selected at any one time. 
		CACHE NOTE if $this->session_cache_extreme is True, then this data is cached and 
		manipulated by the "extreme" caching code, which will pop a cached "msg_structure" out 
		of cache if the message is moved to another folder. If $this->session_cache_extreme is False, 
		then caching is NOT used on this data.
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
			
			// CHECK FOR CACHED ITEM
			// if "session_cache_extreme" is not enabled, do not use caching for this data
			
			if ($this->session_cache_extreme == True)
			{
				// function read_session_cache_item($data_name='misc', $acctnum='', $special_extra_stuff='')
				// this key, if it exists in the cached array of msg_structures, will hold the data we want as its value 
				$specific_key = (string)$msgball['msgnum'].'_'.$this->prep_folder_out();
				// the cached data is returned as a ready to use object if it exists, or False if not existing
				$cache_msg_structure = $this->read_session_cache_item('msg_structure', $acctnum, $specific_key);
				//echo '** phpgw_fetchstructure: $specific_key ['.$specific_key.'] :: $cache_msg_structure DUMP<pre>'; print_r($cache_msg_structure); echo '</pre>';
			}
			else
			{
				// provide an empty var so the following if .. then does not complain about "undefined var"
				// because this var is tested along with the "cache_phpgw_header" flag, it should at least 
				// exist even if caching is not turned on just so the following test is "cool" with it
				$cache_msg_structure = '';
			}
			
			if (($cache_msg_structure)
			&& ($this->session_cache_extreme == True))
			{
				//echo '** phpgw_fetchstructure: $specific_key ['.$specific_key.'] :: $cache_msg_structure DUMP<pre>'; print_r($cache_msg_structure); echo '</pre>';
				return $cache_msg_structure;
			}
			else
			{
				// NO CACHED ITEM or CACHING NOT ENABLED
				// get  the data from the mail server
				$this->ensure_stream_and_folder($msgball, 'phpgw_fetchstructure'.' LINE '.__LINE__);
				$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
				$data = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->fetchstructure($mailsvr_stream, $msgball['msgnum']);
				// PUT THIS IN CACHE
				//  if "session_cache_extreme" is True
				if ($this->session_cache_extreme == True)
				{
					// this msgball *may* not have a "folder" element because fetchstructure can only be for the current folder anyway
					// so sometimes we feed the msgball with no folder into here because it is obvious anyway.
					// But for caching purposes we will MAKE SURE it has folder so we can check the cache for other than the selected folder 
					// at a later date
					if (!(isset($msgball['folder']))
					|| ($msgball['folder'] == ''))
					{
						$msgball['folder'] = $this->get_arg_value('folder');
					}
					// this is the way we pass msg_structure data to the caching function
					$meta_data = array();
					$meta_data['msgball'] = array();
					$meta_data['msgball'] = $msgball;
					$meta_data['msg_structure'] = $data;
					
					// SET_CACHE_ITEM
					//echo 'saving msg_structure to cache<br>';
					$this->save_session_cache_item('msg_structure', $meta_data, $acctnum);
				}
				return $data;
			}
		}
		
		/*!
		@function phpgw_header
		@abstract wrapper for IMAP_HEADER, phpgw supplies the nedessary stream arg and mail_dcom reference
		@param $msgball (typed array)
		@result returns the php IMAP_HEADER data
		@discussion Wrapper supplies the needed mailsvr_stream arg to IMAP_HEADER. 
		Message Information: THE MESSAGE'S HEADERS RETURNED AS A STRUCTURE. 
		The data communications object (class mail_dcom) is supplied by the class. 
		CACHE NOTE if $this->session_cache_extreme is True, then this data is cached and 
		manipulated by the "extreme" caching code, which will pop a cached "phpgw_header" out 
		of cache if the message is moved to another folder, and manually clear a cached "phpgw_header" 
		items flag from "unseen" or "recent", if necessary, if the message is read, and put the updated 
		"phpgw_header" item back in cache, with no need to contact the mailserver about this. Eventhough 
		we still need to contact the mail server to get the body, by manually clearing the flag, if necssary, as 
		described above, then when the user goes back to the message list after reading the message, 
		it is possible that ALL information required to make that index page is "fresh" in local cache, 
		and NO login to the mailserver is done in that case. Situations where ALL the necessary data 
		is not in the cache are as follows, if the user deleted or moved ONE message, for example, it 
		may be possible that the index page needs to contact the mailserver to get one additional "phpgw_header" 
		(and also one additional "msg_structure") item to fill out the message list page. If the user had already 
		viewed the index page that had that message, such as paging forward and then backwards thru the 
		message list, the the single message that *was* on the next message list page that is now on the *current* 
		message list page, would already be in the cache. If $this->session_cache_extreme is False, 
		then caching is NOT used on this data.
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
			
			// CHECK FOR CACHED ITEM
			// if "session_cache_extreme" is not enabled, do not use caching for this data
			
			if ($this->session_cache_extreme == True)
			{
				// function read_session_cache_item($data_name='misc', $acctnum='', $special_extra_stuff='')
				// this key, if it exists in the cached array of msg_structures, will hold the data we want as its value 
				$specific_key = (string)$msgball['msgnum'].'_'.$this->prep_folder_out();
				// the cached data is returned as a ready to use object if it exists, or False if not existing
				$cache_phpgw_header = $this->read_session_cache_item('phpgw_header', $acctnum, $specific_key);
				//echo '** phpgw_header: $specific_key ['.$specific_key.'] :: $cache_phpgw_header DUMP<pre>'; print_r($cache_phpgw_header); echo '</pre>';
			}
			else
			{
				// provide an empty var so the following if .. then does not complain about "undefined var"
				// because this var is tested along with the "cache_phpgw_header" flag, it should at least 
				// exist even if caching is not turned on just so the following test is "cool" with it
				$cache_phpgw_header = '';
			}
			
			if (($cache_phpgw_header)
			&& ($this->session_cache_extreme == True))
			{
				//echo '** phpgw_header: $specific_key ['.$specific_key.'] :: $cache_phpgw_header DUMP<pre>'; print_r($cache_phpgw_header); echo '</pre>';
				return $cache_phpgw_header;
			}
			else
			{
				// NO CACHED ITEM or CACHING NOT ENABLED
				// get  the data from the mail server
				$this->ensure_stream_and_folder($msgball, 'phpgw_header'.' LINE '.__LINE__);
				$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
				$data = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->header($mailsvr_stream, $msgball['msgnum']);
				// PUT THIS IN CACHE
				//  if "session_cache_extreme" is True
				if ($this->session_cache_extreme == True)
				{
					// this msgball *may* not have a "folder" element because header can only be for the current folder anyway
					// so sometimes we feed the msgball with no folder into here because it is obvious anyway.
					// But for caching purposes we will MAKE SURE it has folder so we can check the cache for other than the selected folder 
					// at a later date
					if (!(isset($msgball['folder']))
					|| ($msgball['folder'] == ''))
					{
						$msgball['folder'] = $this->get_arg_value('folder');
					}
					// this is the way we pass phpgw_header data to the caching function
					$meta_data = array();
					$meta_data['msgball'] = array();
					$meta_data['msgball'] = $msgball;
					$meta_data['phpgw_header'] = $data;
				
					// SET_CACHE_ITEM
					//echo 'saving phpgw_header to cache<br>';
					$this->save_session_cache_item('phpgw_header', $meta_data, $acctnum);
				}
				return $data;
			}
		}
		
		/*!
		@function phpgw_fetchheader
		@abstract returns the message RAW headers as a blob, or long string.
		@param $msgball (typed array) 
		@author Angles
		@discussion Used by filtering, and in other cases where testing or checking the 
		actual message headers as a text item, is necessary.
		*/
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
			
			$this->ensure_stream_and_folder($msgball, 'phpgw_fetchheader'.' LINE '.__LINE__);
			
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			// Message Information: THE MESSAGE'S HEADERS RETURNED RAW (no processing)
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->fetchheader($mailsvr_stream, $msgball['msgnum']);
		}
	
		/*!
		@function all_headers_in_folder
		@abstract wrapper for IMAP_HEADERS, phpgw supplies the nedessary stream arg and mail_dcom reference
		@param $fldball   array[folder]   string ; array[acctnum]   int
		@result returns the php IMAP_HEADERS data, php manual says 
		function.imap-headers.php
		Returns headers for all messages in a mailbox 
		Returns an array of string formatted with header info. One element per mail message
		@discussion = = = = USELESS FUNCTION = = = = 
		returns array of strings, each string is extremely truncated
		partial contents of date, from, and subject, also includes the msg size in chars
		*/
		function all_headers_in_folder($fldball='')
		{
			if (!(isset($fldball))
			|| ((string)$fldball == ''))
			{
				$msgball = $this->get_arg_value('fldball');
			}
			$acctnum = $fldball['acctnum'];
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$this->ensure_stream_and_folder($fldball, 'all_headers_in_folder');
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->headers($mailsvr_stream);
		}
		
		/*!
		@function phpgw_get_flag
		@abstract ?
		*/
		function phpgw_get_flag($flag='')
		{
			// sanity check
			if ($flag == '')
			{
				return '';
			}
			else
			{
				$msgball = $this->get_arg_value('msgball');
				$this->ensure_stream_and_folder($msgball , 'phpgw_get_flag'.' LINE '.__LINE__);
				return $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->get_flag($this->get_arg_value('mailsvr_stream'),$this->get_arg_value('["msgball"]["msgnum"]'),$flag);
			}
		}
		
		// ====  Functions For Getting A Message Or A Part (MIME Part) Of A Message  ====
		
		/*!
		@function phpgw_body
		@abstract get the entire body for a message.
		@param $msgball (typed array) 
		@author Angles
		@discussion If only a part of the message body is desired, use "phpgw_fetchbody" instead.
		*/
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
			$this->ensure_stream_and_folder($msgball, 'phpgw_body'.' LINE '.__LINE__);
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			// notice of event
			$this->event_msg_seen($msgball, 'phpgw_body');
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->get_body($mailsvr_stream, $msgball['msgnum']);
		}
		
		/*!
		@function phpgw_fetchbody
		@abstract FETCHBODY get a portion, via MIME part number, of a message body, not the entire body.
		@param $msgball (typed array) 
		@param $flags (defined int) options passed to the mailserver with the php FETCHBODY command. 
		(Not related to a message flag like "unseen", this is an optional argument for the mail server.) 
		@author Angles
		*/
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
			$this->ensure_stream_and_folder($msgball, 'phpgw_fetchbody'.' LINE '.__LINE__);
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$msgnum = $msgball['msgnum'];
			$part_no = $msgball['part_no'];
			//echo 'mail_msg(_wrappers): phpgw_fetchbody: processed: $acctnum: '.$acctnum.'; $mailsvr_stream: '.serialize($mailsvr_stream).'; $msgnum: '.$msgnum.'; $part_no: '.$part_no.'<br> * $msgball dump<pre>'; print_r($msgball); echo '</pre>';
			
			// notice of event
			$this->event_msg_seen($msgball, 'phpgw_fetchbody');
			
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->fetchbody($mailsvr_stream, $msgnum, $part_no, $flags);
		}
		
		
	// =====  Functions For Getting Information About A Folder  =====
		// returns an array of integers which are refer to all the messages in a folder ("INBOX") sorted and ordered
		// any integer in this array can be used to request that specific message from the server
		/*!
		@function get_msgball_list
		@abstract wrapper for IMAP_SORT, sorts a folder in the desired way, then get a list of all message, as integer message numbers
		@param $acctnum int SPECIAL USE ONLY  you may supply an acctnum to get info about a folder the is not the currently selected acct / folder
		@param $folder string SPECIAL USE ONLY you may supply folder name to get info about a folder the is not the currently selected acct / folder
		@author Angles
		@access public
		@result returns an array of of type "msgball" , so it contains acctnum, foldername, message UID, and some other info, such as a 
		pre-prepared "fake URI" a.k.a. a GET URI string of type magball. Important data is the message UID integers which 
		are message numbers referring to messages in the current folder. Because multiple accounts may be in use, the msgball array 
		structure is necessary so the correct acctnum and foldername accompanies each message UID. Therefor you have enough information 
		to take all sorts of action on any particular message in the list, see discussion below.
		@discussion Folder and Account Number SHOULD be obtained from the class vars which were set during begin_request(),
		where folder and acctnum were determined from GET POST data or data supplied to begin_request() in its arg array. This way 
		the desired folder is known to be correctly named (it exists, not a bogus foldername) and associated with the correct acctnum.
		However, some of the filter functions do use these params, but using them is discouraged.
		The return is an array of "msgball" data, which contains acctnum, foldername, message UID, and some other info, such as a 
		pre-prepared "fake URI" a.k.a. a GET URI string of type magball. Use this data and specifically these message numbers 
		to request more detailed information about a message (headers, subject), or the request message itself from the server.
		Sort and Order is applied by the class, so the calling process does not need to specify sorting here
		The data communications object (class mail_dcom) is supplied by the class
		*/
		function get_msgball_list($acctnum='', $folder='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(wrappers).get_msgball_list:  ENTERING $acctnum ['.$acctnum.'] ; $folder ['.$folder.'] <br>'; }
			// IF specifying a folder, as a filter search may do, we need to ensure stream and folder
			if ((isset($acctnum))
			&& ((string)$acctnum != '')
			&& (isset($folder))
			&& ((string)$folder != ''))
			{
				// SPECIAL HANDLING, typical message viewing would not need to specify folder
				// DO NOT SPECIFY FOLDER unless you *really* know what you are doing
				// typically "best" folder and acctnum are obtained during begin request
				// right now only specialized filter searching requires tp specify a folder
				$fake_fldball = array();
				$fake_fldball['acctnum'] = $acctnum;
				$fake_fldball['folder'] = $folder;
				$this->ensure_stream_and_folder($fake_fldball, 'get_msgball_list'.' LINE '.__LINE__);
				// ok, so now we KNOW the stream exists and folder value is what we need for this desired account
			}
			elseif ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			// as I said above, rare to specify folder, if it wasn;t handled above, forget about it
			
			// try to restore "msgball_list" from saved session data store
			$cached_msgball_list = $this->read_session_cache_item('msgball_list', $acctnum);
			if ($cached_msgball_list)
			{
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(wrappers).get_msgball_list: ('.__LINE__.') LEAVING, returning appsession cached "msgball_list"<br>'; }
				return $cached_msgball_list['msgball_list'];
			}
			else
			{
				// right now only specialized filter searching requires tp specify a folder
				$fake_fldball = array();
				$fake_fldball['acctnum'] = $acctnum;
				$fake_fldball['folder'] = $this->get_arg_value('folder');
				$this->ensure_stream_and_folder($fake_fldball, 'get_msgball_list'.' LINE '.__LINE__);
				
				$server_msgnum_list = array();
				
				//if (is_object($GLOBALS['phpgw_dcom_'.$acctnum]))
				//{
				//	$server_msgnum_list = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->sort($this->get_arg_value('mailsvr_stream', $acctnum), $this->get_arg_value('sort', $acctnum), $this->get_arg_value('order', $acctnum));
				//}
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): get_msgball_list: ('.__LINE__.') calling $GLOBALS[phpgw_dcom_'.$acctnum.']->dcom->sort('.$this->get_arg_value('mailsvr_stream', $acctnum).', '.$this->get_arg_value('sort', $acctnum).', '.$this->get_arg_value('order', $acctnum).')<br>'; } 
				$server_msgnum_list = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->sort($this->get_arg_value('mailsvr_stream', $acctnum), $this->get_arg_value('sort', $acctnum), $this->get_arg_value('order', $acctnum));
				
				// put more information about these particular messages into the msgball_list[] structure
				$msgball_list = array();
				$loops = count($server_msgnum_list);
				// folder empty (or an error?), msg_nums_list[] count will be 0, so msgball_list[] will be empty as well
				// because we'll never fill it with anything
				if ($loops > 0)
				{
					// we store folder in URLENCODED form in the msgball and therefor the msgball_list
					$msg_folder = $this->prep_folder_out($this->get_arg_value('folder', $acctnum));
					for($i=0;$i<$loops;$i++)
					{
						$msgball_list[$i]['msgnum'] = $server_msgnum_list[$i];
						$msgball_list[$i]['folder'] = $msg_folder;
						$msgball_list[$i]['acctnum'] = $acctnum;
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
				$this->save_session_cache_item('msgball_list', $msgball_list, $acctnum);
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): get_msgball_list: ('.__LINE__.') LEAVING, had to get data from server<br>'; } 
				return $msgball_list;
			}
		}
		
		/*!
		@function get_folder_size
		@abstract uses IMAP_MAILBOXMSGINFO but returns only the size element
		@result integer returns the SIZE element of the php IMAP_MAILBOXMSGINFO data
		@discussion used only if the total size of a folder is desired, which takes time for the server to return
		The other data IMAP_MAILBOXMSGINFO returns (if size is NOT needed) is obtainable
		from "get_folder_status_info" more quickly and wth less load to the IMAP server
		The data communications object (class mail_dcom) and mailsvr_stream are supplied by the class.
		CACHE NOTE: if $this->session_cache_extreme is True, this function returns a DUMMY 
		value of 1, because "extreme" caching estimates changes to cached data insteading of re-fetching 
		it from the mailserver, estimating folder size is too complicated to be worth the small benefit of 
		having the data available. If $this->session_cache_extreme is False, then this function operates normally.
		@author Angles
		@access public
		*/
		function get_folder_size()
		{
			if ($this->session_cache_extreme == False)
			{
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): get_folder_size: ('.__LINE__.') calling $GLOBALS[phpgw_dcom_'.$this->acctnum.']->dcom->mailboxmsginfo('.$this->get_arg_value('mailsvr_stream').')<br>'; } 
				$mailbox_detail = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->mailboxmsginfo($this->get_arg_value('mailsvr_stream'));
				return $mailbox_detail->Size;
			}
			else
			{
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): get_folder_size: ('.__LINE__.') returning BOGUS size because "session_cache_extreme" is ON<br>'; } 
				return 1;
			}
		}
		
		// ALIAS for get_folder_status_info() , for backward compatibility
		function new_message_check($fldball='')
		{
			return $this->get_folder_status_info($fldball='');
		}
		
		/*!
		@function get_folder_status_info
		@abstract wrapper for IMAP_STATUS, get status info for the current folder, with emphesis on reporting to user about new messages
		@param $fldball  typed array  OPTIONAL  as with many functions in this class, the folder you are interested in is usually the currently 
		"selected" folder, in IMAP terms, which is selected during begin_request(), in which case it is not necessary to supply this information 
		again in this param, instead this function will use the class vars about foldername and acctnum established during begin_request(). However, 
		since there are multiple accounts, and since IMAP accounts themselves can contain many folders, it is understood that you may want 
		information about a folder other than the currently selected folder, or about an  account that you may want to move messges to.  In these 
		cases you may supply this param of type fldball, like this: parmarray[acctnum] = 1,  parmarray[folder]  = "INBOX", for example. The fldball 
		array item is pretty flexible in that only the bare minumum of data is expected to be in it, as opposed to msgball which is supposed to 
		contain quite detailed information.
		@param $force_refresh boolean  DEPRECIATED - PHASED OUT - To speed email functionality, much data collected 
		from the IMAP server is cached in some capacity, in fact the RFC on IMAP strongly encourages this. This function 
		is used by many other functions and may be called sveral times during any single operation, so the return array data 
		is cached in memory and will be returned if it is available. This is desirable in many occasions, but if for some reason 
		you need to be sure the returned information is not from this cache, set this param to TRUE. 
		=UPDATE= now this data is cached in the appsession cache IF $this->session_cache_extreme is True, and 
		assumed to be fresh for X period of time, as defined in $this->timestamp_age_limit. This param is 
		NO LONGER USED buy *MAY* be reimplemented later. 
		@result returns an associative array  with 5 named elements see the example
		@example this is the return structure
		result['is_imap'] boolean - pop3 server do not know what is "new" or not, IMAP servers do
		result['folder_checked'] string - the folder checked, as processed by the msg class, which may have done a lookup on the folder name
		result['alert_string'] string - lang'd string to show the user about status of new messages in this folder
		result['number_new'] integer - for IMAP: the number "recent" and/or "unseen"messages; for POP3: the total number of messages
		result['number_all'] integer - for IMAP and POP3: the total number messages in the folder
		@discussion gives user friendly "alert_string" element to show the user, info is for what ever folder the msg
		class is currently logged into, you may want to apply PHP function "number_format()" to
		the integers after you have done any math code and befor eyou display them to the user, it adds the thousands comma. 
		CACHE NOTE: If $this->session_cache_extreme is True, the data this function gets is cached in the appsession 
		cache and is assumed to be "fresh" for X period of time, as defined in $this->timestamp_age_limit 
		(currently hardcoded at 4 minutes). Any changes to cached elements number_new and number_all (part of this functions 
		data array) during that time are manually changed by the "extreme" caching code, we do not re-fetch this data from 
		the mailserver for changes tht we can make ourselves. 
		If $this->session_cache_extreme is False, this data is NOT put in the appsession cache, instead it is stored in a class 
		variable (L1 cache) that lasts only as long as the page view. 
		MORE CACHE NOTE: The "msgball_list" cached in the appsession cache is verified for "freshness" by comparing 
		against the "number_all" element in this functions data array. If the "number_all" of the cached "msgball_list" is 
		different from the "nunber_all" from this function,  the "msgball_list" is deemed "stale" and we request a new 
		msgball list from the server, which means calling the php SORT command and adding some data to that to make 
		the "msgball_list". If $this->session_cache_extreme is True, the "extreme" caching code manually updates the 
		"number_all" cached data for this function for X minutes, as defined in $this->timestamp_age_limit, and also manually 
		updates that "number_all" that is stored with the "msgball_list" data, so that, during that X period of time, 
		as defined in $this->timestamp_age_limit, the "msgball_list" is deemed "fresh" 
		because its "number_all" element matches the "number_all" element from this functions data array. 
		If $this->session_cache_extreme is False, the same "number_all" test is done, but the data from this 
		function is ALWAYS the latest data obtained from the server because if $this->session_cache_extreme is False, 
		this function ALWAYS gets fresh folder stats data at the start of every pageview, so the "msgball_list" will be deemed 
		"stale" as soon as a change occurs on the mailserver, such as when new mail arrives or when messages are moved or 
		deleted, in which case the "msgball_list" is expired and re-fetched as described above.
		@author Angles
		@access public
		*/
		function get_folder_status_info($fldball='', $force_refresh=False)
		{
			if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') ENTERING, $fldball: '.serialize($fldball).' ; $force_refresh (DEPRECIATED): '.serialize($force_refresh).' <br>'; }
			
			if ( (!isset($fldball))
			|| ($fldball == '') )
			{
				// we have NO instructions on a folder nor acctnum, so make a blank fldball
				$fldball = array();
				$fldball['acctnum'] = '';
				$fldball['folder'] = '';
			}
			// now we know we have a fldball structure to work with, analyse it
			if ((!isset($fldball['acctnum']))
			|| ((string)$fldball['acctnum'] == ''))
			{
				$fldball['acctnum'] = $this->get_acctnum();
			}
			if ((!isset($fldball['folder']))
			|| ((string)$fldball['folder'] == ''))
			{
				$fldball['folder'] = $this->get_arg_value('folder', $fldball['acctnum']);
			}
			
			if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info('.__LINE__.'): ONLY L1 CACHE OF THIS INFO IF IN NON-EXTREME MODE<br>'; } 
			
			if ($this->session_cache_extreme == False)
			{
				if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (non-extreme mode) uses L1/class var cache, NO appsession cache used in non-extreme <br>'; }
				// do we have cached data in L1 cache / class object var, that we can use
				// ONLY L1 CACHE OF THIS INFO IF IN NON-EXTREME MODE
				$folder_status_info = $this->get_arg_value('folder_status_info', $fldball['acctnum']);
				if ((!$force_refresh)
				&& ($folder_status_info)
				&& (count($folder_status_info) > 0)
				&& ($folder_status_info['folder_checked'] == $fldball['folder']))
				{
					// this data is cached, L1 cache, temp cache, so it should still be "fresh"
					$timestamp_age = (time() - $folder_status_info['timestamp']);
					if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (non-extreme mode) got L1/class var cached data, $timestamp_age ['.$timestamp_age.'] ; $folder_status_info dump:<pre>'; print_r($folder_status_info); echo '</pre>'; }
					if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (non-extreme mode) LEAVING returning L1/class var cached data<br>'; }
					return $folder_status_info;
				}
				else
				{
					if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (non-extreme mode) NO data found in L1/class var cached <br>'; }
				}
			}
			else
			{
				if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) uses appsession cache, no L1/class var cached is used in extreme mode, param $fldball DUMP<pre>'; print_r($fldball); echo '</pre>'; } 
				// ONLY USE SESSION CACHE IF IN EXTREME MODE
				
				// we need a folder value
				if ((isset($fldball['folder']))
				&& (is_string($fldball['folder']))
				&& ($fldball['folder'] != ''))
				{
					$fldball['folder'] = $this->prep_folder_out($fldball['folder']);
				}
				else
				{
					$fldball['folder'] = $this->prep_folder_out('INBOX');
				}
				
				// try to restore from saved session data store
				if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) calling $this->read_session_cache_item(folder_status_info, '.serialize($fldball['acctnum']).', '.serialize($fldball['folder']).') <br>'; } 
				$cached_folder_status_info = $this->read_session_cache_item('folder_status_info', $fldball['acctnum'], $fldball['folder']);
				if ($this->debug_session_caching > 2) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) $cached_folder_status_info dump:<pre>'; print_r($cached_folder_status_info); echo '</pre>'; }
				if ($cached_folder_status_info)
				{
					if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) LEAVING returning data obtained from cache<br>'; }
					return $cached_folder_status_info;
				}
				if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) NO data found in cache (or it was stale) <br>'; }
			}
			
			// Make Sure Stream Exists
			// multiple accounts means one stream may be open but another may not
			// "ensure_stream_and_folder" will verify for us, 
			if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') call to $this->ensure_stream_and_folder(), $fldball ['.serialize($fldball).'] <br>'; }
			$this->ensure_stream_and_folder($fldball, 'get_folder_status_info'.' LINE '.__LINE__);
			
			//$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $fldball['acctnum']);
			$server_str = $this->get_arg_value('mailsvr_callstr', $fldball['acctnum']);
			if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') will use $mailsvr_stream ['.serialize($mailsvr_stream).'] ; $server_str ['.$server_str.'] ; $fldball: '.serialize($fldball).' <br>'; }
			
			$clean_folder_name = $this->prep_folder_in($fldball['folder']);
			$urlencoded_folder = $this->prep_folder_out($clean_folder_name);
			if ($this->debug_session_caching > 0 || $this->debug_wrapper_dcom_calls > 0) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) <b>problem area: urlencoding only 1 time</b> $clean_folder_name ['.$clean_folder_name.'], $urlencoded_folder : ['.$urlencoded_folder.']);<br>'; }
			
			// initialize return structure
			$return_data = Array();
			$return_data['is_imap'] = False;
			$return_data['folder_checked'] = $clean_folder_name;
			$return_data['folder'] = $clean_folder_name;
			$return_data['alert_string'] = '';
			$return_data['number_new'] = 0;
			$return_data['number_all'] = 0;
			// these are used to verify cached msg_list_array data, i.e. is it still any good, or is it stale
			$return_data['uidnext'] = 0;
			$return_data['uidvalidity'] = 0;
			$return_data['timestamp'] = time();
			// FIXME: make this a "ensure_stream_and_folder" call, to make a login if needed
			//if (is_object($GLOBALS['phpgw_dcom_'.$fldball['acctnum']]))
			//{
			//	$mailbox_status = $GLOBALS['phpgw_dcom_'.$fldball['acctnum']]->dcom->status($mailsvr_stream,$server_str.$fldball['folder'],SA_ALL);
			//}
			// earlier we called $this->ensure_stream_and_folder, so stream *should* exist
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): get_folder_status_info: ('.__LINE__.') calling $GLOBALS[phpgw_dcom_'.$fldball['acctnum'].']->dcom->status('.$mailsvr_stream.','.$server_str.$clean_folder_name.',SA_ALL)<br>'; } 
			$mailbox_status = $GLOBALS['phpgw_dcom_'.$fldball['acctnum']]->dcom->status($mailsvr_stream,$server_str.$clean_folder_name,SA_ALL);
			
			// cache validity data - will be used to cache msg_list_array data, which is good until UID_NEXT changes
			$return_data['uidnext'] = $mailbox_status->uidnext;
			$return_data['uidvalidity'] = $mailbox_status->uidvalidity;
			
			$mail_server_type = $this->get_pref_value('mail_server_type', $fldball['acctnum']);
			if (($mail_server_type == 'imap')
			|| ($mail_server_type == 'imaps'))
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
			
			if ($this->session_cache_extreme == False)
			{
				if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (non-extreme mode) uses L1/class var cache, NO appsession cache used in non-extreme <br>'; }
				// cache data in a class var (L1 Cache)
				// USE L1 CACHE ONLY IN NON-EXTREME MODE
				if ($this->debug_session_caching > 2) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (non-extreme mode) saving to L1 class var cache, $this->set_arg_value(folder_status_info, $return_data, '.$fldball['acctnum'].') ; $return_data dump:<pre>'; print_r($return_data); echo '</pre>'; }
				$this->set_arg_value('folder_status_info', $return_data, $fldball['acctnum']);
			}
			else
			{
				if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) uses appsession cache, no L1/class var cached is used in extreme mode <br>'; }
				$meta_data = array();
				$meta_data['folder_status_info'] = array();
				$meta_data['folder_status_info'] = $return_data;
				if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) saving to session cache, $this->save_session_cache_item("folder_status_info", $meta_data, $urlencoded_folder : ['.$urlencoded_folder.']);<br>'; }
				if ($this->debug_session_caching > 2) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') (extreme mode) $meta_data DUMP: <pre>'; print_r($meta_data); echo '</pre>'; }
				$this->save_session_cache_item('folder_status_info', $meta_data, $acctnum);
			}
			if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') LEAVING, had contact mailserver to get data<br>'; }
			return $return_data;
		}
		
		// FIXME: change arg to fldball
		/*!
		@function phpgw_status
		@abstract ?
		@author Angles
		@discussion Debug with flag "debug_wrapper_dcom_calls" 
		@access public
		*/
		function phpgw_status($feed_folder_long='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(wrappers): phpgw_status ('.__LINE__.'): ENTERING, $feed_folder_long ['.htmlspecialchars($feed_folder_long).']<br>'; }
			$fake_fldball = array();
			$fake_fldball['acctnum'] = $this->get_acctnum();
			$fake_fldball['folder'] = $feed_folder_long;
			$this->ensure_stream_and_folder($fake_fldball, 'phpgw_status'.' LINE '.__LINE__);
			$server_str = $this->get_arg_value('mailsvr_callstr');
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream');
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(wrappers): phpgw_status ('.__LINE__.'): calling $GLOBALS[phpgw_dcom_$fake_fldball[acctnum]('.$fake_fldball['acctnum'].')]->dcom->status($mailsvr_stream['.$mailsvr_stream.'],"$server_str"."$feed_folder_long"['.htmlspecialchars("$server_str"."$feed_folder_long").'],SA_ALL)<br>'; }
			$retval = $GLOBALS['phpgw_dcom_'.$fake_fldball['acctnum']]->dcom->status($mailsvr_stream,"$server_str"."$feed_folder_long",SA_ALL);
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(wrappers): phpgw_status ('.__LINE__.'): LEAVING, returning $retval ['.serialize($retval).'] <br>'; }
			return $retval;
		}

		/*!
		@function phpgw_server_last_error
		@abstract ?
		@author Angles
		@discussion Debug with flag "debug_wrapper_dcom_calls" 
		@access public
		*/
		function phpgw_server_last_error($acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_server_last_error: ('.__LINE__.') calling $GLOBALS[phpgw_dcom_'.$acctnum.']->dcom->server_last_error()<br>'; } 
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->server_last_error();
		}
		
		/*!
		@function phpgw_ping
		@abstract ?
		@author Angles
		@discussion Debug with flag "debug_wrapper_dcom_calls" 
		@access public
		*/
		function phpgw_ping($acctnum='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_ping ('.__LINE__.'): ENTERING, $acctnum ['.$acctnum.'], we DO NOT use "ensure_stream_and_folder" here because that would open the stream we are testing, making this test useless.<br>'; } 
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_ping ('.__LINE__.'): calling $GLOBALS[phpgw_dcom_'.$acctnum.']->dcom->noop_ping_test('.$mailsvr_stream.') <br>'; } 
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->noop_ping_test($mailsvr_stream);
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_ping ('.__LINE__.'): LEAVING, returing $retval ['.serialize($retval).']<br>'; } 
			return $retval;
		}
		
		/*!
		@function phpgw_search
		@abstract ?
		@author Angles
		@discussion Debug with flag "debug_wrapper_dcom_calls" 
		@access public
		*/
		function phpgw_search($fldball='', $criteria='', $flags='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_search ('.__LINE__.'): ENTERING, $fldball ['.serialize($fldball).']; $criteria ['.$criteria.']; $flags['.serialize($flags).'] <br>'; } 
			$acctnum = (int)$fldball['acctnum'];
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$folder = $fldball['folder'];
			// if folder is blank, we *should* assume INBOX because filters always search the INBOX
			if ((!isset($folder))
			|| ((string)$folder == ''))
			{
				$folder = 'INBOX';
			}
			// Make Sure Stream Exists
			// multiple accounts means one stream may be open but another may not
			// "ensure_stream_and_folder" will verify for us, 
			$fake_fldball = array();
			$fake_fldball['acctnum'] = $acctnum;
			$fake_fldball['folder'] = $folder;
			$this->ensure_stream_and_folder($fake_fldball, 'phpgw_search LINE '.__LINE__);
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			
			// now we have the stream and the desired folder open
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_search ('.__LINE__.'): calling $GLOBALS[phpgw_dcom_'.$acctnum.']->dcom->i_search($mailsvr_stream['.$mailsvr_stream.'], $criteria['.$criteria.'],$flags['.serialize($flags).']) <br>'; } 
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->i_search($mailsvr_stream,$criteria,$flags);
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_search ('.__LINE__.'): LEAVING, returing $retval ['.serialize($retval).']<br>'; } 
			return $retval;
		}
		
		/*!
		@function phpgw_createmailbox
		@abstract ?
		@param $target_fldball (array or type "fldball") NOTE: folder element SHOULD HAVE SERVER CALLSTR.
		@author Angles
		@access public
		*/
		function phpgw_createmailbox($target_fldball)
		{
			$acctnum = (int)$target_fldball['acctnum'];
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$folder = $target_fldball['folder'];
			// if folder is blank, we *should* assume INBOX because BUT mailsvr will give an error INBOX already exists
			if ((!isset($folder))
			|| ((string)$folder == ''))
			{
				// add server string to target folder
				$server_str = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_callstr', $acctnum);
				$folder = $server_str.'INBOX';
			}
			// Make Sure Stream Exists
			// multiple accounts means one stream may be open but another may not
			// "ensure_stream_and_folder" will verify for us, 
			$fake_fldball = array();
			$fake_fldball['acctnum'] = $acctnum;
			$fake_fldball['folder'] = $folder;
			$this->ensure_stream_and_folder($fake_fldball, 'phpgw_createmailbox');
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->createmailbox($mailsvr_stream, $folder);
		}
		
		/*!
		@function phpgw_deletemailbox
		@abstract ?
		@author Angles
		@access public
		*/
		function phpgw_deletemailbox($target_fldball)
		{
			$this->ensure_stream_and_folder($target_fldball, 'phpgw_deletemailbox'.' LINE '.__LINE__);
			$acctnum = $target_fldball['acctnum'];
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$folder = $target_fldball['folder'];
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->deletemailbox($mailsvr_stream, $folder);
		}
		
		/*!
		@function phpgw_renamemailbox
		@abstract ?
		@author Angles
		@access public
		*/
		function phpgw_renamemailbox($source_fldball,$target_fldball)
		{
			$this->ensure_stream_and_folder($source_fldball, 'phpgw_renamemailbox'.' LINE '.__LINE__);
			$acctnum = (int)$source_fldball['acctnum'];
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$folder_old = $source_fldball['folder'];
			$folder_new = $target_fldball['folder'];
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->renamemailbox($mailsvr_stream, $folder_old, $folder_new);
		}

		/*!
		@function phpgw_listmailbox
		@abstract ?
		@author Angles
		@discussion Debug with flag "debug_wrapper_dcom_calls" 
		@access public
		*/
		function phpgw_listmailbox($ref,$pattern,$acctnum)
		{
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			// Make Sure Stream Exists
			// multiple accounts means one stream may be open but another may not
			// "ensure_stream_and_folder" will verify for us, 
			// folder logged into does not matter for listmailbox, so leave it blank
			$fake_fldball = array();
			$fake_fldball['acctnum'] = $acctnum;
			$fake_fldball['folder'] = '';
			$this->ensure_stream_and_folder($fake_fldball, 'phpgw_listmailbox');
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			
			// ... so stream exists, do the transaction ...
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_listmailbox ('.__LINE__.'): calling $GLOBALS[phpgw_dcom_'.$acctnum.']->dcom->listmailbox($mailsvr_stream['.$mailsvr_stream.'],$ref['.$ref.'], $pattern['.$pattern.']); <br>'; } 
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->listmailbox($mailsvr_stream,$ref,$pattern);
		}
		
		/*!
		@function phpgw_append
		@abstract ?
		@author Angles
		@discussion Debug with flag "debug_wrapper_dcom_calls" 
		@access public
		*/
		function phpgw_append($folder="Sent", $message, $flags=0)
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_append: ('.__LINE__.') ENTERING, folder: '.$folder.'<br>'; }
			
			$server_str = $this->get_arg_value('mailsvr_callstr');
			
			// ---  does the target folder actually exist ?  ---
			// strip {server_str} string if it's there
			$folder = $this->ensure_no_brackets($folder);
			// attempt to find a folder match in the lookup list
			$official_folder_long = $this->folder_lookup('', $folder);
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_append: ('.__LINE__.') $official_folder_long: '.$official_folder_long.'<br>'; }
			if ($official_folder_long != '')
			{
				$havefolder = True;
			}
			else
			{
				$havefolder = False;
			}
			
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_append: ('.__LINE__.') $havefolder ['.serialize($havefolder).']<br>'; }
			if ($havefolder == False)
			{
				// add whatever namespace we believe should exist
				// (remember the lookup failed, so we have to guess here)
				$folder_long = $this->get_folder_long($folder);
				// create the specified target folder so it will exist
				//$this->createmailbox($mailsvr_stream,"$server_str"."$folder_long");
				//$this->phpgw_createmailbox("$server_str"."$folder_long");
				$fake_fldball = array();
				$fake_fldball['folder'] = $server_str.$folder_long;
				$fake_fldball['acctnum'] = $this->get_acctnum();
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_append: ('.__LINE__.') calling $this->phpgw_createmailbox('.serialize($fake_fldball).')<br>'; }
				$this->phpgw_createmailbox($fake_fldball);
				
				// try again to get the real long folder name of the just created trash folder
				$official_folder_long = $this->folder_lookup('', $folder);
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_append: ('.__LINE__.') $official_folder_long: '.$official_folder_long.'<br>'; }
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
				// delete appsession msg array data thAt is now stale
				// WE DO NOT GUESS ABOUT APPENDS, WE EXPIRE THE DATA AND GET FRESH
				//$this->expire_session_cache_item('msgball_list');
				$target_fldball = array();
				$target_fldball['folder'] = $official_folder_long;
				$target_fldball['acctnum'] = $this->get_acctnum();
				$this->event_msg_append($target_fldball, 'phpgw_append'.' LINE '.__LINE__);
				
				$this->ensure_stream_and_folder($target_fldball, 'phpgw_append'.' LINE '.__LINE__);
				$mailsvr_stream = $this->get_arg_value('mailsvr_stream');
				// do the append
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_append: $GLOBALS["phpgw_dcom_'.$target_fldball['acctnum'].']->dcom->append('.$mailsvr_stream.', '."$server_str"."$official_folder_long".', $message, '.$flags.') '; } 
				//$acctnum: ['.$acctnum.'] $mailsvr_stream: ['.$mailsvr_stream.'] $msgnum: ['.$msgnum.'] $mailbox: ['.htmlspecialchars($mailbox).']<br>'; } 
				$retval = $GLOBALS['phpgw_dcom_'.$target_fldball['acctnum']]->dcom->append($mailsvr_stream, "$server_str"."$official_folder_long", $message, $flags);
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_append ('.__LINE__.'): LEAVING, returning $retval ['.serialize($retval).']<br>'; } 
				return $retval;
			}
			else
			{
				// we do not have the official long folder name for the target folder
				// we can NOT append the message to a folder name we are not SURE is corrent
				// it will fail  HANG the browser for a while
				// so just SKIP IT
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_append ('.__LINE__.'): LEAVING on error, returning FALSE, unable to get good foldername, unable to append <br>'; }
				return False;
			}
		}
		
		/*!
		@function phpgw_mail_move
		@abstract DEPRECIATED - NO LONGER USED. Use "industrial_interacct_mail_move" instead.
		@author Angles
		@access public
		*/
		function phpgw_mail_move($msg_list,$mailbox)
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_mail_move: (DEPRECIATED) ENTERING<br>'; }
			// OLD FUNCTION does not provide enough information, all we can do is expire
			$this->event_msg_move_or_delete(array(), 'phpgw_mail_move');
			// delete session msg array data thAt is now stale
			//$this->expire_session_cache_item('msgball_list');
			
			$retval = $GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->mail_move($this->get_arg_value('mailsvr_stream'), $msg_list, $mailbox);
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_mail_move: (DEPRECIATED) LEAVING, $retval ['.serialize($retval).'] <br>'; } 
			return $retval;
		}
		
		/*!
		@function interacct_mail_move
		@abstract DEPRECIATED - BEING PHASED OUT. Use "industrial_interacct_mail_move" instead.
		@author Angles
		@access public
		*/
		function interacct_mail_move($mov_msgball='', $to_fldball='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): interacct_mail_move: ENTERING<br>'; }
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): interacct_mail_move: $mov_msgball ['.serialize($mov_msgball).'] ;  $to_fldball ['.serialize($to_fldball).']<br>'; } 
			// this needs A LOT of work!!! do not rely on this yet
			
			// delete session msg array data thAt is now stale
			$this->event_msg_move_or_delete($mov_msgball, 'interacct_mail_move'.' LINE '.__LINE__, $to_fldball);
			//$this->expire_session_cache_item('msgball_list');
			
			// Note: Only call this function with ONE msgball at a time, i.e. NOT a list of msgballs
			$acctnum = (int)$mov_msgball['acctnum'];
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			$this->ensure_stream_and_folder($mov_msgball, 'interacct_mail_move'.' LINE '.__LINE__);
			
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): interacct_mail_move:'.' LINE '.__LINE__.' If this is a move to a DIFFERENT account, then THIS FUNCTION is the WRONG ONE to use, it can not handle that<br>'; } 
			
			// NO - this function only works with folders within the same account
			//$this->ensure_stream_and_folder($to_fldball, 'interacct_mail_move'.' LINE '.__LINE__);
			
			//$mailsvr_stream = (int)$this->get_arg_value('mailsvr_stream', $acctnum);
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$msgnum = (string)$mov_msgball['msgnum'];
			$mailbox = $to_fldball['folder'];
			// the acctnum we are moving FROM *may* be different from the acctnum we are moving TO
			// that requires a fetch then an append - FIXME!!!
			
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): interacct_mail_move: $acctnum: ['.$acctnum.'] $mailsvr_stream: ['.$mailsvr_stream.'] $msgnum: ['.$msgnum.'] $mailbox: ['.htmlspecialchars($mailbox).']<br>'; } 
			$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->mail_move($mailsvr_stream ,$msgnum, $mailbox);
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): interacct_mail_move: LEAVING, $retval ['.serialize($retval).'] <br>'; }
			return $retval;
		}
		
		/*!
		@function industrial_interacct_mail_move
		@abstract ?
		@param $mov_msgball (array of type msgball) the message the will be moved. 
		@param $to_fldball (array of type fldball) the target of the move. 
		@author Angles
		@discussion ?
		@access public
		*/
		function industrial_interacct_mail_move($mov_msgball='', $to_fldball='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): industrial_interacct_mail_move: ENTERING, handing off to $this->buffer_move_commands()<br>'; }
			// Note: Only call this function with ONE msgball at a time, i.e. NOT a list of msgballs
			// then we buffer each command with this function
			$this->buffer_move_commands($mov_msgball, $to_fldball);
			
			//if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): industrial_interacct_mail_move ('.__LINE__.'): ok, now add this folder to this accounts "expunge_folders" arg via "track_expungable_folders"<br>'; } 
			// do this during actual moves
			$this->track_expungable_folders($mov_msgball);

			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): industrial_interacct_mail_move: LEAVING, return True so we do not confuse calling process<br>'; }
			return True;
		}
		
		/*!
		@function buffer_mail_move_commands
		@abstract ?
		@param $mov_msgball (array of type msgball) the message the will be moved. 
		@param $to_fldball (array of type fldball) the target of the move. 
		@author Angles
		@discussion ?
		@access public
		*/
		function buffer_move_commands($mov_msgball='', $to_fldball='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): ENTERING<br>'; } 
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): $mov_msgball ['.serialize($mov_msgball).'] $to_fldball ['.serialize($to_fldball).']<br>'; } 
						
			// assemble the URI like string that will hold the command move request instructions
			$this_move_data = '';
			$this_move_data = 
				 'mov_msgball[acctnum]='.$mov_msgball['acctnum']
				.'&mov_msgball[folder]='.$mov_msgball['folder']
				.'&to_fldball[acctnum]='.$to_fldball['acctnum']
				.'&to_fldball[folder]='.$to_fldball['folder']
				.'&mov_msgball[msgnum]='.$mov_msgball['msgnum'];
			
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): $this_move_data ['.htmlspecialchars($this_move_data).']<br>'; } 
			if ($this->debug_wrapper_dcom_calls > 2)
			{
				$this_move_balls = array();
				parse_str($this_move_data, $this_move_balls);
				echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): parse_str($this_move_data, $this_move_balls) $this_move_balls DUMP <pre>'; print_r($this_move_balls); echo '</pre>';
			}
			
			// add this to the array
			$this->buffered_move_commmands[$this->buffered_move_commmands_count] = $this_move_data;
			// increase the count, avoids calling count() every trip thru this loop
			$this->buffered_move_commmands_count++;
			if ($this->debug_wrapper_dcom_calls > 2) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): added new item to array, new $this->buffered_move_commmands DUMP <pre>'; print_r($this->buffered_move_commmands); echo '</pre>'; } 
			
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): LEAVING: did add $this_move_data to array, new array count $this->buffered_move_commmands_count: ['.$this->buffered_move_commmands_count.'], "from" acctnum is ['.$mov_msgball['acctnum'].']<br>'; } 
			return;
		}
		
		/*!
		@function buffer_delete_commands
		@abstract ?
		@param $mov_msgball (array of type msgball) the message the will be moved. 
		@param $to_fldball (array of type fldball) the target of the move. 
		@author Angles
		@discussion ?
		@access public
		*/
		function buffer_delete_commands($mov_msgball='', $to_fldball='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): ENTERING<br>'; } 
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): $mov_msgball ['.serialize($mov_msgball).'] $to_fldball ['.serialize($to_fldball).']<br>'; } 
						
			// assemble the URI like string that will hold the command move request instructions
			$this_move_data = '';
			$this_move_data = 
				 'mov_msgball[acctnum]='.$mov_msgball['acctnum']
				.'&mov_msgball[folder]='.$mov_msgball['folder']
				.'&to_fldball[acctnum]='.$to_fldball['acctnum']
				.'&to_fldball[folder]='.$to_fldball['folder']
				.'&mov_msgball[msgnum]='.$mov_msgball['msgnum'];
			
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): $this_move_data ['.htmlspecialchars($this_move_data).']<br>'; } 
			if ($this->debug_wrapper_dcom_calls > 2)
			{
				$this_move_balls = array();
				parse_str($this_move_data, $this_move_balls);
				echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): parse_str($this_move_data, $this_move_balls) $this_move_balls DUMP <pre>'; print_r($this_move_balls); echo '</pre>';
			}
			
			// add this to the array
			$this->buffered_move_commmands[$this->buffered_move_commmands_count] = $this_move_data;
			// increase the count, avoids calling count() every trip thru this loop
			$this->buffered_move_commmands_count++;
			if ($this->debug_wrapper_dcom_calls > 2) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): added new item to array, new $this->buffered_move_commmands DUMP <pre>'; print_r($this->buffered_move_commmands); echo '</pre>'; } 
			
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): buffer_move_commands ('.__LINE__.'): LEAVING: did add $this_move_data to array, new array count $this->buffered_move_commmands_count: ['.$this->buffered_move_commmands_count.'], "from" acctnum is ['.$mov_msgball['acctnum'].']<br>'; } 
			return;
		}
		
		/*
Array
(
    [0] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=38
    [1] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=66
    [2] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=28
    [3] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=29
    [4] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=31
    [5] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=32
    [6] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=33
    [7] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=34
    [8] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=35
    [9] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=24
    [10] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=26
    [11] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=27
    [12] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=23
    [13] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=13
    [14] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=14
    [15] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=15
    [16] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=16
    [17] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=17
    [18] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=18
    [19] => mov_msgball[acctnum]=5&mov_msgball[folder]=INBOX&to_fldball[acctnum]=5&to_fldball[folder]=mail%2FPostmaster&mov_msgball[msgnum]=19
)

Array
(
    [mov_msgball] => Array
        (
            [acctnum] => 5
            [folder] => INBOX
            [msgnum] => 19
        )

    [to_fldball] => Array
        (
            [acctnum] => 5
            [folder] => mail/Postmaster
        )

)
		*/


		/*!
		@function flush_buffered_move_commmands
		@abstract ?
		@author Angles
		@discussion ?
		@access public
		*/
		function flush_buffered_move_commmands($called_by='not_specified')
		{
			$do_it_for_real = True;
			//$do_it_for_real = False;
			
			// we tell the cache to flush and surn off during a big move, if we find a move is requested, just call the notice once.
			$did_give_big_move_notice = False;
			
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): ENTERING, called by ['.$called_by.'], <br>'; } 
			// leave now if nothing is in the buffered command array
			if ($this->buffered_move_commmands_count == 0)
			{
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): LEAVING, nothing to do, return False, $this->buffered_move_commmands_count: ['.$this->buffered_move_commmands_count.']<br>'; } 
				return False;
			}
			
			// is this a "big move"
			$big_move_thresh = 2;
			//$big_move_thresh = 11;
			$is_big_move = False;
			if ($this->buffered_move_commmands_count > $big_move_thresh)
			{
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): issue $this->event_begin_big_move because $big_move_thresh: ['.$big_move_thresh.'] $this->buffered_move_commmands_count: ['.$this->buffered_move_commmands_count.']<br>'; } 
				$this->event_begin_big_move(array(), 'mail_msg(_wrappers): buffered_move_commmands: LINE '.__LINE__);
				$is_big_move = True;
			}
			// Sort will GROUP THE MOVES AS MUCH AS POSSIBLE RIGHT NOW
			// the way we put the strings in the $this->buffered_move_commmands is designed to be 
			// used by sort to end up grouping similar moves for us inside the array,
			// grouping by _from_acctnum__from_folder__to_acctnum__to_folder__msgnum
			// so similar moves are grouped as much as possible, simply, by calling sort.
			reset($this->buffered_move_commmands);
			//sort($this->buffered_move_commmands);
			sort($this->buffered_move_commmands, SORT_NUMERIC & SORT_STRING);
			// we know the FROM acct num is the same for all commands
			// we know the list is sorted so all FROM folders are together, and then the TO_FOLDERS
			// note the the "del_pseudo_folder" also will be grouped together, later we determing what command to call whether move or straight delete
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): we have delete instructions(s) to be processed, (sorted) $this->buffered_move_commmands DUMP<pre>'; print_r($this->buffered_move_commmands); echo '</pre>'; } 
			
			$grouped_move_balls = array();
			// group the commands
			for ($x=0; $x < $this->buffered_move_commmands_count; $x++)
			{
				$this_move_balls = array();
				parse_str($this->buffered_move_commmands[$x], $this_move_balls);				
				if ($this->debug_wrapper_dcom_calls > 1) { echo ' * mail_msg(_wrappers): flush_buffered_move_commmands: loop ['.$x.']: $this_move_balls: ['.serialize($this_move_balls).']<br>'; }
				// NOTE PARSE_STR ***WILL ADD SLASHES*** TO ESCAPE QUOTES
				// NO MATTER WHAT YOUR MAGIC SLASHES SETTING IS
				if ($this->debug_args_input_flow > 1) { echo ' * mail_msg(_wrappers): flush_buffered_move_commmands: loop ['.$x.']: NOTE PARSE_STR ***WILL ADD SLASHES*** TO ESCAPE QUOTES NO MATTER WHAT YOUR MAGIC SLASHES SETTING IS **stripping slashes NOW***'; } 
				if (isset($this_move_balls['mov_msgball']['folder']))
				{
					$this_move_balls['mov_msgball']['folder'] = stripslashes($this_move_balls['mov_msgball']['folder']);
				}
				if (isset($this_move_balls['to_fldball']['folder']))
				{
					$this_move_balls['to_fldball']['folder'] = stripslashes($this_move_balls['to_fldball']['folder']);
				}
				
				// no matter what, we know we are going to move this message, so notify cache if needed
				// IF WE ISSUED A BIG MOVE NOTICE THEN THE CACHE IS FLUSHED ALREADY
				if ($is_big_move == False)
				{
					if ($this->debug_wrapper_dcom_calls > 1) { echo ' * mail_msg(_wrappers): flush_buffered_move_commmands: loop ['.$x.'] $is_big_move: ['.serialize($is_big_move).'] so calling $this->event_msg_move_or_delete()<br>'; }
					$this->event_msg_move_or_delete($this_move_balls['mov_msgball'], 'flush_buffered_move_commmands'.' LINE: '.__LINE__, $this_move_balls['to_fldball']);
				}
				
				// --- does the FROM folder match the previous one in the list? ---
				$count_grouped = count($grouped_move_balls);
				// make sure at lease one move is in this array, we need at least on previous to compare to, else just add it to start an array
				if ($count_grouped  == 0)
				{
					// add it to the array to get it started
					//array_push($grouped_move_balls, $this_move_balls);
					$grouped_move_balls[0] = $this_move_balls;
					if ($this->buffered_move_commmands_count > 1)
					{
						// SKIP TO NEXT LOOP, we need to compare (try to group) b4 we know to issue the actual move command or not
						// NOTE: CONTINUE
						if ($this->debug_wrapper_dcom_calls > 1) { echo ' * mail_msg(_wrappers): flush_buffered_move_commmands('.__LINE__.'): loop ['.$x.']: added item to array, skip to next iteration<br>'; }
						continue;
					}
					else
					{
						if ($this->debug_wrapper_dcom_calls > 1) { echo ' * mail_msg(_wrappers): flush_buffered_move_commmands('.__LINE__.'): loop ['.$x.']: added item to array, NOT skipping to next iteration because there is only 1 item in array ['.$this->buffered_move_commmands_count.']<br>'; }
					}
				}
				//elseif (($count_grouped > 0)
				//&& ($grouped_move_balls[$count_grouped-1]['mov_msgball']['folder'] == $this_move_balls['mov_msgball']['folder'])
				//&& ($grouped_move_balls[$count_grouped-1]['to_fldball']['folder'] == $this_move_balls['to_fldball']['folder'])
				//)
				elseif (($count_grouped > 0)
				&& ($x != $this->buffered_move_commmands_count-1)
				&& ($grouped_move_balls[$count_grouped-1]['mov_msgball']['acctnum'] == $this_move_balls['mov_msgball']['acctnum'])
				&& ($grouped_move_balls[$count_grouped-1]['mov_msgball']['folder'] == $this_move_balls['mov_msgball']['folder'])
				&& ($grouped_move_balls[$count_grouped-1]['to_fldball']['folder'] == $this_move_balls['to_fldball']['folder'])
				)
				{
					// PASSES the "is grouped" test, add to the "grouped array"
					// AND this is NOT the last item in buffered_move_commmands (that would require action, not another loop)
					array_push($grouped_move_balls, $this_move_balls);
					// NOTE: CONTINUE
					if ($this->debug_wrapper_dcom_calls > 1) { echo ' * mail_msg(_wrappers): flush_buffered_move_commmands('.__LINE__.'): loop ['.$x.']: added item to array, skip to next iteration<br>'; }
					continue;
				}
				elseif (($count_grouped > 0)
				&& ($x == $this->buffered_move_commmands_count-1)
				&& ($grouped_move_balls[$count_grouped-1]['mov_msgball']['acctnum'] == $this_move_balls['mov_msgball']['acctnum'])
				&& ($grouped_move_balls[$count_grouped-1]['mov_msgball']['folder'] == $this_move_balls['mov_msgball']['folder'])
				&& ($grouped_move_balls[$count_grouped-1]['to_fldball']['folder'] == $this_move_balls['to_fldball']['folder'])
				)
				{
					// PASSES the "is grouped" test, add to the "grouped array"
					// AND this is the FINAL ITEM, so KEEP GOING down to the code to issue the actual move command
					array_push($grouped_move_balls, $this_move_balls);
					if ($this->debug_wrapper_dcom_calls > 1) { echo ' * mail_msg(_wrappers): flush_buffered_move_commmands('.__LINE__.'): loop ['.$x.']: added item to array, but NOT skipping to next iteration<br>'; }
					// DO NOT issue "CONTINUE" here
				}
				else
				{
					if ($this->debug_wrapper_dcom_calls > 1) { echo ' * mail_msg(_wrappers): flush_buffered_move_commmands('.__LINE__.'): loop ['.$x.']: UNHANDLED if .. then, $$grouped_move_balls[$count_grouped-1] DUMP<pre>'; print_r($grouped_move_balls[$count_grouped-1]); echo "\r\n".' $$this_move_balls DUMP'; print_r($this_move_balls); echo '</pre>' ; } 
				}
				
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: if we get here, we can not group anymore, or the series just ended, so issue the command now<br>'; }
				// OK if we are here then we know this
				// * "grouped_move_balls" has at least one command in it
				// ** the current command does not match the preious one in terms or grouping them together
				// ** OR the urrent command is the final in the $this->buffered_move_commmands array
				// THEREFOR:
				// 1) we need now make a IMAP command that has all the grouped msgnums from grouped_move_balls
				// 2) if NOT the final item in $this->buffered_move_commmands we need to
				//     2a) then we need to clear grouped_move_balls and ADD this_move_balls to it to start a new grouping array
				//     2b) then run again thru the loop after that
				
				// update this, this loop may have added to it since we last checked this
				$count_grouped = count($grouped_move_balls);
				
				// IF THIS MOVE IS TO ANOTHER ACCOUNT, HAND IT OFF RIGHT NOW
				if ( ($count_grouped = 1)
				&& ((int)$grouped_move_balls[0]['mov_msgball']['acctnum'] != (int)$grouped_move_balls[0]['to_fldball']['acctnum']) ) 
				{
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): ($do_it_for_real is '.serialize($do_it_for_real).'): 1 single **DIFFERENT** Account Move item in $grouped_move_balls, hand off to "single_interacct_mail_move"<br>'; } 
					if ($do_it_for_real == True)
					{
						$this->single_interacct_mail_move($grouped_move_balls[$count_grouped-1]['mov_msgball'], $grouped_move_balls[$count_grouped-1]['to_fldball']);
					}
				}
				elseif ( ($count_grouped > 1)
				&& ((int)$grouped_move_balls[$count_grouped-1]['mov_msgball']['acctnum'] != (int)$grouped_move_balls[$count_grouped-1]['to_fldball']['acctnum']) ) 
				{
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): ERROR: unhandled if .. then,  $grouped_move_balls has multiple items but accounts do not match, different accounts should be handled one at a time!!!<br>'; } 
					echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): LEAVING with ERROR: unhandled if .. then,  $grouped_move_balls has multiple items but accounts do not match, different accounts should be handled one at a time!!!<br>';
					return False;
				}
				else
				{
					// FIXME: some logic below relies on strlen of $collected_msg_num_string to determine if it has ONE DIGIT or not
					// but a single integer can be 1 char or 5 chars, for example, this causes *very* rare errors in the string groupings
					// causing not all message to be moved, or in the worst case, an error from the mailserver if a msgnum does not exist on it
					// whih can happen if 2 digits are put together without a comman or colon inbetween, makes a number unrelated to the grouping
					
					if ($this->debug_wrapper_dcom_calls > 2) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): action required $grouped_move_balls DUMP:<pre>'; print_r($grouped_move_balls); echo '</pre>'; } 
					// update this, this loop may have added to it since we last checked this
					$count_grouped = count($grouped_move_balls);
					$collected_msg_num_string = '';
					// super dumb but simple way, just put a comma between all msgnum's
					//for ($group_loops=0; $group_loops < $count_grouped; $group_loops++)
					//{
					//	if ($group_loops > 0)
					//	{
					//		$collected_msg_num_string .= ',';
					//	}
					//	$collected_msg_num_string .= $grouped_move_balls[$group_loops]['mov_msgball']['msgnum'];
					//}
					// BETTER way, use rfv2060 specs to put range of msgnums together seperated by a colon
					for ($group_loops=0; $group_loops < $count_grouped; $group_loops++)
					{
						if ($this->debug_wrapper_dcom_calls > 1) { echo ' * flush_buffered_move_commmands ('.__LINE__.'): loop ['.$group_loops.'] of ['.(string)($count_grouped-1).'],  $collected_msg_num_string: ['.$collected_msg_num_string.']<br>'; } 
						if ( ($group_loops > 0)
						//&& (($grouped_move_balls[$group_loops-1]['mov_msgball']['msgnum']+1) == $grouped_move_balls[$group_loops]['mov_msgball']['msgnum']) ) 
						&& ($grouped_move_balls[$group_loops-1]['mov_msgball']['msgnum']+1 == $grouped_move_balls[$group_loops]['mov_msgball']['msgnum']) ) 
						{
							// we have a contiguous series, handle string specially
							if (($count_grouped == 2)
							&& ($group_loops == 1))
							{
								// two items will never make a series
								$collected_msg_num_string .= ','.(string)$grouped_move_balls[$group_loops]['mov_msgball']['msgnum'];
							}
							elseif ($group_loops == $count_grouped-1)
							{
								// the contiguous series of numbers just ended because the list is done
								// if there is not a comma nor a colon after the last number, put one there
								$last_char_idx = strlen((string)$collected_msg_num_string)-1;
								$last_char = $collected_msg_num_string[$last_char_idx];
								// situation is that two contiguos numbers at the end of a list like this need a comma
								if (($last_char != ',')
								&& ($last_char != ':'))
								{
									// COLON OR A COMMAN NEEDED
									if ($this->debug_wrapper_dcom_calls > 1) { echo ' * flush_buffered_move_commmands ('.__LINE__.'): loop ['.$group_loops.'] of ['.(string)($count_grouped-1).'], COLON OR COMMA NEEDED: $last_char: ['.$last_char.'], $collected_msg_num_string: ['.$collected_msg_num_string.']<br>'; } 
									if (($count_grouped > 2)
									//&& (strlen((string)$collected_msg_num_string) == 1))
									&& (!stristr($collected_msg_num_string, ':'))
									&& (!stristr($collected_msg_num_string, ',')))
									{
										// ADD A COLON IF
										// (a) if the total things the move are > 2 AND 
										// (b) there is so far only ONE number in our $collected_msg_num_string
										// then this is a series that had been uninterupted since it began looping here
										// situation is nums are 1, 2, 3, 4, 5, so in the last loop $collected_msg_num_string = "1:5"
										if ($this->debug_wrapper_dcom_calls > 1) { echo ' * flush_buffered_move_commmands ('.__LINE__.'): loop ['.$group_loops.'] of ['.(string)($count_grouped-1).'], ADDING A COLON: $last_char: ['.$last_char.'], $collected_msg_num_string: ['.$collected_msg_num_string.']<br>'; } 
										$collected_msg_num_string .= ':';
									}
									else
									{
										// ADD A COMMA, these are 
										// situation is nums are 3, 37, 38, so in the last loop $collected_msg_num_string = "3,37"
										if ($this->debug_wrapper_dcom_calls > 1) { echo ' * flush_buffered_move_commmands ('.__LINE__.'): loop ['.$group_loops.'] of ['.(string)($count_grouped-1).'], ADDING A COMMA: $last_char: ['.$last_char.'], $collected_msg_num_string: ['.$collected_msg_num_string.']<br>'; } 
										$collected_msg_num_string .= ',';
									}
								}
								$collected_msg_num_string .= (string)$grouped_move_balls[$group_loops]['mov_msgball']['msgnum'];
							}
							elseif ( (strlen($collected_msg_num_string) > 1)
							&& ($collected_msg_num_string[strlen($collected_msg_num_string)-1] != ':') )
							{
								// this is a contiguous series just starting, needs a colon
								$collected_msg_num_string .= ':';
							}
							else
							{
								// DO NOTHING we are in the middle of this contiguous series of numbers
							}
						}
						// did a series just end?
						elseif ( ($group_loops > 1)
						&& (($grouped_move_balls[$group_loops-2]['mov_msgball']['msgnum']+1) == $grouped_move_balls[$group_loops-1]['mov_msgball']['msgnum']) ) 
						{
							// NOTE: ADD A COLON 
							// if the previous existing $collected_msg_num_string does NOT have a colon or comma as its last crag
							$last_char_idx = strlen((string)$collected_msg_num_string)-1;
							$last_char = $collected_msg_num_string[$last_char_idx];
							// situation is that the current end of a list needs something before we can add another number, (a comma or a colon)
							if (($last_char != ',')
							&& ($last_char != ':'))
							{
								// so why colon and no check for a needed comma?  
								// SINCE WE KNOW A SERIES JUST ENDED, whether just 2 contiguous numbers or 20 contiguous numbers
								// WE KNOW that a colon would be valid because we KNOW the previous existing number 
								//   AND the number we are about to add are IN A SERIES
								// so a colon would not create an unwanted series becuase we KNOW we have a series at this point
								// so ... a colon can not hurt, a colon between contiguous numbers is still valid syntax (i.e. "3:4"
								if ($this->debug_wrapper_dcom_calls > 1) { echo ' * flush_buffered_move_commmands ('.__LINE__.'): loop ['.$group_loops.'] of ['.(string)($count_grouped-1).'], ADDING A COLON: $last_char: ['.$last_char.'], $collected_msg_num_string: ['.$collected_msg_num_string.']<br>'; } 
								$collected_msg_num_string .= ':';
							}
							//  inset the number of the end of the series, a comman, and the current non-contiguous number
							$collected_msg_num_string .= 
								 (string)$grouped_move_balls[$group_loops-1]['mov_msgball']['msgnum']
								.','
								.(string)$grouped_move_balls[$group_loops]['mov_msgball']['msgnum'];
						}
						else
						{
							// we are NOT in a contiguous series, inset  a comma, and the current number
							if (strlen((string)$collected_msg_num_string) > 0)
							{
								$collected_msg_num_string .= ',';
							}
							$collected_msg_num_string .= (string)$grouped_move_balls[$group_loops]['mov_msgball']['msgnum'];
						}
						if ($this->debug_wrapper_dcom_calls > 1) { echo ' * flush_buffered_move_commmands ('.__LINE__.'): $collected_msg_num_string: ['.$collected_msg_num_string.']<br>'; } 
					}
					
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: final $collected_msg_num_string: ['.$collected_msg_num_string.']<br>'; }
					// 1b) issue the delete COMMAND finally now
					$mov_msgball = array();
					$mov_msgball = $grouped_move_balls[$count_grouped-1]['mov_msgball'];
					$to_fldball = array();
					$to_fldball = $grouped_move_balls[$count_grouped-1]['to_fldball'];
					// the FROM acctnum we'll use as "this_acctnum"
					$this_acctnum = $mov_msgball['acctnum'];
					// EXPIRE MSGBALL ???? wasn't this done with the notice of big move?
					// note since we ALWAYS turn off extreme caching when weuse this function, we *could* DIRECTLY expire it
					//if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: expire msgball list with call to $this->event_msg_move_or_delete<br>'; }
					//$this->event_msg_move_or_delete($mov_msgball, 'flush_buffered_move_commmands'.' LINE: '.__LINE__.' and CACHE SHOULD BE OFF NOW', $to_fldball);
					//if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: expire msgball list with DIRECT call to $this->expire_session_cache_item (because we know extreme caching os turned off for the duration of this function)<br>'; }
					//$this->expire_session_cache_item('msgball_list', $this_acctnum);
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: ($do_it_for_real is '.serialize($do_it_for_real).'): calling $this->ensure_stream_and_folder($mov_msgball ['.serialize($mov_msgball).'], who_is_calling) <br>'; }
					if ($do_it_for_real == True)
					{
						$this->ensure_stream_and_folder($mov_msgball, 'flush_buffered_move_commmands'.' LINE: '.__LINE__);
					}
					$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $this_acctnum);
					// IS THIS A MOVE OR A DELETE?
					if ($to_fldball['folder'] == $this->del_pseudo_folder)
					{
						// STRAIGHT DELETE
						if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: SRAIGHT DELETE ($do_it_for_real is '.serialize($do_it_for_real).'): $GLOBALS[phpgw_dcom_'.$this_acctnum.']->dcom->delete('.serialize($mailsvr_stream).' ,'.$collected_msg_num_string.')<br>'; }
						if ($do_it_for_real == True)
						{
							$did_delete = $GLOBALS['phpgw_dcom_'.$this_acctnum]->dcom->delete($mailsvr_stream , $collected_msg_num_string);
							if (!$did_delete)
							{
								$imap_err = $GLOBALS['phpgw_dcom_'.$this_acctnum]->dcom->server_last_error();
								if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: STRAIGHT DELETE: LEAVING on ERROR, $imap_err: ['.$imap_err.'] return False'.' LINE '.__LINE__.'<br>'; }
								echo 'mail_msg(_wrappers): flush_buffered_move_commmands: LEAVING on ERROR, $imap_err: ['.$imap_err.'] return False'.' LINE '.__LINE__.'<br>';
								echo '&nbsp; command was: $GLOBALS[phpgw_dcom_'.$this_acctnum.']->dcom->delete('.serialize($mailsvr_stream).' ,'.$collected_msg_num_string.')<br>';
								return False;
							}
						}
					}
					else
					{
						// MOVE
						if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: ($do_it_for_real is '.serialize($do_it_for_real).'): $GLOBALS[phpgw_dcom_'.$this_acctnum.']->dcom->mail_move('.serialize($mailsvr_stream).' ,'.$collected_msg_num_string.', '.serialize($to_fldball['folder']).')<br>'; }
						if ($do_it_for_real == True)
						{
							$did_move = $GLOBALS['phpgw_dcom_'.$this_acctnum]->dcom->mail_move($mailsvr_stream , $collected_msg_num_string, $to_fldball['folder']);
							if (!$did_move)
							{
								$imap_err = $GLOBALS['phpgw_dcom_'.$this_acctnum]->dcom->server_last_error();
								if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: LEAVING on ERROR, $imap_err: ['.$imap_err.'] return False'.' LINE '.__LINE__.'<br>'; }
								echo 'mail_msg(_wrappers): flush_buffered_move_commmands: LEAVING on ERROR, $imap_err: ['.$imap_err.'] return False'.' LINE '.__LINE__.'<br>';
								echo '&nbsp; command was: $GLOBALS[phpgw_dcom_'.$this_acctnum.']->dcom->mail_move('.serialize($mailsvr_stream).' ,'.$collected_msg_num_string.', '.serialize($to_fldball['folder']).')<br>';
								return False;
							}
						}
					}
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: LINE '.__LINE__.': is we get here we probably just issued a move or delete command, we may try to group more (usually only with filter usage) or we may be done with buffered command list<br>'; }
				}
				
				// 2) if NOT the final item in $this->buffered_move_commmands we need to
				//     2a) then we need to clear grouped_move_balls and ADD this_move_balls to it to start a new grouping array
				//     2b) then run again thru the loop after that
				if ($x != $this->buffered_move_commmands_count-1)
				{
					$grouped_move_balls = array();
					array_push($grouped_move_balls, $this_move_balls);
					// 3) then run again thru the loop after that
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: continue; ... to look for groupable move commands for acctnum ['.$mailsvr_stream.']<br>'; }
					// doesn't this happen anyway here?
					continue;
				}
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands: still in that loop $x: ['.$x.'] $this->buffered_move_commmands_count-1: ['.(string)($this->buffered_move_commmands_count-1).'], if we get to here we SHOULD be done with all moves, else a continue would have been hit<br>'; }
			}
			
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): flush_buffered_move_commmands ('.__LINE__.'): LEAVING<br>'; } 
			// FIXME return something more useful
			return True;
		}
		
		
		/*!
		@function single_interacct_mail_move
		@abstract Primary mail move function for DIFFERENT Accounts. Moves single mails, use a loop if moving more than one mail. 
		@param $mov_msgball (array of type msgball) the message the will be moved. 
		@param $to_fldball (array of type fldball) the target of the move. 
		@author Angles
		@discussion Can handle any kind of move, same account, different account, different server.  Now 
		used mostly for different account moves, because we attempt to group single account moves elsewhere. 
		Fills arg "expunge_folders" for any account that has folders needing to be expunged. 
		@access public
		*/
		function single_interacct_mail_move($mov_msgball='', $to_fldball='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): single_interacct_mail_move: ENTERING (note: only feed ONE msgball at a time, i.e. NOT a list of msgballs) <br>'; }
			// Note: Only call this function with ONE msgball at a time, i.e. NOT a list of msgballs
			// INTERACCOUNT -OR- SAME ACCOUNT ?
			if ($this->debug_wrapper_dcom_calls > 2) { echo 'mail_msg(_wrappers): single_interacct_mail_move: $mov_msgball DUMP:<pre>'; print_r($mov_msgball); echo "</pre>\r\n"; }
			if ($this->debug_wrapper_dcom_calls > 2) { echo 'mail_msg(_wrappers): single_interacct_mail_move: $to_fldball DUMP:<pre>'; print_r($to_fldball); echo "</pre>\r\n"; }
			// --- Establist account numbers ----
			$mov_msgball['acctnum'] = (int)$mov_msgball['acctnum'];
			if (!(isset($mov_msgball['acctnum']))
			|| ((string)$mov_msgball['acctnum'] == ''))
			{
				$mov_msgball['acctnum'] = $this->get_acctnum();
			}
			$to_fldball['acctnum'] = (int)$to_fldball['acctnum'];
			if (!(isset($to_fldball['acctnum']))
			|| ((string)$to_fldball['acctnum'] == ''))
			{
				$to_fldball['acctnum'] = $this->get_acctnum();
			}
			
			// Are the acctnums the same?
			if ((string)$mov_msgball['acctnum'] == (string)$to_fldball['acctnum'])
			{
				// SAME ACCOUNT MAIL MOVE
				
				$common_acctnum = $mov_msgball['acctnum'];
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): single_interacct_mail_move: SAME ACCOUNT MOVE $common_acctnum: '.$common_acctnum.' $mailsvr_stream: '.$mailsvr_stream.' $msgnum: '.$msgnum.' $mailsvr_callstr: '.$mailsvr_callstr.' $mailbox: '.$mailbox.'<br>'; }
				$this->event_msg_move_or_delete($mov_msgball, 'single_interacct_mail_move'.' LINE: '.__LINE__, $to_fldball);
				//$this->expire_session_cache_item('msgball_list', $common_acctnum);
				// we need to SELECT the folder the message is being moved FROM
				$mov_msgball['folder'] = urldecode($mov_msgball['folder']);
				
				$this->ensure_stream_and_folder($mov_msgball, 'single_interacct_mail_move'.' LINE: '.__LINE__);
				$mov_msgball['msgnum'] = (string)$mov_msgball['msgnum'];
				$to_fldball['folder'] = urldecode($to_fldball['folder']);
				$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $common_acctnum);
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): single_interacct_mail_move: $GLOBALS[phpgw_dcom_'.$common_acctnum.']->dcom->mail_move('.serialize($mailsvr_stream).' ,'.serialize($mov_msgball['msgnum']).', '.serialize($to_fldball['folder']).')<br>'; }
				$did_move = $GLOBALS['phpgw_dcom_'.$common_acctnum]->dcom->mail_move($mailsvr_stream ,$mov_msgball['msgnum'], $to_fldball['folder']);
				if (!$did_move)
				{
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): single_interacct_mail_move: LEAVING, return False'.' LINE '.__LINE__.'<br>'; }
					return False;
				}
				else
				{
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): single_interacct_mail_move ('.__LINE__.'): SAME ACCOUNT MOVE *SUCCESS*, $did_move ['.serialize($did_move).'], now add this folder to this accounts "expunge_folders" arg via "track_expungable_folders"<br>'; } 
					$this->track_expungable_folders($mov_msgball);
					//if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): industrial_interacct_mail_move: LEAVING, about to call $this->phpgw_expunge('.$mov_msgball['acctnum'].')'.' LINE '.__LINE__.'<br>'; }
					//return $this->phpgw_expunge($mov_msgball['acctnum']);
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): single_interacct_mail_move ('.__LINE__.'): LEAVING, returning True, SAME ACCOUNT MOVE SUCCESS (do not forget to expunge later) <br>'; } 
					return True;
				}
			}
			else
			{
				// DIFFERENT ACCOUNT MAIL MOVE
				
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): single_interacct_mail_move: Different ACCOUNT MOVE $common_acctnum: '.$common_acctnum.' $mailsvr_stream: '.$mailsvr_stream.' $msgnum: '.$msgnum.' $mailsvr_callstr: '.$mailsvr_callstr.' $mailbox: '.$mailbox.'<br>'; }
				$good_to_go = False;
				// delete session msg array data thAt is now stale
				$this->event_msg_move_or_delete($mov_msgball, 'single_interacct_mail_move'.' LINE: '.__LINE__, $to_fldball);
				//$this->expire_session_cache_item('msgball_list', $mov_msgball['acctnum']);
				$mov_msgball['folder'] = urldecode($mov_msgball['folder']);
				// Make Sure Stream Exists
				// multiple accounts means one stream may be open but another may not
				// "ensure_stream_and_folder" will verify for us, 
				$this->ensure_stream_and_folder($mov_msgball, 'single_interacct_mail_move');
				// GET MESSAGE FLAGS (before you get the mgs, so unseen/seen is not tainted by our grab)
				$hdr_envelope = $this->phpgw_header($mov_msgball);
				$mov_msgball['flags'] = $this->make_flags_str($hdr_envelope);
				// GET THE MESSAGE
				// part_no 0 only used to get the headers
				$mov_msgball['part_no'] = 0;
				// (a)  the headers, specify part_no 0
				//$moving_message = $GLOBALS['phpgw']->msg->phpgw_fetchbody($mov_msgball);
				$moving_message = $this->phpgw_fetchbody($mov_msgball);
				// (b) the body, plus a CRLF, reuse headers_msgball b/c "phpgw_body" cares not about part_no
				//$moving_message .= $GLOBALS['phpgw']->msg->phpgw_body($mov_msgball)."\r\n";
				$moving_message .= $this->phpgw_body($mov_msgball)."\r\n";
				$good_to_go = (strlen($moving_message) > 3);
				if (!$good_to_go)
				{
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): single_interacct_mail_move: LEAVING, return False'.' LINE '.__LINE__.'<br>'; }
					return False;
				}
				
				// APPEND TO TARGET FOLDER
				// delete session msg array data thAt is now stale
				// WE DO NOT GUESS ABOUT APPENDS, WE EXPIRE THE DATA AND GET FRESH
				//$this->expire_session_cache_item('msgball_list', $to_fldball['acctnum']);
				$this->event_msg_append($to_fldball, 'single_interacct_mail_move  Line '.__LINE__);
				
				
				$to_fldball['folder'] = urldecode($to_fldball['folder']);
				// TEMP (MUST add this back!!!) append does NOT require we open the target folder, only requires a stream
				$remember_to_fldball = $to_fldball['folder'];
				$to_fldball['folder'] = '';
				$this->ensure_stream_and_folder($to_fldball, 'single_interacct_mail_move');
				$mailsvr_callstr = $this->get_arg_value('mailsvr_callstr', $to_fldball['acctnum']);
				$to_mailsvr_stream = $this->get_arg_value('mailsvr_stream', $to_fldball['acctnum']);
				$to_fldball['folder'] = $remember_to_fldball;
				$good_to_go = $GLOBALS['phpgw_dcom_'.$to_fldball['acctnum']]->dcom->append($to_mailsvr_stream, $mailsvr_callstr.$to_fldball['folder'], $moving_message, $mov_msgball['flags']);
				if (!$good_to_go)
				{
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): single_interacct_mail_move: LEAVING, return False'.' LINE '.__LINE__.'<br>'; }
					return False;
				}
				// DELETE and EXPUNGE from FROM FOLDER
				$from_mailsvr_stream = $this->get_arg_value('mailsvr_stream', $mov_msgball['acctnum']);
				$good_to_go = $GLOBALS['phpgw_dcom_'.$mov_msgball['acctnum']]->dcom->delete($from_mailsvr_stream, $mov_msgball['msgnum']);
				if (!$good_to_go)
				{
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): single_interacct_mail_move: LEAVING, return False'.' LINE '.__LINE__.'<br>'; }
					return False;
				}
				//$good_to_go = $GLOBALS['phpgw']->msg->phpgw_expunge($mov_msgball['acctnum']);
				//$good_to_go = $this->phpgw_expunge($mov_msgball['acctnum']);
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): single_interacct_mail_move ('.__LINE__.'): different account append and delete SUCCESS, now add this folder to this accounts "expunge_folders" arg via "track_expungable_folders"<br>'; } 
				$this->track_expungable_folders($mov_msgball);
				
				if (!$good_to_go)
				{
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): single_interacct_mail_move: LEAVING, return False'.' LINE '.__LINE__.'<br>'; }
					return False;
				}
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): single_interacct_mail_move: LEAVING, return TRUE'.' LINE '.__LINE__.'<br>'; }
				return True;
			}
		}
		
		/*!
		@function track_expungable_folders
		@abstract Keeps track of what accounts folders will need to be expunged. 
		@author Angles
		@discussion Used by "industrial_interacct_mail_move" and "phpgw_delete" to keep track 
		of which folders for any account will need to be expunged. NOTE this tracking occurs 
		automatically in those functions, HOWEVER the calling function is responsible to 
		call "expunge_expungable_folders" when all the moves or deletes are done. 
		@access private
		*/
		function track_expungable_folders($fldball='')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): track_expungable_folders ('.__LINE__.'): ENTERING, $fldball ['.serialize($fldball).']<br>'; } 
			if (!(isset($fldball['acctnum']))
			|| ((string)$fldball['acctnum'] == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			else
			{
				$acctnum = $fldball['acctnum'];
			}
			if (!(isset($fldball['folder']))
			|| ((string)$fldball['folder'] == ''))
			{
				$folder = $this->get_arg_value('folder', $acctnum);
			}
			else
			{
				$folder = $fldball['folder'];
			}
			$my_fldball = array();
			$my_fldball['folder'] = $folder;
			$my_fldball['acctnum'] = $acctnum;
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): track_expungable_folders ('.__LINE__.'): $my_fldball ['.serialize($my_fldball).']<br>'; } 
			
			$first_addition_to_array = False;
			// get an array of folders that need expunging that we know of
			if ($this->get_isset_arg('expunge_folders', $my_fldball['acctnum']) == False)
			{
				$expunge_folders = array();
				$first_addition_to_array = True;
			}
			else
			{
				//$expunge_folders = $this->get_arg_value('expunge_folders', $my_fldball['acctnum']);
				$expunge_folders =& $this->_get_arg_ref('expunge_folders', $my_fldball['acctnum']);
			}
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): track_expungable_folders ('.__LINE__.'): $expunge_folders DUMP <pre>'; print_r($expunge_folders); echo '</pre>'; } 
			// if this particular folder already in the array
			$loops = count($expunge_folders);
			$already_listed = False;
			for ($i=0; $i<$loops;$i++)
			{
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): track_expungable_folders ('.__LINE__.'): loop ['.$i.'] of ['.$loops.'] $expunge_folders[$i] ['.htmlspecialchars($expunge_folders[$i]).'] same as $my_fldball[folder] ['.htmlspecialchars($my_fldball['folder']).'] test<br>'; } 
				if ($expunge_folders[$i] == $my_fldball['folder'])
				{
					$already_listed = True;
					break;
				}
			}
			// if this folder was NOT already in the array, put it there and save the arg  value
			if ($already_listed == False)
			{
				$new_idx = count($expunge_folders);
				$expunge_folders[$new_idx] = $my_fldball['folder'];
				if ($first_addition_to_array == True)
				{
					$this->set_arg_value('expunge_folders', $expunge_folders, $my_fldball['acctnum']);
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): track_expungable_folders ('.__LINE__.'): LEAVING: added first item to $my_fldball[folder] ['.$my_fldball['folder'].'] to $expunge_folders ['.htmlspecialchars(serialize($expunge_folders)).']<br>'; } 
				}
				else
				{
					if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): track_expungable_folders ('.__LINE__.'): LEAVING: added VIA REFERENCE $my_fldball[folder] ['.$my_fldball['folder'].'] to $expunge_folders ['.htmlspecialchars(serialize($expunge_folders)).']<br>'; } 
				}
				return True;
			}
			else
			{
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): track_expungable_folders ('.__LINE__.'): LEAVING: $my_fldball[folder] ['.$my_fldball['folder'].'] was ALREADY in $expunge_folders ['.htmlspecialchars(serialize($expunge_folders)).']<br>'; } 
				return False;
			}
		}
		
		/*!
		@function expunge_expungable_folders
		@abstract loops thru ALL accounts, expunges any account that has folder names in its arg "expunge_folders" 
		@author Angles
		@discussion This function uses the folder tracking from "track_expungable_folders" to know what 
		to expunge. Call this function after all your moves or deletes are done.
		@access public
		*/
		function expunge_expungable_folders($called_by='not_specified')
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): expunge_expungable_folders ('.__LINE__.'): ENTERING, called by ['.$called_by.'], <br>'; } 
			
			$this->flush_buffered_move_commmands('expunge_expungable_folders');
			
			$expunge_folders = array();
			for ($i=0; $i < count($this->extra_and_default_acounts); $i++)
			{
				if ($this->extra_and_default_acounts[$i]['status'] == 'enabled')
				{
					$this_acctnum = $this->extra_and_default_acounts[$i]['acctnum'];
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): expunge_expungable_folders ('.__LINE__.'): acctnum ['.$this_acctnum.'] needs to be checked<br>'; } 
					if ($this->get_isset_arg('expunge_folders', $this_acctnum) == True)
					{
						$expunge_folders = array();
						$expunge_folders = $this->get_arg_value('expunge_folders', $this_acctnum);
						if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): expunge_expungable_folders ('.__LINE__.'): acctnum ['.$this_acctnum.'] indicates these folder(s) need to be expunged, $expunge_folders DUMP<pre>'; print_r($expunge_folders); echo '</pre>'; } 
						
						for ($x=0; $x < count($expunge_folders); $x++)
						{
							$success = False;
							$fake_fldball = array();
							$fake_fldball['acctnum'] = $i;
							$fake_fldball['folder'] = $expunge_folders[$x];
							$success = $this->phpgw_expunge('', $fake_fldball);
							if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): expunge_expungable_folders ('.__LINE__.'): expunge for $fake_fldball ['.htmlspecialchars(serialize($fake_fldball)).'] returns ['.serialize($success).']<br>'; } 
						}
						// we are done with this account, we expunged all expungable folders, not UNSET that arg so it is not left hanging around
						if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): expunge_expungable_folders ('.__LINE__.'): finished expunging folders for acctnum ['.$this_acctnum.'] , now issue: $this->unset_arg("expunge_folders", '.$this_acctnum.') <br>'; } 
						$this->unset_arg('expunge_folders', $this_acctnum);
					}
					else
					{
						$expunge_folders = array();
						if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): expunge_expungable_folders ('.__LINE__.'): acctnum ['.$this_acctnum.'] has NO value for "expunge_folders"<br>'; } 
					}
				}
			}
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): expunge_expungable_folders ('.__LINE__.'): LEAVING<br>'; } 
			// FIXME return something more useful
			return True;
		}
		
		
		/*!
		@function phpgw_expunge
		@abstract Expunge a folder.
		@author Angles
		@discussion Brainless function, used by "expunge_expungable_folders" which is a "smart" 
		function. This may be called directly, but it is preferable to use "expunge_expungable_folders" 
		assuming the move or delete function you used calls "track_expungable_folders". 
		@access public
		*/
		function phpgw_expunge($acctnum='', $fldball='')
		{
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				if (!(isset($fldball['acctnum']))
				|| ((string)$fldball['acctnum'] == ''))
				{
					$acctnum = $this->get_acctnum();
				}
				else
				{
					$acctnum = $fldball['acctnum'];
				}
			}
			
			//$fake_fldball = array();
			//$fake_fldball['folder'] = $this->get_arg_value('folder', $acctnum);
			
			// NOTE: it is OK to pass blank folder to "ensure_stream_and_folder" when FOLDER DOE NOT MATTER
			// for expunge, all we need is a stream, 
			//$fake_fldball['acctnum'] = $acctnum;
			//$this->ensure_stream_and_folder($fake_fldball, 'phpgw_expunge'.' LINE '.__LINE__);
			// NOTE THAT CAUSED MAILSERVER TO REOPEN *AWAY* FROM FOLDER NEEDING EXPUNGE 
			// and re-open to INBOX because a blank folder arg was passed.
			if ((isset($fldball['acctnum']))
			&& ((string)$fldball['acctnum'] != '')
			&& (isset($fldball['folder']))
			&& ((string)$fldball['folder'] != ''))
			{
				$this->ensure_stream_and_folder($fldball, 'phpgw_expunge'.' LINE '.__LINE__);
			}
			
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
			$note_folder = $this->get_arg_value('folder', $acctnum);
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): dcom_call: phpgw_expunge: $acctnum: '.serialize($acctnum).' NOTE current "folder" arg set for that acct is ['.$note_folder.']<br>'; } 
			
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): dcom_call: phpgw_expunge: $acctnum: '.serialize($acctnum).' $mailsvr_stream: '.$mailsvr_stream.'<br>'; } 
			return $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->expunge($mailsvr_stream);
		}
		
		/*!
		@function phpgw_delete
		@abstract Delete a message, will move to "Trash" folder is necessary.
		@author Angles
		@param $msg_num (int) single msgnum of msg to "delete" (or move to trash folder") 
		@param $currentfolder (string) full name (as in folder_long) and urlencoded name of the 
		folder from which we are deleting from.
		@param $acctnum (int) (optional) acctnum this applies to
		@param $known_single_delete (boolean) BEING PHASED OUT was used to take abreviated action 
		if we know this is only a single delete, not just one in a series, this logic being moved elsewhere. 
		@discussion If the user pref wants to use the Trash folder, this function will auto-create 
		that folder if it does not already exist, and move the mail to that trash folder. If 
		the user pref is to not use a trash folder, or if deleting mail that is IN the trash folder, 
		then a straight delete is done. Keeps track of folders needing expunging via calls 
		to "track_expungable_folders", but the calling process is responsible to 
		call "expunge_expungable_folders" after all deletes have been done.
		@access public
		*/
		function phpgw_delete($msg_num,$flags=0, $currentfolder="", $acctnum='', $known_single_delete=False) 
		{
			if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_delete: ENTERING <br>'; }
			
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			// everything from now on MUST specify this $acctnum
			
			// now get the stream that applies to that acctnum
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete: $acctnum: ['.$acctnum.'], $msg_num: ['.$msg_num.']<br>'; }
						
			// this get arg value checks the pref for enabled or not enabled, no need to do it again
			if ($this->get_isset_arg('verified_trash_folder_long', $acctnum) == False)
			{
				$trash_folder_primer = $this->get_arg_value('verified_trash_folder_long', $acctnum);
				$trash_folder_primer = '';
				unset($trash_folder_primer);
			}
			
			// -- determine if we are moving to the trash folder or actually deleting the message
			$trash_folder_long =& $this->_get_arg_ref('verified_trash_folder_long', $acctnum);
			// if $trash_folder_long is not an ampty string, we need to try to move msgs to it
			if ($trash_folder_long != '')
			{
				// get a clean version 
				$currentfolder_encoded = $currentfolder;
				$currentfolder_clean = urldecode($currentfolder);
				
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): "Trash" folder pref is enabled, does $currentfolder_clean ['.htmlspecialchars($currentfolder_clean).'] equal $trash_folder_long ['.htmlspecialchars($trash_folder_long).'] <br>'; }
				//echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): "Trash" folder pref is enabled, does ['.$currentfolder.'] == ['.$trash_folder_long.']<br>'; 
				if ( ($currentfolder_clean != '')
				&& ($currentfolder_clean == $trash_folder_long) )
				{
					$straight_delete = True;
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): "Trash" folder pref is enabled, YES to does $currentfolder_clean ['.htmlspecialchars($currentfolder_clean).'] equal $trash_folder_long ['.htmlspecialchars($trash_folder_long).'] <br>'; }
					//echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): "Trash" folder pref is enabled, shortcut good<br>'; 
				}
				else
				{
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): "Trash" folder pref is enabled, NO to does $currentfolder_clean ['.htmlspecialchars($currentfolder_clean).'] equal $trash_folder_long ['.htmlspecialchars($trash_folder_long).'] <br>'; }
					$straight_delete = False;
				}
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): $straight_delete: ['.serialize($straight_delete).'], $currentfolder_clean: ['.htmlspecialchars($currentfolder_clean).'] $trash_folder_long: ['.htmlspecialchars($trash_folder_long).'] <br>'; }
			}
			else
			{
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): $straight_delete: ['.serialize($straight_delete).'] because $trash_folder_long ['.htmlspecialchars($trash_folder_long).'] is empty string<br>'; }
				$straight_delete = True;
			}
			
			// now that we know if this is a straight delete or not
			// TAKE ACTION
			if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): taking action based on info that $straight_delete: ['.serialize($straight_delete).']<br>'; }
			if ($straight_delete == True)
			{
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): begin code for STRAIGHT DELETE<br>'; }
				$mov_msgball = array();
				if ((isset($currentfolder_encoded))
				&& ((string)$currentfolder_encoded != ''))
				{
					// lets trust that current folder is in long form
					$mov_msgball['folder'] = $currentfolder_encoded;
				}
				else
				{
					$mov_msgball['folder'] = $this->prep_folder_out();				
				}
				$mov_msgball['acctnum'] = $acctnum;
				$mov_msgball['msgnum'] = $msg_num;
				
				// STRAIGHT DELETE has a "PSUEDO FOLDER" called "##DELETE##"
				// that we use in the "flush_buffered_move_commmands" to indicate a delete instead of a move
				// AND we'll use the same "acctnum" as the delete from acctnum because this will group them together during a "sort" of the array
				// so we can use the same function  for both
				$to_fldball = array();
				$to_fldball['acctnum'] = $mov_msgball['acctnum'];
				$to_fldball['folder'] = $this->del_pseudo_folder;
				
				// PUT THIS COMMAND IN THE BUFFERED MOVE (OR DELETE) ARRAY
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): STRAIGHT DELETE: calling $this->industrial_interacct_mail_move($mov_msgball['.serialize($mov_msgball).'],$to_fldball['.serialize($to_fldball).']) <br>'; }
				$did_take_action = $this->industrial_interacct_mail_move($mov_msgball, $to_fldball);
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): LEAVING, returning $did_take_action ['.serialize($did_take_action).'] (does not really mean anything since we buffer the command) DO NOT FORGET TO EXPUNGE LATER<br>'; }
				// LEAVING
				return $did_take_action;
				
				/*
				// BELOW HERE SHOULD GO INTO THE NEW STRAIGHT DELETE BUFFER
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): calling $this->event_msg_move_or_delete()<br>'; }
				$this->event_msg_move_or_delete($mov_msgball, 'phpgw_delete'.' LINE: '.__LINE__);
				//$this->expire_session_cache_item('msgball_list', $acctnum);
				// delete this when we start buffering straight deletes
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): calling $this->ensure_stream_and_folder()<br>'; }
				$this->ensure_stream_and_folder($mov_msgball, 'phpgw_delete'.' LINE '.__LINE__);
			
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): getting "mailsvr_stream"<br>'; }
				$mailsvr_stream = $this->get_arg_value('mailsvr_stream', $acctnum);
				//return imap_delete($mailsvr_stream,$msg_num);
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): calling GLOBALS[phpgw_dcom_'.$acctnum.']->dcom->delete('.serialize($mailsvr_stream).', '.serialize($msg_num).') <br>'; }
				$retval = $GLOBALS['phpgw_dcom_'.$acctnum]->dcom->delete($mailsvr_stream, $msg_num);
				if ($retval)
				{
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete ('.__LINE__.'): delete *SUCCESS*, now add this folder to this accounts "expunge_folders" arg via "track_expungable_folders"<br>'; } 
					$this->track_expungable_folders($mov_msgball);
				}
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): EXITING with $retval ['.serialize($retval).'] DO NOT FORGET TO EXPUNGE LATER<br>'; }
				// LEAVING
				return $retval;
				*/
			}
			else
			{
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): begin do move to trash folder<br>'; }
				$mov_msgball = array();
				if ((isset($currentfolder_encoded))
				&& ((string)$currentfolder_encoded != ''))
				{
					// lets trust that current folder is in long form (and encoded - I guess we like it that way? it came in here that way)
					$mov_msgball['folder'] = $currentfolder_encoded;
				}
				else
				{
					$mov_msgball['folder'] = $this->prep_folder_out();				
				}
				$mov_msgball['acctnum'] = $acctnum;
				$mov_msgball['msgnum'] = $msg_num;
				// destination Trash Folder
				$to_fldball = array();
				$to_fldball['folder'] = $trash_folder_long;
				$to_fldball['acctnum'] = $acctnum;
				// this event MOVED to flush command
				//if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): calling $this->event_msg_move_or_delete<br>'; }
				//$this->event_msg_move_or_delete($mov_msgball, 'phpgw_delete'.' LINE: '.__LINE__, $to_fldball);
				//$this->expire_session_cache_item('msgball_list', $acctnum);
				
				//if ($known_single_delete == True)
				//{
				//	// we were told this is just a SINGLE delete call, NOT multiple deletes involved
				//	if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): $known_single_delete: ['.serialize($known_single_delete).'] so calling $this->single_interacct_mail_move($mov_msgball['.serialize($mov_msgball).'],$to_fldball['.serialize($to_fldball).']) <br>'; }
				//	$did_move = $this->single_interacct_mail_move($mov_msgball, $to_fldball);
				//}
				//else
				//{
					// most (WAS) likely multiple deletes, so use the command that buffers the moves
					// this logic concerning single or not has been moved elsewhere
					if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): $known_single_delete: ['.serialize($known_single_delete).'] so calling $this->industrial_interacct_mail_move($mov_msgball['.serialize($mov_msgball).'],$to_fldball['.serialize($to_fldball).']) <br>'; }
					$did_move = $this->industrial_interacct_mail_move($mov_msgball, $to_fldball);
				//}
				if ($this->debug_wrapper_dcom_calls > 1) { echo 'mail_msg(_wrappers): phpgw_delete('.__LINE__.'): $did_move: ['.serialize($did_move).'], does not mean unless you called $this->single_interacct_mail_move()<br>'; }
				
				if ($this->debug_wrapper_dcom_calls > 0) { echo 'mail_msg(_wrappers): phpgw_delete ('.__LINE__.'): LEAVING, returning $did_move ['.serialize($did_move).'] DO NOT FORGET TO EXPUNGE LATER<br>'; }
				return $did_move;
			}
		}
		
		
		/*!
		@function get_verified_trash_folder_long
		@abstract SPECIAL HANDLER for use by "get_arg_value" for "verified_trash_folder_long"
		@param (int) the account number OPTIONAL if not supplied current acctnum is used
		@result (string) folder long name of trash folder, or empty string on failure
		@discussion First there is a pref called "trash_folder_name" that needs some processing 
		because .1. the name is stored in user friendly form, is not known to be authentic server 
		string folder long style, and .2. if the folder does not yet exist we must make it. This function 
		does that processing, the result of this is put into arg value "verified_trash_folder_long". So 
		if you need to know about the trash folder, use get_arg_value("verified_trash_folder_long") and 
		this is all transparent to you. **CACHE NOTE** this value is stored in LEVEL 1 temporary cache 
		for the duration of the script run, perhaps later we could put in appsession cache but if so then it 
		must be expired if new prefs are submited.
		@author Angles
		*/
		function get_verified_trash_folder_long($acctnum='')
		{
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: ENTERING<br>'; }
			
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_verified_trash_folder_long: after testing feed arg, using $acctnum: ['.$acctnum.']<br>'; }
			
			// are we even supposed to use a trash folder
			if ( (!$this->get_isset_pref('use_trash_folder', $acctnum))
			|| (!$this->get_pref_value('use_trash_folder', $acctnum)) )
			{
				if ($this->debug_args_special_handlers > 0) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: LEAVING, user does NOT prefer to use a trash folder, so returning empty string<br>'; }
				// exit, trash folder pref is NOT TO USE ONE, so we certainly do not have a "verified" name in this case
				return '';
			}
			
			// L1 (temporary) CACHED data available ?
			$class_cached_verified_trash_folder_long = $this->_direct_access_arg_value('verified_trash_folder_long', $acctnum);
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_verified_trash_folder_long: check for L1 class var cached data: $this->_direct_access_arg_value(verified_trash_folder_long, '.$acctnum.'); returns: '.serialize($class_cached_verified_trash_folder_long).'<br>'; }
			if ($class_cached_verified_trash_folder_long != '')
			{
				// return the cached data
				if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_verified_trash_folder_long: LEAVING, returned class var cached data: '.serialize($class_cached_verified_trash_folder_long).'<br>'; }
				return $class_cached_verified_trash_folder_long;
			}
			
			// NO CACHED data, continue ...
			// does the trash folder actually exist ?
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: humm... does the "Trash" folder actually exist :: this->get_pref_value("trash_folder_name", '.$acctnum.') = ['.htmlspecialchars($this->get_pref_value('trash_folder_name', $acctnum)).']<br>'; }
			$verified_trash_folder_long = $this->folder_lookup('', $this->get_pref_value('trash_folder_name', $acctnum));
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: did lookup on pref value for "Trash" folder, got $verified_trash_folder_long ['.htmlspecialchars($verified_trash_folder_long).']<br>'; }
			if ($verified_trash_folder_long != '')
			{
				$havefolder = True;
			}
			else
			{
				$havefolder = False;
			}
			
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: "Trash" folder $havefolder so far is ['.serialize($havefolder).']<br>'; }
			
			if (!$havefolder)
			{
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: we have to create the "Trash" folder so it will exist<br>'; }
				// create the Trash folder so it will exist (Netscape does this too)
				$server_str = $this->get_arg_value('mailsvr_callstr', $acctnum);
				//$this->createmailbox($mailsvr_stream,$server_str .$trash_folder_long);						
				//$this->phpgw_createmailbox("$server_str"."$trash_folder_long");
				$fake_fldball = array();
				$fake_fldball['folder'] = $server_str.$trash_folder_long;
				$fake_fldball['acctnum'] = $acctnum;
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: calling $this->phpgw_createmailbox('.serialize($fake_fldball).') <br>'; }
				$did_create = $this->phpgw_createmailbox($fake_fldball);
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: phpgw_createmailbox  returns $did_create ['.serialize($did_create ).'] <br>'; }
				
				// try again to get the real long folder name of the just created trash folder
				$verified_trash_folder_long = $this->folder_lookup('', $this->get_pref_value('trash_folder_name', $acctnum));
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: Another lookup on pref value for "Trash" folder, got $verified_trash_folder_long ['.htmlspecialchars($verified_trash_folder_long).']<br>'; }
				// did the folder get created and do we now have the official full name of that folder?
				if ($verified_trash_folder_long != '')
				{
					$havefolder = True;
				}
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: Another check of "Trash" folder $havefolder so far is ['.serialize($havefolder).']<br>'; }
			}
			
			if ($havefolder == False)
			{
				// FAILED to find or make trash folder, return empty string
				$verified_trash_folder_long = '';
			}
			else
			{
				// SUCCESS, put the result in L1 (page view only) cache
				// cache the result in "level one cache" class var holder
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_verified_trash_folder_long: set "level 1 cache, class var" arg $this->set_arg_value(verified_trash_folder_long, '.$verified_trash_folder_long.', '.$acctnum.']) <br>'; }
				$this->set_arg_value('verified_trash_folder_long', $verified_trash_folder_long, $acctnum);
				// LATER put it in appsession cache BUT make code to delete it from cache when submitting new prefs
			}
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg(_wrappers): get_verified_trash_folder_long: LEAVING, returning $verified_trash_folder_long ['.serialize($verified_trash_folder_long).']<br>'; }
			return $verified_trash_folder_long;
		}
		
		/**************************************************************************\
		* END DCOM WRAPERS								*
		* - - - - - - - - - - - - - - - - - - - - - - - - -					*
		* BEGIN INPUT ARG/PARAM HANDLERS			*
		\**************************************************************************/
		
		/*!
		@function decode_fake_uri
		@abstract decodes a URI type "query string" into an associative array
		@param $uri_type_string string in the style of a URI such as "&item=phone&action=dial"
		@result associative array where the $key and $value are exploded from the uri like [item] => "phone"
		@discussion HTML select "combobox"s can only return 1 "value" per item, to break that limitation you 
		can use that 1 item like a "fake URI", meaning you make a single string store structured data 
		by using the standard syntax of a HTTP GET URI, see the example
		@example HTTP GET URI, example
		< select name="fake_uri_data" > < option value="&item=phone&action=dial&touchtone=1" > ( ... etc ... )
		In an HTTP POST event, this would appear as in the example
		$this->ref_POST['fake_uri_data'] => "&item=phone&action=dial&touchtone=1"
		Then you feed that string into this function and you get back an associave array like this
		return["item"] => "phone"
		return["action"] => "dial"
		return["touchtone"] => "1"
		NOTE: this differs from PHP's parse_str() because this function  will NOT attempt to decode the urlencoded values.
		In this way you may store many data elements in a single HTML "option" value=" " tag.
		@author Angles
		@access Public
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
			
			// NOTE PARSE_STR ***WILL ADD SLASHES*** TO ESCAPE QUOTES
			// NO MATTER WHAT YOUR MAGIC SLASHES SETTING IS
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri ('.__LINE__.'): NOTE PARSE_STR ***WILL ADD SLASHES*** TO ESCAPE QUOTES NO MATTER WHAT YOUR MAGIC SLASHES SETTING IS **stripping slashes NOW***'; } 
			if (isset($embeded_data['folder']))
			{
				$embeded_data['folder'] = stripslashes($embeded_data['folder']);
			}
			if (isset($embeded_data['msgball']['folder']))
			{
				$embeded_data['msgball']['folder'] = stripslashes($embeded_data['msgball']['folder']);
			}
			
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: decode_fake_uri: post "stripslashes" parse_str('.$uri_type_string.', into $embeded_data dump:<pre>'; print_r($embeded_data); echo '</pre>'; }
			
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
					// re-urlencode folder names, and make acctnum 's integers
					/*
					// NOT NECESSARY HERE
					if ((is_array($new_top_level))
					&& (count($new_top_level) > 0))
					{
						$loops = count($new_top_level);
						for($i=0;$i<$loops;$i++)
						{
							// re-urlencode folder names, because "prep_folder_in" is supposed to be where it gets urldecoded
							if ((isset($this_array_item[$i]['folder']))
							&& ((string)$this_array_item[$i]['folder'] != ''))
							{
								$re_urlencoded_folder = urlencode($this_array_item[$i]['folder']);
								if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: re-urlencode (hopefully) folder element $this_array_item['.$i.'][folder] from ['.$this_array_item[$i]['folder'].'] into ['.$re_urlencoded_folder.'] <br>'; }
								$this_array_item[$i]['folder'] = $re_urlencoded_folder;
							}
							if ((isset($this_array_item[$i]['acctnum']))
							&& ((string)$this_array_item[$i]['acctnum'] != ''))
							{
								$make_int_acctnum = (int)$this_array_item[$i]['acctnum'];
								if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: $make_int_acctnum (hopefully) acctnum element $this_array_item['.$i.'][acctnum] from ['.serialize($this_array_item[$i]['acctnum']).'] into ['.serialize($make_int_acctnum).'] <br>'; }
								$this_array_item[$i]['acctnum'] = $make_int_acctnum;
							}
						}
					}
					*/
					// replace result with $new_top_level
					$embeded_data = $new_top_level;
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: raise embeded up to $new_top_level: <pre>'; print_r($new_top_level); echo '</pre>'; }
				}
				else
				{
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: decode_fake_uri: original result had more than one element, can not raise <br>'; }
				}
			}
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

			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: decode_fake_uri: final $embeded_data (sub parts made into an associative array) dump:<pre>'; print_r($embeded_data); echo '</pre>'; }
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: decode_fake_uri: LEAVING <br>'; }
			return $embeded_data;
		}
		
		/*!
		@function grab_class_args_gpc
		@abstract grab data from $this->ref_POST and $this->ref_GET
		as necessaey, and fill various class arg variables with the available data
		@result none, this is an object call
		@discussion to further seperate the mail functionality from php itself, this function will perform
		the variable handling of the traditional php page view Get Post Cookie (no cookie data used here though)
		The same data could be grabbed from any source, XML-RPC for example, insttead of php's GPC vars,
		so this function could (should) have an equivalent XML-RPC "to handle filling these class variables
		from an alternative source.
		@author Angles
		@access Public
		*/
		function grab_class_args_gpc()
		{
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: grab_class_args_gpc: ENTERING<br>'; }
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: $this->ref_POST dump:<pre>'; print_r($this->ref_POST); echo '</pre>'; }
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: $this->ref_GET dump:<pre>'; print_r($this->ref_GET); echo '</pre>'; }
			
			// ----  extract any "fake_uri" embedded data from HTTP_POST_VARS  ----
			// note: this happens automatically for HTTP_GET_VARS 
			// NOTE this WILL ALTER $_POST inserting processed values for later use (could this be avoided?)
			if (is_array($this->ref_POST))
			{
				while(list($key,$value) = each($this->ref_POST))
				{
					if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: looking for "_fake_uri" token in HTTP_POST_VARS ['.$key.'] = '.$this->ref_POST[$key].'<br>'; }
					if ($key == 'delmov_list')
					{
						if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: FOUND "delmov_list_fake_uri" needs decoding HTTP_POST_VARS['.$key.'] = ['.$this->ref_POST[$key].'] <br>'; }
						// apache2 on test RH8.0 box submits "delmov_list" array with duplicate items in it, track this
						$seen_delmov_list_items=array();
						$sub_loops = count($this->ref_POST[$key]);
						for($i=0;$i<$sub_loops;$i++)
						{
							
							// do duplicate test on the "delmov_list" array items
							if (in_array($this->ref_POST[$key][$i], $seen_delmov_list_items) == True)
							{
								if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: <u>unsetting</u> and *skipping* duplicate (buggy apache2) "delmov_list" array item ['.$this->ref_POST[$key][$ii].'] <br>'; }
								$this->ref_POST[$key][$i] = '';
								// can I UNSET this and have the next $i index item actually be the next one
								// YES, a) array count calculated before loop, and b) does not squash array to unset an item
								unset($this->ref_POST[$key][$i]);
								//array_splice($this->ref_POST[$key], $i, 1);
								// NOTE USE OF CONTINUE COMMAND HERE!
								// we do not increase $ii because the next array item just fell into the current slot
								continue;
							}
							else
							{
								// track seen items for duplicate test
								if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: good (not duplicate, not buggy apache2) "delmov_list" array item ['.$this->ref_POST[$key][$ii].'] <br>'; }
								$tmp_next_idx = count($seen_delmov_list_items);
								$seen_delmov_list_items[$tmp_next_idx] = $this->ref_POST[$key][$i];
							}
							// if we get here, it is not duplicate, go ahead
							$sub_embedded_data = array();
							// True = attempt to "raise up" embedded data to top level
							$sub_embedded_data = $this->decode_fake_uri($this->ref_POST[$key][$i], True);
							$this->ref_POST[$key][$i] = $sub_embedded_data;
						}
						// increment our shadow iteation count
						if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: decoded ARRAY "_fake_uri" data: HTTP_POST_VARS['.$key.'] data dump: <pre>'; print_r($this->ref_POST[$key]); echo '</pre>'; }
					}
					elseif (strstr($key, '_fake_uri'))
					{
						if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: FOUND "_fake_uri" token in HTTP_POST_VARS['.$key.'] = ['.$this->ref_POST[$key].'] <br>'; }
						$embedded_data = array();
						$embedded_data = $this->decode_fake_uri($this->ref_POST[$key]);
						// Strip "_fake_uri" from $key and insert the associative array into HTTP_POST_VARS
						$new_key = str_replace('_fake_uri', '', $key);
						if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: embedded "_fake_uri" data will be inserted into POST VARS with key name: ['.$new_key.'] = ['.$this->ref_POST[$key].'] <br>'; }
						$this->ref_POST[$new_key] = array();
						$this->ref_POST[$new_key] = $embedded_data;
						if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: decoded "_fake_uri" data: HTTP_POST_VARS['.$new_key.'] data dump: <pre>'; print_r($this->ref_POST[$new_key]); echo '</pre>'; }
					}
					/*
					elseif ($key == 'delmov_list')
					{
						if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: FOUND "delmov_list" needs decoding HTTP_POST_VARS['.$key.'] = ['.$this->ref_POST[$key].'] <br>'; }
						$sub_loops = count($this->ref_POST[$key]);				
						for($i=0;$i<$sub_loops;$i++)
						{
							$sub_embedded_data = array();
							$sub_embedded_data = $this->decode_fake_uri($this->ref_POST[$key][$i]);
							$this->ref_POST[$key][$i] = $sub_embedded_data;
						}
						if ($this->debug_args_input_flow > 2) { echo 'mail_msg: grab_class_args_gpc: decoded ARRAY "_fake_uri" data: HTTP_POST_VARS['.$key.'] data dump: <pre>'; print_r($this->ref_POST[$key]); echo '</pre>'; }
					}
					*/
				}
			}
			
			$got_args = array();
			// insert *known* external args we find into $got_args[], then return that data
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: grab_class_args_gpc: about to loop thru $this->known_external_args<br>'; }
			$loops = count($this->known_external_args);
			for($i=0;$i<$loops;$i++)
			{
				$this_arg_name = $this->known_external_args[$i];
				//if ($this->debug_args_input_flow > 2) { echo ' * * (grab pref - external) $this_arg_name: ['.$this_arg_name.']<br>'; }
				if (isset($this->ref_POST[$this_arg_name]))
				{
					if ($this->debug_args_input_flow> 2) { echo ' * * (grab pref - external) $this->ref_POST['.$this_arg_name.'] IS set to ['.$this->ref_POST[$this_arg_name].']<br>'; }
					$got_args[$this_arg_name] = $this->ref_POST[$this_arg_name];
				}
				elseif (isset($this->ref_GET[$this_arg_name]))
				{
					if ($this->debug_args_input_flow > 2) { echo ' * * (grab pref - external) $this->ref_GET['.$this_arg_name.'] IS set to ['.$this->ref_GET[$this_arg_name].']<br>'; }
					$got_args[$this_arg_name] = $this->ref_GET[$this_arg_name];
					
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
			
			// in order to know wgat account's arg array to insert $got_args[] into, we need to determine what account 
			// we are dealing with before we can call $this->set_arg_array or "->get_isset_arg" or "->get_arg_value", etc...
			// so whoever called this function should obtain that before calling $this->set_arg_array() with the data we return here
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: grab_class_args_gpc: LEAVING, returning $got_args<br>'; }
			return $got_args;
		}
		
		/*!
		@function grab_class_args_xmlrpc
		@abstract grab data an XML-RPC call and fill various class arg variables with the available data
		@result none, this is an object call
		@discussion functional relative to function "grab_class_args_gpc()", except this function grabs the
		data from an alternative, non-php-GPC, source
		NOT YET IMPLEMENTED
		@author Angles
		@access Public
		*/
		function grab_class_args_xmlrpc()
		{
			// STUB, for future use
			echo 'call to un-implemented function grab_class_args_xmlrpc';
		}
		
		
		/*!
		@function get_best_acctnum
		@abstract search a variety of vars to find a legitimate account number, fallsback to $this->get_acctnum
		@param $args_array ARRAY that was passed to ->begin_request, pass that into here if possible, it is a primary source
		@param $got_args ARRAY of the *External* params / args fed to this script via GPC or other methods
		Note: these are NOT the "internal args"
		@param $force_feed_acctnum INTEGER if for some reason you want to force an account number (DEPRECIATED)
		@result integer, most legitimate account number that was obtained
		@discussion ?
		@author Angles
		@access Private
		*/
		function get_best_acctnum($args_array='', $got_args='', $force_feed_acctnum='')
		{
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: get_best_acctnum: ENTERING, param $force_feed_acctnum ['.$force_feed_acctnum.'] ; parm DUMP $args_array[] then $got_args[] dumps:<pre>'; print_r($args_array);  print_r($got_args); echo '</pre>'; }
			
			// ---  which email account do are these args intended to apply to  ----
			// ORDER OF PREFERENCE for determining account num: just look at the code, it has comments
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": searching...: <br>'; }
			// initialize
			$acctnum = '';
			
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: get acctnum from feed args if possible<br>'; }
			$found_acctnum = False;
			while(list($key,$value) = each($args_array))
			{
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: (acctnum search) this loop feed arg : ['.$key.'] => ['.serialize($args_array[$key]).'] <br>'; }
				// try to find feed acctnum value
				if ($key == 'fldball')
				{
					$fldball = $args_array[$key];
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: (acctnum search) $args_array passed in $fldball[] : '.serialize($fldball).'<br>'; }
					$acctnum = (int)$fldball['acctnum'];
					
					// SET OUR ACCTNUM ACCORDING TO FEED ARGS
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: (acctnum search) ACCTNUM from $args_array fldball : ['.$acctnum.']<br>'; }
					$found_acctnum = True;
					break;
				}
				elseif ($key == 'msgball')
				{
					$msgball = $args_array[$key];
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: (acctnum search) $args_array passed in $msgball[] : '.serialize($msgball).'<br>'; }
					$acctnum = (int)$msgball['acctnum'];
					// SET OUR ACCTNUM ACCORDING TO FEED ARGS
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: (acctnum search) ACCTNUM from $args_array msgball : ['.$acctnum.']<br>'; }
					$found_acctnum = True;
					break;
				}
				elseif ($key == 'acctnum')
				{
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: (acctnum search) $args_array passed in "acctnum" : '.serialize($args_array[$key]).'<br>'; }
					$acctnum = (int)$args_array[$key];
					// SET OUR ACCTNUM ACCORDING TO FEED ARGS
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: (acctnum search) ACCTNUM from $args_array "acctnum" feed args : ['.$acctnum.']<br>'; }
					$found_acctnum = True;
					break;
				}
			}
			// did the above work?
			if ($found_acctnum == True)
			{
				// SET THE ACCTNUM AND RETURN IT
				if ($this->debug_args_input_flow > 0) { echo 'mail_msg: get_best_acctnum: (from $args_array) * * * *SETTING CLASS ACCTNUM* * * * by calling $this->set_acctnum('.serialize($acctnum).')<br>'; }
				$this->set_acctnum($acctnum);
				if ($this->debug_args_input_flow > 0) { echo 'mail_msg: get_best_acctnum: LEAVING early, $args_array had the data, returning $acctnum ['.serialize($acctnum).']<br>'; }
				return $acctnum;
			}
			
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": continue searching...: <br>'; }
			
			// ok, now we need to broaden the search for a legit account number
			if ((isset($force_feed_acctnum))
			&& ((string)$force_feed_acctnum != ''))
			{
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": will use function param $force_feed_acctnum=['.serialize($force_feed_acctnum).']<br>'; }
				$acctnum = (int)$force_feed_acctnum;
			}
			elseif ((isset($got_args['msgball']['acctnum']))
			&& ((string)$got_args['msgball']['acctnum'] != ''))
			{
				// we are requested to handle (display, move, forward, etc...) this msgball, use it's properties
				$acctnum = (int)$got_args['msgball']['acctnum'];
				// make sure this is an integer
				$got_args['msgball']['acctnum'] = $acctnum;
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": will use GPC aquired $got_args[msgball][acctnum] : ['.serialize($got_args['msgball']['acctnum']).']<br>'; }
			}
			elseif ((isset($got_args['fldball']['acctnum']))
			&& ((string)$got_args['fldball']['acctnum'] != ''))
			{
				// we are requested to handle (display, .... ) data concerning this fldball, use it's properties
				$acctnum = (int)$got_args['fldball']['acctnum'];
				// make sure this is an integer
				$got_args['fldball']['acctnum'] = $acctnum;
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": will use GPC aquired $got_args[fldball][acctnum] : ['.serialize($got_args['fldball']['acctnum']).']<br>'; }
			}
			elseif ((isset($got_args['source_fldball']['acctnum']))
			&& ((string)$got_args['source_fldball']['acctnum'] != ''))
			{
				// we are *probably* requested to delete or rename this fldball, use it's properties
				$acctnum = (int)$got_args['source_fldball']['acctnum'];
				// make sure this is an integer
				$got_args['source_fldball']['acctnum'] = $acctnum;
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": will use GPC aquired $got_args[source_fldball][acctnum] : ['.serialize($got_args['source_fldball']['acctnum']).']<br>'; }
			}
			elseif ((isset($got_args['delmov_list'][0]['acctnum']))
			&& ((string)$got_args['delmov_list'][0]['acctnum'] != ''))
			{
				// at the very least we know that we'll need to login to this account to delete or move this particular msgball
				// also, we will need to open the particular folder where the msg is localted
				$acctnum = (int)$got_args['delmov_list'][0]['acctnum'];
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": will use GPC aquired $got_args[delmov_list][0][acctnum] : ['.serialize($got_args['delmov_list'][0]['acctnum']).']<br>'; }
			}
			elseif ((isset($got_args['target_fldball']['acctnum']))
			&& ((string)$got_args['target_fldball']['acctnum'] != ''))
			{
				// at the very least we know we need to login to this account to append a message to a folder there
				// NOTE: we need not open the particular folder we are going to append to,
				// all we need is a stream to that particular account, "opened" folder is not important
				// therefor we can just use INBOX as the folder to log into in this case
				$acctnum = (int)$got_args['target_fldball']['acctnum'];
				// make sure this is an integer
				$got_args['target_fldball']['acctnum'] = $acctnum;
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": will use GPC aquired $got_args[target_fldball][acctnum] : ['.serialize($got_args['target_fldball']['acctnum']).']<br>'; }
			}
			else
			{
				// FALLBACK
				// ok, we have NO acctnum in $args_array, did NOT get it from GPC got_args, nor the force fed $force_feed_acctnum
				// so, we grab the class's current value for $this->acctnum
				// $this->get_acctnum() will return a default value for us to use if $this->acctnum is not set
				// note, this is identical to $this->get_acctnum(True) because True is the default arg there if one is not passed
				// True means "return a default value, NOT boolean false, if $this->acctnum is not set
				$acctnum = $this->get_acctnum(True);
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_acctnum: "what acctnum to use": NO *incoming* acctnum specified, called $this->get_acctnum(True), got: ['.serialize($acctnum).']<br>'; }
			}
			
			// SET THE ACCTNUM WITH THE "BEST VALUE" WE COULD FIND
			// DEPRECIATED - we no longer set it here
			//if ($this->debug_args_input_flow > 0) { echo 'mail_msg: get_best_acctnum: * * * *SETTING CLASS ACCTNUM* * * * by calling $this->set_acctnum('.serialize($acctnum).')<br>'; }
			//$this->set_acctnum($acctnum);
			
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: get_best_acctnum: LEAVING, returning $acctnum ['.serialize($acctnum).']<br>'; }
			return $acctnum;
		}
		
		/*!
		@function init_internal_args_and_set_them
		@abstract initialize Internally controlled params (args). MUST already have an acctnum
		@param $acctnum integer the current account number whose array we will fill with these initialized args
		@result none, this is an object call
		@discussion ?
		@author Angles
		@access Public
		*/
		function init_internal_args_and_set_them($acctnum='')
		{
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: init_internal_args: ENTERING, (parm $acctnum=['.serialize($acctnum).'])<br>'; }
			// we SHOULD have already obtained a valid acctnum before calling this function
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			// INTERNALLY CONTROLLED ARGS
			// preserve pre-existing value, for which "acctnum" must be already obtained, so we
			// know what account to check for existing arg values when we use "get_isset_arg" or "get_arg_value"
			$internal_args = Array();
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: init_internal_args: about to loop thru $this->known_internal_args<br>'; }
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
					$internal_args[$this_arg_name] = $preserve_this;
				}
				else
				{
					if ($this->debug_args_input_flow > 2) { echo ' * (grab pref - internal) no pre-existing value for ['.$this_arg_name.'], using initialization default: <br>'; }
					if ($this_arg_name == 'folder_status_info')
					{
						$internal_args['folder_status_info'] = array();
					}
					elseif ($this_arg_name == 'folder_list')
					{
						$internal_args['folder_list'] = array();
					}
					elseif ($this_arg_name == 'mailsvr_callstr')
					{
						$internal_args['mailsvr_callstr'] = '';
					}
					elseif ($this_arg_name == 'mailsvr_namespace')
					{
						$internal_args['mailsvr_namespace'] = '';
					}
					elseif ($this_arg_name == 'mailsvr_delimiter')
					{
						$internal_args['mailsvr_delimiter'] = '';
					}
					elseif ($this_arg_name == 'mailsvr_stream')
					{
						$internal_args['mailsvr_stream'] = '';
					}
					elseif ($this_arg_name == 'mailsvr_account_username')
					{
						$internal_args['mailsvr_account_username'] = '';
					}
					// experimental (by it being *here*): this arg is handles elsewhere, but Iput it here
					// to help remember and be consistant about accounting for all args we may use
					// UPDATE: "expunge_folders" can NOT BE HERE because it should NOT EXIST unless set during a move or delete
					//  putting it here will initialize it to a value of "" (empty string) which is different than unset.
					//elseif ($this_arg_name == 'expunge_folders')
					//{
					//	$internal_args['expunge_folders'] = '';
					//}
					// experimental: Set Flag indicative we've run thru this function
					elseif ($this_arg_name == 'already_grab_class_args_gpc')
					{
						$internal_args['already_grab_class_args_gpc'] = True;
					}
				}
			}
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: init_internal_args: post-loop (internal args) $internal_args[] dump:<pre>'; print_r($internal_args); echo '</pre>'; }
			
			
			// clear old args (if any) and set the args we just obtained (or preserved)
			//$this->unset_all_args();
			// set new args, some may require processing (like folder will go thru prep_folder_in() automatically
			//while(list($key,$value) = each($internal_args))
			//{
			//	$this->set_arg_value($key, $internal_args[$key]);
			//}
			
			// use this one call to do it all
			//$this->set_arg_array($internal_args);
			
			// add these items to the args array for the appropriate account
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: init_internal_args: about to add $internal_args to acounts class args array<br>'; }
			while(list($key,$value) = each($internal_args))
			{
				if ($this->debug_args_input_flow > 2) { echo ' * mail_msg: init_internal_args: (looping) setting internal arg: $this->set_arg_value('.$key.', '.$internal_args[$key].', '.$acctnum.'); <br>'; }
				$this->set_arg_value($key, $internal_args[$key], $acctnum);
				//$this->set_arg_value($key, $internal_args[$key]);
			}
			
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: grab_class_args_gpc: LEAVING, returning $internal_args<br>'; }
			return $internal_args;
		}
		
		/*!
		@function get_best_folder_arg
		@abstract search a variety of vars to find a legitimate folder value to open on the mail server number, 
		@param $args_array ARRAY that was passed to ->begin_request, pass that into here if possible, it is a primary source
		@param $got_args ARRAY of the *External* params / args fed to this script via GPC or other methods
		Note: these are NOT the "internal args"
		@param $acctnum INTEGER used to querey various already-set args
		@result string, mostt legitimate folder value that was obtained
		@discussion The return folder string MUST *NOT* BE URLENCODED. 
		@author Angles
		@access Private
		*/
		function get_best_folder_arg($args_array='', $got_args='', $acctnum='')
		{
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: get_best_folder_arg: ENTERING <br>'; }
			if ($this->debug_args_input_flow > 2) { echo 'mail_msg: get_best_folder_arg: param $acctnum ['.$acctnum.'] ; parm DUMP $args_array[] then $got_args[] dumps:<pre>'; print_r($args_array);  print_r($got_args); echo '</pre>'; }
			// we SHOULD have already obtained a valid acctnum before calling this function
			if (!(isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			//  ----  Get Folder Value  ----
			// ORDER OF PREFERENCE for pre-processed "folder" input arg
			// (1) $args_array, IF FILLED, overrides any previous data or any other data source, look for these:
			//	$args_array['msgball']['folder']
			//	$args_array['fldball']['folder']
			//	$args_array['folder']
			// (2) GPC ['msgball']['folder']
			// (3) GPC ['fldball']['folder']
			// (4) GPC ['delmov_list'][0]['folder']
			// (5) if "folder" arg it is already set, (probably during the reuse attempt, probably obtained from $args_array alreadt) then use that
			// (6) default to blank string, which "prep_folder_in()" changes to defaultg value INBOX
			
			// note: it's OK to send blank string to "prep_folder_in", because it will return a default value of "INBOX"
			if ((isset($args_array['folder']))
			&& ($args_array['folder'] != ''))
			{
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_folder_arg: $input_folder_arg chooses $args_array[folder] ('.$args_array['folder'].') over any existing "folder" arg<br>'; }
				$input_folder_arg = $args_array['folder'];
			}
			elseif ($this->get_isset_arg('["msgball"]["folder"]'))
			{
				$input_folder_arg = $this->get_arg_value('["msgball"]["folder"]');
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_folder_arg: $input_folder_arg chooses $this->get_arg_value(["msgball"]["folder"]): ['.$input_folder_arg.']<br>'; }
			}
			elseif ($this->get_isset_arg('["fldball"]["folder"]'))
			{
				$input_folder_arg = $this->get_arg_value('["fldball"]["folder"]');
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_folder_arg: $input_folder_arg chooses $this->get_arg_value(["fldball"]["folder"]): ['.$input_folder_arg.']<br>'; }
			}
			elseif ($this->get_isset_arg('delmov_list'))
			{
				// we know we'll need to loginto this folder to get this message and move/delete it
				// there may be other msgballs in the delmov_list array, but we know at the very list we'll need to open this folder anyway
				$this_delmov_list = $this->get_arg_value('delmov_list');
				$input_folder_arg = $this_delmov_list[0]['folder'];
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_folder_arg: $input_folder_arg chooses $this_delmov_list[0][folder]: ['.$input_folder_arg.']<br>'; }
			}
			else
			{
				if (($this->get_isset_arg('folder'))
				&& ((string)trim($this->get_arg_value('folder')) != ''))
				{
					$input_folder_arg = $this->get_arg_value('folder');
				}
				if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_folder_arg: $input_folder_arg *might* chooses $this->get_arg_value(folder): ['.serialize($input_folder_arg).']<br>'; }
				
				$input_folder_arg = (string)$input_folder_arg;
				$input_folder_arg = trim($input_folder_arg);
				if ($input_folder_arg != '')
				{
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_folder_arg: $this->get_arg_value(folder) passes test, so $input_folder_arg chooses $this->get_arg_value(folder): ['.serialize($input_folder_arg).']<br>'; }
				}
				else
				{
					if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_folder_arg: no folder value found, so $input_folder_arg takes an empty string<br>'; }
					$input_folder_arg = '';
				}
			}
			// ---- Prep the Folder Name (remove encodings, verify it's long name (with namespace)
			// folder prepping does a lookup which requires a folder list which *usually* (unless caching) requires a login
			if ($this->debug_args_input_flow > 1) { echo 'mail_msg: get_best_folder_arg: about to issue $processed_folder_arg = $this->prep_folder_in('.$input_folder_arg.')<br>'; }
			$processed_folder_arg = $this->prep_folder_in($input_folder_arg);
			if ($this->debug_args_input_flow > 0) { echo 'mail_msg: get_best_folder_arg: LEAVING, returning $processed_folder_arg value: ['.$processed_folder_arg.']<br>'; }
			return $processed_folder_arg;
		}	
		
		
		/**************************************************************************\
		* END INPUT ARG/PARAM HANDLERS								*
		* - - - - - - - - - - - - - - - - - - - - - - - - -									*
		* BEGIN APPSESSION TEMPORARY CACHING HANDLERS		*
		\**************************************************************************/
		
		/*!
		@cabability appsession TEMPORARY DATA CACHING - data we generate
		@abstract Caching via the the api appsession cache occurs in 2 basic forms. 
		(ONE) information we are responsible for generating in this mail_msg class, and 
		(TWO) data that the mail server sends us. This discussion is about ONE.
		@discussion Data this class must generate includes preferences, mail server callstring, namespace, delimiter, 
		(not a complete list). The first time a data element is generated, for example ->get_arg_value("mailsvr_namespace"), 
		which is needed before we can login to the mailserver, the private function "get_mailsvr_namespace" determines this 
		string, then places that value in what I refer to as the "Level1 cache" (L1 cache) which is simply a class variable that is filled 
		with that value. Additionally, that data is saved in the appsession cache. The L1 cache only exists as long as the script 
		is run, usually one pageview. The appsession cache exists as long as the user is logged in. When the user requests 
		another page view, private function ""get_mailsvr_namespace" checks (always) the L1 cache for this value, if this 
		is the first time this function has been called for this pageview, that L1 cache does not yet exist. Then the functions 
		checks the appsession cache for this value. In this example, it will find it there, put that value in the L1 cache, then 
		return the value and exit. For the rest of the pageview, any call to this function will return the L1 cache value, no 
		logic in that function is actually encountered. 
		*/
		
		/*!
		@cabability appsession TEMPORARY DATA CACHING - data from the mailserver
		@abstract Caching via the the api appsession cache occurs in 2 basic forms. 
		(ONE) information we are responsible for generating in this mail_msg class, and 
		(TWO) data that the mail server sends us. This discussion is about TWO
		@discussion CACHE FORM TWO is anything the mail server sends us that we want to cache. The IMAP rfc requires we cache as much 
		as we can so we do not ask the server for the same information unnecessarily. Take function "get_msgball_list" as an example. 
		This is a list of messages in a folder, the list we save is in the form of typed array "msgball" which means the list included 
		message number, full folder name, and account number. 
		BEGIN DIGRESSION Why is all this data cached? Traditionally, a mail message has a 
		"ball and chain" association with a particular mail server, a particular account on that mail server, and a particular folder 
		within that account. This is the traditional way to think of email. HOWEVER, this email app desires to completely seperate 
		an individual mail message from any of those traditional associations. So what does a "msgball" list allow us to so? This way 
		we can move messages between accounts without caring where that account is located, what type of server its on, or what  
		folder its in. We can have exotic search results where the "msgball" list contains references to messages from different 
		accounts on different servers of different types in different folders therein. Because every peice of data about the message 
		we need is stored in the typed array "msgball", we have complete freedom of movement and manipulation of those 
		messages.  END DIGRESSION. 
		So the function "get_msgball_list", the first time it is called for any particular folder, asks the mail server for a list of 
		message UIDs (Unique Message ID good for as long as that message is in that folder), and assembles the "msgball" list 
		by adding the associated account number and folder name to that message number. This list is then stored in the 
		appsession cache. Being in the appsession cache means this data will persist for as long as the user is logged in. 
		The data becomes STALE if 1. new mail arrives in the folder, or 2. messages are moved out of the folder. 
		So the next pageview the user requests for that folder calls "get_msgball_list" which attempts to find the 
		data stored in the appsession cache. If it is found cached there, the data is checked for VALIDITY during 
		function "read_session_cache_item" which calls function "get_folder_status_info" and checks for 2 things, 
		1. that this "msgball" is in fact referring to the same server, account, and folder as the newly requested data, 
		and (CRUCIAL ITEM) 2. checks for staleness using the data returned from "get_folder_status_info", especially 
		"uidnext", "uidvalidity", and "number_all" to determine if the data is stale or not. MORE ON THIS LATER. If the 
		data is not stale and correctly refers to the right account, the "msgball" list stored in the appsession cache is used 
		as the return value of "get_msgball_list" and THE SERVER IS NOT ASKED FOR THE MESSAGE LIST 
		UNNECESSARILY. This allows for folders with thousands of messages to reduce client - server xfers dramatically. 
		HOWEVER - this is an area where additional speed could be achieved by NOT VALIDIATING EVERY TIME, meaning 
		we could set X minutes where the "msgball" list is considered NOT STALE. This eliminates a server login just to 
		get validity information via "get_folder_status_info". HOWEVER, even though we have the message list for that 
		folder in cache, we still must request the envelope info (from, to, subject, date) in order to show the index page. 
		THIS DATA COULD BE CACHED TOO. Conclusion - you have seen how a massage list is cached, validated, and 
		reused. Additionally, we have discussed ways to gain further speed with X minutes of assumed "freshness" and 
		by caching envelope data. *UPDATE* AngleMail has begun to cache message structure and message envelope 
		data, but this is under development. THIS DOC NEEDS UPDATING. 
		*/
		
		/*!
		@function expire_session_cache_item
		@abstract ?
		@discussion used with appsession TEMPORARY DATA CACHING server-side caching of limited, 
		ephermal data, such as a list of messages from an imap search, via appsession
		@author Angles
		@access private
		*/
		// ---- session-only data cached to appsession  ----
		function expire_session_cache_item($data_name='misc',$acctnum='', $special_extra_stuff='')
		{		
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ($this->debug_session_caching > 0) { echo 'mail_msg: expire_session_cache_item: ENTERED, $this->session_cache_enabled='.serialize($this->session_cache_enabled).', $data_name: ['.$data_name.'], $acctnum (optional): ['.$acctnum.'], $special_extra_stuff: ['.$special_extra_stuff.']<br>'; }
			
			if ($this->session_cache_extreme == False)
			{
				// ---  get rid of any L1 cache folder status info  ---
				if ($this->debug_session_caching > 1) { echo 'class_msg: expire_session_cache_item: (non-extreme mode) uses "folder_status_info" L1/class var cache only, NO appsession cache used in non-extreme <br>'; }
				// cache data in a class var (L1 Cache)
				// ALWAYS expire "folder_status_info" because many time this expire function is called because of a message move or delete
				if ($this->debug_session_caching > 1) { echo 'mail_msg: expire_session_cache_item: (non-extreme mode) Mandatory clearing of L1 cache/class data "folder_status_info" <br>'; }
				$empty_array = array();
				$this->set_arg_value('folder_status_info', $empty_array, $acctnum);
			}
			
			// now eliminate the EXPIRED data, 1st get rid of any L1 cache it it exists for this item
			if ($this->debug_session_caching > 1) { echo 'mail_msg: expire_session_cache_item('.__LINE__.'): checking for L1 cache/class var for $data_name = ['.$data_name.']<br>'; }
			if ($this->debug_session_caching > 1) { echo 'mail_msg: expire_session_cache_item('.__LINE__.'): NOTE when session_class_extreme is True, "folder_status_info" is NOT cached in L1 cache/class var, only in appsession<br>'; }
			
			if (($this->get_isset_arg($data_name, $acctnum))
			&& ($data_name != 'folder_status_info'))
			{
				// NOTE 
				$old_content = $this->get_arg_value($data_name, $acctnum);
				if ($this->debug_session_caching > 2) { echo 'mail_msg: expire_session_cache_item('.__LINE__.'): found and clearing L1 cache/class for ['.$data_name.'] OLD value dump:<pre>'; print_r($old_content); echo '</pre>'; }
				if (gettype($old_content) == 'array')
				{
					$empty_data = array();
				}
				else
				{
					$empty_data = '';
				}
				// set the arg item to this blank value, effectively clearing/expiring it
				$this->set_arg_value($data_name, $empty_data, $acctnum);
			}
			
			// ---  now get rid of any "$data_name" value saved in the session cache  ---
			// DUDE IF THE DATA REQUIRES "$special_extra_stuff" THEN PUT IT HERE, CHECK FOR ITS $data_name HERE
			// OR ELSE YOU EXPIRE A SUPERSTRUCTURE OF DATA INSTEAD OF THE INDIVIDUAL SUB ELEMENT!
			if ((($data_name == 'msg_structure')
				|| ($data_name == 'phpgw_header')
				|| ($data_name == 'folder_status_info'))
			&& ($special_extra_stuff != ''))
			{
				// NOTE if no $special_extra_stuff is passed, then indeed we will expire the entire array, (that is expiration as usual, done below)
				if ($this->debug_session_caching > 1) { echo 'class_msg: expire_session_cache_item: expire INDIVIDUAL sub element if any $special_extra_stuff ['.$special_extra_stuff.'] is passed with $data_name ['.$data_name.'] <br>'; }
				
				// these items are unusual because they expire ONE SINGLE element ($special_extra_stuff) of the data array
				// NOT the entire data array
				//$specific_key = (string)$data['msgball']['msgnum'].'_'.$data['msgball']['folder'];
				$specific_key = $special_extra_stuff;
				$location = 'acctnum='.(string)$acctnum.';data_name='.$data_name;
				$app = 'email';
				$meta_data = array();
				$meta_data = $GLOBALS['phpgw']->session->appsession($location,$app);
				// we can only expire an array element if the array exists
				if ($meta_data)
				{
					// does the specific_key exist
					if (isset($meta_data[$data_name][$specific_key]))
					{
						// erase it
						$meta_data[$data_name][$specific_key] = '';
						unset($meta_data[$data_name][$specific_key]);
						// save the altered data array back to the appsession
						if ($this->debug_session_caching > 1) { echo 'mail_msg: expire_session_cache_item: location: ['.$location.']; $app ['.$app.']; $data_name ['.$data_name.'] erase single array element $special_extra_stuff ['.$special_extra_stuff.']<br>'; }
						if ($this->session_cache_debug_nosave == False)
						{
							$GLOBALS['phpgw']->session->appsession($location,$app,$meta_data);
						}
					}
				}
			}
			else
			{
				// for session cache, we can simple set the value to an empty string to blank it out
				// ***** NOTE THIS NEEDS UPDATING *****
				// WE NO LONGER CACHE ANY "msg_structure" NOR ANY "phpgw_header" DATA 
				// IF $this->session_cache_extreme IS FALSE
				if ($this->session_cache_extreme == False)
				{
					// ---  get rid of any L1 cache folder status info  ---
					if ($this->debug_session_caching > 1) { echo 'class_msg: expire_session_cache_item: (non-extreme mode) uses "folder_status_info" L1/class var cache only, NO appsession folder_status_info" cache used in non-extreme <br>'; }
					if ($this->debug_session_caching > 1) { echo 'class_msg: expire_session_cache_item: (non-extreme mode) clear "folder_status_info" L1/class var cache now.<br>'; }
					$empty_data = '';
					$this->set_arg_value('folder_status_info', $empty_array, $acctnum);
				}
				// save blank data to session to erase/expire it
				$empty_data = '';
				if ($this->debug_session_caching > 1) { echo 'class_msg: expire_session_cache_item: expire this data by calling "save_session_cache_item" with empty data. $data_name ['.$data_name.'] $empty_data ['.serialize($empty_data).'] $acctnum ['.$acctnum.']<br>'; }
				$this->save_session_cache_item($data_name, $empty_data, $acctnum);
			}
			if ($this->debug_session_caching > 0) { echo 'mail_msg: expire_session_cache_item: LEAVING<br>'; }
		}
		
		/*!
		@function save_session_cache_item
		@abstract TEMPORARY DATA CACHING server-side in the phpgw appsession cache.
		@param $data_name (string) 
		@param $data (mixed) usually an array 
		@param $acctnum (int) 
		@author Angles
		@discussion Server-side caching of limited, ephermal data, such as a list of messages from 
		an imap search, saved to phpgw appsession. All appsession data gets deleted when the user logs out, which is 
		why this is a temporary cache. NOTE: to cache an item you must add it to list of data items that 
		has a handler here, otherwise we skip it. In general, we store stuff in the cache in the form 
		$meta_data[$data_name] [actual-data] and *some* cached items get meta_data saved with it, 
		such as the "msgball_list", $meta_data["msgball_list"] [validity-data]  and $meta_data["msgball_list"] [actual-data] 
		where that meta_data is used to verify if the cached "msgball_list" is valid, not-stale data, when restoring 
		a "msgball_list" from the cache. But not all cached data has actual meta_data saved with it, but the 
		capability is there if meta_data is needed. Additionally, in general the"actual-data" is saved whole and 
		restored whole, HOWEVER "msg_structure" and "phpgw_header" caching is UNUSUAL because we 
		save and restore a SINGLE ELEMENT of an EXISTING ARRAY of "actual-data" at any one time. 
		@access private
		*/
		function save_session_cache_item($data_name='misc',$data,$acctnum='')
		{
			$has_handler = False;
			
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: ENTERED, $this->session_cache_enabled='.serialize($this->session_cache_enabled).'<br>'; }
			
			if (($this->session_cache_enabled)
			&& (!$data))
			{
				// we know what to do here, so this data "has a handler"
				$has_handler = True;
				// empty $data means "EXPIRE the data"
				$location = 'acctnum='.(string)$acctnum.';data_name='.$data_name;
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
				if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: LEAVING, expired data for $data_name ['.$data_name.'] $acctnum ['.$acctnum.']<br>'; }
			}
			elseif ($this->session_cache_enabled)
			{
				if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: session_cache_enabled and data exists<br>'; }
				// process the data according to what it is
				if ($data_name == 'msgball_list')
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: session_cache_enabled and data exists AND $data_name ['.$data_name.'] has a handler<br>'; }
					// we know what to do here, so this data "has a handler"
					$has_handler = True;
					
					// ----  set the data in appsession  ----
					if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: $data_name ['.$data_name.'] is saved with validity data from "get_folder_status_info" for later staleness testing<br>'; }
					// we use folder_info for validity testing of data "stale" or not when we retrieve the cached data later
					$fldball = array();
					$fldball['acctnum'] = $acctnum;
					$fldball['folder'] = $this->get_arg_value('folder', $acctnum);
					$folder_info = $this->get_folder_status_info($fldball);
					// make the structure for the data
					$meta_data = Array();
					$meta_data[$data_name] = $data;
					$meta_data['validity'] = Array();
					$meta_data['validity']['folder_long'] = $this->get_arg_value('folder', $acctnum);
					$meta_data['validity']['sort'] = $this->get_arg_value('sort', $acctnum);
					$meta_data['validity']['order'] = $this->get_arg_value('order', $acctnum);
					$meta_data['validity']['uidnext'] = $folder_info['uidnext'];
					$meta_data['validity']['uidvalidity'] = $folder_info['uidvalidity'];
					$meta_data['validity']['number_all'] = $folder_info['number_all'];
					$meta_data['validity']['get_mailsvr_callstr'] = $this->get_arg_value('mailsvr_callstr', $acctnum);
					$meta_data['validity']['mailsvr_account_username'] = $this->get_arg_value('mailsvr_account_username', $acctnum);
					
					// SANITY CHECK
					//echo ' ** '.serialize($meta_data['validity']['sort']) .'<br>';
					//echo ' ** '.serialize($meta_data['validity']['order']) .'<br>';
				}
				elseif (($data_name == 'mailsvr_namespace')
				|| ($data_name == 'folder_list'))
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: session_cache_enabled and data exists for "'.$data_name.'" AND has a handler<br>'; }
					// we know what to do here, so this data "has a handler"
					$has_handler = True;
					// make the structure for the data
					$meta_data = Array();
					$meta_data[$data_name] = $data;
				}
				elseif ($data_name == 'mailsvr_callstr')
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: session_cache_enabled and data exists for "'.$data_name.'" AND has a handler<br>'; }
					// we know what to do here, so this data "has a handler"
					$has_handler = True;
					if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: "'.$data_name.'" before encoding: '.serialize($data).'<br>'; }
					// DATABASE DEFANG, this item has "database unfriendly" chars in it so we encode it before it goes to appsession cache
					$data = base64_encode($data);
					if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: "'.$data_name.'" AFTER encoding: '.serialize($data).'<br>'; }
					// make the structure for the data
					$meta_data = Array();
					$meta_data[$data_name] = $data;
				}
				elseif (($data_name == 'msg_structure')
				|| ($data_name == 'phpgw_header')
				|| ($data_name == 'folder_status_info'))
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: session_cache_enabled and data exists for "'.$data_name.'" AND has a handler<br>'; }
					if ($this->debug_session_caching > 2) { echo 'mail_msg: save_session_cache_item: "'.$data_name.'" ARRIVING param $data as it is passed into this function DUMP:<pre>'; print_r($data); echo '</pre>'; } 
					// we know what to do here, so this data "has a handler"
					$has_handler = True;
					/*!
					@capability msg_structure and phpgw_header unique handling
					@discussion These 2 data items are cached ONLY IF this->session_cache_extreme IS TRUE. 
					Notes on this data caching: 
					(1) Fetchstructure data can ONLY BE OBTAINED for the currently selected folder, 
					so we automatically know which folder this data is applies to by calling get_arg_value("folder") 
					HOWEVER in order to break free from this limitation in grabbing the data we are about to cache, 
					ALWAYS pass a msgball WITH a folder value, even though it is obvious now, it may not be later. 
					Note: folder name must be in urlencoded form, because it may contain "database unfriendly" chars 
					This applies to "phpgw_header" data also. 
					(Note: a msgball without a folder is *extremely* rare, and probably never occurs.) 
					(2) "msg_structure" and "phpgw_header" caching scheme is UNUSUAL because we get and set a SINGLE ELEMENT 
					of an EXISTING ARRAY, we do not set or return the entire array. We ADD a single key, value pair 
					to an existing array, or we return a single value from the cached array, not the entire array itself. 
					@syntax (3) Data Param ARRIVES to this function like this
					msg_structure $data param arrives into this function like this:
						$data['msgball'] = $msgball;
						$data['msg_structure'] = $data;
					 
					phpgw_header $data param arrives into this function like this:
						$data['msgball'] = $msgball;
						$data['phpgw_header'] = $data;
					
					(4) In general, we store stuff in the cache in this form $meta_data[$data_name] 
					But in this case we want to manipulate individual key, value pairs IN that array. 
					So we use the $data['msgball']  part of the $data param to make the KEY 
					of the key value pair. The KEY is "UID_FOLDER" where UID is the msgnum UID, and 
					FOLDER is the "long" foldername, meaning it has the Namespace_Delimiter prefixing it. 
					So we put "msg_structure" and "phpgw_header" stuff in the cache in this form 
					$meta_data["msg_structure"][UID_FOLDER][Serialized msg_structure object]  
					$meta_data["phpgw_header"][UID_FOLDER][Serialized phpgw_header object]  
					*/
					
					// DO IT:
					// (a) make the key, value pair that we will add to the cache
					// KEY = MSGNUM_FOLDERNAME
					// we know the acctnum, so we already know the username and server name is correct
					// but within that account there are many folders, all data for those folders is indexed by a unique KEY
					// by using KEY of msgnum and foldername, we know everything we need to identify the exact data we need
					
					if ($data_name == 'folder_status_info')
					{
						if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: ('.__LINE__.') ['.$data_name.'] needs folder stats to have a PLAIN (not urlencoded) "folder" value ['.$data[$data_name]['folder'].'] so we can urlencode it to make the $specific_key<br>'; } 
						// $data has info to generate out specific key
						// KEY MUST BE FOLDER IN URLENCODED FORM
						//$specific_key = $this->prep_folder_out($data[$data_name]['folder']);
						// ****************
						$specific_key = urlencode($data[$data_name]['folder']);
						$specific_data = serialize($data[$data_name]);
					}
					else
					{
						$specific_key = (string)$data['msgball']['msgnum'].'_'.$data['msgball']['folder'];
						// "msg_structure" is an object, we must serialize it inorder to save it to cache
						//$specific_data = serialize($data['msg_structure']);
						//$specific_data = serialize($data['phpgw_header']);
						$specific_data = serialize($data[$data_name]);
					}
					
					if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: ['.$data_name.'] $specific_key ['.$specific_key.']<br>'; } 
					if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: ['.$data_name.'] $specific_data <p>'.htmlspecialchars($specific_data).']</p>'; } 
					
					$meta_data = array();
					
					
					// (c) GET EXISTING array which we will ADD TO then save back to cache
					$location = 'acctnum='.(string)$acctnum.';data_name='.$data_name;
					$app = 'email';
					// we store stuff in the cache in this form $meta_data[$data_name]
					// we want to add directly to existing cached msg_structure array, (or existing phpgw_header array)
					// therefor to the existing $meta_data["msg_structure"][ITEM_KEY][ITEM_VALUE]
					// therefor to the existing $meta_data["phpgw_header"][ITEM_KEY][ITEM_VALUE]
					$meta_data = $GLOBALS['phpgw']->session->appsession($location,$app);
					if (!$meta_data)
					{
						// initialize a fresh array, this "msg_structure" (or "phpgw_header") will be its first element
						$meta_data = array();
						// this is the form we save stuff in the cache as: ARRAY[$data_name]
						$meta_data[$data_name] = array();
					}
					if ($this->debug_session_caching > 2) { echo 'mail_msg: save_session_cache_item: ['.$data_name.'] has pre-existing "meta_data" cached array before we add our new element :: DUMP:<pre>'; print_r($meta_data); echo '</pre>'; } 
					// cached stuff is in this form in the cache ARRAY["data_name"] = DATA
					// ARRAY["msg_structure"] = ARRAY_of_msg_structure_items
					// we need to go 1 level in, add DIRECTLY to the "ARRAY_of_msg_structure_items" itself
					$meta_data[$data_name][$specific_key] = $specific_data;
					if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: ('.__LINE__.') data ready for $data_name ['.$data_name.'] $acctnum ['.$acctnum.'] $specific_key ['.$specific_key.']<br>'; }
					
					// (d) now we have an existing array of "msg_structure" to which we added another "msg_structure" item, 
					// and now we treat this stuff generically like all other cached items for this part of the code where we actually 
					// save the stuff to the appsession cache.
					
					// (e) FIX ME: encode stuff that may be database unfriendly
					//if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: "'.$data_name.'" before encoding: '.serialize($data).'<br>'; }
					// 
					//if ($this->debug_session_caching > 1) { echo 'mail_msg: save_session_cache_item: "'.$data_name.'" AFTER encoding: '.serialize($data).'<br>'; }
				}
				else
				{
					// this data_name has no specific handler
					if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: error - NO HANDLER for data_name='.$data_name.'<br>'; }
					// make an empty $meta_data Array as a sign there's no data to save
					$meta_data = Array();
				}
				
				// save data, assuming we've "handled" it
				if ((isset($meta_data))
				&& (count($meta_data) > 0))
				{
					$location = 'acctnum='.(string)$acctnum.';data_name='.$data_name;
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
					if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: LEAVING, did set data for $data_name ['.$data_name.'] $acctnum ['.$acctnum.']<br>'; }
				}
				else
				{
					if ($this->debug_session_caching > 0) { echo 'mail_msg: save_session_cache_item: unable to save data for data_name: ['.$data_name.'] $meta_data is an array wit count of 0, probably unhandled data<br>'; }
				}
			}
		}
		
		/*!
		@function read_session_cache_item
		@abstract ? 
		@param $data_name (string) 
		@param $acctnum (int) optional
		@param $special_extra_stuff (string ?) currently only needed for restoring "msg_structure" and "phpgw_header" data 
		@author Angles
		@discussion used with appsession TEMPORARY DATA CACHING server-side caching of limited, 
		ephermal data, such as a list of messages from an imap search, via appsession. 
		NOTE: currently only ONE "msgball_list" is saved per account, and when user changes folders 
		within an account, we must request a new "msgball_list" for that folder from the mail server. 
		ISSUES (pro) this saves appsession from building up massive lists of msgballs, and (con) this 
		results in needless requesting of info that we may have already asked for. NOTE the msgball_list 
		does not care about number new, and only looks at number all to know when to refresh itself. 
		The primary handler of that status info is function "get_folder_status_info".  THEREFOR 
		msgball_list most important freshness meta_data is "number_all" for what we need to do. 
		We need to use an event to directly make fresh the cached data and save it back to cache, 
		so in order for the data not to look "stale" we need to do some math on the "number_all" in 
		that msgball_list meta_data also. 
		@access private
		*/
		function read_session_cache_item($data_name='misc', $acctnum='', $special_extra_stuff='')
		{
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: ENTERED, $this->session_cache_enabled='.serialize($this->session_cache_enabled).'<br>'; }
			
			if ($this->session_cache_enabled)
			{
				if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: begin get data<br>'; }
				
				$location = 'acctnum='.(string)$acctnum.';data_name='.$data_name;
				$app = 'email';
				// get session data
				$got_data = $GLOBALS['phpgw']->session->appsession($location,$app);
				//if ($this->debug_session_caching > 2) { echo 'mail_msg: read_session_cache_item: location: ['.$location.'] $app='.$app.'; $got_data dump:<pre>'; print_r($got_data); echo '</pre>'; }
				
				// use a specific handler for the data
				if ($data_name == 'folder_status_info')
				{
					// THIS DATA IS NEVER CACHED IF $this->session_cache_extreme IS FALSE
					if ($this->debug_session_caching > 2) { echo 'mail_msg: read_session_cache_item: ('.__LINE__.') location: ['.$location.'] $app='.$app.'; $got_data dump:<pre>'; print_r($got_data); echo '</pre>'; }
					if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: ('.__LINE__.') $data_name ['.$data_name.'], is only cached is $this->session_cache_extreme is TRUE<br>'; }
					// this is set as a class param in file mail_msg_base
					$timestamp_age_limit = $this->timestamp_age_limit;
					
					if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: ('.__LINE__.')  handler exists for $data_name ['.$data_name.'], this item requires param $special_extra_stuff ['.serialize($special_extra_stuff).']<br>'; }
					// this special handler uses timestamp info to determine "freshness"
					if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: ('.__LINE__.')  KEY ($special_extra_stuff) MUST BE FED INTO HERE AS A FOLDER IN URLENCODED FORM'; } 
					$specific_key = $special_extra_stuff;
					if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: ('.__LINE__.') $data_name ['.$data_name.'], param $special_extra_stuff gives us $specific_key ['.$specific_key.']<br>'; }
					// does the data exist in this form
					// $got_data[$data_name][$specific_key]
					if (($got_data)
					&& (isset($got_data[$data_name][$specific_key])))
					{
						
						$folder_status_info = unserialize($got_data[$data_name][$specific_key]);
						$timestamp_age = (time() - $folder_status_info['timestamp']);
						if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') got cached data, $timestamp_age ['.$timestamp_age.'] ; $timestamp_age_limit ['.$timestamp_age_limit.']<br>'; }
						if ($this->debug_session_caching > 2) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') $folder_status_info DUMP:<pre>'; print_r($folder_status_info); echo '</pre>'; }
						if ($timestamp_age > $timestamp_age_limit) 
						{
							if ($this->debug_session_caching > 1) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') LEAVING, $timestamp_age ['.$timestamp_age.'] EXCEEDS $timestamp_age_limit ['.$timestamp_age_limit.'], this data NEEDS REFRESHING, expire this element<br>'; }
							$got_data[$data_name][$specific_key] = '';
							unset($got_data[$data_name][$specific_key]);
							$this->expire_session_cache_item('folder_status_info', $acctnum, $specific_key);
							if ($this->debug_session_caching > 0) { echo 'class_msg: get_folder_status_info: ('.__LINE__.') LEAVING,  $data_name ['.$data_name.'] $specific_key ['.$specific_key.'], $timestamp_age ['.$timestamp_age.'] EXCEEDS $timestamp_age_limit ['.$timestamp_age_limit.'], this data NEEDS REFRESHING, returning False<br>'; }
							return False;
							
						}
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: ('.__LINE__.') LEAVING, successfully restored $data_name ['.$data_name.'] session data, $acctnum: ['.$acctnum.'],  $specific_key ['.$specific_key.'], data passed timestamp test<br>'; }
						return $folder_status_info;
					}
					else
					{
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: ('.__LINE__.') LEAVING, returning False, $data_name ['.$data_name.'] had NO data stored, $acctnum: ['.$acctnum.'],  $specific_key ['.$specific_key.']<br>'; }
						return False;
					}
					
				}
				elseif ($data_name == 'msgball_list')
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: handler exists for $data_name ['.$data_name.']<br>'; }
					// folder_info used to test validity (stale or not) of the cached msgball_list data
					if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: handling $data_name ['.$data_name.'] session validity and/or relevance, check against "get_folder_status_info" data<br>'; }
					$fldball = array();
					$fldball['acctnum'] = $acctnum;
					$fldball['folder'] = $this->get_arg_value('folder', $acctnum);
					$folder_info = $this->get_folder_status_info($fldball);
					
					/*!
					@capability VERIFY appsession cached "msgball_list" data is still valid (in read_session_cache_item)
					@discussion The "msgball_list" is a list of all messages in a folder sorted according to user prefs and 
					containing additional information to make each message UID into a msgball by adding the acctnum 
					and folder data to the msgnum. This data is put into a numbered array we call the "msgball_list". 
					This "msgball_list" is cached in the appsession along with some meta_data that we use verify the 
					cached msgball_list as (a) applicable to the current mailserver and user, and (b) for "freshness". 
					For (b) "freshness", we compare the meta_data items for folder status against the 
					current "folder_status_info" data to determine if this cached msgball_list is "fresh" or "stale". 
					See the example for the exact data structure of the cached data. Additionally, for (a) "applicablility" 
					just to be super safe, the cached msgball_list meta_data is tested against the current "mailsvr_callstr" and 
					the current "mailsvr_account_username" args, not really needed but is does make us confident 
					that the cached msgball_list applies to the same account as existed when the cache was set. It is not 
					really known how they could not match, but this additional test can not hurt. 
					This test is the SAME for session_cache_extreme True or False, 
					the difference is that for session_cache_extreme TRUE, the "number_all" element 
					of this data and the "number_all" element of the "folder_status_info" appsession cached 
					data is manually updated so (1) they are "fresh" with respect to changes the user makes 
					such as moving or deleting mail from a folder, and (2) so the following test which matches 
					"number_all" elements of both data sets remain the same, so the "msgball_list" is considered 
					"fresh" by this test.
			@example This is the meta data that is saved and tested on reading the data from cache. 
			$freshness['folder_long'] = $this->get_arg_value('folder', $acctnum);
			$freshness['sort'] = $this->get_arg_value('sort', $acctnum);
			$freshness['order'] = $this->get_arg_value('order', $acctnum);
			$freshness['uidnext'] = $folder_info['uidnext'];
			$freshness['uidvalidity'] = $folder_info['uidvalidity'];
			$freshness['number_all']  = $folder_info['number_all'];
			$freshness['get_mailsvr_callstr'] = $this->get_arg_value('mailsvr_callstr', $acctnum);
			$freshness['mailsvr_account_username'] = $this->get_arg_value('mailsvr_account_username', $acctnum);
					*/
					if ($got_data)
					{
						$freshness = array();
						$freshness['folder_long'] = $this->get_arg_value('folder', $acctnum);
						$freshness['sort'] = $this->get_arg_value('sort', $acctnum);
						$freshness['order'] = $this->get_arg_value('order', $acctnum);
						$freshness['uidnext'] = $folder_info['uidnext'];
						$freshness['uidvalidity'] = $folder_info['uidvalidity'];
						$freshness['number_all']  = $folder_info['number_all'];
						$freshness['get_mailsvr_callstr'] = $this->get_arg_value('mailsvr_callstr', $acctnum);
						$freshness['mailsvr_account_username'] = $this->get_arg_value('mailsvr_account_username', $acctnum);
						
						if ($this->debug_session_caching > 2) { echo 'mail_msg: read_session_cache_item: $data_name ['.$data_name.'] validity check, freshness litmus test match this $freshness DUMP:<pre>'; print_r($freshness); echo '</pre>'; }
						if ($this->debug_session_caching > 2) { echo 'mail_msg: read_session_cache_item: $data_name ['.$data_name.'] validity check, restored data validity $got_data[validity] DUMP<pre>'; print_r($got_data['validity']); echo '</pre>'; }
						
						if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: handling $data_name ['.$data_name.'] session validity and/or relevance<br>'; }
						if (($got_data['validity']['folder_long'] == $this->get_arg_value('folder', $acctnum))
						&& ($got_data['validity']['sort'] == $this->get_arg_value('sort', $acctnum))
						&& ($got_data['validity']['order'] == $this->get_arg_value('order', $acctnum))
						&& ($got_data['validity']['uidnext'] == $folder_info['uidnext'])
						&& ($got_data['validity']['uidvalidity'] == $folder_info['uidvalidity'])
						&& ($got_data['validity']['number_all']  == $folder_info['number_all'])
						&& ($got_data['validity']['get_mailsvr_callstr'] == $this->get_arg_value('mailsvr_callstr', $acctnum))
						&& ($got_data['validity']['mailsvr_account_username'] == $this->get_arg_value('mailsvr_account_username', $acctnum)))
						{
							if (($this->debug_session_caching > 2) && ($this->debug_allow_magball_list_dumps)) { echo 'mail_msg: read_session_cache_item: $data_name ['.$data_name.'] verified NOT Stale, restored data dump:<pre>'; print_r($got_data); echo '</pre>'; }
							if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, successfully restored VALID and NOT Stale $data_name ['.$data_name.'] session data, $acctnum: ['.$acctnum.']<br>'; }
							return $got_data;
						}
						else
						{
							if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, returning False, $data_name ['.$data_name.'] session was STALE, $acctnum: ['.$acctnum.']<br>'; }
							return False;
						}
					}
					else
					{
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, returning False, $data_name ['.$data_name.'] had NO data stored, $acctnum: ['.$acctnum.']<br>'; }
						return False;
					}
				}
				elseif (($data_name == 'mailsvr_namespace')
				|| ($data_name == 'folder_list'))
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: handler exists for $data_name ['.$data_name.']<br>'; }
					// this is not really a special handler
					if ($got_data)
					{
						if ($this->debug_session_caching > 2) { echo 'mail_msg: read_session_cache_item: restored $data_name ['.$data_name.'] data dump:<pre>'; print_r($got_data); echo '</pre>'; }
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, successfully restored $data_name ['.$data_name.'] session data, $acctnum: ['.$acctnum.']<br>'; }
						return $got_data;
					}
					else
					{
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, returning False, $data_name ['.$data_name.'] had NO data stored, $acctnum: ['.$acctnum.']<br>'; }
						return False;
					}
					
				}
				elseif ($data_name == 'mailsvr_callstr')
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: handler exists for $data_name ['.$data_name.']<br>'; }
					// this special handler decodes the database defanging
					if ($got_data)
					{
						// DATABASE DEFANG, this item has "database unfriendly" chars in it so we encode it before it goes to appsession cache
						// now we mode DECODE it coming out of the appsession cache
						if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: restored $data_name ['.$data_name.'] (pre-decoded) data dump:<pre>'; print_r($got_data); echo '</pre>'; }
						$got_data[$data_name] = base64_decode($got_data[$data_name]);
						if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: restored $data_name ['.$data_name.'] (decoded) data dump:<pre>'; print_r($got_data); echo '</pre>'; }
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, successfully restored $data_name ['.$data_name.'] session data, $acctnum: ['.$acctnum.']<br>'; }
						return $got_data;
					}
					else
					{
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, returning False, $data_name='.$data_name.' had NO data stored, $acctnum: ['.$acctnum.']<br>'; }
						return False;
					}
					
				}
				elseif (($data_name == 'msg_structure')
				|| ($data_name == 'phpgw_header')
				|| ($data_name == 'folder_status_info'))
				{
					if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: handler exists for $data_name ['.$data_name.']<br>'; }
					// this IS a special handler because we do not return the entire cached array of "msg_structure" elements, 
					// we only return ONE SINGLE "msg_structure" (or "phpgw_header" ) value from that array, 
					// if it exists in that array. 
					// the array can exist but not have the specific element we are looking for
					// NOTE: we need param $special_extra_stuff because other cached items are returned whole
					// but "msg_structure" (or "phpgw_header" ) need that EXTRA peice of information 
					// to return only a portion of cached data. 
					$specific_value = '';
					if ($got_data)
					{
						if ($this->debug_session_caching > 2) { echo 'mail_msg: read_session_cache_item: found an existing array of items for $data_name ['.$data_name.'] data DUMP:<pre>'; print_r($got_data); echo '</pre>'; }
						// we need more info than the usual cached item to get what we want, because we want a SINGLE element only
						// the array KEY in the key,value pair is passed in param $special_extra_stuff
						// data lives in the cache in this form: ARRAY[$data_name] = VALUE
						// in the case of "msg_structure" and "phpgw_header", it looks like this: 
						//   ARRAY[$data_name][ITEM_KEY] = [ITEM_VALUE]
						$specific_key = $special_extra_stuff;
						// does our specific element exist?
						if ((isset($got_data[$data_name][$specific_key]))
						&& ($got_data[$data_name][$specific_key] != ''))
						{
							// SUCCESS - desired single element within that array does exist
							// also unserialize it back into an object
							$specific_value = unserialize($got_data[$data_name][$specific_key]);
						}
						
						// FIXME unencode any database defanging we may have done
						//if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: restored $data_name='.$data_name.' data dump:<pre>'; print_r($got_data); echo '</pre>'; }
						//if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, successfully restored $data_name='.$data_name.' session data, $acctnum: ['.$acctnum.']<br>'; }
					}
					
					if ($specific_value != '')
					{
						if ($this->debug_session_caching > 1) { echo 'mail_msg: read_session_cache_item: restored specific element for $data_name ['.$data_name.'] specific_value dump:<pre>'; print_r($specific_value); echo '</pre>'; }
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, successfully restored $data_name ['.$data_name.'] session data, $acctnum: ['.$acctnum.']<br>'; }
						return $specific_value;
					}
					else
					{
						if ($this->debug_session_caching > 0) { echo 'mail_msg: read_session_cache_item: LEAVING, returning False, $data_name ['.$data_name.'] had NO data stored, $acctnum: ['.$acctnum.']<br>'; }
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
		
		/**************************************************************************\
		* END APPSESSION TEMPORARY CACHING HANDLERS		*
		* - - - - - - - - - - - - - - - - - - - - - - - - -									*
		* BEGIN **DEPRECIATED *** UNUSED *** 						*
		* 		SEMI-PERMENANT CACHING HANDLERS					*
		\**************************************************************************/
		
		/*!
		@cabability Pref-Based SEMI-PERMENANT DATA CACHING (DEPRECIATED) (NOT USED ANYMORE)
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
		*/

		/*!
		@function get_cached_data
		@abstract NOT USED, part of semi-permanent, session-spanning caching of data
		@discussion DORMANT CODE, used with  server-side session-spanning caching. 
		currently not used but may be implemented again.
		@author Angles
		@access private
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
		
		/*!
		@function set_cached_data
		@abstract NOT USED, part of semi-permanent, session-spanning caching of data
		@discussion DORMANT CODE, used with  server-side session-spanning caching. 
		currently not used but may be implemented again.
		@author Angles
		@access private
		*/
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
		
		/*!
		@function remove_cached_data
		@abstract NOT USED, part of semi-permanent, session-spanning caching of data
		@discussion DORMANT CODE, used with  server-side session-spanning caching. 
		currently not used but may be implemented again.
		@author Angles
		@access private
		*/
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
		
		/**************************************************************************\
		* END **DEPRECIATED *** UNUSED *** 							*
		* 		SEMI-PERMENANT CACHING HANDLERS					*
		* - - - - - - - - - - - - - - - - - - - - - - - - -									*
		* BEGIN PARAM / ARGS / PREFS  ACCESS FUNCTIONS 			*
		\**************************************************************************/
		
		/*!
		@capability OOP-Style Access Methods to Private Object Properties
		@abstract  simple access methods to read and set data, with transparent account number handling
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
		@abstract  read which account number the object is currently activated on
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
		@abstract  instruct the object which email account is the desired active account for all params,
		args, and preferences should refer to.
		@param $acctnum  integer  
		@result True if a valid param $acctnum is given and the object->acctnum value is set, False if 
		invalid data is passed in the param.
		@author Angles
		@discussion ?
		@access public
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
		/*!
		@function get_pref_value
		@abstract ?
		@param $pref_name  (string)
		@param $acctnum  (int) OPTIONAL 
		@result none
		@author Angles
		@discussion ?
		@access public
		*/
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
		
		/*!
		@function set_pref_value
		@abstract ?
		@param $pref_name  (string)
		@param $this_value (string, int, or array)
		@param $acctnum  (int) OPTIONAL 
		@result True if pref value was set, False on failure to set the item, such as invalid data is passed in the param.
		@author Angles
		@discussion ?
		@access public
		*/
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
		
		/*!
		@function get_isset_pref
		@abstract Check if a given preference is set
		@param $pref_name  (string)
		@param $acctnum  (int) OPTIONAL 
		@result True if preference $pref_name value exists, False if not
		@author Angles
		@discussion It is common the boolean preference items are simply not set if their value 
		is supposed to be false. This function can be used for this discovery. Note that some 
		string preferences items, such as the email sig, can be set yet have a value of an empty string, 
		in this case this function follows strict logic and returns True because the preference exists. 
		Similarly, uwash mail location is another example of a preference item where an empty string 
		is a valid value.
		@access public
		*/
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
		
		/*!
		@function unset_pref
		@abstract unset a preference item.
		@param $pref_name  (string)
		@param $acctnum  (int) OPTIONAL 
		@result True if $pref_name existed and was made unset, False on failure, such as $pref_name not 
		being set in the first place. This function can not unset something that does not exist to begin with.
		@author Angles
		@discussion ?
		@access public
		*/
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
		
		/*!
		@function get_all_prefs
		@abstract get the entire preference data array FOR ONE ACCOUNT
		@param $acctnum  (int) OPTIONAL 
		@result none
		@author Angles
		@discussion The result are not the raw preferences directly from the database, this function returns 
		the preference array for an email account as explosed to the email app, that is these are preferences that 
		have passed through some logic to process and normalize them,
		@access public
		*/
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
		
		/*!
		@function set_pref_array
		@abstract set the entire preference data array FOR ONE ACCOUNT
		@param $pref_array_data  (array) either (a) correctly formed emai pref array data, or (b) an empty array
		@param $acctnum  (int) OPTIONAL 
		@result boolean True is successfully sets $pref_array_data, False to indicate all we did was clear the args, no data was fed
		@author Angles
		@discussion NOTE  the first thing this function does is clear the existing preference array for the 
		emal account. This happens no matter what. This effectively is a way to clear an accounts email preference 
		array by passing an empty array, which can be useful in certain situations. More commonly this function 
		is used to set the entire preference array for an account in one operation. In that case you better know 
		what youre doing, $pref_array_data must be correctly formed emai pref array data. By clearing the 
		existing preference array no matter what, this is why a return value of False indicates that, while no 
		new preference data was set, still something did occur and that was the clearing of any pre-existing 
		preference array.
		@access private
		*/
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
		/*!
		@function _get_arg_is_known
		@abstract utility function for private use, tests if a given  $arg_name is in the $this->known_external_args[] array.
		@param $arg_name  (string) 
		@param $calling_function_name  (string) used for debug output
		@result boolean
		@author Angles
		@discussion ?
		@access private
		*/
		function _get_arg_is_known($arg_name='', $calling_function_name='')
		{
			// skip this unless debug level 4
			if ($this->debug_args_oop_access < 4)
			{
				return False;
			}
			
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
		
		/*!
		@function get_isset_arg
		@abstract Check if a given variable is set
		@param $arg_name  (string)
		@param $acctnum  (int) OPTIONAL 
		@result boolean
		@author Angles
		@discussion ?
		@access public
		*/
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
			
			// Best Version at this time, if something is not set, DO NOT handoff to a support function to fill it
			// that way we can return false if something is indeed NOT set
			
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
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_isset_arg: LEAVING returning False<br>'; }
			return False;
		}
		
		/*!
		@function unset_arg
		@abstract unset a class variable
		@param $arg_name  (string)
		@param $acctnum  (int) OPTIONAL 
		@result boolean True if $arg_name existed and was made unset, False on failure, such as $arg_name not 
		being set in the first place. This function can not unset something that does not exist to begin with.
		@author Angles
		@discussion ?
		@access public
		*/
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
		
		/*!
		@function get_arg_value
		@abstract Obtain the value of a given class variable, will handoff to helper functions if necessary.
		@param $arg_name  (string)
		@param $acctnum  (int) OPTIONAL 
		@result (string, int, or array)
		@author Angles
		@discussion Some class variables, such as "mailsvr_namespace", have functions dedicated only to determining their value. 
		In these cases this function will hand off the request directly to that specialized function. In other cases the 
		class variable desired is a simple variable and its value is returned.
		@access public
		*/
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
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_mailsvr_callstr('.$acctnum.')<br>'; }
					return $this->get_mailsvr_callstr($acctnum);
				}
				elseif ($arg_name == 'mailsvr_namespace')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_mailsvr_namespace('.$acctnum.')<br>'; }
					return $this->get_mailsvr_namespace($acctnum);
				}
				elseif ($arg_name == 'mailsvr_delimiter')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_mailsvr_delimiter('.$acctnum.')<br>'; }
					return $this->get_mailsvr_delimiter($acctnum);
				}
				elseif ($arg_name == 'folder_list')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_folder_list()<br>'; }
					return $this->get_folder_list($acctnum);
				}
				elseif ($arg_name == 'verified_trash_folder_long')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value: LEAVING with HANDOFF to get_verified_trash_folder_long()<br>'; }
					return $this->get_verified_trash_folder_long($acctnum);
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
		
		/*!
		@function _direct_access_arg_value
		@abstract utility function for private use, used to bypass any special handlers, to directly access the "args" array.
		@param $arg_name  (string) 
		@param $acctnum  (int) optional
		@result (mixed)
		@author Angles
		@discussion Esoteric utility function for specialized private use.
		@access private
		*/
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

		/*!
		@function _get_arg_ref
		@abstract utility function for private use, used to bypass any special handlers, get a reference to something in 
		the args array.
		@param $arg_name  (string) 
		@param $acctnum  (int) optional
		@result (mixed) direct refernce to an arg value in memory, or a reference to a constant "##NOTHING##" on failure.
		@author Angles
		@discussion Esoteric utility function for specialized private use. Primary for use where speed is an issue.
		NOTE: Returning References requires the ampersand in BOTH the call to the function AND the function 
		declaration here.
		@example 
			function &find_var ($param)
			{
				...code...
				return $found_var;
			}
			$foo =& find_var ($bar);
			// that was straing from the phpmanual
		@access private
		*/
		function &_get_arg_ref($arg_name='',$acctnum='')
		{
			if ($this->debug_args_oop_access > 1) { $this->_get_arg_is_known($arg_name, 'get_arg_ref'); }
			
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
				//return '##NOTHING##';
				return $this->nothing;
			}
		}

		
		/*!
		@function get_arg_value_ref
		@abstract return reference to the value, but smart enough to make it if possible, then return reference. 
		@param (string) $arg_name 
		@param (int) $acctnum (optional)
		@result REFERENCE 
		@discussion get a ref instead of a copy of a value, Try to use "special handler" to generate the 
		value if it does not already exist, then again try to return the reference. Returns ref to 
		$this->nothing on failure.
		@author Angles
		*/
		function &get_arg_value_ref($arg_name='',$acctnum='')
		{
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: ENTERING ($arg_name: ['.$arg_name.'], $acctnum: ['.$acctnum.'] )<br>'; }
			if ($this->debug_args_oop_access > 1) { $this->_get_arg_is_known($arg_name, 'get_arg_value_ref'); }
			
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->get_acctnum();
			}
			
			if (isset($this->a[$acctnum]['args'][$arg_name]))
			{
				if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: LEAVING found and returning ref for ['.$arg_name.'] for ['.$acctnum.']<br>'; }
				return $this->a[$acctnum]['args'][$arg_name];
			}
			else
			{
				// try to geberate the arg value, then again try to return the ref
				if ($arg_name == 'mailsvr_callstr')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: generate arg value using get_mailsvr_callstr('.$acctnum.')<br>'; }
					$this->get_mailsvr_callstr($acctnum);
					
				}
				elseif ($arg_name == 'mailsvr_namespace')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: generate arg value using get_mailsvr_namespace('.$acctnum.')<br>'; }
					$this->get_mailsvr_namespace($acctnum);
				}
				elseif ($arg_name == 'mailsvr_delimiter')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: generate arg value using get_mailsvr_delimiter('.$acctnum.')<br>'; }
					$this->get_mailsvr_delimiter($acctnum);
				}
				elseif ($arg_name == 'folder_list')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: generate arg value using get_folder_list()<br>'; }
					$this->get_folder_list($acctnum);
				}
				elseif ($arg_name == 'verified_trash_folder_long')
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: generate arg value using get_verified_trash_folder_long()<br>'; }
					$this->get_verified_trash_folder_long($acctnum);
				}
				else
				{
					if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: could not find "special handler" to generate an arg value, returning ref to $this->nothing ['.$this->nothing.']<br>'; }
					return $this->nothing;
				}
			}
			// ok, we tried to generate the arg value, were we successful?
			if (isset($this->a[$acctnum]['args'][$arg_name]))
			{
				if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: LEAVING was able to generate arg value, found and returning ref for ['.$arg_name.'] for ['.$acctnum.']<br>'; }
				return $this->a[$acctnum]['args'][$arg_name];
			}
			// fallback, we must have failed to find or make then find an arg value, so no reference to something we can not find
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): get_arg_value_ref: if we get here we probably tried but failed to generate a arg value (tried a "special handler"), so returning ref to $this->nothing ['.$this->nothing.']<br>'; }
			return $this->nothing;
		}

		/*!
		@function set_arg_value
		@abstract Sets a variable in the "args" array.  Should only be used for args that do not require specialized functions.
		@param $arg_name  (string) 
		@param $this_value  (mixed) 
		@param $acctnum  (int) optional
		@result (mixed)
		@author Angles
		@discussion ?
		@access public
		*/
		function set_arg_value($arg_name='', $this_value='', $acctnum='')
		{
			if ($this->debug_args_oop_access > 1) { $this->_get_arg_is_known($arg_name, 'set_arg_value'); }
			if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): set_arg_value: ENTERING, $arg_name: ['.$arg_name.'] ; $this_value: ['.$this_value.'] ; $acctnum: ['.$acctnum.']<br>'; }
			
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
				if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): set_arg_value: LEAVING, returning TRUE, set data $this->a['.$acctnum.'][args]['.$arg_name.']: ['.$this->a[$acctnum]['args'][$arg_name].']<br>'; }
				// return True to indicate success
				return True;
			}
			else
			{
				// return False to indicate invalid input $arg_name
				if ($this->debug_args_oop_access > 0) { echo 'mail_msg(_wrappers): set_arg_value: LEAVING, returning FALSE, invalid $arg_name: ['.$arg_name.']<br>'; }
				return False;
			}
		}
		
		/*!
		@function set_arg_array
		@abstract ?
		@param $arg_array_data  (array) 
		@param $acctnum  (int) optional
		@result boolean
		@author Angles
		@discussion ?
		@access private
		*/
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
		
		/*!
		@function get_all_args
		@abstract ?
		@param $acctnum  (int) optional
		@result (mixed)
		@author Angles
		@discussion ?
		@access private
		*/
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
		
		/*!
		@function unset_all_args
		@abstract ?
		@param $acctnum  (int) optional
		@result none
		@author Angles
		@discussion ?
		@access private
		*/
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
