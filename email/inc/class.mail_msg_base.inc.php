<?php
	/**************************************************************************\
	* phpGroupWare - E-Mail Message Processing Functions				*
	* http://www.phpgroupware.org							*
	*/
	/**************************************************************************\
	* phpGroupWare API - E-Mail Message Processing Functions			*
	* This file written by Angelo Tony Puglisi (Angles) <angles@phpgroupware.org> *
	* Handles specific operations in manipulating email messages			*
	* Copyright (C) 2001 Angelo Tony Puglisi (Angles)					*
	* -------------------------------------------------------------------------			*
	* This library is part of the phpGroupWare API					*
	* http://www.phpgroupware.org/api							* 
	* ------------------------------------------------------------------------ 			*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.								*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of		*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License	*
	* along with this library; if not, write to the Free Software Foundation,		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/

	/* $Id$ */

  class mail_msg_base
  {
	// ----  account - an array where key=mail_account  and  value=all_class_vars for that account
	var $a = array();
	var $acctnum = 0;
	var $fallback_default_acctnum = 0;
	
	// ----  args that are known to be used for email
	// externally filled args, such as thru GPC values, or xmlrpc call
	var $known_external_args = array();
	// args that are typically set and controlled internally by this class
	var $known_internal_args = array();
	// ----  class-wide settings - not account specific
	// some functions use $not_set instead of actuallt having something be "unset"
	var $not_set = '-1';
	// when uploading files for attachment to outgoing mail, use this location in the filesystem
	var $att_files_dir;
	// *maybe* future use - does the client's browser support CSS
	var $browser = 0;
	// use message UIDs instead of "message sequence numbers" in requests to the mail server
	var $force_msg_uids = True;
	// if an existing mail_dcom object exists from a prev request, attempt to adopt and re-use it
	//var $reuse_existing_obj = True;
	var $reuse_existing_obj = False;
	
	// ---- Data Caching  ----
	// (A) session data caching in appsession, for data that is temporary in nature
	var $session_cache_enabled=True;
	//var $session_cache_enabled=False;
	
	// ----  session cache runthru without actuall saving data to appsession
	//var $session_cache_debug_nosave = True;
	var $session_cache_debug_nosave = False;
	
	// (B) "folder list" caching (default value here, will be overridden by preferences item "cache_data")
	// currently caches "mailsvr_namespace" and "get_folder_list" responses to the prefs DB
	var $cache_mailsvr_data_disabled = True;
	//var $cache_mailsvr_data = True;
	var $cache_mailsvr_data = False;
	var $cachable_server_items = Array(
		0	=> 'get_mailsvr_namespace',
		1	=> 'get_folder_list',
		// match_cached_account is vestigal - depreciated
		2	=> 'match_cached_account'
	);
	
	// DEBUG FLAGS generally take int 0, 1, 2, or 3
	var $debug_logins = 0;
	var $debug_session_caching = 0;
	var $debug_longterm_caching = 0;
	var $debug_accts = 0;
	var $debug_args_input_flow = 0;
	var $debug_args_oop_access = 0;
	var $debug_args_special_handlers = 0;
	//var $skip_args_special_handlers = 'get_mailsvr_callstr, get_mailsvr_namespace, get_mailsvr_delimiter, get_folder_list';
	//var $skip_args_special_handlers = 'get_folder_list';
	var $skip_args_special_handlers = '';

	
	// future (maybe never) usage
	//var $known_subtypes = array();



	
	// ----  the "old" way, straight up class vars
	////var $dcom;
	////var $args = Array();
	// data from $GLOBALS['phpgw_info']['user']['preferences']['email'] goes here
	////var $prefs = Array();
	// holds data retored from appsession (this var not needed)
	// var $session_data = array();
	// holding data in a class var for very temporary caching (L1 cache)
	////var $folder_status_info = array();
	////var $folder_list = array();
	////var $mailsvr_callstr = '';
	////var $mailsvr_namespace = '';
	////var $mailsvr_delimiter = '';
	
	// mailsvr_stream and mailsvr_account_username are also used to determine if we are logged in already
	// pointer (well, actually a data holder) to the primary mailbox stream (you may open others) returned by the first login 
	////var $mailsvr_stream = '';
	// user name the we logged in as on the mailserver
	////var $mailsvr_account_username = '';
	////var $folder = '';
	//var $newsmode = False;
	//var $sort = '';
	//var $order = '';
	//var $start = '';
	////var $msgnum = '';
	////var $msgnum_idx = '-1';
	
	

	/*
	function mail_msg_init()
	{
		$this->att_files_dir = $GLOBALS['phpgw_info']['server']['temp_dir'].SEP.$GLOBALS['phpgw_info']['user']['sessionid'];
		$this->create_email_preferences();
		
		
		/// ==== EXPERIMENTAL CODE ====
		/// assemble a list of MIME subtypes that are known to this code
		/// subytpes not in this list will be treated as rfc default specifies
		//$this->known_subtypes['text'] = Array();
		//$this->known_subtypes['message'] = Array();
		//$this->known_subtypes['multipart'] = Array();
		/// populate the array for MULTIPART - unknown subtypes default to MIXED
		//$this->known_subtypes['multipart'] = array(
		//	0 => 'alternative', 
		//	1 => 'digest',
		//	2 => 'mixed',
		//	3 => 'related'
		//);
	}
	*/
	
	function mail_msg()
	{
		if ($this->debug_logins > 0) { echo 'mail_msg: *constructor*: $GLOBALS[PHP_SELF] = ['.$GLOBALS['PHP_SELF'].'] $this->acctnum = ['.$this->acctnum.']  get_class($this) : "'.get_class($this).'" ; get_parent_class($this) : "'.get_parent_class($this).'"<br>'; }
		if ($this->debug_logins > 1) { echo 'mail_msg: *constructor*: $this->acctnum = ['.$this->acctnum.'] ; $this->a  Dump<pre>'; print_r($this->a); echo '</pre>'; }
		
		$this->known_external_args = array(
			// === NEW GPC "OBJECTS" or Associative Arrays === 
			// msgball "object" is a message-descriptive "object" or associative arrays that has all
			// important message reference data as array data, passed via URI (real or embedded)
			'msgball',
			// fldball "object" is an assiciative array of folder data passed via URI (real or embedded)
			'fldball',
			
			// === NEW HTTP POST VARS Embedded Associative Arrays === 
			// "fldball_fake_uri" HTTP_POST_VARS varsion of a URI GET "fldball"
			// usually sourced from a folder combobox where HTML only allows a single value to be passed
			// thus we make a string in the syntax of a URI to contain multiple data values in that single HTML element
			// in this way we embed extra data in an otherwise very limiting HTML element
			// note: even php's POST vars array handling can not do anything with a HTML combobox option value.
			// example: POST data
			// folder_fake_uri="fldball['folder']=INBOX&fldball['acctnum']=0"
			// Will be processed into this (using php function "parse_str()" to emulate URI GET behavior)
			// fldball[folder] => INBOX
			// fldball[acctnum] => 0
			'fldball_fake_uri',
			
			// "delmov_list_fake_uri"
			// comes from the checkbox form data in uiindex.index page, where multiple 
			// boxes may be checked but the POST data is limited to a simple string per checkbox,
			// so additional information is embedded in delmov_list_fake_uri and converted to an 
			// associative array via php function "parse_str"
			//'delmov_list_fake_uri',
			'delmov_list',
			// if moving msgs, this is where they should go
			'to_fldball_fake_uri',
			'to_fldball',
			
			// === SORT/ORDER/START === 
			// if sort,order, and start are sometimes passed as GPC's, if not, default prefs are used
			'sort',
			'order',
			'start',
			
			// newsmode is NOT yet implemented
			//'newsmode',
			
			// === REPORT ON MOVES/DELETES ===
			// ----  td, tm: integer  ----
			// ----  tf: string  ----
			// USAGE:
			//	 td = total deleted ; tm = total moved, tm used with tf, folder messages were moved to
			// (outgoing) class.boaction: when action on a message is taken, report info is passed in these
			// (in) index.php: here the report is diaplayed above the message list, used to give user feedback
			// generally these are in the URI (GET var, not a form POST var)
			'td',
			'tm',
			'tf',
			
			// === MOVE/DELETE MESSAGE INSTRUCTIONS ===
			// ----  what: string ----
			// USAGE: 
			// (outgoing) class.uiindex "move", "delall"
			//	used with msglist (see below) an array (1 or more) of message numbers to move or delete
			//	AND with "toacctnum" which is the acctnum associated with the "tofolder"
			// (outgoing) message.php: "delete" used with msgnum (see below) what individual message to delete
			// (in) class.boaction: instruction on what action to preform on 1 or more message(s) (move or delete)
			'what',
				//'tofolder',
				//'toacctnum',
			// *update*
			// both "tofolder" and "toacctnum" are incorporated into "delmov_list" which is a msgball list of
			// msgball's which are message-descriptive "objects" or associative arrays that have all
			// the necessary data on each message that is to be deleted or moved.
			// the iuindex.index page uses the same form with different submit buttons (what)
			// so the "delmov_list" is applicable to either deleting or moving messages depending
			// on which submit button was clicked
			// 'delmov_list', (see above)
			
			// (passed from class.uiindex) this may be an array of numbers if many boxes checked and a move or delete is called
			//'msglist',
			
			// *update* "msglist" is being depreciated!
			
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
			'action',
			
			// === MESSAGE NUMBER AND MIME PART REFERENCES ===
			// *update* now in msgball
			// msgnum: integer			
			// USAGE:
			// (a) class.boaction, called from from message.php: used with "what=delete" to indicate a single message for deletion
			// (b) compose.php: indicates the referenced message for reply, replyto, and forward handling
			// (c) boaction.get_attach: the msgnum of the email that contains the desired body part to get
			// *update* now in msgball
			//'msgnum',
			
			// ----  part_no: string  ----
			// representing a specific MIME part number (example "2.1.2") within a multipart message
			// (a) compose.php: used in combination with msgnum
			// (b) boaction.get_attach: used in combination with msgnum
			
			// *update* now in msgball
			//'part_no',
			
			// ----  encoding: string  ----
			// USAGE: "base64" "qprint"
			// (a) compose.php: if replying to, we get the body part to reply to, it may need to be un-qprint'ed
			// (b) boaction.get_attach: appropriate decoding of the part to feed to the browser 
			'encoding',
			
			// ----  fwd_proc: string  ----
			// USAGE: "encapsulation", "pushdown (not yet supported 9/01)"
			// (outgoing) message.php much detail is known about the messge, there the forward proc method is determined
			// (a) compose.php: used with action = forward, (outgoing) passed on to send_message.php
			// (b) send_message.php: used with action = forward, instructs on how the SMTP message should be structured
			'fwd_proc',
			// ----  name, type, subtype: string  ----
			// the name, mime type, mime subtype of the attachment
			// this info is passed to the browser to help the browser know what to do with the part
			// (outgoing) message.php: "name" is set in the link to the addressbook,  it's the actual "personal" name part of the email address
			// boaction.get_attach: the name of the attachment
			
			// NOT in msgball, with the other data already in msgball, it should be obvious 
			// what these items are ment to apply to
			'name',
			'type',
			'subtype',
			
			// === FOLDER ADD/DELETE/RENAME & DISPLAY ===
			// ----  "target_folder" , "source_folder" (source used in renaming only)  ----
			// (outgoing) and (in) folder.php: used with "action" to add/delete/rename a mailbox folder
			// 	where "action" can be: create, delete, rename, create_expert, delete_expert, rename_expert
			//'target_folder',
			'target_fldball',
			//'source_folder',
			'source_fldball',
			'source_fldball_fake_uri',
			// ----  show_long: unset / true  ----
			// folder.php: set there and sent back to itself
			// if set - indicates to show 'long' folder names with namespace and delimiter NOT stripped off
			'show_long',
			
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
			'to',
			'cc',
			// body - POST var, never in URI (GET) that I know of, but it is possible, URI (EXTREMELY rare)
			'body',
			'subject',
			// Less Common Usage:
			// ----  sender : string : set or unset
			// RFC says use header "Sender" ONLY WHEN the sender of the email is NOT the author, this is somewhat rare
			'sender',
			// ----  attach_sig: set-True/unset  ----
			// USAGE:
			// (outgoing) compose.php: if checkbox attach sig is checked, this is passed as GPC var to sent_message.php
			// (in) send_message.php: indicate if message should have the user's "sig" added to the message
			'attach_sig',
			// ----  msgtype: string  ----
			// USAGE:
			// flag to tell phpgw to invoke "special" custom processing of the message
			// 	extremely rare, may be obsolete (not sure), most implementation code is commented out
			// (outgoing) currently NO page actually sets this var
			// (a) send_message.php: will add the flag, if present, to the header of outgoing mail
			// (b) message.php: identify the flag and call a custom proc
			'msgtype',
			
			// === MAILTO URI SUPPORT ===
			// ----  mailto: unset / ?set?  ----
			// USAGE:
			// (in and out) compose.php: support for the standard mailto html document mail app call
			// 	can be used with the typical compose vars (see above)
			//	indicates that to, cc, and subject should be treated as simple MAILTO args
			'mailto',
			'personal',
			
			// === MESSAGE VIEWING MODS ===
			// ----  no_fmt: set-True/unset  ----
			// USAGE:
			// (in and outgoing) message.php: will display plain body parts without any html formatting added
			'no_fmt',
			
			// === VIEW HTML INSTRUCTIONS ===
			// html_part: string : actually a pre-processed HTML/RELATED MIME part with
			// the image ID's swapped with msgball data for each "related" image, so the 
			// MUA may obtain the images from the email server using these msgball details
			'html_part',
			
			// === FOLDER STATISTICS - CALCULATE TOTAL FOLDER SIZE
			// as a speed up measure, and to reduce load on the IMAP server
			// there is an option to skip the calculating of the total folder size
			// user may request an override of this for 1 page view
			'force_showsize',
			
			// === SEARCH RESULT MESSAGE SET ===
			'mlist_set',
			// *update* DEPRECIATED - not yet fixed
			
			// === THE FOLDER ARG ===
			// used in almost every procedure, IMAP can be logged into only one folder at a time
			// and POP3 has only one folder anyway (INBOX)
			// this *may* be overrided elsewhere in the class initialization and/or login
			// if not supplied anywhere, then INBOX is the assumed default value for "folder"
			
			// *update* "folder" obtains it's value from (1) args_array, (2) fldball, (3) msgball, (4) default "INBOX"
			'folder'
			
			// which email account is the object of this operation
			// *update* now in fldball
			//'acctnum',
			);
		
		$this->known_internal_args = array(
			// === OTHER ARGS THAT ARE USED INTERNALLY  ===
			'folder_status_info',
			'folder_list',
			'mailsvr_callstr',
			'mailsvr_namespace',
			'mailsvr_delimiter',
			'mailsvr_stream',
			'mailsvr_account_username',
			
			/*
			// DEPRECIATED
			// these are the supported menuaction strings
			'index_menuaction',
			'mlist_menuaction',
			// for message delete or move
			'delmov_menuaction',
			'folder_menuaction',
			'send_menuaction',
			'get_attach_menuaction',
			'view_html_menuaction',
			*/
			// use this uri in any auto-refresh request - filled during "fill_sort_order_start_msgnum()"
			'index_refresh_uri',
			// experimental: Set Flag indicative we've run thru this function
			'already_grab_class_args_gpc'
		);
		//if ($this->debug_logins > 2) { echo 'mail_msg: constructor: $this->known_args[] dump<pre>'; print_r($this->known_args); echo '</pre>'; }
	}
	
	function is_logged_in()
	{
		if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: ENTERING'.'<br>'; }
		// fallback values
		$ping_test_passed = False;
		$username_test_passed = False;
		//$callstr_test_passed = False;
		
		//&& ($this->get_isset_pref('userid'))
		//&& ($this->is_logged_in($this->get_pref_value('userid')) == True))

		// ping test
		if (($this->get_isset_arg('mailsvr_stream') == True)
		&& ((string)$this->get_arg_value('mailsvr_stream') != '')
		&& ($this->phpgw_ping() == True))
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: is_logged_in: mailsvr stream exists and passed ping test'.'<br>';}
			$ping_test_passed = True;
		}
		else
		{
			if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: LEAVING, ping test failed and/or mailsvr_stream not set'.'<br>';}
			return False;
		}
		
		// username match
		if (($this->get_isset_pref('userid'))
		&& ($this->get_isset_arg('mailsvr_account_username')))
		{
			$pref_value_userid = $this->get_pref_value('userid');
			$mailsvr_account_username = $this->get_arg_value('mailsvr_account_username');
			if ($this->debug_logins > 1) { echo 'mail_msg: is_logged_in: comparing ($this->get_pref_value(userid) == this->get_arg_value(mailsvr_account_username)) ; ['.$pref_value_userid.']=['.$mailsvr_account_username.']'.'<br>';}
			// ALSO verify the username we are logged in as, IF a compare_account_username was passed as an arg
			if ($pref_value_userid == $this->get_arg_value('mailsvr_account_username'))
			{
				if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: username match passed'.'<br>';}
				$username_test_passed = True;
			}
			else
			{
				// stream is open but username does not match
				if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: LEAVING, username match failed'.'<br>';}
				return False;
			}
		}
		else
		{
			if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: LEAVING, returning False, pref userid and/or arg mailsvr_account_username NOT SET'.'<br>';}
			return False;
		}
		
		if (($ping_test_passed)
		&& ($username_test_passed))
		{
			if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: LEAVING, returning True, passed ping_test and mailsvr_account_username test'.'<br>';}
			return True;
		}
		else
		{
			if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: LEAVING, returning False, failed ping_test and/or mailsvr_account_username test'.'<br>';}
			return False;
		}
	}

	
	// ----  BEGIN request from Mailserver / Initialize This Mail Session  -----
	function begin_request($args_array)
	{
		if ($this->debug_logins > 0) { echo '<br>mail_msg: begin_request: ENTERING'.'<br>';}
		if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: local var $this->reuse_existing_obj=['.serialize($this->reuse_existing_obj).']<br>'; }
		if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: feed var args_array[] dump:<pre>'; print_r($args_array); echo '</pre>'; }
		
		// ====  Already Logged In / Reuse Existing ?  =====
		// IF RE-USING YOU BETTER FEED THE DESIRED FOLDER IN "$args_array['folder']"
		// or better yet: IF RE-USING YOU BETTER FEED THE DESIRED FOLDER IN "$args_array['fldball']['folder'] " or ['msgball']['folder']
		// IF RE-USING YOU BETTER MAKE SURE THE CORRECT ACCTNUM IS SET via "get_acctnum"/"set_acctnum"
		if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: get acctnum from feed args if possible<br>'; }
		$found_acctnum = False;
		while(list($key,$value) = each($args_array))
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: (acctnum search) this loop feed arg : ['.$key.'] => ['.serialize($args_array[$key]).'] <br>'; }
			// try to find feed acctnum value
			if ($key == 'fldball')
			{
				$fldball = $args_array[$key];
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: (acctnum search) feed args passed in $fldball[] : '.serialize($fldball).'<br>'; }
				$acctnum = (int)$fldball['acctnum'];
				
				// SET OUR ACCTNUM ACCORDING TO FEED ARGS
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: (acctnum search) SETTING ACCTNUM from fldball : ['.$acctnum.']<br>'; }
				$this->set_acctnum($acctnum);
				$found_acctnum = True;
				break;
			}
			elseif ($key == 'msgball')
			{
				$msgball = $args_array[$key];
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: (acctnum search) feed args passed in $msgball[] : '.serialize($msgball).'<br>'; }
				$acctnum = (int)$msgball['acctnum'];
				// SET OUR ACCTNUM ACCORDING TO FEED ARGS
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: (acctnum search) SETTING ACCTNUM from msgball : ['.$acctnum.']<br>'; }
				$this->set_acctnum($acctnum);
				$found_acctnum = True;
				break;
			}
			elseif ($key == 'acctnum')
			{
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: (acctnum search) feed args passed in "acctnum" : '.serialize($args_array[$key]).'<br>'; }
				$acctnum = (int)$args_array[$key];
				// SET OUR ACCTNUM ACCORDING TO FEED ARGS
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: (acctnum search) SETTING ACCTNUM from "acctnum" feed args : ['.$acctnum.']<br>'; }
				$this->set_acctnum($acctnum);
				$found_acctnum = True;
				break;
			}
		}
		if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: (acctnum search) locate acctnum in feed args $found_acctnum result ['.serialize($found_acctnum).'] <br>'; }
		
		// grab GPC values, only pass an acctnumm to that function if we already found it
		if ($found_acctnum == True)
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: grab_class_args_gpc is being called WITH already found acctnum: ('.serialize($acctnum).')<br>'; }
			$this->grab_class_args_gpc($acctnum);
		}
		else
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: grab_class_args_gpc is being called with NO acctnum yet having been found<br>'; }
			$this->grab_class_args_gpc();
		}
		if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: POST "grab_class_args_gpc": $this->get_all_args() dump <pre>';  print_r($this->get_all_args()); echo '</pre>'; }
		// grab_class_args_gpc will look for an acctnum in GPC values if one is not yet found
		// grab_class_args_gpc will ASSIGN A DEFAULT acctnum if NONE is foud anywhere
		// so by now, WE HAVE AN ACCT NUM
		if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: POST "grab_class_args_gpc": $this->get_acctnum() returns: '.serialize($this->get_acctnum()).'<br>'; }
		
		/*
		// disable this for the moment, will re-enable it later
		// attempt to reuse an existing stream
		if (($this->reuse_existing_obj == True)
		&& ($this->is_logged_in() == True))
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: attempt to reuse existing login'.'<br>'; }
			// we're already logged in, now...
			if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: $this->get_all_args() (dump BEFORE we change anything)<pre>';  print_r($this->get_all_args()); echo '</pre>'; }
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: override any existing re-fill $this->get_arg_value(args) with feed var args_array'.'<br>'; }
			while(list($key,$value) = each($args_array))
			{
				// "do_login" is never included as a class arg, it should only be specified here
				// and since we're already logged in, it's irrelevant here
				if (stristr($key, 'do_login') == False)
				{
					// put the raw data (value) for this particular arg into a local var
					$new_arg_value = $args_array[$key];
					// replace the previously existing class arg with this
					if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: feed_args loading into class args ('.$key.', '.$new_arg_value.');<br>'; }
					// ONLY BECAUSE we are already logged in, we can call prep_folder_in, which calls "folder_lookup" which needs an active login
					// AND since the folder arg is *always* prep'd out for transit over the ether
					// it must be pred'd in here, if we were not re-using existing, this would happen below anyway, after the login occured
					if ($key == 'fldball')
					{
						$fldball = $args_array[$key];
						if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: feed args passed in $fldball[] : '.serialize($fldball).'<br>'; }
						$preped_folder = $this->prep_folder_in($fldball['folder']);
						if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: fldball folder prep-ed in, b4=['.$fldball['folder'].'], after=['.$preped_folder.']<br>'; }
						$fldball['folder'] = $preped_folder;
						// SET GENERIC FOLDER VALUE FOR BACKWARDS COMPAT
						if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: set generic "folder" arg from the $fldball[folder] prep value ['.$preped_folder.']<br>'; }
						$this->set_arg_value('folder', $preped_folder);
						$new_arg_value = $fldball;
					}
					elseif ($key == 'msgball')
					{
						$msgball = $args_array[$key];
						if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: feed args passed in $msgball[] : '.serialize($msgball).'<br>'; }
						$preped_folder = $this->prep_folder_in($msgball['folder']);
						if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: msgball folder prep-ed in, b4=['.$msgball['folder'].'], after=['.$preped_folder.']<br>'; }
						$msgball['folder'] = $preped_folder;
						// SET GENERIC FOLDER VALUE FOR BACKWARDS COMPAT
						if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: set generic "folder" arg from the $msgball[folder] prep value ['.$preped_folder.']<br>'; }
						$this->set_arg_value('folder', $preped_folder);
						$new_arg_value = $msgball;
					}
					if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $this->set_arg_value('.$key.', '.$new_arg_value.');<br>'; }
					$this->set_arg_value($key, $new_arg_value);
				}
			}
			if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: $this->get_all_args() dump (AFTER re-filling with feed data)<pre>';  print_r($this->get_all_args()); echo '</pre>'; }
			
			// pre-fetch somw vars
			$reopen_mailsvr_callstr = $this->get_arg_value('mailsvr_callstr');
			$reopen_mailsvr_stream = $this->get_arg_value('mailsvr_stream');
			
			// do we need to switch to a different folder ?
			$current_folder = $this->get_arg_value('folder');
			$desired_folder = $this->prep_folder_in($args_array['folder']);
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $this->get_arg_value(folder) = ['.$current_folder.'] compare to $this->prep_folder_in($args_array[folder] = ['.$desired_folder.'] (the latter was just "prepped in"<br>'; }
			if ($current_folder != $desired_folder)
			{
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: already loggedin but need to change (reopen) folder from ['.$current_folder.'] to this ['.$desired_folder.']<br>';}
				// switch to the desired folder now that we are sure we have it's official name
				$did_reopen = $this->a[$this->acctnum]['dcom']->reopen($reopen_mailsvr_stream, $reopen_mailsvr_callstr.$desired_folder, '');
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: already loggedin but reopening, reopen returns: '.serialize($did_reopen).'<br>';}
				// error check
				$ok_to_exit = $did_reopen;
				if ($did_reopen == True)
				{
					if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: did reopen, calling $this->set_arg_value("folder", $desired_folder) desired is now the current folder ['.serialize($desired_folder).']<br>';}
					$this->set_arg_value('folder', $desired_folder);
				}
			}
			else
			{
				// we know we are logged in, we know we refilled args, and we did not need to change folders, so...
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: already logged in to the desired folder, no reopen necessary<br>';}
				$ok_to_exit = True;
			}
			// if we get to here, we are going OK
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: "re-use existing" has been tried, $ok_to_exit = ['.serialize($ok_to_exit).']<br>';}
			if ($ok_to_exit == True)
			{
				if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING, "re-use existing" succeeded, returning the mailsvr_stream'.serialize($this->get_arg_value('mailsvr_stream')).']<br>';}
				return $this->get_arg_value('mailsvr_stream');
			}
			else
			{
				if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: *NOT* Leaving! "re-use existing" Failed, change folder didnot work, or some other error... continue on with "begin request"<br>';}
			}
		}
		*/
		
		// ===  we are here ONLY if creating NO OBJECT mail_msg exists  =====
		// === or we are Not Already Logged In?  =====
		// === or we *something* did not work during "re-use existing" attempt  =====
		// === OR we are not attempting to re-use an existing mail_msg object  ====
		if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: NOT reusing an established logged-in stream-object, will create new'.'<br>'; }
		
		// ----  Things To Be Done Whether You Login Or Not  -----
		// Grab GPC vars, they'll go into the "args" data
		// already did this above
		//if ( ($this->get_isset_arg('already_grab_class_args_gpc'))
		//&& ((string)$this->get_arg_value('already_grab_class_args_gpc') != '') )
		//{
		//	// somewhere, there's already been a call to grab_class_args_gpc(), do NOT re-run
		//	if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: "already_grab_class_args_gpc" is set, do not re-grab<br>'; }
		//	if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: "already_grab_class_args_gpc" pre-existing $this->get_all_args() dump:<pre>'; print_r($this->get_all_args()) ; echo '</pre>';}
		//}
		//else
		//{
		//	if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: "already_grab_class_args_gpc" is NOT set, call grab_class_args_gpc() now<br>'; }
		//	$this->grab_class_args_gpc();
		//}
		
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: PRE create_email_preferences GLOBALS[phpgw_info][user][preferences][email] dump:<pre>'; print_r($GLOBALS['phpgw_info']['user']['preferences']['email']) ; echo '</pre>';}
		// ----  Obtain Preferences Data  ----
		$tmp_prefs = array();
		// obtain the preferences from the database
		$tmp_prefs = $GLOBALS['phpgw']->preferences->create_email_preferences();
		// fill $GLOBALS['phpgw_info']['user']['preferences'] with the data for backwards compatibility (we don't use that)
		$GLOBALS['phpgw_info']['user']['preferences'] = $tmp_prefs;
		// for our use, put prefs in a class var to be accessed thru OOP-style access calls in mail_msg_wrapper
		$this->set_pref_array($tmp_prefs['email']);
		// clear the temp var
		$tmp_prefs = array();
		
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: POST create_email_preferences GLOBALS[phpgw_info][user][preferences][email] dump:<pre>'; print_r($GLOBALS['phpgw_info']['user']['preferences']['email']) ; echo '</pre>';}
		if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: POST create_email_preferences $this->get_all_prefs() dump:<pre>'; print_r($this->get_all_prefs()) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: POST create_email_preferences direct access dump of $this->a  :<pre>'; print_r($this->a) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: preferences->create_email_preferences called, GLOBALS[phpgw_info][user][preferences] dump:<pre>'; print_r($GLOBALS['phpgw_info']['user']['preferences']) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: preferences->create_email_preferences called, GLOBALS[phpgw_info][user] dump:<pre>'; print_r($GLOBALS['phpgw_info']['user']) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: preferences->create_email_preferences called, GLOBALS[phpgw_info] dump:<pre>'; print_r($GLOBALS['phpgw_info']) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: preferences->create_email_preferences called, GLOBALS[phpgw] dump:<pre>'; print_r($GLOBALS['phpgw']) ; echo '</pre>';}
		
		// ---- SET important class vars  ----
		$this->att_files_dir = $GLOBALS['phpgw_info']['server']['temp_dir'].SEP.$GLOBALS['phpgw_info']['user']['sessionid'];
		
		// and.or get some vars we will use later in this function
		$mailsvr_callstr = $this->get_arg_value('mailsvr_callstr');
		if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $mailsvr_callstr '.$mailsvr_callstr.'<br>'; }
		
		// set class var "$this->cache_mailsvr_data" based on prefs info
		// FIXME: why have this in 2 places, just keep it in prefs (todo)
		if ((isset($this->cache_mailsvr_data_disabled))
		&& ($this->cache_mailsvr_data_disabled == True))
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: folder cache DISABLED, $this->cache_mailsvr_data_disabled = '.serialize($this->cache_mailsvr_data_disabled).'<br>'; }
			$this->cache_mailsvr_data = False;
		}
		elseif (($this->get_isset_pref('cache_data'))
		&& ($this->get_pref_value('cache_data') != ''))
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: folder cache is enabled in user prefs'.'<br>'; }
			$this->cache_mailsvr_data = True;
		}
		else
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: folder cache is NOT enabled in user prefs'.'<br>'; }
			$this->cache_mailsvr_data = False;
		}
		
		// ----  Should We Login  -----
		if (!isset($args_array['do_login']))
		{
			$args_array['do_login'] = False;
		}
		
		/*
		// ----  Are We In Newsmode Or Not  -----
		// FIXME: !!! this needs better handling
		if ((isset($args_array['newsmode']))
		&& (($args_array['newsmode'] == True) || ($args_array['newsmode'] == "on")))
		{
			$args_array['newsmode'] = True;
			$this->set_arg_value('newsmode', True);
			$this->set_pref_value('mail_server_type', 'nntp');
		}
		else
		{
			$args_array['newsmode'] = False;
			$this->set_arg_value('newsmode', False);
		}
		*/
		
		// Browser Detection =FUTURE=
		// 0 = NO css ; 1 = CSS supported ; 2 = text only
		// currently not implemented, use default 0 (NO CSS support in browser)
		$this->browser = 0;
		//$this->browser = 1;
		
		// ----  Things Specific To Loging In, and Actually Logging In  -----
		// $args_array['folder'] gets prep_folder_in and then is stored in class var $this->get_arg_value('folder')
		if ($args_array['do_login'] == True)
		{
			//  ----  Get Email Password
			if ($this->get_isset_pref('passwd') == False)
			{
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: this->a[$this->acctnum][prefs][passwd] NOT set, fallback to $GLOBALS[phpgw_info][user][passwd]'.'<br>'; }
				// DO NOT alter the password and put that altered password BACK into the preferences array
				// why not? used to have a reason, but that was obviated, no reason at the moment
				//$this->set_pref_value('passwd',$GLOBALS['phpgw_info']['user']['passwd']);
				//$this->a[$this->acctnum]['prefs']['passwd'] = $GLOBALS['phpgw_info']['user']['passwd'];
				$pass = $GLOBALS['phpgw_info']['user']['passwd'];
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: pass grabbed from GLOBALS[phpgw_info][user][passwd] = '.htmlspecialchars(serialize($pass)).'<br>'; }
			}
			else
			{
				// DO NOT alter the password and do NOT put that altered password BACK into the preferences array
				// keep the one in GLOBALS in encrypted form if possible ????
				//$this->a[$this->acctnum]['prefs']['passwd'] = $this->decrypt_email_passwd($this->a[$this->acctnum]['prefs']['passwd']);
				$pass = $this->decrypt_email_passwd($this->get_pref_value('passwd'));
				//$this->set_pref_value('passwd', $pass);
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: pass decoded from prefs: '.htmlspecialchars(serialize($this->get_pref_value('passwd'))).'<br>'; }
			}
			// ----  ISSET CHECK for userid and passwd to avoid garbage logins  ----
			if ( $this->get_isset_pref('userid')
			&& ($this->get_pref_value('userid') != '')
			&& (isset($pass))
			&& ($pass != '') )
			{
				$user = $this->get_pref_value('userid');
				// we set pass up above, we no longer alter the pass and put it back intoi the prefs array
				//$pass = $this->get_pref_value('passwd');
			}
			else
			{
				// problem - invalid or nonexistant info for userid and/or passwd
				//if ($this->debug_logins > 0) {
					echo 'mail_msg: begin_request: ERROR: userid or passwd empty'."<br>\r\n"
						.' * * $this->get_pref_value(userid) = '
							.$this->get_pref_value('userid')."<br>\r\n"
						.' * * if the userid is filled, then it must be the password that is missing'."<br>\r\n"
						.' * * tell your admin if a) you have a custom email password or not when reporting this error'."<br>\r\n";
				//}
				if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING with ERROR: userid or passwd empty<br>';}
				return False;
			}
			
			// ----  Create email server Data Communication Class  ----
			// 1st arg to the constructor is the "mail_server_type"
			// we feed from here because when there are multiple mail_msg objects
			// we need to make sure we load the appropriate type dcom class
			// which that class may not know which accounts prefs to use, so tell it here
			
			//$this->a[$this->acctnum]['dcom'] = CreateObject("email.mail_dcom",$this->get_pref_value('mail_server_type'));
			
			// ----  php3 compatibility  ----
			// apparently php3 wants you to create the object first, then put it in the array
			$this_server_type = $this->get_pref_value('mail_server_type');
			$this_dcom = CreateObject("email.mail_dcom", $this_server_type);
			// ok, now put that object into the array
			$this->a[$this->acctnum]['dcom'] = $this_dcom;
			
			// initialize the dcom class variables
			$this->a[$this->acctnum]['dcom']->mail_dcom_base();
			
			// ----  there are 2 settings from this mail_msg object we need to pass down to the child dcom object:  ----
			// (1)  Do We Use UTF7 encoding/decoding of folder names
			if (($this->get_isset_pref('enable_utf7'))
			&& ($this->get_pref_value('enable_utf7')))
			{
				$this->a[$this->acctnum]['dcom']->enable_utf7 = True;
			}
			// (2)  Do We Force use of msg UID's
			if ($this->force_msg_uids == True)
			{
				$this->a[$this->acctnum]['dcom']->force_msg_uids = True;
			}
			
			set_time_limit(60);
			// login to INBOX because we know that always(?) should exist on an imap server and pop server
			// after we are logged in we can get additional info that will lead us to the desired folder (if not INBOX)
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: about to call dcom->open: this->a['.$this->acctnum.'][dcom]->open('.$mailsvr_callstr."INBOX".', '.$user.', '.$pass.', )'.'<br>'; }
			$mailsvr_stream = $this->a[$this->acctnum]['dcom']->open($mailsvr_callstr."INBOX", $user, $pass, '');
			$pass = '';
			set_time_limit(0);
			
			if ($this->debug_logins > 1) {  echo 'mail_msg: begin_request: open returns $mailsvr_stream = ['.serialize($mailsvr_stream).']<br>'; }
			
			// Logged In Success or Faliure check
			if ( (!isset($mailsvr_stream))
			|| ($mailsvr_stream == '') )
			{
				// set the "mailsvr_stream" to blank so all will know the login failed
				$this->set_arg_value('mailsvr_stream', '');
				if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING with ERROR: failed to open mailsvr_stream : '.$mailsvr_stream.'<br>';}
				// we return false, but SHOULD WE ERROR EXIT HERE?
				return False;
			}
			
			// SUCCESS - we are logged in to the server, at least we got to "INBOX"
			$this->set_arg_value('mailsvr_stream', $mailsvr_stream);
			$this->set_arg_value('mailsvr_account_username', $user);
			// BUT if "folder" != "INBOX" we still have to "reopen" the stream to that "folder"
			
			// ----  Get additional Data now that we are logged in to the mail server  ----
			// namespace is often obtained by directly querying the mailsvr
			$mailsvr_namespace = $this->get_arg_value('mailsvr_namespace');
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $mailsvr_namespace: '.serialize($mailsvr_namespace).'<br>'; }
			$mailsvr_delimiter = $this->get_arg_value('mailsvr_delimiter');
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $mailsvr_delimiter: '.serialize($mailsvr_delimiter).'<br>'; }
			
			//  ----  Get Folder Value  ----
			// ORDER OF PREFERENCE for pre-processed "folder" input arg
			// (1) $args_array, IF FILLED, overrides any previous data or any other data source, look for these:
			//	$args_array['msgball']['folder']
			//	$args_array['fldball']['folder']
			//	$args_array['folder']
			// (2) GPC ['msgball']['folder']
			// (3) GPC ['fldball']['folder']
			// (4) if "folder" arg it is already set, (probably during the reuse attempt, probably obtained from $args_array alreadt) then use that
			// (5) default to blank string, which "prep_folder_in()" changes to defaultg value INBOX
			
			// note: it's OK to send blank string to "prep_folder_in", because it will return a default value of "INBOX"
			if ((isset($args_array['folder']))
			&& ($args_array['folder'] != ''))
			{
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $input_folder_arg chooses $args_array[folder] ('.$args_array['folder'].') over any existing "folder" arg<br>'; }
				$input_folder_arg = $args_array['folder'];
			}
			elseif ($this->get_isset_arg('["msgball"]["folder"]'))
			{
				$input_folder_arg = $this->get_arg_value('["msgball"]["folder"]');
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $input_folder_arg chooses $this->get_arg_value(["msgball"]["folder"]): ['.$input_folder_arg.']<br>'; }
			}
			elseif ($this->get_isset_arg('["fldball"]["folder"]'))
			{
				$input_folder_arg = $this->get_arg_value('["fldball"]["folder"]');
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $input_folder_arg chooses $this->get_arg_value(["fldball"]["folder"]): ['.$input_folder_arg.']<br>'; }
			}
			elseif ($this->get_isset_arg('delmov_list'))
			{
				$this_delmov_list = $this->get_arg_value('delmov_list');
				$input_folder_arg = $this_delmov_list[0]['folder'];
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $input_folder_arg chooses $this_delmov_list[0][folder]: ['.$input_folder_arg.']<br>'; }
			}
			else
			{
				if (($this->get_isset_arg('folder'))
				&& ((string)trim($this->get_arg_value('folder')) != ''))
				{
					$input_folder_arg = $this->get_arg_value('folder');
				}
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $input_folder_arg *might* chooses $this->get_arg_value(folder): ['.serialize($input_folder_arg).']<br>'; }
				
				$input_folder_arg = (string)$input_folder_arg;
				$input_folder_arg = trim($input_folder_arg);
				if ($input_folder_arg != '')
				{
					if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $this->get_arg_value(folder) passes test, so $input_folder_arg chooses $this->get_arg_value(folder): ['.serialize($input_folder_arg).']<br>'; }
				}
				else
				{
					if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: no folder value found, so $input_folder_arg takes an empty string<br>'; }
					$input_folder_arg = '';
				}
			}
			// ---- Prep the Folder Name (remove encodings, verify it's long name (with namespace)
			// folder prepping does a lookup which requires a folder list which *usually* (unless caching) requires a login
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: about to issue $processed_folder_arg = $this->prep_folder_in('.$input_folder_arg.')<br>'; }
			$processed_folder_arg = $this->prep_folder_in($input_folder_arg);
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: $processed_folder_arg value: ['.$processed_folder_arg.']<br>'; }
			
			// ---- Switch To Desired Folder If Necessary  ----
			if ($processed_folder_arg == 'INBOX')
			{
				// NO need to switch to another folder
				// put this $processed_folder_arg in arg "folder", replacing any unprocessed value that may have been there
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: NO need to switch folders, about to issue: $this->set_arg_value("folder", '.$processed_folder_arg.')<br>'; }
				$this->set_arg_value('folder', $processed_folder_arg);
			}
			else
			{
				// switch to the desired folder now that we are sure we have it's official name
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: need to switch folders (reopen) from INBOX to $processed_folder_arg: '.$processed_folder_arg.'<br>';}
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: about to issue: $this->a['.$this->acctnum.'][dcom]->reopen('.$mailsvr_stream.', '.$mailsvr_callstr.$processed_folder_arg,', )'.'<br>';}
				$did_reopen = $this->a[$this->acctnum]['dcom']->reopen($mailsvr_stream, $mailsvr_callstr.$processed_folder_arg, '');
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: reopen returns: '.serialize($did_reopen).'<br>';}
				// error check
				if ($did_reopen == False)
				{
					if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING with re-open ERROR, closing stream, FAILED to reopen (change folders) $mailsvr_stream ['.$mailsvr_stream.'] INBOX to ['.$mailsvr_callstr.$processed_folder_arg.'<br>';}
					// log out since we could not reopen, something must have gone wrong
					$this->end_request();
					return False;
				}
				else
				{
					if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: Successful switch folders (reopen) from (default initial folder) INBOX to ['.$processed_folder_arg.']<br>';}
					// put this $processed_folder_arg in arg "folder", since we were able to successfully switch folders
					if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: switched folders (via reopen), about to issue: $this->set_arg_value("folder", '.$processed_folder_arg.')<br>'; }
					$this->set_arg_value('folder', $processed_folder_arg);
				}
			}
			// ----  Process "sort" "order" "start" and "msgnum" GPC args (if any) passed to the script  -----
			// these args are so fundamental, they get stored in their own class vars
			// no longer referenced as args after this
			// requires args saved to $this->a[$this->acctnum]['args'], only relevant if you login
			$this->fill_sort_order_start_msgnum();
			
			// now we have folder, sort and order, make a URI for auto-refresh use
			// we can NOT put "start" in auto refresh or user may not see the 1st index page on refresh
			$this->index_refresh_uri = 
				$this->index_menuaction
				.'&folder='.$this->prep_folder_out('')
				.'&sort='.$this->get_arg_value('sort')
				.'&order='.$this->get_arg_value('order');
			
			if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: about to leave, direct access dump of $this->a  :<pre>'; print_r($this->a) ; echo '</pre>';}
			if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING, success'.'<br>';}
			// returning this is vestigal, not really necessary, but do it anyway
			// it's importance is that it returns something other then "False" on success
			return $this->get_arg_value('mailsvr_stream');
		}
	}
 
	function end_request($args_array='')
	{
		// args array currently not used
		if ($this->debug_logins > 0) { echo 'mail_msg: end_request: ENTERING'.'<br>';}
		if ($this->debug_logins > 2) { echo 'mail_msg: end_request: direct access info dump of $this->a  :<pre>'; print_r($this->a) ; echo '</pre>';}
		if (($this->get_isset_arg('mailsvr_stream') == True)
		&& ($this->get_arg_value('mailsvr_stream') != ''))
		{
			if ($this->debug_logins > 0) { echo 'mail_msg: end_request: stream exists, logging out'.'<br>';}
			$this->a[$this->acctnum]['dcom']->close($this->get_arg_value('mailsvr_stream'));
			$this->set_arg_value('mailsvr_stream', '');
		}
		if ($this->debug_logins > 0) { echo 'mail_msg: end_request: LEAVING'.'<br>';}
	}
		
	function login_error($called_from='')
	{
		if ($called_from == '')
		{
			$called_from = lang('this data not supplied.');
		}
		
		$imap_err = imap_last_error();
		if ($imap_err == '')
		{
			$error_report = lang('No Error Returned From Server');
		}
		else
		{
			$error_report = $imap_err;
		}
		// this should be templated
		echo "<p><center><b>"
		  . lang("There was an error trying to connect to your mail server.<br>Please, check your username and password, or contact your admin.")."<br> \r\n"
		  ."source: email class.mail_msg_base.inc.php"."<br> \r\n"
		  ."called from: ".$called_from."<br> \r\n"
		  ."imap_last_error: ".$error_report."<br> \r\n"
		  . "</b></center></p>";
		$GLOBALS['phpgw']->common->phpgw_exit(True);
	}


  // ----  Various Functions Used To Support Email   -----
	function prep_folder_in($feed_folder)
	{
		// ----  Ensure a Folder Variable exists, if not, set to INBOX (typical practice)   -----
		if (!$feed_folder)
		{
			return 'INBOX';
			// note: return auto-exits this function
		}
		
		// FILESYSTEM imap server "dot_slash" CHECK
		if ((strstr(urldecode($feed_folder), './'))
		&& 	((($this->get_pref_value('imap_server_type') == 'UW-Maildir')
			|| ($this->get_pref_value('imap_server_type') == 'UWash'))) )
		{
			// UWash and UW-Maildir IMAP servers are filesystem based,
			// so anything like "./" or "../" *might* make the server list files and directories
			// somewhere in the parent directory of the users mail directory
			// this could be undesirable a
			// (a) IMAP servers really should not do this unless specifically enabled and/or told to do so, and
			// (b) many would consider this a security risk to display filesystem data outside the users directory
			return 'INBOX';
			// note: return auto-exits this function
		}
		
		// an incoming folder name has generally been urlencoded before it gets here
		// particularly if the folder has spaces and is included in the URI, then a + will be where the speces are
		$feed_folder = urldecode($feed_folder);
		return $this->folder_lookup('', $feed_folder);
	}

	function prep_folder_out($feed_folder='')
	{
		if ($feed_folder == '')
		{
			// this allows us to call this with no args and the current folder is "prep'ed"
			// foldnames with spaces and other URL unfriendly chars are encoded here
			// must be decoded on the next input (script session) to undo what we do here
			$feed_folder = $this->get_arg_value('folder');
		}
		return urlencode($feed_folder);
	}

	/* * * * * * * * * * *
	  *  ensure_no_brackets
	  * used for removing the bracketed server call string from a full IMAP folder name string
	  *  Example: ensure_no_brackets('{mail.yourserver.com:143}INBOX') = 'INBOX'
	  * * * * * * *  * * * */
	function ensure_no_brackets($feed_str='')
	{
		if ((strstr($feed_str,'{') == False) && (strstr($feed_str,'}') == False))
		{
			// no brackets to remove
			$no_brackets = $feed_str;
		}
		else
		{
			// get everything to the right of the bracket "}", INCLUDES the bracket itself
			$no_brackets = strstr($feed_str,'}');
			// get rid of that 'needle' "}"
			$no_brackets = substr($no_brackets, 1);
		}
		return $no_brackets;
	}

	/*!
	@function  get_mailsvr_callstr
	will generate the appropriate string to access a mail server of type
	pop3, pop3s, imap, imaps
	the returned string is the server call string from beginning bracker "{" to ending bracket "}"
	the returned string is the server call string from beginning bracker "{" to ending bracket "}"
	Example:  {mail.yourserver.com:143}
	@access PRIVATE  (public access is object->get_arg_value('mailsvr_namespace')
	PRIVATE
	*/
	function get_mailsvr_callstr()
	{
		if (stristr($this->skip_args_special_handlers, 'get_mailsvr_callstr'))
		{
			$fake_return = '{brick.earthlink.net:143}';
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_callstr: debug SKIP, $fake_return: '.serialize($fake_return).' <br>'; }
			return $fake_return;
		}
		
		if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_callstr: ENTERING <br>'; }
		// do we have "level one cache" class var data that we can use?
		$class_cached_mailsvr_callstr = $this->_direct_access_arg_value('mailsvr_callstr');
		if ($class_cached_mailsvr_callstr != '')
		{
			// return the "level one cache" class var data
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_callstr: LEAVING, returned class var cached data: '.serialize($class_cached_mailsvr_callstr).'<br>'; }
			return $class_cached_mailsvr_callstr;
		}
		
		// what's the name or IP of the mail server
		$mail_server = $this->get_pref_value('mail_server');
				
		// determine the Mail Server Call String
		// construct the email server call string from the opening bracket "{"  to the closing bracket  "}"
		switch($this->get_pref_value('mail_server_type'))
		{
			case 'imaps':	// IMAP over SSL
				$extra = '/imap/ssl/novalidate-cert';
				//$extra = '/imap/ssl/novalidate-cert';
				break;
			case 'pop3s':	// POP3 over SSL
				$extra = '/pop3/ssl/novalidate-cert';
				//$extra = '/pop3/ssl';
				break;
			case 'pop3':	// POP3 normal connection, No SSL
				$extra = '/pop3';
				break;
			case 'imap':	// IMAP normal connection, No SSL
			default:			// UNKNOW SERVER type
				$extra = '';
				break;
		}
		$server_call = '{' .$mail_server .':' .$this->get_pref_value('mail_port') . $extra . '}';
			
		// cache the result
		$this->set_arg_value('mailsvr_callstr', $server_call);
		if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_callstr: LEAVING, returning $server_call: '.serialize($server_call).'<br>'; }
		return $server_call;
	}

	/*!
	@function  get_mailsvr_namespace
	will generate the appropriate namespace (aka filter) string to access an imap mail server
	Example: {mail.servyou.com:143}INBOX    where INBOX is the namespace
	for more info see: see http://www.rfc-editor.org/rfc/rfc2342.txt
	@access PRIVATE  (public access is object->get_arg_value('mailsvr_namespace')
	PRIVATE
	*/
	function get_mailsvr_namespace()
	{
		if (stristr($this->skip_args_special_handlers, 'get_mailsvr_namespace'))
		{
			$fake_return = '';
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_namespace: debug SKIP, $fake_return: '.serialize($fake_return).' <br>'; }
			return $fake_return;
		}
		
		if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_namespace: ENTERING <br>'; }
		// UWash patched for Maildir style: $Maildir.Junque ?????
		// Cyrus and Courier style =" INBOX"
		// UWash style: "mail"

		// do we have cached data that we can use?
		$class_cached_mailsvr_namespace = $this->_direct_access_arg_value('mailsvr_namespace');
		if ($class_cached_mailsvr_namespace != '')
		{
			// return the cached data
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_namespace: LEAVING, returned class var cached data: '.serialize($class_cached_mailsvr_namespace).'<br>'; }
			return $class_cached_mailsvr_namespace;
		}
		
		
		// -----------
		// TRY CACHED DATA FROM PREFS DB
		// -----------
		$cached_data = '';
		$my_function_name = 'get_mailsvr_namespace';
		$cached_data = $this->get_cached_data($my_function_name,'string');
		// if there's no data we'll get back a FALSE
		if ($cached_data)
		{
			// cache the result to a class var
			$this->set_arg_value('mailsvr_namespace', $cached_data);
			return $cached_data;
		}

		// no cached data of any kind we can use ...
		
		// we *may* need this data later
		$mailsvr_stream = $this->get_arg_value('mailsvr_stream');
		$server_str = $this->get_pref_value('mailsvr_callstr');
		
		if (($this->get_pref_value('imap_server_type') == 'UW-Maildir')
		|| ($this->get_pref_value('imap_server_type') == 'UWash'))
		{
			if (($this->get_isset_pref('mail_folder'))
			&& (trim($this->get_pref_value('mail_folder')) != ''))
			{
				// if the user fills this option correctly, this should yield an unqualified foldername which
				// UWash should qualify (juat like any unix command line "cd" command) with the
				// appropriate $HOME variable (I THINK) ...
				// DO I NEED to add the "~" here too?
				$name_space = trim($this->get_pref_value('mail_folder'));
			}
			else
			{
				// in this case, the namespace is blank, indicating the user's $HOME is where the MBOX files are
				// or in the case of UW-Maildir, where the maildir files are
				// thus we can not have <blank><slash> preceeding a folder name
				// note that we *may* have <tilde><slash> preceeding a folder name, SO:
				// default value for this UWash server, $HOME = tilde (~)
				$name_space = '~';
			}
		}
		/*
		elseif ($this->get_pref_value('imap_server_type') == 'Cyrus')
		// ALSO works for Courier IMAP
		{
			$name_space = 'INBOX';
		}
		*/
		// ------- Dynamically Discover User's Private Namespace ---------
		// existing "$this->get_arg_value('mailsvr_stream')" means we are logged in and can querey the server
		elseif ((isset($mailsvr_stream) == True)
		&& ($mailsvr_stream != ''))
		{
			// a LIST querey with "%" returns the namespace of the current reference
			// in format {SERVER_NAME:PORT}NAMESPACE
			// also, it MAY (needs testing) return all available namespaces
			// however this is less useful if the IMAP server makes available shared folders and/or usenet groups
			// in addition to the users private mailboxes
			// see http://www.faqs.org/rfcs/rfc2060.html  section 6.3.8 (which is not entirely clear on this)
			// FIXME: abstract this class dcom call in mail_msg_wrappers
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_mailsvr_namespace: issuing: $this->a['.$this->acctnum.'][dcom]->listmailbox('.$mailsvr_stream.', '.$server_str.', %)'.'<br>'; }
			$name_space = $this->a[$this->acctnum]['dcom']->listmailbox($mailsvr_stream, $server_str, '%');
			if ($this->debug_args_special_handlers > 2) { echo 'mail_msg: get_mailsvr_namespace: raw $name_space dump<pre>'; print_r($name_space); echo '</pre>'; }
			
			if (!$name_space)
			{
				// if the server returns nothing, just use the most common namespace, "INBOX"
				// note: "INBOX" is NOT case sensitive according to rfc2060
				$name_space = 'INBOX';
			}
			elseif (is_array($name_space))
			{
				// if the server returns an array of namespaces, the first one is usually the users personal namespace
				// tyically "INBOX", there can be any number of other, unpredictable, namespaces also
				// used for the shared folders and/or nntp access (like #ftp), but we want the users "personal"
				// namespace used for their mailbox folders here
				// most likely that the first element of the array is the users primary personal namespace
				// I'm not sure but I think it's possible to have more than one personal (i.e. not public) namespace
				// note: do not use php function "is_array()" because php3 does not have it
				// later note: i think php3 does have "is_array()"
				$processed_name_space = $this->ensure_no_brackets($name_space[0]);
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_mailsvr_namespace: ($name_space is_array) $processed_name_space = $this->ensure_no_brackets($name_space[0]) [that arg='.$name_space[0].'] returns '.serialize($processed_name_space).'<br>'; }
				// put that back in name_space var
				$name_space = $processed_name_space;
			}
			elseif (is_string($name_space))
			{
				// if the server returns a string (not likely) just get rid of the brackets
				// note: do not use is_string() because php3 does not have it ???
				$processed_name_space = $this->ensure_no_brackets($name_space);
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_mailsvr_namespace: ($name_space is string) $processed_name_space = $this->ensure_no_brackets($name_space) [that arg='.$name_space.'] returns '.serialize($processed_name_space).'<br>'; }
				// put that back in name_space var
				$name_space = $processed_name_space;
			}
			else
			{
				// something really screwed up, EDUCATED GUESS
				// note: "INBOX" is NOT case sensitive according to rfc2060
				$name_space = 'INBOX';
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_mailsvr_namespace: ($name_space is NOT string nor array) GUESSING: $name_space = '.serialize($name_space).'<br>'; }
			}
		}
		else
		{
			// GENERIC IMAP NAMESPACE
			// imap servers usually use INBOX as their namespace
			// this is supposed to be discoverablewith the NAMESPACE command
			// see http://www.rfc-editor.org/rfc/rfc2342.txt
			// however as of PHP 4.0 this is not implemented, and some IMAP servers do not cooperate with it anyway
			$name_space = 'INBOX';
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_mailsvr_namespace: no stream, so could not query server, GUESSING: $name_space = '.serialize($name_space).'<br>'; }
		}
		
		// cache the result in "level one cache" class var holder
		$this->set_arg_value('mailsvr_namespace', $name_space);
		
		// -----------
		// SAVE DATA TO PREFS DB CACHE
		// -----------
		$my_function_name = 'get_mailsvr_namespace';
		$this->set_cached_data($my_function_name,'string',$name_space);
		if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_namespace: LEAVING, returning $name_space: '.serialize($name_space).'<br>'; }
		return $name_space;
	}

	/*!
	@function  get_mailsvr_delimiter
	will generate the appropriate token that goes between the namespace and the inferior folders (subfolders)
	Example: typical imap: "INBOX.Sent"  then the "." is the delimiter
	Example: UWash imap (stock mbox)  "email/Sent"  then the "/" is the delimiter
	@access PRIVATE  (public access is object->get_arg_value('mailsvr_delimiter')
	PRIVATE
	*/
	function get_mailsvr_delimiter()
	{
		if (stristr($this->skip_args_special_handlers, 'get_mailsvr_delimiter'))
		{
			$fake_return = '/';
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_delimiter: debug SKIP, $fake_return: '.serialize($fake_return).' <br>'; }
			return $fake_return;
		}
		
		if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_delimiter: ENTERING <br>'; }
		// UWash style: "/"
		// all other imap servers *should* be "."

		// do we have cached data that we can use?
		$class_cached_mailsvr_delimiter = $this->_direct_access_arg_value('mailsvr_delimiter');
		if ($class_cached_mailsvr_delimiter != '')
		{
			// return the cached data
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_delimiter: LEAVING, returned class var cached data: '.serialize($class_cached_mailsvr_delimiter).'<br>'; }
			return $class_cached_mailsvr_delimiter;
		}
		
		if ($this->get_pref_value('imap_server_type') == 'UWash')
		{
			//$delimiter = '/';
			//$delimiter = SEP;

			// UWASH is a filesystem based thing, so the delimiter is whatever the system SEP is
			// unix = /  and win = \ (win maybe even "\\" because the backslash needs escaping???
			// currently the filesystem seterator is provided by phpgw api as constant "SEP"
			if (!SEP)
			{
				$delimiter = '/';
			}
			else
			{
				$delimiter = SEP;
			}
		}
		else
		{
			// GENERIC IMAP DELIMITER
			// imap servers usually use a "." as their delimiter
			// this is supposed to be discoverable with the NAMESPACE command
			// see http://www.rfc-editor.org/rfc/rfc2342.txt
			// however as of PHP 4.0 this is not implemented
			$delimiter = '.';
		}
		// cache the result to "level 1 cache" class arg holder var
		$this->set_arg_value('mailsvr_delimiter', $delimiter);
		if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_mailsvr_delimiter: LEAVING, returning: '.serialize($delimiter).'<br>'; }
		return $delimiter;
	}

	function get_mailsvr_supports_folders()
	{
		// Does This Mailbox Support Folders (i.e. more than just INBOX)?
		if (($this->get_pref_value('mail_server_type') == 'imap')
		|| ($this->get_pref_value('mail_server_type') == 'imaps')
		|| ($this->newsmode))
		{
			return True;
		}
		else
		{
			return False;
		}
	}
	
	/*!
	@function get_folder_long
	@abstract  will generate the long name of an imap folder name, contains NAMESPACE_DELIMITER_FOLDER string
	but NOT the {serverName:port} part.
	@param $feed_folder : string
	@return : string : the long name of an imap folder name, contains NAMESPACE_DELIMITER_FOLDER string
	@discussion  Note that syntax "{serverName:port}NAMESPACE_DELIMITER_FOLDER" is called a "fully qualified" 
	folder name here. The param $feed_folder will be compared to the folder list supplied by the server to insure 
	an accurate folder name is returned because a param $feed_folder LACKING a namespace or delimiter MUST 
	have them added in order to become a "long" folder name, and just guessing is not good enough to ensure accuracy.
	Works with supported imap servers: UW-Maildir, Cyrus, Courier, UWash
	Example (Cyrus or Courier):  INBOX.Templates
	Example (if subfolders a.k.a. "inferior folders" are enabled):  INBOX.drafts.rfc
	????   Example (UW-Maildir only): /home/James.Drafts   ????
	The above examle would suggext that UW-Maildir takes "~" as namespace and "/" as its pre-folder name delimiter, 
	which as somewhat nonstandard because it appears the rest of the folder name uses "." as the delimiter.
	@access	Public
	*/
	function get_folder_long($feed_folder='INBOX')
	{
		$feed_folder = urldecode($feed_folder);
		$folder = $this->ensure_no_brackets($feed_folder);
		if ($folder == 'INBOX')
		{
			// INBOX is (always?) a special reserved word with nothing preceeding it in long or short form
			$folder_long = 'INBOX';
		}
		else
		{
			$name_space = $this->get_arg_value('mailsvr_namespace');
			$delimiter = $this->get_arg_value('mailsvr_delimiter');
			//if (strstr($folder,"$name_space" ."$delimiter") == False)
			// "INBOX" as namespace is NOT supposed to be case sensitive
			if (stristr($folder,"$name_space" ."$delimiter") == False)
			{
				// the [namespace][delimiter] string was not present
				// CONTROVERSIAL: add the [namespace][delimiter] string
				// this will incorrectly change a shared folder name, whose name may not
				// supposed to have the [namespace][delimiter] string
				$folder_long = "$name_space" ."$delimiter" ."$folder";
			}
			else
			{
				// this folder is already in "long" format (it's namespace and delimiter already there)
				$folder_long = $folder;
			}
		}
		//echo 'get_folder_long('.$folder.')='.$folder_long.'<br>';
		return trim($folder_long);
	}
	
	/*!
	@function get_folder_short
	@abstract  will generate the SHORT name of an imap folder name, i.e. strip off {SERVER}NAMESPACE_DELIMITER
	@param $feed_folder : string
	@return : string : the "shortened" name of a given imap folder name
	@discussion  Simply, this is the folder name without the {serverName:port} nor the NAMESPACE 
	nor the DELIMITER  preceeding it. 
	Works with supported imap servers UWash, UW-Maildir, Cyrus, Courier
	(old) Example (Cyrus or Courier):  Templates
	(old) Example (Cyrus only):  drafts.rfc
	*/
	function get_folder_short($feed_folder='INBOX')
	{
		// Example: "Sent"
		// Cyrus may support  "Sent.Today"

		$feed_folder = urldecode($feed_folder);
		$folder = $this->ensure_no_brackets($feed_folder);
		if ($folder == 'INBOX')
		{
			// INBOX is (always?) a special reserved word with nothing preceeding it in long or short form
			$folder_short = 'INBOX';
		}
		else
		{
			$name_space = $this->get_arg_value('mailsvr_namespace');
			$delimiter = $this->get_arg_value('mailsvr_delimiter');
			//if (strstr($folder,"$name_space" ."$delimiter") == False)
			// "INBOX" as namespace is NOT supposed to be case sensitive
			if (stristr($folder,"$name_space" ."$delimiter") == False)
			{
				$folder_short = $folder;
			}
			else
			{
				//$folder_short = strstr($folder,$delimiter);
				$folder_short = stristr($folder,$delimiter);
				// get rid of that delimiter (it's included from the stristr above)
				$folder_short = substr($folder_short, 1);
			}
		}
		return $folder_short;
	}
	
	/*!
	@function get_folder_list
	@abstract  list of folders in a numbered array, each element has 2 properties, "folder_long" and "folder_short"
	@param $mailsvr_stream : DEPRECIATED - do not use
	@param $force_refresh : boolean, will cause any cached folder data to expire, and "fresh" data is retrieved from the mailserver
	@return : array : numbered, with each numbered element having array keys  "folder_long" and "folder_short"
	@discussion  returns a numbered array, each element has 2 properties, "folder_long" and "folder_short"
	so every available folder is in the structure in both long form [namespace][delimiter][foldername]
	and short form (does not have the [namespace][delimiter] prefix to the folder name)
	This function can cache data in 2 ways
	(1) caching as server data in the prefs DB cache department, and
	(2) in the class var $this->get_arg_value('folder_list')
	Data will be grabbed from cache when available and when allowed.
	@access PRIVATE  (public access is object->get_arg_value('folder_list')
	PRIVATE
	may call directly if you can't to manually force_refresh any cached data 
	*/
	function get_folder_list($mailsvr_stream='', $force_refresh=False)
	{
		// what acctnum is operative here, we can only get a folder list for one account at a time (obviously)
		$this_acctnum = $this->get_acctnum();
		
		if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_folder_list: ENTERING<br>'; }
		if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: for the rest of this function we will use $this_acctnum: ['.$this_acctnum.'] <br>'; }
		
		if (stristr($this->skip_args_special_handlers, 'get_folder_list'))
		{
			$fake_return = array();
			$fake_return[0] = array();
			$fake_return[0]['folder_long'] = 'INBOX';
			$fake_return[0]['folder_short'] = 'INBOX';
			$fake_return[0]['acctnum'] = $this_acctnum;
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_folder_list: LEAVING, debug SKIP, $fake_return: '.serialize($fake_return).' <br>'; }
			return $fake_return;
		}
		
		if ($this->debug_args_special_handlers > 2) { echo 'mail_msg: get_folder_list: $$this->_direct_access_arg_value(folder_list) dump:<pre>'; print_r($this->_direct_access_arg_value('folder_list')); echo '</pre>'; }
		
		if ((!$mailsvr_stream)
		|| ($mailsvr_stream == ''))
		{
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream');
		}
		
		// check if class dcom reports that the folder list has changed
		if ((isset($this->a[$this_acctnum]['dcom']))
		&& ($this->a[$this_acctnum]['dcom']->folder_list_changed == True))
		{
			// class dcom recorded a change in the folder list
			// supposed to happen when create or delete mailbox is called
			// reset the changed flag
			$this->a[$this_acctnum]['dcom']->folder_list_changed = False;
			// set up for a force_refresh
			$force_refresh = True;
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: class dcom report folder list changed<br>'; }
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: make sure folder data is removed from cache <br>'; }
			// set_arg_value to empty array not necessary, it will be replaced later anyway
			//$blank_list = array();
			//$this->set_arg_value('folder_list', $blank_list);
			$my_function_name = 'get_folder_list';
			//$this->remove_cached_data($my_function_name);
			// if we do not provide $my_function_name, then we expire all "cachable_server_items"
			// which is probably a good idea, we do not want mismatched cached items
			$this->remove_cached_data('');
		}

		// see if we have object class var cached data that we can use
		$class_cached_folder_list = $this->_direct_access_arg_value('folder_list');
		if ((count($class_cached_folder_list) > 0)
		&& ($force_refresh == False))
		{
			// use the cached data
			if ($this->debug_args_special_handlers > 2) { echo ' * * $class_cached_folder_list DUMP<pre>'; print_r($class_cached_folder_list); echo '</pre>'; }
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_folder_list: LEAVING,  using object cached folder list data<br>'; }
			return $class_cached_folder_list;
		}
		elseif (($this->get_pref_value('mail_server_type') == 'pop3')
		|| ($this->get_pref_value('mail_server_type') == 'pop3s'))
		{
			// normalize the folder_list property
			$my_folder_list = array();
			// POP3 servers have 1 folder: INBOX
			$my_folder_list[0] = array();
			$my_folder_list[0]['folder_long'] = 'INBOX';
			$my_folder_list[0]['folder_short'] = 'INBOX';
			$my_folder_list[0]['acctnum'] = $this_acctnum;
			// save result to "Level 1 cache" class arg holder var
			$this->set_arg_value('folder_list', $my_folder_list);
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_folder_list: LEAVING,  pop3 servers only have one folder: INBOX<br>'; }
			return $my_folder_list;
		}
		elseif ($force_refresh == False)
		{
			// -----------
			// TRY CACHED DATA FROM PREFS DB
			// -----------
			// whether or not caching is enabled is handled in the "get_cached_data" function itself
			$my_function_name = 'get_folder_list';
			$cached_data = $this->get_cached_data($my_function_name,'array');
			// if there's no data we'll get back a FALSE
			if ($cached_data)
			{
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: using *Prefs DB* cached folder list data<br>';}
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: setting object var $this->a['.$this_acctnum.'][folder_list] to hold list data<br>';}
				// cached folder list does NOT contain "folder_short" data
				// that cuts cached data in 1/2, no need to cache something this easy to deduce
				// therefor... add FOLDER SHORT element to cached_data array structure
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: adding [folder_short] element to $this->a['.$this_acctnum.'][folder_list] array<br>';}
				for ($i=0; $i<count($cached_data);$i++)
				{
					$cached_data[$i]['folder_short'] = $this->get_folder_short($cached_data[$i]['folder_long']);
					if ($this->debug_args_special_handlers > 2) { echo ' * * $cached_data['.$i.'][folder_long]='.$cached_folder_list[$i]['folder_long'].' ; $cached_folder_list['.$i.'][folder_short]='.$cached_folder_list[$i]['folder_short'].'<br>';}
				}
				// cache the result in "Level 1 cache" class object var
				$this->set_arg_value('folder_list', $cached_data);
				if ($this->debug_args_special_handlers > 2) { echo 'mail_msg: get_folder_list: $cached_data *after* adding "folder_short" data<pre>'; print_r($cached_data); echo '</pre>'; }
				if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_folder_list: LEAVING, got data from cache<br>'; }
				return $cached_data;
			}
			else
			{
				if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: NO cached folder list data, fallback to get data from mailserver<br>';}
			}
		}
		
		// if we get here we must actually get the data from the mailsvr
		// otherwise we would have return/broke out of this function
		// only IF statement above that allows code to reach here is if we are allowed to use
		// cached data, BUT none exists
		if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: need to get data from mailserver<br>'; }
		
		// Establish Email Server Connectivity Information
		$server_str = $this->get_arg_value('mailsvr_callstr');
		$name_space = $this->get_arg_value('mailsvr_namespace');
		$delimiter = $this->get_arg_value('mailsvr_delimiter');
		
		// get a list of available folders from the server
		if ($this->get_pref_value('imap_server_type') == 'UWash')
		{
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: mailserver is of type UWash<br>';}
			// uwash is file system based, so it requires a filesystem slash after the namespace
			// note with uwash the delimiter is in fact the file system slash
			// example: requesting list for "mail/*"
			// (NOTE  this <slash><star> request will NOT yield a list the INBOX folder included in the returned folder list)
			// however, we have no choice since without the <slash> filesystem delimiter, requesting "email*" returns NOTHING
			// example querey: "~/"
			// OR if the user specifies specific mbox folder,
			// then: "~/emails/*"  OR  "emails/*" give the same result, much like a unix "ls" command
			// At this time we use "unqualified" a.k.a. "relative" directory names if the user provides a namespace
			// UWash will consider it relative to the mailuser's $HOME property as with "emails/*" (DOES THIS WORK ON ALL PLATFORMS??)
			// BUT we use <tilde><slash> "~/" if no namespace is given
			$mailboxes = $this->a[$this_acctnum]['dcom']->listmailbox($mailsvr_stream, $server_str, "$name_space" ."$delimiter" ."*");
			// UWASH IMAP returns information in this format:
			// {SERVER_NAME:PORT}FOLDERNAME
			// example:
			// {some.server.com:143}Trash
			// {some.server.com:143}Archives/Letters
		}
		else
		{
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: mailserver is other than UWash type<br>';}
			// handle non-UWash IMAP servers, i.e. not using filesystem slash as the "delimiter"
			// the last arg is typically "INBOX*" (no dot) which DOES include the inbox in the list of folders
			// wheres adding the delimiter "INBOX.*" (has dot) will NOT include the INBOX in the list of folders
			// so - it's safe to include the delimiter here, but the INBOX will not be included in the list
			// this is typically the ONLY TIME you would ever *not* use the delimiter between the namespace and what comes after it
			//$mailboxes = $this->a[$this_acctnum]['dcom']->listmailbox($mailsvr_stream, $server_str, "$name_space" ."*");
			// UPDATED information of this issue: to get shared folders included in the return, better NOT include the "." delimiter
			// example: Cyrus does not like anything but a "*" as the pattern IF you want shared folders returned.
			$mailboxes = $this->a[$this_acctnum]['dcom']->listmailbox($mailsvr_stream, $server_str, "*");
			// returns information in this format:
			// {SERVER_NAME:PORT} NAMESPACE DELIMITER FOLDERNAME
			// example:
			// {some.server.com:143}INBOX
			// {some.server.com:143}INBOX.Trash
		}
		if ($this->debug_args_special_handlers > 2) { echo 'mail_msg: get_folder_list: server returned $mailboxes dump:<pre>'; print_r($mailboxes); echo '</pre>'; }
		//echo 'raw mailbox list:<br>'.htmlspecialchars(serialize($mailboxes)).'<br>';
		
		// ERROR DETECTION
		if (!$mailboxes)
		{
			// we got no information back, clear the folder_list property
			// normalize the folder_list property
			$my_folder_list = array();
			// *assume* (i.e. pretend)  we have a server with only one box: INBOX
			$my_folder_list[0] = array();
			$my_folder_list[0]['folder_long'] = 'INBOX';
			$my_folder_list[0]['folder_short'] = 'INBOX';
			$my_folder_list[0]['acctnum'] = $this_acctnum;
			// save result to "Level 1 cache" class arg holder var
			$this->set_arg_value('folder_list', $my_folder_list);
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: error, no mailboxes returned from server, fallback to "INBOX" as only folder, $this->set_arg_value(folder_list, $my_folder_list) to hold that value<br>'; }
			if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_folder_list: LEAVING, with error, no mailboxes returned from server, return list with only INBOX<br>'; }
			return $my_folder_list;
		}
		
		// was INBOX included in the list? Some servers (uwash) do not return it
		$has_inbox = False;
		for ($i=0; $i<count($mailboxes);$i++)
		{
			$this_folder = $this->get_folder_short($mailboxes[$i]);
			//if ($this_folder == 'INBOX')
			// rfc2060 says "INBOX" as a namespace can not be case sensitive
			if ((stristr($this_folder, 'INBOX'))
			&& (strlen($this_folder) == strlen('INBOX')))
			{
				$has_inbox = True;
				break;
			}
		}
		// ADD INBOX if necessary
		if ($has_inbox == False)
		{
			if ($this->debug_args_special_handlers > 1) { echo 'mail_msg: get_folder_list: adding INBOX to mailboxes data<br>'; }
			// use the same "fully qualified" folder name format that "listmailbox" returns, includes the {serverName:port}
			$add_inbox = $server_str.'INBOX';
			$next_available = count($mailboxes);
			// add it to the $mailboxes array
			$mailboxes[$next_available] = $add_inbox;
		}
		
		// sort folder names
		// note: php3 DOES have is_array(), ok to use it here
		if (is_array($mailboxes))
		{
			// mainly to avoid warnings
			sort($mailboxes);
		}
		
		// normalize the folder_list property, we will transfer raw data in $mailboxes array to processed data in $my_folder_list
		$my_folder_list = array();
		
		// make a $my_folder_list array structure with ONLY FOLDER LONG data
		// save that to cache, that cuts cached data in 1/2
		// (LATER - we will add the "folder_short" data
		for ($i=0; $i<count($mailboxes);$i++)
		{
			// "is_imap_folder" really just a check on what UWASH imap returns, may be files that are not MBOX's
			if ($this->is_imap_folder($mailboxes[$i]))
			{
				//$this->a[$this_acctnum]['folder_list'][$i]['folder_long'] = $this->get_folder_long($mailboxes[$i]);
				// what we (well, me, Angles) calls a "folder long" is the raw data returned from the server (fully qualified name)
				// MINUS the bracketed server, so we are calling "folder long" a NAMESPACE_DELIMITER_FOLDER string
				// WITHOUT the {serverName:port} part, if that part is included we (Angles) call this "fully qualified"
				$next_idx = count($my_folder_list);
				$my_folder_list[$next_idx]['folder_long'] = $this->ensure_no_brackets($mailboxes[$i]);
				// AS SOON as possible, add data indicating WHICH ACCOUNT this folder list came from
				// while it is still somewhat easy to determine this
				$my_folder_list[$next_idx]['acctnum'] = $this_acctnum;
			}
		}
		if ($this->debug_args_special_handlers > 2) { echo 'mail_msg: get_folder_list: my_folder_list with only "folder_long" dump<pre>'; print_r($my_folder_list); echo '</pre>'; }
		// -----------
		// SAVE DATA TO PREFS DB CACHE (without the [folder_short] data)
		// -----------
		$my_function_name = 'get_folder_list';
		$this->set_cached_data($my_function_name,'array',$my_folder_list);
		
		// add FOLDER SHORT element to folder_list array structure
		// that cuts cached data in 1/2, no need to cache something this easy to deduce
		for ($i=0; $i<count($my_folder_list);$i++)
		{
			$my_folder_list[$i]['folder_short'] = $this->get_folder_short($my_folder_list[$i]['folder_long']);
		}
		// cache the result to "level 1 cache" class arg holder var
		$this->set_arg_value('folder_list', $my_folder_list);
		
		// finished, return the folder_list array atructure
		if ($this->debug_args_special_handlers > 2) { echo 'mail_msg: get_folder_list: finished, $my_folder_list dump:<pre>'; print_r($my_folder_list); echo '</pre>'; }
		if ($this->debug_args_special_handlers > 0) { echo 'mail_msg: get_folder_list: LEAVING, got folder data from server<br>'; }
		return $my_folder_list;
	}


	/* * * * * * * * * * *
	  *  folder_lookup
	  *  searches thru the list of available folders to determine if a given folder already exists
	  *  uses "folder_list[folder_long]" as the "haystack" because it is the most unaltered folder
	  *  information returned from the server that we have
	  *  if TRUE, then the "official" folder_long name is returned - the one supplied by the server itself
	  *  during the get_folder_list routine - "folder_list[folder_long]"
	  *  if False, an empty string is returned
	  * * * * * * *  * * * */
	function folder_lookup($mailsvr_stream, $folder_needle='INBOX')
	{
		if ((!$mailsvr_stream)
		|| ($mailsvr_stream == ''))
		{
			$mailsvr_stream = $this->get_arg_value('mailsvr_stream');
		}

		$folder_list = $this->get_folder_list($mailsvr_stream);

		//$debug_folder_lookup = True;
		$debug_folder_lookup = False;
		
		// retuen an empty string if the lookup fails
		$needle_official_long = '';
		for ($i=0; $i<count($folder_list);$i++)
		{
			// folder_haystack is the official folder long name returned from the server during "get_folder_list"
			$folder_haystack = $folder_list[$i]['folder_long'];
			  if ($debug_folder_lookup) { echo '['.$i.'] [folder_needle] '.$folder_needle.' len='.strlen($folder_needle).' [folder_haystack] '.$folder_haystack.' len='.strlen($folder_haystack).'<br>' ;}

			// first try to match the whole name, i.e. needle is already a folder long type name
			// the NAMESPACE should NOT be case sensitive
			// mostly, this means "INBOX" must not be matched case sensitively
			if (stristr($folder_haystack, $folder_needle))
			{
				if ($debug_folder_lookup) { echo 'entered stristr statement<br>'; }
				if (strlen($folder_haystack) == strlen($folder_needle))
				{
					// exact match - needle is already a fully legit folder_long name
					  if ($debug_folder_lookup) { echo 'folder exists, exact match, already legit long name: '.$needle_official_long.'<br>'; }
					$needle_official_long = $folder_haystack;
					break;
				}
				  if ($debug_folder_lookup) { echo 'exact match failed<br>'; }
				// if the needle is smaller than the haystack, then it is possible that the 
				// needle is a partial folder name that will match a portion of the haystack
				// look for pattern [delimiter][folder_needle] in the last portion of string haystack
				// because we do NOT want to match a partial word, folder_needle should be a whole folder name
				//tried this: if (preg_match('/.*([\]|[.]|[\\/]){1}'.$folder_needle.'$/i', $folder_haystack))
				// problem: unescaped forward slashes will be in UWASH folder names needles
				// and unescaped dots will be in other folder names needles
				// so use non-regex comparing
				// haystack must be larger then needle+1 (needle + a delimiter) for this to work
				if (strlen($folder_haystack) > strlen($folder_needle))
				{
					if ($debug_folder_lookup) { echo 'entered partial match logic<br>'; }
					// at least the needle is somewhere in the haystack
					// 1) get the length of the needle
					$needle_len = strlen($folder_needle);
					// get a negative value for use in substr
					$needle_len_negative = ($needle_len * (-1));
					// go back one more char in haystack to get the delimiter
					$needle_len_negative = $needle_len_negative - 1;
					  if ($debug_folder_lookup) { echo 'needle_len: '.$needle_len.' and needle_len_negative-1: '.$needle_len_negative.'<br>' ;}
					// get the last part of haystack that is that length
					$haystack_end = substr($folder_haystack, $needle_len_negative);
					// look for pattern [delimiter][folder_needle]
					// because we do NOT want to match a partial word, folder_needle should be a whole folder name
					  if ($debug_folder_lookup) { echo 'haystack_end: '.$haystack_end.'<br>' ;}
					if ((stristr('/'.$folder_needle, $haystack_end))
					|| (stristr('.'.$folder_needle, $haystack_end))
					|| (stristr('\\'.$folder_needle, $haystack_end)))
					{
						$needle_official_long = $folder_haystack;
						  if ($debug_folder_lookup) { echo 'folder exists, lookup found partial match, official long name: '.$needle_official_long.'<br>'; }
						break;
					}
					 if ($debug_folder_lookup) { echo 'partial match failed<br>'; }
				}
			}
		}
		return $needle_official_long;
	}


	function is_imap_folder($folder)
	{
		// UWash is the only (?) imap server where there is any question whether a folder is legit or not
		if ($this->get_pref_value('imap_server_type') != 'UWash')
		{
			//echo 'is_imap_folder TRUE 1<br>';
			return True;
		}

		$folder_long = $this->get_folder_long($folder);	

		// INBOX is ALWAYS a valid folder, and is ALWAYS called INBOX because it's a special reserved word
		// although it is NOT case sensitive 
		//if ($folder_long == 'INBOX')
		if ((stristr($folder_long, 'INBOX'))
		&& (strlen($folder_long) == strlen('INBOX')))
		{
			//echo 'is_imap_folder TRUE 2<br>';
			return True;
		}

		// UWash IMAP server looks for MBOX files, which it considers to be email "folders"
		// and will return any file, whether it's an actual IMAP folder or not
		if (strstr($folder_long,"/."))
		{
			// any pattern matching "/." for UWash is NOT an MBOX
			// because the delimiter for UWash is "/" and the immediately following "." indicates a hidden file
			// not an MBOX file, at least on Linux type system
			//echo 'is_imap_folder FALSE 3<br>';
			return False;
		}

		// if user specifies namespace like "mail" then MBOX files are in $HOME/mail
		// so this server knows to put MBOX files in a special place
		// BUT if the namespace used is associated with $HOME, such as ~
		// then how many folders deep do you want to go? UWash is recursive, it will go as deep as possible into $HOME
	
		// is this a $HOME type of namespace
		$the_namespace = $this->get_arg_value('mailsvr_namespace');
		if ($the_namespace == '~')
		{
			$home_type_namespace = True;
		}
		else
		{
			$home_type_namespace = False;
		}
	
		// DECISION: no more than 4 DIRECTORIES DEEP of recursion
		$num_slashes = $GLOBALS['phpgw']->msg->substr_count_ex($folder_long, "/");
		if (($home_type_namespace)
		&& ($num_slashes >= 4))
		{
			// this folder name indicates we are too deeply recursed, we don't care beyond here
			//echo 'is_imap_folder FALSE 4<br>';
			return False;
		}

		// if you get all the way to here then this must be a valid folder name
		//echo 'is_imap_folder TRUE 5<br>';
		return True;
	}


	function care_about_unseen($folder)
	{
		$folder = $this->get_folder_short($folder);
		// we ALWAYS care about new messages in the INBOX
		//if ($folder == 'INBOX')
		if ((stristr($folder_long, 'INBOX'))
		&& (strlen($folder_long) == strlen('INBOX')))
		{
			return True;
		}

		$we_care = True; // initialize
		$ignore_these_folders = Array();
		// DO NOT CHECK UNSEEN for these folders
		$ignore_these_folders[0] = "sent";
		$ignore_these_folders[1] = "trash";
		$ignore_these_folders[2] = "templates";
		for ($i=0; $i<count($ignore_these_folders); $i++)
		{
			$match_this = $ignore_these_folders[$i];
			if (eregi("^.*$match_this$",$folder))
			{
				$we_care = False;
				break;
			}
		}
		return $we_care;
	}


	// =====  OBSOLETED -- To Be Removed  ========
	function get_mime_info($this_part)
	{
		// rfc2045 says to assume "text" if this if not specified
		$mime_type = "text";
		if (isset($this_part->type) && $this_part->type)
		{
			switch ($this_part->type)
			{
				case TYPETEXT:		$mime_type = "text"; break;
				case TYPEMESSAGE:	$mime_type = "message"; break;
				case TYPEAPPLICATION:	$mime_type = "application"; break;
				case TYPEAUDIO:		$mime_type = "audio"; break;
				case TYPEIMAGE:		$mime_type = "image"; break;
				case TYPEVIDEO:		$mime_type = "video"; break;
				case TYPEMODEL:		$mime_type = "model"; break;
				default:		$mime_type = "text";
			} 
		}
		$mime_info['mime_type'] = $mime_type;

		// assume no info
		$mime_info['subtype'] = 'plain';
		if ((isset($part->ifsubtype)) && ($part->ifsubtype)
		&& (isset($part->subtype)) && ($part->subtype) )
		{
			$mime_info['subtype'] = trim(strtolower($part->subtype));
		}

		// rfc2045 says to assume "7bit" if this is not specified
		$mime_encoding = '7bit';
		if (isset($this_part->encoding) && $this_part->encoding)
		{
			switch ($this_part->encoding)
			{
				case ENC7BIT:		$mime_encoding = "7bit"; break;
				case ENC8BIT:		$mime_encoding = "8bit"; break;
				case ENCBINARY:		$mime_encoding = "binary"; break;
				case ENCBASE64:		$mime_encoding = "base64"; break;
				case ENCQUOTEDPRINTABLE:	$mime_encoding = "qprint"; break;
				case ENCOTHER:		$mime_encoding = "other";  break;
				default:		$mime_encoding = "7bit";
			}
		}
		$mime_info['mime_encoding'] = $mime_encoding;

		$mime_info['mime_params'] = Array();
		if ($this_part->ifparameters)
		{
			for ($i = 0; $i < count($this_part->parameters); $i++) 
			{
				$param = $this_part->parameters[$i];
				$mime_info['mime_params'][$i]['attribute'] = $param->attribute;
				$mime_info['mime_params'][$i]['value'] = $param->value;
			}
		}
	}

// ----  Password Crypto Workaround broken common->en/decrypt  -----
	/*!
	@function encrypt_email_passwd
	@abstract passes directly to crypto class
	@param $data data string to be encrypted
	@discussion: if mcrypt is not enabled, then the password data should be unmolested thru the 
	crypto functions, i.e. do not alter the string if mcrypt will not be preformed on that string.
	*/
	function encrypt_email_passwd($data)
	{
		return $GLOBALS['phpgw']->crypto->encrypt($data);
	}
	
	/*!
	@function decrypt_email_pass
	@abstract decrypt $data
	@param $data data to be decrypted
	@discussion: if mcrypt is not enabled, then the password data should be unmolested thru the 
	crypto functions, i.e. do not alter the string if mcrypt will not be preformed on that string.
	*/
	function decrypt_email_passwd($data)
	{
		return $GLOBALS['phpgw']->crypto->decrypt($data);
	}
	/*
	function decrypt_email_passwd($data)
	{
		if ($GLOBALS['phpgw_info']['server']['mcrypt_enabled'] && extension_loaded('mcrypt'))
		{
			$passwd = $data;
			// this will return a string that has:
			// (1) been decrypted with mcrypt (assuming mcrypt is enabled and working)
			// (2) had stripslashes applied and
			// (3) *MAY HAVE* been unserialized (ambiguous... see next comment)
			// correction Dec 14, 2001, (3) and definately was unserialized
			$cryptovars[0] = md5($GLOBALS['phpgw_info']['server']['encryptkey']);
			$cryptovars[1] = $GLOBALS['phpgw_info']['server']['mcrypt_iv'];
			$crypto = CreateObject('phpgwapi.crypto', $cryptovars);
			//$passwd = $crypto->decrypt($passwd);
			$passwd = $crypto->decrypt_mail_pass($passwd);
		}
		else
		{
			$passwd = $data;
			// ASSUMING set_magic_quotes_runtime(0) has been specified, then
			// there should be NO escape slashes coming from the database
			//if ($this->is_serialized($passwd))
			if ($this->is_serialized_str($passwd))
			{
				$passwd = unserialize($passwd);
			}


			// #### (begin) Upgrade Routine for 0.9.12 and earlier versions ####
			/*!
			@capability: Upgrade Routine for 0.9.12 and earlier Custom Passwords
			@discussion: 
			the phpgw versions prior to and including 0.9.12 *may* have double or even tripple serialized
			passwd strings stored in their preferences table. SO:
			(1) check for this
			(2) unserialize to the real string
			(3) feed the unserialized / fixed passwd in the prefs class and save the "upgraded" passwd
			//* //
			// (1) check for this 
			//$multi_serialized = $this->is_serialized($passwd);
			$multi_serialized = $this->is_serialized_str($passwd);
			if ($multi_serialized)
			{
				$pre_upgrade_passwd = $passwd;
				// (2) unserialize to the real string
				$failure = 10;
				$loop_num = 0;
				do
				{
					$loop_num++;
					if ($loop_num == $failure)
					{
						break;
					}
					$passwd = unserialize($passwd);
				}
				//while ($this->is_serialized($passwd));
				while ($this->is_serialized_str($passwd));
				
				// 10 loops is too much, something is wrong
				if ($loop_num == $failure)
				{
					// screw it and continue as normal, user will need to reenter password
					echo 'ERROR: decrypt_email_passwd: custom pass upgrade procedure failed to restore passwd to useable state<br>';
					$passwd = $pre_upgrade_passwd;
				}
				else
				{
					// (3) SAVE THE FIXED / UPGRADED PASSWD TO PREFS
					// feed the unserialized / fixed passwd in the prefs class
					$GLOBALS['phpgw']->preferences->delete('email','passwd');
					// make any html quote entities back to real form (i.e. ' or ")
					$encrypted_passwd = $this->html_quotes_decode($passwd);
					// encrypt it as it would be as if the user had just submitted the preferences page (no need to strip slashes, no POST occured)
					$encrypted_passwd = $this->encrypt_email_passwd($passwd);
					// store in preferences so this does not happen again
					$GLOBALS['phpgw']->preferences->add('email','passwd',$encrypted_passwd);
					$GLOBALS['phpgw']->preferences->save_repository();
				}
			}
			// #### (end) Upgrade Routine for 0.9.12 and earlier versions ####

			$passwd = $this->html_quotes_decode($passwd);
			//echo 'decrypt_email_passwd result: '.$passwd;
		}
		return $passwd;
	}
	*/
	
	// ----  Make Address accoring to RFC2822 Standards  -----
	function make_rfc2822_address($addy_data, $html_encode=True)
	{
		//echo '<br>'.$this->htmlspecialchars_encode(serialize($addy_data)).'<br>'.'<br>';
		
		if ((!isset($addy_data->mailbox)) && (!$addy_data->mailbox)
		&& (!isset($addy_data->host)) && (!$addy_data->host))
		{
			// fallback value, we do not want to sent a string like this "@" if no data if available
			return '';
		}
		// now we can continue, 1st make a simple, plain address
		// RFC2822 allows this simple form if not using "personal" info
		$rfc_addy = $addy_data->mailbox.'@'.$addy_data->host;
		// add "personal" data if it exists
		if (isset($addy_data->personal) && ($addy_data->personal))
		{
			// why DECODE when we are just going to feed it right back into a header?
			// answer - it looks crappy to have rfc2047 encoded personal info in the to: box
			$personal = $this->decode_header_string($addy_data->personal);
			// need to format according to RFC2822 spec for non-plain email address
			$rfc_addy = '"'.$personal.'" <'.$rfc_addy.'>';
			// if using this addy in an html page, we need to encode the ' " < > chars
			if ($html_encode)
			{
				$rfc_addy = $this->htmlspecialchars_encode($rfc_addy);
				//NOTE: in rfc_comma_sep we will decode any html entities back into these chars
			}
		}
		return $rfc_addy;
	}


	// ----  Make a To: string of comma seperated email addresses into an array structure  -----
	/*
	// param $data should be a comma seperated string of email addresses (or just one email address) such as:
	// john@doe.com,"Php Group" <info@phpgroupware.org>,jerry@example.com,"joe john" <jj@example.com>
	// which will be decomposed into an array of individual email addresses
	// where each numbered item will be like this this:
	// 	array[x]['personal'] 
	// 	array[x]['plain'] 
	// the above example would return this structure:
	// 	array[0]['personal'] = ""
	// 	array[0]['plain'] = "john@doe.com"
	// 	array[1]['personal'] = "Php Group"
	// 	array[1]['plain'] = "info@phpgroupware.org"
	// 	array[2]['personal'] = ""
	// 	array[2]['plain'] = "jerry@example.com"
	// 	array[3]['personal'] = "joe john"
	// 	array[3]['plain'] = "jj@example.com"
	*/
	function make_rfc_addy_array($data)
	{
		// if we are fed a null value, return nothing (i.e. a null value)
		if (isset($data))
		{
			$data = trim($data);
			// if we are fed a whitespace only string, return a blank string
			if ($data == '')
			{
				return $data;
				// return performs an implicit break, so we are outta here
			}
			// in some cases the data may be in html entity form
			// i.e. the compose page uses html entities when filling the To: box with a predefined value
			$data = $this->htmlspecialchars_decode($data);
			//reduce all multiple spaces to just one space
			//$data = ereg_replace("[' ']{2,20}", ' ', $data);
			$this_space = " ";
			$data = ereg_replace("$this_space{2,20}", " ", $data);
			// explode into an array of email addys
			//$data = explode(",", $data);


			// WORKAROUND - comma inside the "personal" part will incorrectly explode
			//$debug_explode = True;
			$debug_explode = False;
			
			/*// === ATTEMPT 1 ====
			// replace any comma(s) INSIDE the "personal" part with this:  "C-O-M-M-A"
			echo 'PRE replace: '.$this->htmlspecialchars_encode($data).'<br>';
			$comma_replacement = "C_O_M_M_A";
			do
			{
				//$data = preg_replace('/(".*?)[,](.*?")/',"$1"."C_O_M_M_A"."$2", $data);
				//$data = preg_replace('/("[/001-/063,/065-/255]*?)[,]([/001-/063,/065-/255]*?")/',"$1"."$comma_replacement"."$2", $data);
				$data = preg_replace('/("(.(?!@))*?)[,]((.(?!@))*?")/',"$1"."$comma_replacement"."$3", $data);
			}
			while (preg_match('/("(.(?!@))*?)[,]((.(?!@))*?")/',$data));
			echo 'POST replace: '.$this->htmlspecialchars_encode($data).'<br>';
			//DEBUG
			return " ";
			// explode into an array of email addys
			//$data = explode(",", $data);
			*/

			// === Explode Prep: STEP 1 ====
			// little is known about an email address at this point
			// what is known is that the following pattern should be present in ALL non-simple addy's
			// " <  (doublequote_space_lessThan)
			// so replace that with a known temp string
			
			if ($debug_explode) { echo '[known sep] PRE replace: '.$this->htmlspecialchars_encode($data).'<br>'.'<br>'; }
			//$known_sep_item = "_SEP_COMPLEX_SEP_";
			// introduce some randomness to make accidental replacements less likely
			$sep_rand = $GLOBALS['phpgw']->common->randomstring(3);
			$known_sep_item = "_SEP_COMPLEX_".$sep_rand."_SEP_";
			$data = str_replace('" <',$known_sep_item,$data);
			if ($debug_explode) { echo '[known sep] POST replace: '.$this->htmlspecialchars_encode($data).'<br>'.'<br>'; }

			// === Explode Prep: STEP 2 ====
			// now we know more
			// the area BETWEEN a " (doubleQuote) and the $known_sep_item is the "personal" part of the addy
			// replace any comma(s) in there with another known temp string
			if ($debug_explode) { echo 'PRE replace: '.$this->htmlspecialchars_encode($data).'<br>'.'<br>'; }
			//$comma_replacement = "_C_O_M_M_A_";
			// introduce some randomness to make accidental replacements less likely
			$comma_rand = $GLOBALS['phpgw']->common->randomstring(3);
			$comma_replacement = "_C_O_M_".$comma_rand."_M_A_";
			//$data = preg_replace('/(".*?)[,](.*?'.$known_sep_item.')/',"$1"."$comma_replacement "."$2", $data);
			//$data = preg_replace('/(".*?)(?<!>)[,](.*?'.$known_sep_item.')/',"$1"."$comma_replacement"."$2", $data);
			do
			{
				$data = preg_replace('/("(.(?<!'.$known_sep_item.'))*?)[,](.*?'.$known_sep_item.')/',"$1"."$comma_replacement"."$3", $data);
			}
			while (preg_match('/("(.(?<!'.$known_sep_item.'))*?)[,](.*?'.$known_sep_item.')/',$data));
			if ($debug_explode) { echo 'POST replace: '.$this->htmlspecialchars_encode($data).'<br>'.'<br>'; }

			// Regex Pattern Explanation:
			//	openQuote_anythingExcept$known_sep_item_repeated0+times_NOT GREEDY
			//	_aComma_anything_repeated0+times_NOT GREEDY_$known_sep_item
			// syntax: "*?" is 0+ repetion symbol with the immediately following '?' being the Not Greedy modifier
			// NotGreedy: match as little as possible that still makes the pattern match
			// syntax: "?<!" is a "lookbehind negative assertion"
			// indicating that the ". *" can not contain anything EXCEPT the $known_sep_item string
			// lookbehind is necessary because this assertion applies to something BEFORE the thing (comma) we are trying to capture with the regex
			// Methodology:
			// (1) We need to specify NO $known_sep_item before the comma or else the regex will match
			// commas OUTSIDE of the intended "personal" part of the email addy, which are the
			// special commas that seperate email addresses in a comma seperated string
			// these special commas MUST NOT be altered
			// (2) this preg_replace will only replace ONE comma in the designated "personal" part
			// therefor we need a do ... while loop to keep running the preg_replace until all matches are replaced
			// the while statement is the SAME regex expression used in a preg_match function

			// === Explode Prep: STEP 3 ====
			// UNDO the str_replace from STEP 1
			$data = str_replace($known_sep_item, '" <', $data);
			if ($debug_explode) { echo 'UNDO Step 1: '.$this->htmlspecialchars_encode($data).'<br>'.'<br>'; }

			// === ACTUAL EXPLODE ====
			// now the only comma(s) (if any) existing in $data *should* be the
			// special commas that seperate email addresses in a comma seperated string
			// with this as a (hopefully) KNOWN FACTOR - we can now EXPLODE by comma
			// thus: Explode into an array of email addys
			$data = explode(",", $data);
			if ($debug_explode) { echo 'EXPLODED: '.$this->htmlspecialchars_encode(serialize($data)).'<br>'.'<br>'; }

			// === POST EXPLODE  CLEANING====
			// explode occasionally produces empty elements in the resulting array, so
			// (1) eliminate any empty array elements
			// (2) UNDO the preg_replace from STEP 2 (add back the actual comma(s) in the "personal" part)
			$data_clean = Array();
			for ($i=0;$i<count($data);$i++)
			{
				// is there actual data in this array element
				if ((isset($data[$i])) && ($data[$i] != ''))
				{
					// OK, now undo the preg_replace from step 2 above
					$data[$i] = str_replace($comma_replacement, ',', $data[$i]);
					// add this to our $data_clean array
					$next_empty = count($data_clean);
					$data_clean[$next_empty] = $data[$i];
				}
			}
			if ($debug_explode) { echo 'Cleaned Exploded Data: '.$this->htmlspecialchars_encode(serialize($data_clean)).'<br>'.'<br>'; }


			// --- Create Compund Array Structure To Hold Decomposed Addresses -----
			// addy_array is a simple numbered array, each element is a addr_spec_array
			$addy_array = Array();
			// $addr_spec_array has this structure:
			//  addr_spec_array['plain'] 
			//  addr_spec_array['personal']

			// decompose addy's into that array, and format according to rfc specs
			for ($i=0;$i<count($data_clean);$i++)
			{
				// trim off leading and trailing whitespaces and \r and \n
				$data_clean[$i] = trim($data_clean[$i]);
				// is this a rfc 2822 compound address (not a simple one)
				if (strstr($data_clean[$i], '" <'))
				{
					// SEPERATE "personal" part from the <x@x.com> part
					$addr_spec_parts = explode('" <', $data_clean[$i]);
					// that got rid of the closing " in personal, now get rig of the first "
					$addy_array[$i]['personal'] = substr($addr_spec_parts[0], 1);
					//  the "<" was already removed, , NOW remove the closing ">"
					$grab_to = strlen($addr_spec_parts[1]) - 1;
					$addy_array[$i]['plain'] = substr($addr_spec_parts[1], 0, $grab_to);

					// QPRINT NON US-ASCII CHARS in "personal" string, as per RFC2047
					// the actual "plain" address may NOT have any other than US-ASCII chars, as per rfc2822
					$addy_array[$i]['personal'] = $this->encode_header($addy_array[$i]['personal']);

					// REVISION: rfc2047 says the following escaping technique is not much help
					// use the encoding above instead
					/*
					// ESCAPE SPECIALS:  rfc2822 requires the "personal" comment string to escape "specials" inside the quotes
					// the non-simple (i.e. "personal" info is included) need special escaping
					// escape these:  ' " ( ) 
					$addy_array[$i]['personal'] = ereg_replace('\'', "\\'", $addy_array[$i]['personal']);
					$addy_array[$i]['personal'] = str_replace('"', '\"', $addy_array[$i]['personal']);
					$addy_array[$i]['personal'] = str_replace("(", "\(", $addy_array[$i]['personal']);
					$addy_array[$i]['personal'] = str_replace(")", "\)", $addy_array[$i]['personal']);
					*/
				}
				else
				{
					// this is an old style simple address
					$addy_array[$i]['personal'] = '';
					$addy_array[$i]['plain'] = $data_clean[$i];
				}

				//echo 'addy_array['.$i.'][personal]: '.$this->htmlspecialchars_encode($addy_array[$i]['personal']).'<br>';
				//echo 'addy_array['.$i.'][plain]: '.$this->htmlspecialchars_encode($addy_array[$i]['plain']).'<br>';
			}
			if ($debug_explode) { echo 'FINAL processed addy_array:<br>'.$this->htmlspecialchars_encode(serialize($addy_array)).'<br>'.'<br>'; }
			return $addy_array;
		}
	}

	// takes an array generated by "make_rfc_addy_array()" and makes it into a string
	// ytpically used to make to and from headers, etc...
	function addy_array_to_str($data, $include_personal=True)
	{
		$addy_string = '';
		
		// reconstruct data in the correct email address format
		//if (count($data) == 0)
		//{
		//	$addy_string = '';
		//}
		if (count($data) == 1)
		{
			if (($include_personal == False) || (strlen(trim($data[0]['personal'])) < 1))
			{
				$addy_string = trim($data[0]['plain']);
			}
			else
			{
				$addy_string = '"'.trim($data[0]['personal']).'" <'.trim($data[0]['plain']).'>';
			}
		}
		elseif ($include_personal == False)
		{
			// CLASS SEND CAN NOT HANDLE FOLDED HEADERS OR PERSONAL ADDRESSES
			// this snippit just assembles the headers
			for ($i=0;$i<count($data);$i++)
			{
				// addresses should be seperated by one comma with NO SPACES AT ALL
				$addy_string = $addy_string .trim($data[$i]['plain']) .',';
			}
			// catch any situations where a blank string was included, resulting in two commas with nothing inbetween
			$addy_string = ereg_replace("[,]{2}", ',', $addy_string);
			// trim again, strlen needs to be accurate without trailing spaces included
			$addy_string = trim($addy_string);
			// eliminate that final comma
			$grab_to = strlen($addy_string) - 1;
			$addy_string = substr($addy_string, 0, $grab_to);
		}
		else
		{
			// if folding headers - use SEND_2822  instead of class.send
			// FRC2822 recommended max header line length, excluding the required CRLF
			$rfc_max_length = 78;

			// establish an arrays in case we need a multiline header string
			$header_lines = Array();
			$line_num = 0;
			$header_lines[$line_num] = '';
			// loop thru the addresses, construct the header string
			for ($z=0;$z<count($data);$z++)
			{
				// make a string for this individual address
				if (trim($data[$z]['personal']) != '')
				{
					$this_address = '"'.trim($data[$z]['personal']).'" <'.trim($data[$z]['plain']).'>';
				}
				else
				{
					$this_address = trim($data[$z]['plain']);
				}
				// see how long this line would be if this address were added
				//if ($z == 0)
				$cur_len = strlen($header_lines[$line_num]);
				if ($cur_len < 1)
				{
					$would_be_str = $this_address;
				}
				else
				{
					$would_be_str = $header_lines[$line_num] .','.$this_address;
				}
				//echo 'would_be_str: '.$this->htmlspecialchars_encode($would_be_str).'<br>';
				//echo 'strlen(would_be_str): '.strlen($would_be_str).'<br>';
				if ((strlen($would_be_str) > $rfc_max_length)
				&& ($cur_len > 1))
				{
					// Fold Header: RFC2822 "fold" = CRLF followed by a "whitespace" (#9 or #32)
					// preferable to "fold" after the comma, and DO NOT TRIM that white space, preserve it
					//$whitespace = " ";
					$whitespace = chr(9);
					$header_lines[$line_num] = $header_lines[$line_num].','."\r\n";
					// advance to the next line
					$line_num++;
					// now start the new line with the "folding whitespace" then the address
					$header_lines[$line_num] = $whitespace .$this_address;
				}
				else
				{
					// simply comma sep the items (as we did when making "would_be_str")
					$header_lines[$line_num] = $would_be_str;
				}
			}
			// assemble $header_lines array into a single string
			$addy_string = '';
			for ($x=0;$x<count($header_lines);$x++)
			{
				$addy_string = $addy_string .$header_lines[$x];
			}
			$addy_string = trim($addy_string);
		}
		// data leaves here with NO FINAL (trailing) CRLF - will add that later
		return $addy_string;
	}

	// ----  Ensure CR and LF are always together, RFCs prefer the CRLF combo  -----
	function normalize_crlf($data)
	{
		// this is to catch all plain \n instances and replace them with \r\n.  
		$data = ereg_replace("\r\n", "\n", $data);
		$data = ereg_replace("\r", "\n", $data);
		$data = ereg_replace("\n", "\r\n", $data);
		
		//$data = preg_replace("/(?<!\r)\n/m", "\r\n", $data);
		//$data = preg_replace("/\r(?!\n)/m", "\r\n", $data);
		return $data;
	}

	// ----  Explode by Linebreak, ANY kind of line break  -----
	function explode_linebreaks($data)
	{
		$data = preg_split("/\r\n|\r(?!\n)|(?<!\r)\n/m",$data);
		// match \r\n, OR \r with no \n after it , OR /n with no /r before it
		// modifier m = multiline
		return $data;
	}

	// ----  Create a Unique Mime Boundary  -----
	function make_boundary($part_length=4)
	{
		$part_length = (int)$part_length;
		
		$rand_stuff = Array();
		$rand_stuff[0]['length'] = $part_length;
		$rand_stuff[0]['string'] = $GLOBALS['phpgw']->common->randomstring($rand_stuff[0]['length']);
		$rand_stuff[0]['rand_numbers'] = '';
		for ($i = 0; $i < $rand_stuff[0]['length']; $i++)
		{
			if ((ord($rand_stuff[0]['string'][$i]) > 47) 
			&& (ord($rand_stuff[0]['string'][$i]) < 58))
			{
				// this char is already a digit
				$rand_stuff[0]['rand_numbers'] .= $rand_stuff[0]['string'][$i];
			}
			else
			{
				// turn this into number form, based on this char's ASCII value
				$rand_stuff[0]['rand_numbers'] .= ord($rand_stuff[0]['string'][$i]);
			}
		}
		$rand_stuff[1]['length'] = $part_length;
		$rand_stuff[1]['string'] = $GLOBALS['phpgw']->common->randomstring($rand_stuff[1]['length']);
		$rand_stuff[1]['rand_numbers'] = '';
		for ($i = 0; $i < $rand_stuff[1]['length']; $i++)
		{
			if ((ord($rand_stuff[1]['string'][$i]) > 47) 
			&& (ord($rand_stuff[1]['string'][$i]) < 58))
			{
				// this char is already a digit
				$rand_stuff[1]['rand_numbers'] .= $rand_stuff[1]['string'][$i];
			}
			else
			{
				// turn this into number form, based on this char's ASCII value
				$rand_stuff[1]['rand_numbers'] .= ord($rand_stuff[1]['string'][$i]);
			}
		}
		$unique_boundary = '---=_Next_Part_'.$rand_stuff[0]['rand_numbers'].'_'.$GLOBALS['phpgw']->common->randomstring($part_length)
			.'_'.$GLOBALS['phpgw']->common->randomstring($part_length).'_'.$rand_stuff[1]['rand_numbers'];
		
		return $unique_boundary;
	}

	// ----  Create a Unique RFC2822 Message ID  -----
	function make_message_id()
	{
		if ($GLOBALS['phpgw_info']['server']['hostname'] != '')
		{
			$id_suffix = $GLOBALS['phpgw_info']['server']['hostname'];
		}
		else
		{
			$id_suffix = $GLOBALS['phpgw']->common->randomstring(3).'local';
		}
		// gives you timezone dot microseconds space datetime
		$stamp = microtime();
		$stamp = explode(" ",$stamp);
		// get rid of tomezone info
		$grab_from = strpos($stamp[0], ".") + 1;
		$stamp[0] = substr($stamp[0], $grab_from);
		// formay the datetime into YYYYMMDD
		$stamp[1] = date('Ymd', $stamp[1]);
		// a small random string for the middle
		$rand_middle = $GLOBALS['phpgw']->common->randomstring(3);
		
		$mess_id = '<'.$stamp[1].'.'.$rand_middle.'.'.$stamp[0].'@'.$id_suffix.'>';
		return $mess_id;
	}

  // ----  HTML - Related Utility Functions   -----
	function qprint($string)
	{
		$string = str_replace("_", " ", $string);
		$string = str_replace("=\r\n","",$string);
		$string = quoted_printable_decode($string);
		return $string;
	}
	
	/*
	// ----  RFC Header Decoding  -----
	function qprint_rfc_header($data)
	{
		// SAME FUNCTIONALITY as decode_header_string()  in /inc/functions, (but Faster, hopefully)
		// non-us-ascii chars in email headers MUST be encoded using the special format:  
		//  =?charset?Q?word?=
		// currently only qprint and base64 encoding is specified by RFCs
		if (ereg("=\?.*\?(Q|q)\?.*\?=", $data))
		{
			$data = ereg_replace("=\?.*\?(Q|q)\?", '', $data);
			$data = ereg_replace("\?=", '', $data);
			$data = $this->qprint($data);
		}
		return $data;
	}
	*/

	// non-us-ascii chars in email headers MUST be encoded using the special format:  
	//  =?charset?Q?word?=
	// currently only qprint and base64 encoding is specified by RFCs
	function decode_header_string($string)
	{
		if($string)
		{
			$pos = strpos($string,"=?");
			if(!is_int($pos))
			{
				return $string;
			}
			// save any preceding text
			$preceding = substr($string,0,$pos);
			$end = strlen($string);
			// the mime header spec says this is the longest a single encoded word can be
			$search = substr($string,$pos+2,$end - $pos - 2 );
			$d1 = strpos($search,"?");
			if(!is_int($d1))
			{
				return $string;
			}
			$charset = strtolower(substr($string,$pos+2,$d1));
			$search = substr($search,$d1+1);
			$d2 = strpos($search,"?");
			if(!is_int($d2))
			{
				return $string;
			}
			$encoding = substr($search,0,$d2);
			$search = substr($search,$d2+1);
			$end = strpos($search,"?=");
			if(!is_int($end))
			{
				return $string;
			}
			$encoded_text = substr($search,0,$end);
			$rest = substr($string,(strlen($preceding.$charset.$encoding.$encoded_text)+6));
			if(strtoupper($encoding) == "Q")
			{
				$decoded = $GLOBALS['phpgw']->msg->qprint(str_replace("_"," ",$encoded_text));
			}
			if (strtoupper($encoding) == "B")
			{
				$decoded = urldecode(base64_decode($encoded_text));
			}
			return $preceding . $decoded . $this->decode_header_string($rest);
		} 
		else
		{
			return $string;
		}
	}

	// SUB-FUNCTION - do not call directly, used by "encode_header()"
	function encode_iso88591_word($string)
	{
		$qprint_prefix = '=?iso-8859-1?Q?';
		$qprint_suffix = '?=';
		$new_str = '';
		$did_encode = False;
		
		for( $i = 0 ; $i < strlen($string) ; $i++ )
		{
			$val = ord($string[$i]);
			// my interpetation of what to encode from RFC2045 and RFC2822
			if ( (($val >= 1) && ($val <= 31))
			|| (($val >= 33) && ($val <= 47))
			|| ($val == 61)
			|| ($val == 62)
			|| ($val == 64)
			|| (($val >= 91) && ($val <= 94))
			|| ($val == 96)
			|| ($val >= 123))
			{
				$did_encode = True;
				//echo 'val needs encode: '.$val.'<br>';
				$val = dechex($val);
				// rfc2045 requires quote printable HEX letters to be uppercase
				$val = strtoupper($val);
				//echo 'val AFTER encode: '.$val.'<br>';
				//$text .= '='.$val;
				$new_str = $new_str .'='.$val;
			}
			else
			{
				$new_str = $new_str . $string[$i];
			}
		}
		if ($did_encode)
		{
			$new_str =  $qprint_prefix .$new_str .$qprint_suffix;
		}
		return $new_str;
	}
	
	// encode email headers as per rfc2047, non US-ASCII chars in email headers
	// basic idea is to qprint any word with "header-unfriendly" chars in it
	// then surround that qprinted word with the stuff specified in rfc2047
	// Example:
	//  "my //name\\ {iS} L@@T" <leet@email.com>
	// that email address has "header-unfriendly" chars in it
	// this function would encode it suitable for email transport
	function encode_header($data)
	{
		// explode string into an array or words
		$words = explode(' ', $data);
		
		for($i=0; $i<count($words); $i++)
		{
			//echo 'words['.$i.'] in loop: '.$words[$i].'<br>';
			
			// my interpetation of what to encode from RFC2045, RFC2047, and RFC2822
			// all these chars seem to cause trouble, so encode them
			if (preg_match('/'
				. '['.chr(1).'-'.chr(31).']'
				. '['.chr(33).'-'.chr(38).']'
				.'|[\\'.chr(39).']'
				.'|['.chr(40).'-'.chr(46).']'
				.'|[\\'.chr(47).']'
				.'|['.chr(61).'-'.chr(62).']'
				.'|['.chr(64).']'
				.'|['.chr(91).'-'.chr(94).']'
				.'|['.chr(96).']'
				.'|['.chr(123).'-'.chr(255).']'
				.'/', $words[$i]))
			{
				/*
				// qprint this word, and add rfc2047 header special words
				$len_before = strlen($words[$i]);
				echo 'words['.$i.'] needs encode: '.$words[$i].'<br>';
				$words[$i] = imap_8bit($words[$i]);
				echo 'words['.$i.'] AFTER encode: '.$words[$i].'<br>';
				// php may not encode everything that I expect, so check to see if encoding happened
				$len_after = strlen($words[$i]);
				if ($len_before != $len_after)
				{
					// indeed, encoding did happen, add rfc2047 header special words
					$words[$i] = $qprint_prefix .$words[$i] .$qprint_suffix;
				}
				*/
				
				// qprint this word, and add rfc2047 header special words
				//echo 'words['.$i.'] needs encode: '.$words[$i].'<br>';
				$words[$i] = $this->encode_iso88591_word($words[$i]);
				//echo 'words['.$i.'] AFTER encode: '.$words[$i].'<br>';
			}
		}
		
		// reassemble the string
		$encoded_str = implode(' ',$words);
		return $encoded_str;
	}

	// PHP "htmpspecialchars" is unreliable sometimes, and does not encode single quotes (unless told to)
	// this is a somewhat more reliable version of that PHP function
	// with a corresponding 'decode' function below it
	function htmlspecialchars_encode($str)
	{
		/*// replace  '  and  "  with htmlspecialchars */
		$str = ereg_replace('&', '&amp;', $str);
		// any ampersand & that ia already in a "&amp;" should NOT be encoded
		//$str = preg_replace("/&(?![:alnum:]*;)/", "&amp;", $str);
		$str = ereg_replace('"', '&quot;', $str);
		$str = ereg_replace('\'', '&#039;', $str);
		$str = ereg_replace('<', '&lt;', $str);
		$str = ereg_replace('>', '&gt;', $str);
		// these {  and  }  must be html encoded or else they conflict with the template system
		//$str = str_replace("{", '&#123;', $str);
		//$str = str_replace("}", '&#125;', $str);
		return $str;
	}

	// reverse of the above encode function
	function htmlspecialchars_decode($str)
	{
		/*// reverse of htmlspecialchars */
		//$str = str_replace('&#125;', "}", $str);
		//$str = str_replace('&#123;', "{", $str);
		
		$str = ereg_replace('&gt;', '>', $str);
		$str = ereg_replace('&lt;', '<', $str);
		$str = ereg_replace('&#039;', '\'', $str);
		$str = ereg_replace('&quot;', '"', $str);
		$str = ereg_replace('&amp;', '&', $str);
		return $str;
	}

	// ==  "poor-man's" database compatibility ==
	function html_quotes_encode($str)
	{
		// ==  "poor-man's" database compatibility ==
		// encode database unfriendly chars as html entities
		// it'll work for now, and it can be easily un-done later when real DB classes take care of this issue
		// replace  '  and  "  with htmlspecialchars
		$str = ereg_replace('"', '&quot;', $str);
		$str = ereg_replace('\'', '&#039;', $str);
		// replace  , (comma)
		$str = ereg_replace(',', '&#044;', $str);
		// replace /  (forward slash)
		$str = ereg_replace('/', '&#047;', $str);
		// replace \  (back slash)
		$str = ereg_replace("\\\\", '&#092;', $str);
		return $str;
	}

	// ==  "poor-man's" database compatibility ==
	function html_quotes_decode($str)
	{
		// ==  "poor-man's" database compatibility ==
		// reverse of html_quotes_encode - html specialchar convert to actual ascii char
		// backslash \ 
		$str = ereg_replace('&#092;', "\\", $str);
		// forward slash /
		$str = ereg_replace('&#047;', '/', $str);
		// comma ,
		$str = ereg_replace('&#044;', ',', $str);
		// single quote '
		$str = ereg_replace('&#039;', '\'', $str);
		// double quote "
		$str = ereg_replace('&quot;', '"', $str);
		return $str;
	}

	// base64 decoding
	function de_base64($text) 
	{
		//return $this->a[$this->acctnum]['dcom']->base64($text);
		//return imap_base64($text);
		return base64_decode($text);
	}

	// DEPRECIATED - not used currently (9/25/2001)
	function space_to_nbsp($data)
	{
		// change every other space to a html "non breaking space" so lines can still wrap
		$data = str_replace("  "," &nbsp;",$data);
		return $data;
	}

	// my implementation of a PHP4 only function
	function body_hard_wrap($in, $size=80)
	{
		// this function formats lines according to the defined
		// linesize. Linebrakes (\n\n) are added when neccessary,
		// but only between words.

		$out='';
		$exploded = explode ("\r\n",$in);

		for ($i = 0; $i < count($exploded); $i++)
		{
			$this_line = $exploded[$i];
			$this_line_len = strlen($this_line); 
			if ($this_line_len > $size)
			{
				$temptext='';
				$temparray = explode (' ',$this_line);
				$z = 0;
				while ($z <= count($temparray))
				{
					while ((strlen($temptext.' '.$temparray[$z]) < $size) && ($z <= count($temparray)))
					{
						$temptext = $temptext.' '.$temparray[$z];
						$z++;
					}
					$out = $out."\r\n".$temptext;
					$temptext = $temparray[$z];
					$z++;
				}
			}
			else
			{
				//$out = trim($out);
				// get the rest of the line now
				$out = $out . $this_line . "\r\n";
			}
			//$out = trim($out);
			//$out = $out . "\r\n";
		}
		// one last trimming
		$temparray = explode("\r\n",$out);
		for ($i = 0; $i < count($temparray); $i++)
		{
			//$temparray[$i] = trim($temparray[$i]);
			// NOTE: I see NO reason to trim the LEFT part of the string, use RTRIM instead
			$temparray[$i] = rtrim($temparray[$i]);
		}
		$out = implode("\r\n",$temparray);
		
		return $out;
	}


	// magic_quotes_gpc  PHP MANUAL:
	/* Sets the magic_quotes state for GPC (Get/Post/Cookie) operations. 
	  When magic_quotes are on, all ' (single-quote), " (double quote), \ (backslash) and NUL's 
	  are escaped with a backslash automatically.
	  GPC means GET/POST/COOKIE which is actually EGPCS these days (Environment, GET, POST, Cookie, Server).
	  This cannot be turned off in your script because it operates on the data before your script is called. 
	  You can check if it is on using that function and treat the data accordingly." (by Rasmus Lerdorf) */
	function stripslashes_gpc($data)
	{	/* get rid of the escape \ that magic_quotes HTTP POST will add, " becomes \" and  '  becomes  \'  
		  but ONLY if magic_quotes is on, less likely to strip user intended slashes this way */
		if (get_magic_quotes_gpc()==1)
		{
			return stripslashes($data);
		}
		else
		{
			return $data;
		}
	}

	function addslashes_gpc($data)
	{	/* add the escape \ that magic_quotes HTTP POST would add, " becomes \" and  '  becomes  \'  
		  but ONLY if magic_quotes is OFF, else we may *double* add slashes */
		if (get_magic_quotes_gpc()==1)
		{
			return $data;
		}
		else
		{
			return addslashes($data);
		}
	}

// ----  Functions PHP Should Have OR Functions From PHP4+ Backported to PHP3  ---------
	/*!
	@function is_serialized
	@abstract find out if something is already serialized
	@param $data could be almost anything
	*/
	function is_serialized($data)
	{
		/* not totally complete: currently works with strings, arrays, and booleans (update this if more is added) */
		
		 /* FUTURE: detect a serialized data that had addslashes appplied AFTER it was serialized
		 you can NOT unserialize that data until those post-serialization slashes are REMOVED */

		//echo 'is_serialized initial input [' .$data .']<br>';
		//echo 'is_serialized unserialized input [' .unserialize($data) .']<br>';

		if (is_array($data))
		{
			// arrays types are of course not serialized (at least not at the top level)
			// BUT there  may be serialization INSIDE in a sub part
			return False;
		}
		elseif ($this->is_bool_ex($data))
		{
			// a boolean type is of course not serialized
			return False;
		}
		elseif ((is_string($data))
		&& (($data == 'b:0;') || ($data == 'b:1;')) )
		{
			// check for easily identifiable serialized boolean values
			return True;
		}
		elseif ((is_string($data))
		&& (unserialize($data) == False))
		{
			// when you unserialize a normal (not-serialized) string, you get False
			return False;
		}
		elseif ((is_string($data))
		&& (ereg('^s:[0-9]+:"',$data) == True))
		{
			// identify pattern of a serialized string (that did NOT have slashes added AFTER serialization )
			return True;
		}
		elseif ((is_string($data))
		&& (is_array(unserialize($data))))
		{
			// if unserialization produces an array out of a string, it was serialized
			//(ereg('^a:[0-9]+:\{',$data) == True))  also could work
			return True;
		}
		//Best Guess - UNKNOWN / ERROR / NOY YET SUPPORTED TYPE
		elseif (is_string($data))
		{
			return True;
		}
		else
		{
			return False;
		}
	}

	/*!
	@function is_serialized_str
	@abstract find out if a string is already serialized, speed increases since string is known type
	@param $string_data SHOULD be a string, or else call "is_serialized()" instead
	*/
	function is_serialized_str($string_data)
	{
		if ((is_string($string_data))
		&& (unserialize($string_data) == False))
		{
			// when you unserialize a normal (not-serialized) string, you get False
			return False;
		}
		else
		{
			return True;
		}
	}

	// PHP3 SAFE Version of "substr_count"
	/*!
	@function substr_count_ex
	@abstract returns the number of times the "needle" substring occurs in the "haystack" string
	@param $haystack  string
	@param $needle  string
	*/
	function substr_count_ex($haystack='', $needle='')
	{
		if (floor(phpversion()) == 3)
		{
			if (($haystack == '') || ($needle == ''))
			{
				return 0;
			}

			$crtl_struct = Array();
			// how long is needle
			$crtl_struct['needle_len'] = strlen($needle);
			// how long is haystack before the replacement
			$crtl_struct['haystack_orig_len'] = strlen($haystack);
		
			// we will replace needle with a BLANK STRING
			$crtl_struct['haystack_new'] = str_replace("$needle",'',$haystack);
			// how long is the new haystack string
			$crtl_struct['haystack_new_len'] = strlen($crtl_struct['haystack_new']);
			// the diff in length between orig haystack and haystack_new diveded by len of needle = the number of occurances of needle
			$crtl_struct['substr_count'] = ($crtl_struct['haystack_orig_len'] - $crtl_struct['haystack_new_len']) / $crtl_struct['needle_len'];
		
			//echo '<br>';
			//var_dump($crtl_struct);
			//echo '<br>';
		
			// return the finding
			return $crtl_struct['substr_count'];
		}
		else
		{
			return substr_count($haystack, $needle);
		}
	}

	// PHP3 SAFE Version of "is_bool"
	/*!
	@function is_bool_ex
	@abstract Find out whether a variable is boolean
	@param $bool  mixed
	*/
	function is_bool_ex($bool)
	{
		if (floor(phpversion()) == 3)
		{
			// this was suggested in the user notes of the php manual
			// yes I know there are other ways, but for now this must work in .12 and devel versions
			return (gettype($bool) == 'boolean');
		}
		else
		{
			return is_bool($bool);
		}
	}
	
	// PHP3 and PHP<4.0.6 SAFE Version of "array_search"
	function array_search_ex($needle='', $haystack='', $strict=False)
	{
		if(!$haystack)
		{
			$haystack=array();
		}
		// error check
		if ((trim($needle) == '')
		|| (!$haystack)
		|| (count($haystack) == 0))
		{
			return False;
		}
		
		$finding = False;
		@reset($haystack);
		$i = 0;
		while(list($key,$value) = each($haystack))
		{
			//if ((string)$value == (string)$needle)
			if ((string)$haystack[$key] == (string)$needle)
			{
				$finding = $i;
				break;
			}
			else
			{
				$i++;
			}
		}
		return $finding;
	}


	/**************************************************************************\
	* USEFUL  AND   SIMPLE  HTML  FUNCTIONS	*
	\**************************************************************************/

	/* * * * * * * * * * *
	  *  href_maketag
	  *  will generate a typical A HREF html item
	  * * * * * * *  * * * */
	function href_maketag($href_link='',$href_text='default text')
	{
		return '<a href="' .$href_link .'">' .$href_text .'</a>' ."\n";
	}

	/* * * * * * * * * * *
	  *  img_maketag
	  *  will generate a typical IMG html item
	  * * * * * * *  * * * */
	function img_maketag($location='',$alt='',$height='',$width='',$border='')
	{
		$alt_default_txt = 'image';
		$alt_unknown_txt = 'unknown';
		if ($location == '')
		{
			return '<img src="" alt="['.$alt_unknown_txt.']">';
		}
		if ($alt != '')
		{
			$alt_tag = ' alt="['.$alt.']"';
		}
		else
		{
			$alt_tag = ' alt="['.$alt_default_txt.']"';
		}
		if ($height != '')
		{
			$height_tag = ' height="' .$height .'"';
		}
		else
		{
			$height_tag = '';
		}
		if ($width != '')
		{
			$width_tag = ' width="' .$width .'"';
		}
		else
		{
			$width_tag = '';
		}
		if ($border != '')
		{
			$border_tag = ' border="' .$border .'"';
		}
		else
		{
			$border_tag = '';
		}
		$image_html = '<img src="'.$location.'"' .$height_tag .$width_tag .$border_tag .$alt_tag .'>';
		return $image_html;
	}

  }
// end of class mail_msg
?>
