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
	var $dcom;
	var $args = Array();
	// data from $GLOBALS['phpgw_info']['user']['preferences']['email'] goes here
	var $prefs = Array();
	var $not_set = '-1';
	var $att_files_dir;
	var $folder_list = array();
	var $mailsvr_callstr = '';
	var $mailsvr_namespace = '';
	var $mailsvr_delimiter = '';
	// these are the supported menuaction strings
	var $index_menuaction = 'menuaction=email.uiindex.index';
	var $mlist_menuaction = 'menuaction=email.uiindex.mlist';
	var $action_menuaction = 'menuaction=email.boaction.action';
	// use this uri in any auto-refresh request - filled during "fill_sort_order_start_msgnum()"
	var $index_refresh_uri ='';
	// mailsvr_stream and mailsvr_account_username are also used to determine if we are logged in already
	// pointer (well, actually a data holder) to the primary mailbox stream (you may open others) returned by the first login 
	var $mailsvr_stream = '';
	// user name the we logged in as on the mailserver
	var $mailsvr_account_username = '';
	var $folder = '';
	var $newsmode = False;
	var $sort = '';
	var $order = '';
	var $start = '';
	var $msgnum = '';
	var $browser = 0;
	
	var $force_msg_uids = True;
	//var $force_msg_uids = False;
	
	// default, will be overridden by prefs "cache_data"
	// currently caches "mailsvr_namespace" and "get_folder_list" responses to the prefs DB
	var $cache_mailsvr_data = False;
	var $cachable_server_items = Array(
		0	=> 'match_cached_account',
		1	=> 'get_mailsvr_namespace',
		2	=> 'get_folder_list'
	);
	// DEBUG FLAGS generally take int 0, 1, 2, or 3
	var $debug_logins = 0;
	var $debug_caching = 0;
	
	//var $known_subtypes = array();

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
		
	}
	
	function is_logged_in($compare_account_username='')
	{
		if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: ENTERING'.'<br>';}
		// fallback value
		$finding = False;
		if ((isset($this->mailsvr_stream))
		&& ($this->mailsvr_stream != '')
		&& ($this->phpgw_ping() == True))
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: is_logged_in: mailsvr stream exists and passed ping test'.'<br>';}
			// at lease we have an existing stream open
			if ((isset($compare_account_username))
			&& ($compare_account_username != ''))
			{
				if ($this->debug_logins > 1) { echo 'mail_msg: is_logged_in: since arg $compare_account_username was specified we will attempt to match it'.'<br>';}
				// ALSO verify the username we are logged in as, IF a compare_account_username was passed as an arg
				if ($compare_account_username == $this->mailsvr_account_username)
				{
					if ($this->debug_logins > 1) { echo 'mail_msg: is_logged_in: MATCH compared ($compare_account_username == $this->mailsvr_account_username)'.'<br>';}
					$finding = True;
				}
				else
				{
					// stream is open but username does not match
					if ($this->debug_logins > 1) { echo 'mail_msg: is_logged_in: failed compare ($compare_account_username == $this->mailsvr_account_username)'.'<br>';}
					$finding = False;
				}
			}
			else
			{
				// we are asked only to check the existance of the stream, since no user name data was fed into here
				if ($this->debug_logins > 1) { echo 'mail_msg: is_logged_in: arg $compare_account_username NOT specified so we do NOT try to match it'.'<br>';}
				$finding = True;
			}
		}
		else
		{
			// no stream, no chance
			if ($this->debug_logins > 1) { echo 'mail_msg: is_logged_in: no previous mailsvr_stream exists'.'<br>';}
			$finding = False;
		}
		
		if ($this->debug_logins > 0) { echo 'mail_msg: is_logged_in: LEAVING, returning: '.serialize($finding).'<br>';}
		return $finding;
	}
	
	// ----  BEGIN request from Mailserver / Initialize This Mail Session  -----
	function begin_request($args_array)
	{
		// whether or not to attempt to reuse an existing mail_msg object's existing login/mailsvr_stream
		$attempt_reuse = True;	
		//$attempt_reuse = False;
		
		if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: ENTERING'.'<br>';}
		if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: local var attempt_reuse=['.serialize($attempt_reuse).']<br>'; }
		if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: feed var args_array[] dump:<pre>'; print_r($args_array); echo '</pre>'; }
		
		// ====  Already Logged In / Reuse Existing ?  =====
		if (($attempt_reuse == True)
		&& (isset($this->prefs['userid']))
		&& ($this->is_logged_in($this->prefs['userid']) == True))
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: attempt to reuse existing login'.'<br>'; }
			// we're already logged in, now...
			if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: class->args[] (dump BEFORE coming into this function)<pre>';  print_r($GLOBALS['phpgw']->msg->args); echo '</pre>'; }
			// clear what is leftr over in the class->args[] array from last request
			$this->args = array();
			// can not re-grab because original GPC values are still in POST and GET GLOBALS
			//$this->grab_class_args_gpc();
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: CLEARED (can not re-grab because original GPC values are still in GLOBALS)'.'<br>'; }
			// (2) we need to will fill class->args[] with data from feed var args_array
			// SECURITY CHECK ???? is it needed here, "bad" args_array could be passed ?
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: re-fill class->args with feed var args_array'.'<br>'; }
			while(list($key,$value) = each($args_array))
			{
				// "do_login" is never included as a class arg, it should only be specified here
				// and since we're already logged in, it's irrelevant here
				if (stristr($key, 'do_login') == False)
				{
					// put the raw data (value) for this particular arg into a local var
					$new_arg_value = $args_array[$key];
					// replace the previously existing class arg with this
					if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: fill class->rgs['.$key.'] with feed value ['.$new_arg_value.']'.'<br>'; }
					$this->args[$key] = $new_arg_value;
				}
			}
			if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: class->args[] dump (AFTER re-filling with feed data)<pre>';  print_r($GLOBALS['phpgw']->msg->args); echo '</pre>'; }
			// do we need to switch to a different folder ?
			if ($this->folder != $this->prep_folder_in($args_array['folder']))
			{
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: already loggedin but need to change (reopen) folder from ['.$this->folder.'] to this ['.$args_array['folder'].'] (name will be preped in)<br>';}
				$this->folder = $this->prep_folder_in($args_array['folder']);
				// switch to the desired folder now that we are sure we have it's official name
				$did_reopen = $this->dcom->reopen($this->mailsvr_stream, $this->mailsvr_callstr.$this->folder, '');
				  if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: already loggedin but reopening, reopen returns: '.serialize($did_reopen).'<br>';}
				// error check
				if ($did_reopen == False)
				{
					if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING reuse existing, folder reopen FAILED for mailsvr_stream to (mailsvr_callstr): '.$this->folder.'<br>';}
					return False;
				}
			}
			// if we get to here, we are going OK
			if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING reuse existing suceeded<br>';}
			return $this->mailsvr_stream;
		}
		
		// ===  we are here ONLY if creating NO OBJECT mail_msg exists  =====
		if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: NOT reusing an established logged-in stream-object, will create new'.'<br>'; }
		
		// ===== Not Already Logged In?  =====
		// ----  Things To Be Done Whether You Login Or Not  -----
		if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: PRE create_email_preferences GLOBALS[phpgw_info][user][preferences][email] dump:<pre>'; print_r($GLOBALS['phpgw_info']['user']['preferences']['email']) ; echo '</pre>';}
		// obtain the preferences from the database
		$GLOBALS['phpgw_info']['user']['preferences'] = $GLOBALS['phpgw']->preferences->create_email_preferences();
		/*
		// ----  stick prefs from GLOBALS into $this->prefs
		// array element $GLOBALS['phpgw_info']['user']['preferences']['email'] -goesto- $this->prefs
		$tmp_prefs = array();
		$tmp_prefs = $GLOBALS['phpgw']->preferences->create_email_preferences();
		$this->prefs = $tmp_prefs['email'];
		$tmp_prefs = array();
		*/
		// REFERENCE class var $this->prefs to $GLOBALS['phpgw_info']['user']['preferences']['email']
		$this->prefs = &$GLOBALS['phpgw_info']['user']['preferences']['email'];
		
		if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: POST create_email_preferences GLOBALS[phpgw_info][user][preferences][email] dump:<pre>'; print_r($GLOBALS['phpgw_info']['user']['preferences']['email']) ; echo '</pre>';}
		if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: POST create_email_preferences $this->prefs -reference- to GLOBALS[phpgw_info][user][preferences][email] dump:<pre>'; print_r($this->prefs) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: preferences->create_email_preferences called, GLOBALS[phpgw_info][user][preferences] dump:<pre>'; print_r($GLOBALS['phpgw_info']['user']['preferences']) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: preferences->create_email_preferences called, GLOBALS[phpgw_info][user] dump:<pre>'; print_r($GLOBALS['phpgw_info']['user']) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: preferences->create_email_preferences called, GLOBALS[phpgw_info] dump:<pre>'; print_r($GLOBALS['phpgw_info']) ; echo '</pre>';}
		//if ($this->debug_logins > 2) { echo 'mail_msg: begin_request: preferences->create_email_preferences called, GLOBALS[phpgw] dump:<pre>'; print_r($GLOBALS['phpgw']) ; echo '</pre>';}
		
		// set class var "$this->cache_mailsvr_data" based on prefs info
		if ((isset($this->prefs['cache_data']))
		&& ($this->prefs['cache_data'] != ''))
		{
			$this->cache_mailsvr_data = True;
		}
		else
		{
			$this->cache_mailsvr_data = False;
		}
		
		// Get Email Password
		if (!isset($this->prefs['passwd']))
		{
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: this->prefs[passwd] NOT set, fallback to $GLOBALS[phpgw_info][user][passwd]'.'<br>'; }
			// DO NOT alter the password and put that altered password BACK into the preferences array
			// why not? used to have a reason, but that was obviated, no reason at the moment
			//$this->prefs['passwd'] = $GLOBALS['phpgw_info']['user']['passwd'];
			$pass = $GLOBALS['phpgw_info']['user']['passwd'];
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: pass grabbed from GLOBALS[phpgw_info][user][passwd] = '.htmlspecialchars(serialize($pass)).'<br>'; }
		}
		else
		{
			// DO NOT alter the password and put that altered password BACK into the preferences array
			// keep the one in GLOBALS in encrypted form if possible
			//$this->prefs['passwd'] = $this->decrypt_email_passwd($this->prefs['passwd']);
			$pass = $this->decrypt_email_passwd($this->prefs['passwd']);
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: pass decoded from prefs: '.htmlspecialchars(serialize($pass)).'<br>'; }
		}
		// initalize some important class variables
		$this->att_files_dir = $GLOBALS['phpgw_info']['server']['temp_dir'].SEP.$GLOBALS['phpgw_info']['user']['sessionid'];
		$this->get_mailsvr_callstr();
		
		// make sure all the necessary args_array items are present, else set missing ones to a default value
		// ----  What "folder" arg was passed to the script  -----
		if (!isset($args_array['folder']))
		{
			$args_array['folder'] = '';
		}
		// ----  Should We Login  -----
		if (!isset($args_array['do_login']))
		{
			$args_array['do_login'] = False;
		}
		// ----  Are We In Newsmode Or Not  -----
		// note: this needs better handling in the future
		if ((isset($args_array['newsmode']))
		&& (($args_array['newsmode'] == True) || ($args_array['newsmode'] == "on")))
		{
			$args_array['newsmode'] = True;
			$this->newsmode = True;
			$this->prefs['mail_server_type'] = 'nntp';
		}
		else
		{
			$args_array['newsmode'] = False;
			$this->newsmode = False;
		}
		
		// Browser Detection =FUTURE=
		// 0 = NO css ; 1 = CSS supported ; 2 = text only
		// currently not implemented, use default 0 (NO CSS support in browser)
		$this->browser = 0;
		//$this->browser = 1;
		
		// ----  Things Specific To Loging In, and Actually Logging In  -----
		// $args_array['folder'] gets prep_folder_in and then is stored in class var $this->folder
		if ($args_array['do_login'] == True)
		{
			// === ISSET CHECK for userid and passwd to avoid garbage logins ==
			if ( (isset($this->prefs['userid']))
			&& ($this->prefs['userid'] != '')
			&& (isset($pass))
			&& ($pass != '') )
			{
				$user = $this->prefs['userid'];
				// we set pass up above, we no longer alter the pass and put it back intoi the prefs array
				//$pass = $this->prefs['passwd'];
			}
			else
			{
				// problem - invalid or nonexistant info for userid and/or passwd
				//if ($this->debug_logins > 0) {
					echo 'mail_msg: begin_request: ERROR: userid or passwd empty'."<br>\r\n"
						.' * * this->prefs[userid] = '
							.$this->prefs['userid']."<br>\r\n"
						.' * * if the userid is filled, then it must be the password that is missing'."<br>\r\n"
						.' * * tell your admin if a) you have a custom email password or not when reporting this error'."<br>\r\n";
				//}
				return False;
			}
		
			// Create email server Data Communication Class
			$this->dcom = CreateObject("email.mail_dcom",$this->prefs['mail_server_type']);
			// initialize the dcom class variables
			$this->dcom->mail_dcom_base();
			// ----  Do We Use UTF7 encoding/decoding of folder names  -----
			if (isset($this->prefs['enable_utf7'])
			&& ($this->prefs['enable_utf7']))
			{
				$this->dcom->enable_utf7 = True;
			}
			// ----  Do We Force use of msg UID's  -----
			if ($this->force_msg_uids == True)
			{
				$this->dcom->force_msg_uids = True;
			}
			
			set_time_limit(60);
			// login to INBOX because we know that always(?) should exist on an imap server
			// after we are logged in we can get additional info that will lead us to the desired folder (if not INBOX)
			//$server_str = $GLOBALS['phpgw']->msg->get_mailsvr_callstr();
			$server_str = $this->get_mailsvr_callstr();
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: about to call dcom->open'.'<br>'; }
			$this->mailsvr_stream = $this->dcom->open($server_str."INBOX", $user, $pass, '');
			$pass = '';
			if ($this->debug_logins > 1)
			{
				echo 'this->mailsvr_stream: '.serialize($this->mailsvr_stream).'<br>';
				//echo 'user = ' . $user . '; pass = ' . $pass . '<br>';
			}
			set_time_limit(0);
			
			// error check
			if (!$this->mailsvr_stream)
			{
				if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING with ERROR: failed to open mailsvr stream<br>';}
				return False;
			}
			
			// SUCCESS - we are logged in
			// BUT we may still fail to "reopen" if we need to go to a folder oither than INBOX
			$this->mailsvr_account_username = $user;
			
			// get some more info now that we are logged in
			// namespace is often obtained by directly querying the mailsvr
			$this->get_mailsvr_namespace();
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: this->mailsvr_namespace: '.$this->mailsvr_namespace.'<br>';}
			$this->get_mailsvr_delimiter();
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: this->mailsvr_delimiter: '.$this->mailsvr_delimiter.'<br>';}
			// make sure we have a useful folder name to log into
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: args_array[folder] before prep: '.$args_array['folder'].'<br>';}
			$this->folder = $this->prep_folder_in($args_array['folder']);
			if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: this->folder after prep: '.$this->folder.'<br>';}
			if ($this->folder != 'INBOX')
			{
				// switch to the desired folder now that we are sure we have it's official name
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: reopen mailsvr_stream to this->folder: '.$server_str.$this->folder.'<br>';}
				$did_reopen = $this->dcom->reopen($this->mailsvr_stream, $this->mailsvr_callstr.$this->folder, '');
				if ($this->debug_logins > 1) { echo 'mail_msg: begin_request: reopen returns: '.serialize($did_reopen).'<br>';}
				// error check
				if ($did_reopen == False)
				{
					if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING with ERROR: FAILED to reopen mailsvr_stream (change folders) to: '.$server_str.$this->folder.'<br>';}
					return False;
				}
			}
			// ----  Process "sort" "order" "start" and "msgnum" GPC args (if any) passed to the script  -----
			// these args are so fundamental, they get stored in their own class vars
			// no longer referenced as args after this
			// requires args saved to $this->args, only relevant if you login
			$this->fill_sort_order_start_msgnum();
			
			// now we have folder, sort and order, make a URI for auto-refresh use
			// we can NOT put "start" in auto refresh or user may not see the 1st index page on refresh
			$this->index_refresh_uri = 
				$this->index_menuaction
				.'&folder='.$this->prep_folder_out('')
				.'&sort='.$this->sort
				.'&order='.$this->order;
		}
		// anything not specific to logging in goes here (nothing I can think of)
		
		// ----  Things Again Specific To Loging In  -----
		if ($args_array['do_login'] == True)
		{
			if ($this->debug_logins > 0) { echo 'mail_msg: begin_request: LEAVING, success'.'<br>';}
			// returning this is vestigal, not really necessary, but do it anyway
			// it's importance is that it returns something other then "False" on success
			return $this->mailsvr_stream;
		}
	}
 
	function end_request($args_array='')
	{
		// args array currently not used
		if ((isset($this->mailsvr_stream))
		&& ($this->mailsvr_stream != ''))
		{
			$this->dcom->close($this->mailsvr_stream);
			$this->mailsvr_stream = '';
		}
	}

	/*
	// EXPERIMENTAL code
	function end_request($args_array='')
	{
		// args array recognized params:
		//	logging_out == True
		if ((isset($this->prefs['p_persistent']))
		&& ($this->prefs['p_persistent'] != ''))
		{
			$handle_persistent_prefs_data = True;
			if ((isset($args_array['logging_out']))
			&& ($args_array['logging_out'] == True))
			{
				$really_close_stream = True;
			}	
			else
			{
				$really_close_stream = False;
			}
		}
		else
		{
			$handle_persistent_prefs_data = False;
			$really_close_stream = True;
		}
		
		if (($really_close_stream == True)
		&& (isset($this->mailsvr_stream))
		&& ($this->mailsvr_stream != ''))
		{
			if ($handle_persistent_prefs_data == True)
			{
				// prefs are already open and available, were read on page view
				// DELETE persistent data to preferences DB
				// (a) integer representing the stream
				$GLOBALS['phpgw']->preferences->delete('email','p_mailsvr_stream');
				// (b) Fully Qualified Server Name, from Bracket to Bracket {mail.some.com/imap:143 }
				$GLOBALS['phpgw']->preferences->delete('email','p_mailsvr_callstr');
				// (c) username on that server we logged in as
				$GLOBALS['phpgw']->preferences->delete('email','p_mailsvr_account_username');
				// SAVE the repository
				$GLOBALS['phpgw']->preferences->save_repository();
			}
			$this->dcom->close($this->mailsvr_stream);
			$this->mailsvr_stream = '';
		}
		elseif (($really_close_stream == False)
		&& (isset($this->mailsvr_stream))
		&& ($this->mailsvr_stream != ''))
		{
			if ($handle_persistent_prefs_data == True)
			{
				// prefs are already open and available, were read on page view
				// we always first delete them from the prefs, then add them back with new values
				$GLOBALS['phpgw']->preferences->delete('email','p_mailsvr_stream');
				$GLOBALS['phpgw']->preferences->delete('email','p_mailsvr_callstr');
				$GLOBALS['phpgw']->preferences->delete('email','p_mailsvr_account_username');
				// SAVE persistent data to preferences DB
				// (a) integer representing the stream
				$persistent['p_mailsvr_stream'] = (string)$this->mailsvr_stream;
				$GLOBALS['phpgw']->preferences->add('email','p_mailsvr_stream',$persistent['p_mailsvr_stream']);
				// (b) Fully Qualified Server Name, from Bracket to Bracket {mail.some.com/imap:143 }
				$persistent['p_mailsvr_callstr'] = $this->get_mailsvr_callstr();
				$GLOBALS['phpgw']->preferences->add('email','p_mailsvr_callstr',$persistent['p_mailsvr_callstr']);
				// (c) username on that server we logged in as
				$persistent['p_mailsvr_account_username'] = $this->mailsvr_account_username;
				$GLOBALS['phpgw']->preferences->add('email','p_mailsvr_account_username',$persistent['p_mailsvr_account_username']);
				// SAVE the repository
				$GLOBALS['phpgw']->preferences->save_repository();
			}
		}
		else
		{
			// if we get here we were probably not asked to actually login during "begin_request"
		}
	}
	*/
	
	function match_cached_account()
	{
		if ($this->debug_caching > 0) { echo 'mail_msg: match_cached_account: ENTERING<br>'; }
		
		$my_function_name = 'match_cached_account';
		// quick check for failure
		if (($this->cache_mailsvr_data == True)
		&& (isset($this->prefs[$my_function_name]['mailsvr_account_username']))
		&& ($this->prefs[$my_function_name]['mailsvr_account_username'] != '')
		&& (isset($this->prefs[$my_function_name]['mailsvr_callstr']))
		&& ($this->prefs[$my_function_name]['mailsvr_callstr'] != ''))
		{
			$server = $this->get_mailsvr_callstr();
			if (($this->prefs[$my_function_name]['mailsvr_account_username'] == $this->mailsvr_account_username)
			&& ($this->prefs[$my_function_name]['mailsvr_callstr'] == $server))
			{
				if ($this->debug_caching > 0) { echo 'mail_msg: match_cached_account: LEAVING, successful match<br>'; }
				return True;
			}
			else
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: match_cached_account: caching disabled, OR cached data account/callstr do not match current session<br>'; }
				// remove the "match_cached_account" data ("mailsvr_callstr" and "mailsvr_account_username" used for cached mailaccount matching)
				if (isset($this->prefs[$my_function_name]))
				{
					if ($this->debug_caching > 1) { echo 'mail_msg: match_cached_account: remove "match_cached_account" data that appears to exist<br>'; }
					//$this->remove_cached_data($my_function_name);
					// if we do not provide $my_function_name, then we expire all "cachable_server_items"
					// which is probably a good idea, we do not want mismatched cached items
					$this->remove_cached_data('');
				}
				if ($this->debug_caching > 0) { echo 'mail_msg: match_cached_account: LEAVING, cached data account/callstr do not match current session<br>'; }
				return False;
			}
		}
		else
		{
			if ($this->debug_caching > 1) { echo 'mail_msg: match_cached_account: caching disabled, or NO cached data account/callstr do not match current session<br>'; }
			// remove the "match_cached_account" data ("mailsvr_callstr" and "mailsvr_account_username" used for cached mailaccount matching)
			if (isset($this->prefs[$my_function_name]))
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: match_cached_account: remove "match_cached_account" data that appears to exist<br>'; }
				//$this->remove_cached_data($my_function_name);
				// if we do not provide $my_function_name, then we expire all "cachable_server_items"
				// which is probably a good idea, we do not want mismatched cached items
				$this->remove_cached_data('');
			}
			if ($this->debug_caching > 0) { echo 'mail_msg: match_cached_account: LEAVING, comparitive data does not exist or caching disabled<br>'; }
			return False;
		}
	}
	
	function get_cached_data($calling_function_name='',$data_type='string')
	{
		if ($this->debug_caching > 0) { echo 'mail_msg: get_cached_data: ENTERING, called by "'.$calling_function_name.'"<br>';}
		
		$got_data = False;
		
		// preliminary compare userid and mailsvr callstr to that assicoated with cached data (if any)
		$account_match = $this->match_cached_account();
		
		if (($calling_function_name == '')
		|| ($this->cache_mailsvr_data == False)
		|| ($account_match == False))
		{
			if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: caching not enabled, or $calling_function_name was blank, or "match_cached_account" returned false<br>';}
			// we may not use cached data
			// if data IS cached, it should be considered STALE and deleted
			if ((isset($this->prefs[$calling_function_name]))
			&& ($this->prefs[$calling_function_name] != ''))
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
		if ((isset($this->prefs[$calling_function_name]))
		&& ($data_type == 'string')
		&& ($this->prefs[$calling_function_name] != ''))
		{
			if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: retrieving string data from cache<br>';}
			$got_data = $this->prefs[$calling_function_name];
		}
		elseif ((isset($this->prefs[$calling_function_name]))
		&& ($data_type == 'array')
		&& (count($this->prefs[$calling_function_name]) > 0))
		{
			if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: retrieving array data from cache<br>';}
			$got_data = Array();
			$got_data = $this->prefs[$calling_function_name];
		}
		elseif (isset($this->prefs[$calling_function_name]))
		{
			if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: no data type given, retrieve data anyway, from cache<br>';}
			$got_data = $this->prefs[$calling_function_name];
		}
		else
		{
			if ($this->debug_caching > 1) { echo 'mail_msg: get_cached_data: cached data NOT SET for $this->prefs['.$calling_function_name.'] <br>';}
			// return a boolean False
			//if ($this->debug_caching > 0) { echo 'mail_msg: get_cached_data: LEAVING, returning False<br>';}
			//return False;
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
			if ($this->debug_caching > 0) { echo 'mail_msg: get_cached_data: LEAVING, returning False, cached data was not set or was empty<br>';}
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
			if ((isset($this->prefs[$calling_function_name]))
			&& ($this->prefs[$calling_function_name] != ''))
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
			$server = $this->get_mailsvr_callstr();
			$match_cached_account = Array(
				'mailsvr_account_username'	=> $this->mailsvr_account_username,
				'mailsvr_callstr'		=> $server
			);
			if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: writting comparitive data for "match_cached_account"<br>';}
			if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: "match_cached_account" dump:<pre>'; print_r($match_cached_account); echo '</pre>'; }
			$GLOBALS['phpgw']->preferences->delete('email','match_cached_account');
			$GLOBALS['phpgw']->preferences->add('email','match_cached_account',$match_cached_account);
			// write do DB
			$GLOBALS['phpgw']->preferences->save_repository();
			// save repository *should* not alter our carefully constructed prefs array in $this->prefs[]
			// so we need to put the data there, next session start, when the prefs are initially read, then this data will automatically end up there
			if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: until next prefs read (on next session start), we need to manually put the data in our prefs array that is already in memory<br>';}
			$this->prefs[$calling_function_name] = $data;
			$this->prefs['match_cached_account'] = $match_cached_account;
			
			if ($this->debug_caching > 2) { echo 'mail_msg: set_cached_data: POST data write to $this->prefs['.$calling_function_name.']  data dump:<pre>'; print_r($this->prefs[$calling_function_name]); echo '</pre>'; }
			if ($this->debug_caching > 0) { echo 'mail_msg: set_cached_data: LEAVING, returning True<br>';}
			return True;
		}
		
		if ($this->debug_caching > 1) { echo 'mail_msg: set_cached_data: unexpectedly got past caching logic, nothing saved<br>';}
		if ($this->debug_caching > 0) { echo 'mail_msg: set_cached_data: LEAVING, returning False, unexpected, no action taken<br>'; }
		return False;
	}
	
	function remove_cached_data($calling_function_name='')
	{
		if ($this->debug_caching > 0) { echo 'mail_msg: remove_cached_data: ENTERING, data set: ['.$calling_function_name.'], if blank will remove all cachable_server_items<br>';}
		if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: about to remove .... <br>'; }
		if ($calling_function_name == '')
		{
			if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: no calling_function_name was provided, deleting ALL cachable_server_items<br>';}
			for ($i=0; $i<count($this->cachable_server_items);$i++)
			{
				$this_cachable_item_name = $this->cachable_server_items[$i];
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
				$clearing_needed = isset($this->prefs[$this_cachable_item_name]);
				if ($clearing_needed)
				{
					if ($this->debug_caching > 2) { echo 'mail_msg: remove_cached_data: ['.$this_cachable_item_name.'] until next prefs read (on next session start), we need to manually remove the data in our prefs array that is already in memory<br>';}
					$this->prefs[$this_cachable_item_name] = nil;
					unset($this->prefs[$this_cachable_item_name]);
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
			if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: removeing item based on "calling_function_name" arg<br>';}
			$GLOBALS['phpgw']->preferences->delete('email',$calling_function_name);
			$GLOBALS['phpgw']->preferences->save_repository();
			if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: until next prefs read (on next session start), we need to manually remove the data in our prefs array that is already in memory<br>';}
			$clearing_needed = isset($this->prefs[$calling_function_name]);
			if ($clearing_needed)
			{
				$this->prefs[$calling_function_name] = nil;
				unset($this->prefs[$calling_function_name]);
			}
			else
			{
				if ($this->debug_caching > 1) { echo 'mail_msg: remove_cached_data: prefs array that is already in memory did not have any data to remove<br>';}
			}
		}
		if ($this->debug_caching > 0) { echo 'mail_msg: remove_cached_data: LEAVING, returning true<br>';}
		return True;
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
		&& 	((($this->prefs['imap_server_type'] == 'UW-Maildir')
			|| ($this->prefs['imap_server_type'] == 'UWash'))) )
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
			$feed_folder = $this->folder;
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

	/* * * * * * * * * * *
	  *  get_mailsvr_callstr
	  * will generate the appropriate string to access a mail server of type
	  * pop3, pop3s, imap, imaps
	  * the returned string is the server call string from beginning bracker "{" to ending bracket "}"
	  * the returned string is the server call string from beginning bracker "{" to ending bracket "}"
	  *  Example:  {mail.yourserver.com:143}
	  * * * * * * *  * * * */
	function get_mailsvr_callstr()
	{
		// do we have cached data that we can use?
		if ($this->mailsvr_callstr != '')
		{
			// return the cached data
			return $this->mailsvr_callstr;
		}

		// what's the name or IP of the mail server
		$mail_server = $this->prefs['mail_server'];
				
		// determine the Mail Server Call String
		// construct the email server call string from the opening bracket "{"  to the closing bracket  "}"
		switch($this->prefs['mail_server_type'])
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
		$server_call = '{' .$mail_server .':' .$this->prefs['mail_port'] . $extra . '}';
			
		// cache the result
		$this->mailsvr_callstr = $server_call;
		//echo $server_call.'<br>';
		return $server_call;
	}

	/* * * * * * * * * * *
	  *  get_mailsvr_namespace
	  *  will generate the appropriate namespace (aka filter) string to access an imap mail server
	  *  Example: {mail.servyou.com:143}INBOX    where INBOX is the namespace
	  *  for more info see: see http://www.rfc-editor.org/rfc/rfc2342.txt
	  * * * * * * *  * * * */
	function get_mailsvr_namespace()
	{
		// UWash patched for Maildir style: $Maildir.Junque ?????
		// Cyrus and Courier style =" INBOX"
		// UWash style: "mail"

		// do we have cached data that we can use?
		if ($this->mailsvr_namespace != '')
		{
			// return the data cached in this class var
			return $this->mailsvr_namespace;
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
			$this->mailsvr_namespace = $cached_data;
			return $cached_data;
		}

		// no cached data of any kind we can use ...
		
		if (($this->prefs['imap_server_type'] == 'UW-Maildir')
		|| ($this->prefs['imap_server_type'] == 'UWash'))
		{
			if ((isset($this->prefs['mail_folder']))
			&& (trim($this->prefs['mail_folder']) != ''))
			{
				// if the user fills this option correctly, this should yield an unqualified foldername which
				// UWash should qualify (juat like any unix command line "cd" command) with the
				// appropriate $HOME variable (I THINK) ...
				// DO I NEED to add the "~" here too?
				$name_space = trim($this->prefs['mail_folder']);
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
		elseif ($this->prefs['imap_server_type'] == 'Cyrus')
		// ALSO works for Courier IMAP
		{
			$name_space = 'INBOX';
		}
		*/
		// ------- Dynamically Discover User's Private Namespace ---------
		elseif (isset($this->mailsvr_stream)
		&& ($this->mailsvr_stream != ''))
		{
			// existing "$this->mailsvr_stream" means we are logged in and can querey the server
			$server_str = $this->get_mailsvr_callstr();

			// a LIST querey with "%" returns the namespace of the current reference
			// in format {SERVER_NAME:PORT}NAMESPACE
			// also, it MAY (needs testing) return all available namespaces
			// however this is less useful if the IMAP server makes available shared folders and/or usenet groups
			// in addition to the users private mailboxes
			// see http://www.faqs.org/rfcs/rfc2060.html  section 6.3.8 (which is not entirely clear on this)
			$name_space = $this->dcom->listmailbox($this->mailsvr_stream, $server_str, '%');
			//echo 'list with percent sign arg returns: '.$this->htmlspecialchars_encode(serialize($name_space)).'<br>';

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
				$name_space = $this->ensure_no_brackets($name_space[0]);
			}
			elseif (is_string($name_space))
			{
				// if the server returns a string (not likely) just get rid of the brackets
				// note: do not use is_string() because php3 does not have it ???
				$name_space = $this->ensure_no_brackets($name_space);
			}
			else
			{
				// something really screwed up, EDUCATED GUESS
				// note: "INBOX" is NOT case sensitive according to rfc2060
				$name_space = 'INBOX';
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
		}

		//echo 'name_space='.$name_space.'<br>';
		// cache the result in a class var
		$this->mailsvr_namespace = $name_space;
		
		// -----------
		// SAVE DATA TO PREFS DB CACHE
		// -----------
		$my_function_name = 'get_mailsvr_namespace';
		$this->set_cached_data($my_function_name,'string',$name_space);

		return $name_space;
	}

	/* * * * * * * * * * *
	  *  get_mailsvr_delimiter
	  *  will generate the appropriate token that goes between the namespace and the inferior folders (subfolders)
	  *  Example: typical imap: "INBOX.Sent"  then the "." is the delimiter
	  *  Example: UWash imap (stock mbox)  "email/Sent"  then the "/" is the delimiter
	  * * * * * * *  * * * */
	function get_mailsvr_delimiter()
	{
		// UWash style: "/"
		// all other imap servers *should* be "."

		// do we have cached data that we can use?
		if ($this->mailsvr_delimiter != '')
		{
			// return the cached data
			return $this->mailsvr_delimiter;
		}

		if ($this->prefs['imap_server_type'] == 'UWash')
		{
			//$delimiter = '/';
			//$delimiter = SEP;
			// UWASH is a filesystem based thing, so the delimiter is whatever the system SEP is
			// unix = /  and win = \
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
		// cache the result
		$this->mailsvr_delimiter = $delimiter;
		return $delimiter;
	}

	function get_mailsvr_supports_folders()
	{
		// Does This Mailbox Support Folders (i.e. more than just INBOX)?
		if (($this->prefs['mail_server_type'] == 'imap')
		|| ($this->prefs['mail_server_type'] == 'imaps')
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
			$name_space = $this->get_mailsvr_namespace();
			$delimiter = $this->get_mailsvr_delimiter();
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
			$name_space = $this->get_mailsvr_namespace();
			$delimiter = $this->get_mailsvr_delimiter();
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
	@param $mailbox : DEPRECIATED - do not use
	@param $force_refresh : boolean, will cause any cached folder data to expire, and "fresh" data is retrieved from the mailserver
	@return : array : numbered, with each numbered element having array keys  "folder_long" and "folder_short"
	@discussion  returns a numbered array, each element has 2 properties, "folder_long" and "folder_short"
	so every available folder is in the structure in both long form [namespace][delimiter][foldername]
	and short form (does not have the [namespace][delimiter] prefix to the folder name)
	This function can cache data in 2 ways
	(1) caching as server data in the prefs DB cache department, and
	(2) in the class var $this->folder_list
	Data will be grabbed from cache when available and when allowed.
	*/
	function get_folder_list($mailbox, $force_refresh=False)
	{
		$debug_get_folder_list = 0;
		//$debug_get_folder_list = 1;
		//$debug_get_folder_list = 2;
		//$debug_get_folder_list = 3;
		
		if ($debug_get_folder_list > 0) { echo 'mail_msg: get_folder_list: ENTERING <br>'; }
		if ($debug_get_folder_list > 2) { echo 'mail_msg: get_folder_list: $this->folder_list dump:<pre>'; print_r($this->folder_list); echo '</pre>'; }
		
		if (!$mailbox)
		{
			$mailbox = $this->mailsvr_stream;
		}

		// check if class dcom reports that the folder list has changed
		if ((isset($this->dcom))
		&& ($this->dcom->folder_list_changed == True))
		{
			// class dcom recorded a change in the folder list
			// supposed to happen when create or delete mailbox is called
			// reset the changed flag
			$this->dcom->folder_list_changed = False;
			// set up for a force_refresh
			$force_refresh = True;
			if ($debug_get_folder_list > 1) { echo 'mail_msg: get_folder_list: class dcom report folder list changed<br>'; }
			if ($debug_get_folder_list > 1) { echo 'mail_msg: get_folder_list: make sure folder data is removed from cache <br>'; }
			$this->folder_list = Array();
			$my_function_name = 'get_folder_list';
			//$this->remove_cached_data($my_function_name);
			// if we do not provide $my_function_name, then we expire all "cachable_server_items"
			// which is probably a good idea, we do not want mismatched cached items
			$this->remove_cached_data('');
		}

		// see if we have object class var cached data that we can use
		if ((count($this->folder_list) > 0)
		&& ($force_refresh == False))
		{
			// use the cached data
			if ($debug_get_folder_list > 0) { echo 'mail_msg: get_folder_list: LEAVING,  using object cached folder list data<br>'; }
			return $this->folder_list;
		}
		elseif (($this->prefs['mail_server_type'] == 'pop3')
		|| ($this->prefs['mail_server_type'] == 'pop3s'))
		{
			// normalize the folder_list property
			$this->folder_list = Array();
			// POP3 servers have 1 folder: INBOX
			$this->folder_list[0]['folder_long'] = 'INBOX';
			$this->folder_list[0]['folder_short'] = 'INBOX';
			if ($debug_get_folder_list > 0) { echo 'mail_msg: get_folder_list: LEAVING,  pop3 servers only have one folder: INBOX<br>'; }
			return $this->folder_list;
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
				if ($debug_get_folder_list > 1) { echo 'mail_msg: get_folder_list: using *Prefs DB* cached folder list data<br>';}
				if ($debug_get_folder_list > 1) { echo 'mail_msg: get_folder_list: setting object var $this->folder_list to hold list data<br>';}
				// cache the result in a class object var
				$this->folder_list = $cached_data;
				if ($debug_get_folder_list > 0) { echo 'mail_msg: get_folder_list: LEAVING, got data from cache<br>'; }
				return $this->folder_list;
			}
			else
			{
				if ($debug_get_folder_list > 1) { echo 'mail_msg: get_folder_list: NO cached folder list data, fallback to get data from mailserver<br>';}
			}
		}
		
		// if we get here we must actually get the data
		// otherwise we would have return/broke out of this function
		// only IF statement above that allows code to reach here is if we are allowed to use
		// cached data, BUT none exists
		
		{
			// Establish Email Server Connectivity Information
			$server_str = $this->get_mailsvr_callstr();
			$name_space = $this->get_mailsvr_namespace();
			$delimiter = $this->get_mailsvr_delimiter();

			// get a list of available folders from the server
			if ($this->prefs['imap_server_type'] == 'UWash')
			{
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
				$mailboxes = $this->dcom->listmailbox($mailbox, $server_str, "$name_space" ."$delimiter" ."*");
				// UWASH IMAP returns information in this format:
				// {SERVER_NAME:PORT}FOLDERNAME
				// example:
				// {some.server.com:143}Trash
				// {some.server.com:143}Archives/Letters
			}
			else
			{
				// handle non-UWash IMAP servers, i.e. not using filesystem slash as the "delimiter"
				// the last arg is typically "INBOX*" (no dot) which DOES include the inbox in the list of folders
				// wheres adding the delimiter "INBOX.*" (has dot) will NOT include the INBOX in the list of folders
				// so - it's safe to include the delimiter here, but the INBOX will not be included in the list
				// this is typically the ONLY TIME you would ever *not* use the delimiter between the namespace and what comes after it
				//$mailboxes = $this->dcom->listmailbox($mailbox, $server_str, "$name_space" ."*");
				// UPDATED information of this issue: to get shared folders included in the return, better NOT include the "." delimiter
				// example: Cyrus does not like anything but a "*" as the pattern IF you want shared folders returned.
				$mailboxes = $this->dcom->listmailbox($mailbox, $server_str, "*");
				// returns information in this format:
				// {SERVER_NAME:PORT} NAMESPACE DELIMITER FOLDERNAME
				// example:
				// {some.server.com:143}INBOX
				// {some.server.com:143}INBOX.Trash
			}

			//echo 'raw mailbox list:<br>'.htmlspecialchars(serialize($mailboxes)).'<br>';

			// ERROR DETECTION
			if (!$mailboxes)
			{
				// we got no information back, clear the folder_list property
				// normalize the folder_list property
				$this->folder_list = Array();
				// *assume* (i.e. pretend)  we have a server with only one box: INBOX
				$this->folder_list[0]['folder_long'] = 'INBOX';
				$this->folder_list[0]['folder_short'] = 'INBOX';
				if ($debug_get_folder_list > 1) { echo 'mail_msg: get_folder_list: error, no mailboxes returned from server, fallback to "INBOX" as only folder, set class var $this->folder_list to hold that value<br>'; }
				if ($debug_get_folder_list > 0) { echo 'mail_msg: get_folder_list: LEAVING, with error, no mailboxes returned from server<br>'; }
				return $this->folder_list;
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
				// use the same "fully qualified" folder name format that "listmailbox" returns, includes the {serverName:port}
				$add_inbox = $server_str.'INBOX';
				$next_available = count($mailboxes);
				// add it to the $mailboxes array
				$mailboxes[$next_available] = $add_inbox;
			}

			// sort folder names
			// note: do not use is_array() because php3 does not have it
			if (is_array($mailboxes))
			{
				sort($mailboxes);
			}

			// normalize the folder_list property
			$this->folder_list = Array();

			// make the folder_list array structure
			for ($i=0; $i<count($mailboxes);$i++)
			{
				// "is_imap_folder" really just a check on what UWASH imap returns, may be files that are not MBOX's
				if ($this->is_imap_folder($mailboxes[$i]))
				{
					//$this->folder_list[$i]['folder_long'] = $this->get_folder_long($mailboxes[$i]);
					// what we (well, me, Angles) calls a "folder long" is the raw data returned from the server (fully qualified name)
					// MINUS the bracketed server, so we are calling "folder long" a NAMESPACE_DELIMITER_FOLDER string
					// WITHOUT the {serverName:port} part, if that part is included we (Angles) call this "fully qualified"
					$next_idx = count($this->folder_list);
					$this->folder_list[$next_idx]['folder_long'] = $this->ensure_no_brackets($mailboxes[$i]);
					$this->folder_list[$next_idx]['folder_short'] = $this->get_folder_short($mailboxes[$i]);
				}
			}

			// -----------
			// SAVE DATA TO PREFS DB CACHE
			// -----------
			$my_function_name = 'get_folder_list';
			$this->set_cached_data($my_function_name,'array',$this->folder_list);
			
			// finished, return the folder_list array atructure
			if ($debug_get_folder_list > 0) { echo 'mail_msg: get_folder_list: LEAVING, got folder data from server<br>'; }
			return $this->folder_list;
		}
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
	function folder_lookup($mailbox, $folder_needle='INBOX')
	{
		if ((!$mailbox)
		|| ($mailbox == ''))
		{
			$mailbox = $this->mailsvr_stream;
		}

		$folder_list = $this->get_folder_list($mailbox);

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
		if ($this->prefs['imap_server_type'] != 'UWash')
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
		$the_namespace = $this->get_mailsvr_namespace();
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
	@abstract encrypt data passed to the function
	@param $data data string to be encrypted
	@discussion: if mcrypt is not enabled, then the password data should be unmolested thru the 
	crypto functions, i.e. do not alter the string if mcrypt will not be preformed on that string.
	*/
	function encrypt_email_passwd($data)
	{
		if ($GLOBALS['phpgw_info']['server']['mcrypt_enabled'] && extension_loaded('mcrypt'))
		{
			$encrypted_passwd = $data;
			// this will return a string that has
			// (1) been serialized
			// (2) had addslashes applied, and
			// (3) been encrypted with mcrypt (assuming mcrypt is enabled and working)
			$cryptovars[0] = md5($GLOBALS['phpgw_info']['server']['encryptkey']);
			$cryptovars[1] = $GLOBALS['phpgw_info']['server']['mcrypt_iv'];
			$crypto = CreateObject('phpgwapi.crypto', $cryptovars);
			//$encrypted_passwd = $crypto->encrypt($encrypted_passwd);
			$encrypted_passwd = $crypto->encrypt_mail_pass($encrypted_passwd);
		}
		else
		{
			// ***** STRIP SLASHES BEFORE CALLING THIS FUNCTION !!!!!!! ????? ******
			// we have no way of knowing if it's necessary, but you do, you who call this function
			// well, it appears stripslashes_gpc being called before this function, so comment out
			//$encrypted_passwd = $this->stripslashes_gpc($encrypted_passwd);
			$encrypted_passwd = $data;
			//if ($this->is_serialized($encrypted_passwd))
			if ($this->is_serialized_str($encrypted_passwd))
			{
				$encrypted_passwd = unserialize($encrypted_passwd);
			}
			// html_quotes_encode is used here ONLY as a lame way do make this string friendly to the 
			// preferences database. Slashes and quotes can wipe out much data in the prefs DB if
			// they are not "de-fanged" by changing them into something else.
			$encrypted_passwd = $this->html_quotes_encode($encrypted_passwd);
		}
		return $encrypted_passwd;
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
			/*
			@capability: Upgrade Routine for 0.9.12 and earlier Custom Passwords
			@discussion: 
			the phpgw versions prior to and including 0.9.12 *may* have double or even tripple serialized
			passwd strings stored in their preferences table. SO:
			(1) check for this
			(2) unserialize to the real string
			(3) feed the unserialized / fixed passwd in the prefs class and save the "upgraded" passwd
			*/
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

	// DEPRECIATED -- TO BE  REMOVED
	// ---  should not be used, this is taken care of in create_email_preferences  -----
	function get_email_passwd()
	{
		$tmp_prefs = $GLOBALS['phpgw']->preferences->read();

		if (!isset($tmp_prefs['email']['passwd']))
		{
			return $GLOBALS['phpgw_info']['user']['passwd'];
		}
		else
		{
			return $this->decrypt_email_passwd($tmp_prefs['email']['passwd']);
		}
	}

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
		//return $this->dcom->base64($text);
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
