<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$d1 = strtolower(substr(APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	if(floor(phpversion()) == 4)
	{
		global $phpgw, $phpgw_info, $PHP_SELF;  // This was a problem for me (author unknown).
	}

	// Changed by Milosch on 3-26-2001
	// Its better then them using a ton of PHP errors.
	// This check was not working, and the code progressed to giving stream pointer errors
	// From the msg_imap class.  I tried to clean it up here so I could see what was happening.
	// -- (obviously, PHP_SELF is the built-in php variable = "filename on the currently executing script") --
	if (!$PHP_SELF)
	{
		// This was a problem for me (author unknown)
		global $PHP_SELF;
	}

// ----  Turn Off Magic Quotes Runtime    -----
	// magic_quotes_runtime (handles slashes when communicating with databases). PHP MANUAL:
	/*  If magic_quotes_runtime is enabled, most functions that return data from any sort of 
	  external source including databases and text files will have quotes escaped with a backslash. */
	set_magic_quotes_runtime(0);


// ----  == IS IT OK TO LOGIN To Mailserver ==  -----
	//$debug_logins = True;
	$debug_logins = False;
	
	// OK TO LOGIN pre-conditions
	// were we called from the main screen (user's home page)
	if (strstr($GLOBALS['phpgw_info']['server']['versions']['phpgwapi'], '0.9.12'))
	{
		// user's welcome page was called "index.php" in ver 0.9.12
		$in_mainscreen = eregi("^.*\/index\.php.*$",$PHP_SELF);
	}
	else
	{
		// in version 0.9.13 (current devel) users welcome page is "home.php"
		$in_mainscreen = eregi("^.*\/home\.php.*$",$PHP_SELF);
	}
	// were we in a typical email session
	$in_email = eregi("^.*\/email\/.*$",$PHP_SELF);
	
	// DO NOT LOGIN for these conditions  --------
	$login_allowed = True; // initialize
	$no_login_check = Array();
	// these files do not require login to email server
	$no_login_check[0] = "preferences\.php";
	$no_login_check[1] = "attach_file\.php";
	$no_login_check[2] = "addressbook\.php";
	for ($i=0; $i<count($no_login_check); $i++)
	{
		$match_this = $no_login_check[$i];
		if (eregi("^.*\/email\/$match_this.*$",$PHP_SELF))
		{
			$login_allowed = False;
			break;
		}
	}

	// MORE Login Restrictions That Need Work - Disabled for now
	// send_message needs to access the mailserver to get parts sometimes, can't limit this here
	// AND ALSO  Do Not Login - if sent message will NOT be put in the "Sent" folder
	//if ( (eregi("^.*\/email\/send_message\.php.*$",$PHP_SELF))
	//&& ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] != 'imap')
	//&& ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] != 'imaps') )
	//{
	//	$login_allowed = False;
	//}

	/* // FINE TUNE THIS - TOO BROAD
	// AND ALSO  Do Not Login - if composing message when server is not IMAP/IMAPS
	if ( (eregi("^.*\/email\/compose\.php.*$",$PHP_SELF))
	&& ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] != 'imap')
	&& ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] != 'imaps') )
	{
		$login_allowed = False;
	}
	*/

	/*
	if ($debug_logins)
	{
		echo '<br>';
		echo 'PHP_SELF='.$PHP_SELF.'<br>';
		echo 'phpgw_info[server][webserver_url]='.$GLOBALS['phpgw_info']['server']['webserver_url'].'<br>';
		echo 'in_mainscreen='.serialize($in_mainscreen).'<br>';
		echo 'in_email='.serialize($in_email).'<br>';
		echo 'login_allowed='.serialize($login_allowed).'<br>';
		echo 'folder='.$folder.'<br>';
		echo 'get_mailsvr_callstr='.$GLOBALS['phpgw']->msg->get_mailsvr_callstr().'<br>';
		echo 'get_folder_long='.$GLOBALS['phpgw']->msg->get_folder_long($folder).'<br>';
	}
	*/

/*
// ----  INSTRUCTIONS:   -------
 1: create an instance of the mail_msg class
 2: (optional) if you want to pass some GPC type args to the class, put then in the $phpgw->msg->args[] array
 3: create an array (example: $args_array[]) to be the sole argument to "begin_request()"
 4: there is 1 needed and 2 optional params:
 	"do_login" : boolean (necessary)
	"folder" : string (defaults to 'INBOX' if not supplied)
	"newsmode" : boolean (NOT YET IMPLEMENTED - DOES NOTHING)
 5: call "begin_request" with that args array as such:
 	$GLOBALS['phpgw']->msg->begin_request($args_array);

 Simple Example:
	$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
	$args_array = Array();
	$args_array['folder'] = $folder;
	$args_array['do_login'] = True;
	$GLOBALS['phpgw']->msg->begin_request($args_array);

 6: when you are done, do this:
 	$GLOBALS['phpgw']->msg->end_request('');
*/

// ----  Create the base email Msg Class    -----
	$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");

// ----  HANDLE SET PREFERENCE GPC ARGS  -------
	// setting prefs does not require a login, in fact you may not be able to login until you set
	// some basic prefs, so it makes sence to handle that here
	if (isset($submit_prefs))
	{
		$GLOBALS['phpgw']->msg->args['submit_prefs'] = $submit_prefs;
		if (isset($email_sig))
		{
			$GLOBALS['phpgw']->msg->args['email_sig'] = $email_sig;
			$email_sig = '';
			//unset($email_sig);
		}
		if (isset($default_sorting))
		{
			$GLOBALS['phpgw']->msg->args['default_sorting'] = $default_sorting;
			$default_sorting = '';
			//unset($default_sorting);
		}
		if (isset($layout))
		{
			$GLOBALS['phpgw']->msg->args['layout'] = $layout;
			$layout = '';
			//unset($layout);
		}
		if (isset($show_addresses))
		{
			$GLOBALS['phpgw']->msg->args['show_addresses'] = $show_addresses;
			$show_addresses = '';
			//unset($show_addresses);
		}
		if (isset($mainscreen_showmail))
		{
			$GLOBALS['phpgw']->msg->args['mainscreen_showmail'] = $mainscreen_showmail;
			$mainscreen_showmail = '';
			//unset($mainscreen_showmail);
		}
		if (isset($use_sent_folder))
		{
			$GLOBALS['phpgw']->msg->args['use_sent_folder'] = $use_sent_folder;
			$use_sent_folder = '';
			//unset($use_sent_folder);
		}
		if (isset($use_trash_folder))
		{
			$GLOBALS['phpgw']->msg->args['use_trash_folder'] = $use_trash_folder;
			$use_trash_folder = '';
			//unset($use_trash_folder);
		}
		if (isset($trash_folder_name))
		{
			$GLOBALS['phpgw']->msg->args['trash_folder_name'] = $trash_folder_name;
			$trash_folder_name = '';
			//unset($trash_folder_name);
		}
		if (isset($sent_folder_name))
		{
			$GLOBALS['phpgw']->msg->args['sent_folder_name'] = $sent_folder_name;
			$sent_folder_name = '';
			//unset($sent_folder_name);
		}
		if (isset($enable_utf7)) {
			$GLOBALS['phpgw']->msg->args['enable_utf7'] = $enable_utf7;
			$enable_utf7 = '';
			//unset($enable_utf7);
		}
		if (isset($use_custom_settings))
		{
			$GLOBALS['phpgw']->msg->args['use_custom_settings'] = $use_custom_settings;
			$use_custom_settings = '';
			//unset($use_custom_settings);
		}
		if (isset($userid))
		{
			$GLOBALS['phpgw']->msg->args['userid'] = $userid;
			$userid = '';
			//unset($userid);
		}
		if (isset($passwd))
		{
			$GLOBALS['phpgw']->msg->args['passwd'] = $passwd;
			$passwd = '';
			//unset($passwd);
		}
		if (isset($address))
		{
			$GLOBALS['phpgw']->msg->args['address'] = $address;
			$address = '';
			//unset($address);
		}
		if (isset($mail_server))
		{
			$GLOBALS['phpgw']->msg->args['mail_server'] = $mail_server;
			$mail_server = '';
			//unset($mail_server);
		}
		if (isset($mail_server_type))
		{
			$GLOBALS['phpgw']->msg->args['mail_server_type'] = $mail_server_type;
			$mail_server_type = '';
			//unset($mail_server_type);
		}
		if (isset($imap_server_type))
		{
			$GLOBALS['phpgw']->msg->args['imap_server_type'] = $imap_server_type;
			$imap_server_type = '';
			//unset($imap_server_type);
		}
		if (isset($mail_folder))
		{
			$GLOBALS['phpgw']->msg->args['mail_folder'] = $mail_folder;
			$mail_folder = '';
			//unset($mail_folder);
		}
		// now unset the GPC var
		$submit_prefs = '';
		//unset($submit_prefs);
	}

	// UNKNOWN if this is still used
//	$args_array['totalerrors'] = $totalerrors;
//	$args_array['errors'] = $errors;

// ----  CONNECT TO MAILSERVER - IF IT'S OK  -------
	if ((($in_email) || ($in_mainscreen))
	&& ($login_allowed))
	{
		if ($debug_logins) {  echo 'CALL TO LOGIN IN FUNCTIONS.INC.PHP'.'<br>'.'userid='.$GLOBALS['phpgw_info']['user']['preferences']['email']['userid']; }


		// ----  Create the base email Msg Class    -----
		//$phpgw->msg = CreateObject("email.mail_msg");

		// === SORT/ORDER/START === 
		// if sort,order, and start are sometimes passed as GPC's, if not, default prefs are used
		if (isset($sort))
		{
			$GLOBALS['phpgw']->msg->args['sort'] = $sort;
			//$args_array['sort'] = $sort;
			//unset($sort);
		}
		if (isset($order))
		{
			$GLOBALS['phpgw']->msg->args['order'] = $order;
			//$args_array['order'] = $order;
			//unset($order);
		}
		if (isset($start))
		{
			$GLOBALS['phpgw']->msg->args['start'] = $start;
			//$args_array['start'] = $start;
			//unset($start);
		}

		// this newsmode thing needs to be further worked out
		if (isset($newsmode))
		{
			$GLOBALS['phpgw']->msg->args['newsmode'] = $newsmode;
			//$args_array['newsmode'] = $newsmode;
			//unset($newsmode);
		}

		// === REPORT ON MOVES/DELETES ===
		// ----  td, tm: integer  ----
		// ----  tf: string  ----
		// USAGE:
		//	 td = total deleted ; tm = total moved, tm used with tf, folder messages were moved to
		// (outgoing) action.php: when action on a message is taken, report info is passed in these
		// (in) index.php: here the report is diaplayed above the message list, used to give user feedback
		if (isset($td))
		{
			$GLOBALS['phpgw']->msg->args['td'] = $td;
			//$args_array['td'] = $td;
			//unset($td);
		}
		if (isset($tm))
		{
			$GLOBALS['phpgw']->msg->args['tm'] = $tm;
			//$args_array['tm'] = $tm;
			//unset($tm);
		}
		if (isset($tf))
		{
			$GLOBALS['phpgw']->msg->args['tf'] = $tf;
			//$args_array['tf'] = $tf;
			//unset($tf);
		}

		// === MOVE/DELETE MESSAGE INSTRUCTIONS ===
		// ----  what: string ----
		// USAGE: 
		// (outgoing) index.php: "move", "delall"
		//	used with msglist (see below) an array (1 or more) of message numbers to move or delete
		// (outgoing) message.php: "delete" used with msgnum (see below) what individual message to delete
		// (in) action.php: instruction on what action to preform on 1 or more message(s) (move or delete)
		if (isset($what))
		{
			$GLOBALS['phpgw']->msg->args['what'] = $what;
			//$args_array['what'] = $what;
			//unset($what);
		}
		if (isset($tofolder))
		{
			$GLOBALS['phpgw']->msg->args['tofolder'] = $tofolder;
			//$args_array['tofolder'] = $tofolder;
			//unset($tofolder);
		}
		// (passed from index.php) this may be an array of numbers if many boxes checked and a move or delete is called
		if (isset($msglist))
		{
			$GLOBALS['phpgw']->msg->args['msglist'] = $msglist;
			//$args_array['msglist'] = $msglist;
			//unset($msglist);
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
		if (isset($action))
		{
			$GLOBALS['phpgw']->msg->args['action'] = $action;
			//$args_array['action'] = $action;
			//unset($action);
		}

		// === MESSAGE NUMBER AND MIME PART REFERENCES ===
		// msgnum: integer 
		// USAGE:
		// (a) action.php, called from from message.php: used with "what=delete" to indicate a single message for deletion
		// (b) compose.php: indicates the referenced message for reply, replyto, and forward handling
		// (c) get_attach.php: the msgnum of the email that contains the desired body part to get
		if (isset($msgnum))
		{
			$GLOBALS['phpgw']->msg->args['msgnum'] = $msgnum;
			//$args_array['msgnum'] = $msgnum;
			//unset($msgnum);
		}
		// ----  part_no: string  ----
		// representing a specific MIME part number (example "2.1.2") within a multipart message
		// (a) compose.php: used in combination with msgnum
		// (b) get_attach.php: used in combination with msgnum
		if (isset($part_no))
		{
			$GLOBALS['phpgw']->msg->args['part_no'] = $part_no;
			//$args_array['part_no'] = $part_no;
			//unset($part_no);
		}
		// ----  encoding: string  ----
		// USAGE: "base64" "qprint"
		// (a) compose.php: if replying to, we get the body part to reply to, it may need to be un-qprint'ed
		// (b) get_attach.php: appropriate decoding of the part to feed to the browser 
		if (isset($encoding))
		{
			$GLOBALS['phpgw']->msg->args['encoding'] = $encoding;
			//$args_array['encoding'] = $encoding;
			//unset($encoding);
		}
		// ----  fwd_proc: string  ----
		// USAGE: "encapsulation", "pushdown (not yet supported 9/01)"
		// (outgoing) message.php much detail is known about the messge, there the forward proc method is determined
		// (a) compose.php: used with action = forward, (outgoing) passed on to send_message.php
		// (b) send_message.php: used with action = forward, instructs on how the SMTP message should be structured
		if (isset($fwd_proc))
		{
			$GLOBALS['phpgw']->msg->args['fwd_proc'] = $fwd_proc;
			//$args_array['fwd_proc'] = $fwd_proc;
			//unset($fwd_proc);
		}
		// ----  name, type, subtype: string  ----
		// the name, mime type, mime subtype of the attachment
		// this info is passed to the browser to help the browser know what to do with the part
		// (outgoing) message.php: "name" is set in the link to the addressbook,  it's the actual "personal" name part of the email address
		// get_attach.php: the name of the attachment
		if (isset($name))
		{
			$GLOBALS['phpgw']->msg->args['name'] = $name;
			//$args_array['name'] = $name;
			//unset($name);
		}
		if (isset($type))
		{
			$GLOBALS['phpgw']->msg->args['type'] = $type;
			//$args_array['type'] = $type;
			//unset($type);
		}
		if (isset($subtype))
		{
			$GLOBALS['phpgw']->msg->args['subtype'] = $subtype;
			//$args_array['subtype'] = $subtype;
			//unset($subtype);
		}

		// === FOLDER ADD/DELETE/RENAME & DISPLAY ===
		// ----  "target_folder" , "source_folder" (source used in renaming only)  ----
		// (outgoing) and (in) folder.php: used with "action" to add/delete/rename a mailbox folder
		// 	where "action" can be: create, delete, rename, create_expert, delete_expert, rename_expert
		if (isset($target_folder))
		{
			$GLOBALS['phpgw']->msg->args['target_folder'] = $target_folder;
			//$args_array['target_folder'] = $target_folder;
			//unset($target_folder);
		}
		if (isset($source_folder))
		{
			$GLOBALS['phpgw']->msg->args['source_folder'] = $source_folder;
			//$args_array['source_folder'] = $source_folder;
			//unset($source_folder);
		}
		// ----  show_long: unset / true  ----
		// folder.php: set there and sent back to itself
		// if set - indicates to show 'long' folder names with namespace and delimiter NOT stripped off
		if (isset($show_long))
		{
			$GLOBALS['phpgw']->msg->args['show_long'] = $show_long;
			//$args_array['show_long'] = $show_long;
			//unset($show_long);
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
		if (isset($to))
		{
			$GLOBALS['phpgw']->msg->args['to'] = $to;
			//$args_array['to'] = $to;
			//unset($to);
		}
		if (isset($cc))
		{
			$GLOBALS['phpgw']->msg->args['cc'] = $cc;
			//$args_array['cc'] = $cc;
			//unset($cc);
		}
		if (isset($body))
		{
			$GLOBALS['phpgw']->msg->args['body'] = $body;
			//$args_array['body'] = $body;
			// body GPC var may be huge, so set it to empty for memory management purposes
			$body = '';
			//unset($body);
		}
		if (isset($subject))
		{
			$GLOBALS['phpgw']->msg->args['subject'] = $subject;
			//$args_array['subject'] = $subject;
			//unset($subject);
		}
		// ----  attach_sig: set-True/unset  ----
		// USAGE:
		// (outgoing) compose.php: if checkbox attach sig is checked, this is passed as GPC var to sent_message.php
		// (in) send_message.php: indicate if message should have the user's "sig" added to the message
		if (isset($attach_sig))
		{
			$GLOBALS['phpgw']->msg->args['attach_sig'] = $attach_sig;
			//$args_array['attach_sig'] = $attach_sig;
			//unset($attach_sig);
		}
		// ----  msgtype: string  ----
		// USAGE:
		// flag to tell phpgw to invoke "special" custom processing of the message
		// 	extremely rare, may be obsolete (not sure), most implementation code is commented out
		// (outgoing) currently NO page actually sets this var
		// (a) send_message.php: will add the flag, if present, to the header of outgoing mail
		// (b) message.php: identify the flag and call a custom proc
		if (isset($msgtype))
		{
			$GLOBALS['phpgw']->msg->args['msgtype'] = $msgtype;
			//$args_array['msgtype'] = $msgtype;
			//unset($msgtype);
		}

		// === MAILTO URI SUPPORT ===
		// ----  mailto: unset / ?set?  ----
		// USAGE:
		// (in and out) compose.php: support for the standard mailto html document mail app call
		// 	can be used with the typical compose vars (see above)
		//	indicates that to, cc, and subject should be treated as simple MAILTO args
		if (isset($mailto))
		{
			$GLOBALS['phpgw']->msg->args['mailto'] = $mailto;
			//$args_array['mailto'] = $mailto;
			//unset($mailto);
		}
		if (isset($personal))
		{
			$GLOBALS['phpgw']->msg->args['personal'] = $personal;
			//$args_array['personal'] = $personal;
			//unset($personal);
		}

		// === MESSAGE VIEWING MODS ===
		// ----  no_fmt: set-True/unset  ----
		// USAGE:
		// (in and outgoing) message.php: will display plain body parts without any html formatting added
		if (isset($no_fmt))
		{
			$GLOBALS['phpgw']->msg->args['no_fmt'] = $no_fmt;
			//$args_array['no_fmt'] = $no_fmt;
			//unset($no_fmt);
		}


		// === VIEW HTML INSTRUCTIONS ===
		if (isset($html_part))
		{
			$GLOBALS['phpgw']->msg->args['html_part'] = $html_part;
			//$args_array['html_part'] = $html_part;
			// this is a pre-processed string passes from a posted form, may be really big
			// so set to empty for memory management purposes now
			$html_part = '';
			//unset($html_part);
		}
		if (isset($html_reference))
		{
			$GLOBALS['phpgw']->msg->args['html_reference'] = $html_reference;
			//$args_array['html_reference'] = $html_reference;
			//unset($html_reference);
		}

		// === FOLDER STATISTICS - CALCULATE TOTAL FOLDER SIZE
		// as a speed up measure, and to reduce load on the IMAP server
		// there is an option to skip the calculating of the total folder size
		// user may request an override of this for 1 page view
		if (isset($force_showsize))
		{
			$GLOBALS['phpgw']->msg->args['force_showsize'] = $force_showsize;
			//$args_array['force_showsize'] = $force_showsize;
			//unset($force_showsize);
		}

		// ----  INITIALIZE ARGS ARRAY HOLDER VARIABLE  -------
		// needed whether you intend to login or not
		$args_array = Array();
		// ====== 3 ARGUMENTS THAT "BEGIN_REQUEST() ARGS_ARRAY TAKES  =====
		// there are 2 necessary args to pass, the 3rd (newsmode) is for FUTURE USE
		// these next 2 are really all you need to use this class
		// (1) ----  folder: string  ----
		// used in almost every file, IMAP can be logged into only one folder at a time
		if (isset($folder))
		{
			// folder is not meant to be in class args[] array
			// instead, it should be fed as an agrument to begin_request, it will be processed there
			//$GLOBALS['phpgw']->msg->args['folder'] = $folder;
			$args_array['folder'] = $folder;
			//unset($folder);
		}
		// (2) ----  do_login: true/false  ----
		// if true: class dcom is created and a login is attaemted, and a reopen to the "foler" var is attempted
		// if false: used for information only, such as to fill preferences for squirrelmail,
		//	or for the preferences page, where info necessary for logino may not yet be filled in
		$args_array['do_login'] = True;
		// (3) lastly, the third applicatble arg to begin request is "newsmode" which is not yet developed
		$args_array['newsmode'] = False; // NOT IMPLEMENTED YET
		// this will obtain the email preferences from the db (currently "phpgw_preferences")
		// and prep the folder name, and login if desired, and set msg->mailsvr_stream
		$GLOBALS['phpgw']->msg->begin_request($args_array);

		// ----  Error Msg And Exit If Mailbox Connection Not Established  -----
		if (!$GLOBALS['phpgw']->msg->mailsvr_stream)
		{
			$imap_err = imap_last_error();
			if ($imap_err == '')
			{
				$error_report = 'No Error Returned From Server';
			}
			{
				$error_report = $imap_err;
			}

			echo "<p><center><b>"
			  . lang("There was an error trying to connect to your mail server.<br>Please, check your username and password, or contact your admin.")
			  ."<br>source: email functions.inc.php"
			  ."<br>imap_last_error: ".$error_report
			  . "</b></center></p>";
			$GLOBALS['phpgw']->common->phpgw_exit(True);
		}
	}
	else
	{
		// use the msg class BUT DO NOT login
		// ----  INITIALIZE ARGS ARRAY HOLDER VARIABLE  -------
		// needed whether you intend to login or not
		$args_array = Array();
		$args_array['folder'] = '';
		$args_array['do_login'] = False;
		// this will obtain the email preferences from the db (currently "phpgw_preferences")
		$GLOBALS['phpgw']->msg->begin_request($args_array);
	}

// ----  UN-INITIALIZE ARGS ARRAY HOLDER VARIABLE  -------
	// it's no longer needed
	$args_array = Array();

	//echo '<br>user_pass='.$GLOBALS['phpgw_info']['user']['passwd']
	//   .'<br>email_pass='.$GLOBALS['phpgw_info']['user']['preferences']['email']['passwd'].'<br><br>';
	//var_dump($GLOBALS['phpgw_info']['user']['preferences']['email']);
	//var_dump($GLOBALS['phpgw_info']['user']);

// ----  Various Functions Used To Support Email   -----

	function get_mime_type($de_part)
	{
		$mime_type = 'unknown';
		if (isset($de_part->type) && $de_part->type)
		{
			switch ($de_part->type)
			{
				case TYPETEXT:		$mime_type = 'text'; break;
				case TYPEMESSAGE:	$mime_type = 'message'; break;
				case TYPEAPPLICATION:	$mime_type = 'application'; break;
				case TYPEAUDIO:		$mime_type = 'audio'; break;
				case TYPEIMAGE:		$mime_type = 'image'; break;
				case TYPEVIDEO:		$mime_type = 'video'; break;
				case TYPEMODEL:		$mime_type = 'model'; break;
				default:		$mime_type = 'unknown';
			} 
		}
		return $mime_type;
	}

	function get_mime_encoding($de_part)
	{
		$mime_encoding = "other";
		if (isset($de_part->encoding) && $de_part->encoding)
		{
			switch ($de_part->encoding)
			{
				case ENCBASE64:		$mime_encoding = 'base64'; break;
				case ENCQUOTEDPRINTABLE:	$mime_encoding = 'qprint'; break;
				case ENCOTHER:		$mime_encoding = 'other'; break;
				default:		$mime_encoding = 'other';
			}
		}
		return $mime_encoding;
	}


	function get_att_name($de_part)
	{
		$att_name = 'Unknown';
		if ($de_part->ifparameters)
		{
			for ($i = 0; $i < count($de_part->parameters); $i++) 
			{
				$param = $de_part->parameters[$i];
				if (strtoupper($param->attribute) == 'NAME')
				{
					$att_name = $param->value;
					break;
				}
			}
		}
		// added by Angles: used for improperly formatted messages, RARELY needed, if at all
		if (trim($att_name) == '')
		{
			$att_name = 'error_blank_name';
		}
		return $att_name;
	}

	// this is BROKEN
	function mime_is_packagelist($part_nice)
	{
		if ((stristr($part_nice['subtype'], 'MIXED')) 
		|| (stristr($part_nice['type'], 'multipart'))
		|| (stristr($part_nice['param_attribute'], 'boundry')))
		{
			return True;
		}
		else
		{
			return False;
		}
	}

	function section_sep($title, $str)
	{
		$sep_str = 
		    '</td>'
		  . '<td bgcolor"' . $GLOBALS['phpgw_info']['theme']['th_bg'] .'">'
			  . '<font size="2" face="' .$GLOBALS['phpgw_info']['theme']['font'] .'">'
			  . '<b>'.$title.'</b>'.' :: ' .$str
		  . '</td>' . "\r\n"
		  //. '<td bgcolor="' .$GLOBALS['phpgw_info']['theme']['row_on'] . '" width="570">'
		//	  . '<font size="2" face="' . $GLOBALS['phpgw_info']['theme']['font'] .'">'.$str
		//  . '</td>'
		  . '<td>';
		return $sep_str;
	}

	function attach_display($de_part, $part_no)
	{
		global $msgnum, $folder;
		$mime_type = get_mime_type($de_part);  
		$mime_encoding = get_mime_encoding($de_part);

		$att_name = 'unknown';

		for ($i = 0; $i < count($de_part->parameters); $i++)
		{
			$param = $de_part->parameters[$i];
			if (strtoupper($param->attribute) == 'NAME')
			{
				$att_name = $param->value;
				$url_att_name = urlencode($att_name);
				$att_name = $GLOBALS['phpgw']->msg->decode_header_string($att_name);
			}
		}

		//    $jnk = "<a href=\"".$GLOBALS['phpgw']->link('get_attach.php','folder='.$GLOBALS['phpgw_info']['user']['preferences']['email']['folder']
		$jnk = '<a href="'.$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/get_attach.php','folder='.$folder
		       .'&msgnum='.$msgnum.'&part_no='.$part_no.'&type='.$mime_type
		       .'&subtype='.$de_part->subtype.'&name='.$url_att_name
		       .'&encoding='.$mime_encoding).'">'.$att_name.'</a>';
		return $jnk;
	}


	function inline_display($de_part, $part_no, $msgnum, $mailbox)
	{
		$mime_type = get_mime_type($de_part);
		$mime_encoding = get_mime_encoding($de_part);

		if (!$mailbox)
		{
			$mailbox = $GLOBALS['phpgw']->msg->mailsvr_stream;
		}
		$dsp = $GLOBALS['phpgw']->msg->dcom->fetchbody($mailbox, $msgnum, $part_no);

		$tag = "pre";
		$jnk = $de_part->ifdisposition ? $de_part->disposition : 'unknown';
		if ($mime_encoding == 'qprint')
		{
			$dsp = $GLOBALS['phpgw']->msg->qprint($dsp);
			$tag = 'tt';
		}

		// Thanks to Omer Uner Guclu <oquclu@superonline.com> for figuring out
		// a better way to do message wrapping

		if (strtoupper($de_part->subtype) == 'PLAIN')
		{
			// nlbr and htmlentities functions are strip latin5 characters
			if (strtoupper(lang('charset')) <> 'BIG5')
			{
				$dsp = $GLOBALS['phpgw']->strip_html($dsp);
			}
			$dsp = ereg_replace( "^","<p>",$dsp);
			$dsp = ereg_replace( "\n","<br>",$dsp);
			$dsp = ereg_replace( "$","</p>", $dsp);
			$dsp = make_clickable($dsp);
			echo '<table border="0" align="left" cellpadding="10" width="80%">'
			  .'<tr><td>'.$dsp.'</td></tr></table>';
		}
		elseif (strtoupper($de_part->subtype) == 'HTML')
		{
			output_bound(lang('section').':' , $mime_type.'/'.strtolower($de_part->subtype));
			echo $dsp;
		}
		else
		{
			output_bound(lang('section').':' , $mime_type.'/'.strtolower($de_part->subtype));
			echo '<'.$tag.'>'.$dsp.'</'.$tag.'>'."\n";
		}
	}


	function output_bound($title, $str)
	{
		echo '</td></tr></table>'."\n"
		  . '<table border="0" cellpadding="4" cellspacing="3" '
		  . 'width="700">'."\n".'<tr><td bgcolor"' . $GLOBALS['phpgw_info']['theme']['th_bg'] . '" ' 
		  . 'valign="top"><font size="2" face="' . $GLOBALS['phpgw_info']['theme']['font'] . '">'
		  . '<b>'.$title.'</b></td>'."\n".'<td bgcolor="' . $GLOBALS['phpgw_info']['theme']['row_on'] . '" '
		  . 'width="570"><font size="2" face="' . $GLOBALS['phpgw_info']['theme']['font'] . '">'
		  . $str.'</td></tr></table>'."\n".'<p>'."\n".'<table border="0" cellpadding="2" '
		  . 'cellspacing="0" width="100%"><tr><td>';
	}

	function image_display($folder, $msgnum, $de_part, $part_no, $att_name) 
	{
		output_bound(lang('image').':' , $att_name);
		$extra_parms = 'folder='.urlencode($folder).'&m='.$msgnum
			. '&p='.$part_no.'&s='.strtolower($de_part->subtype).'&n='.$att_name;
		if (isset($GLOBALS['phpgw_info']['flags']['newsmode']) && $GLOBALS['phpgw_info']['flags']['newsmode'])
		{
			$extra_parms .= '&newsmode=on';
		}
		$view_link = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/view_image.php',$extra_parms);
		echo "\n".'<img src="'.$view_link.'">'."\n".'<p>'."\n";
	}

	/*
	// function make_clickable taken from text_to_links() in the SourceForge Snipplet Library
	// http://sourceforge.net/snippet/detail.php?type=snippet&id=100004
	// modified to make mailto: addresses compose in phpGW
	function make_clickable($data, $folder)
	{
		if(empty($data))
		{
			return $data;
		}

		$lines = split("\n",$data);

		while ( list ($key,$line) = each ($lines))
		{
			$line = eregi_replace("([ \t]|^)www\."," http://www.",$line);
			$line = eregi_replace("([ \t]|^)ftp\."," ftp://ftp.",$line);
			$line = eregi_replace("(http://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("(https://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("(ftp://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+))",
				'<a href="'.$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/compose.php','folder='.$GLOBALS['phpgw']->msg->prep_folder_out($folder))
				."&to=\\1\">\\1</a>", $line);

			$newText .= $line . "\n";
		}
		return $newText;
	}
	*/


	/* * * * * * * * * * *
	  *  isValidUrl
	  *  validates that a URL exists
	  *  Discussion:
	  *  compiled from user notes on: http://www.php.net/manual/en/function.parse-url.php
	  *  comments there indicate this code does not leak descriptors, paraphrasing:
	  *  "you don't need to store the file pointer into a variable if you just need to check that the file can be opened.
	  *  Files which are opened with fopen() get automatically closed when their last reference is lost."
	  * * * * * * *  * * * */
	function isValidUrl($url)
	{
		// make sure $url is not a "file://" uri
		// this function also works on files, but we are concerned only with URLs here
		$parts = parse_url( $url );
		if (isset($parts[scheme])
		&& ($parts[scheme] == 'file'))
		{
			return false;
		}
		// try to open the URL
		if (fopen($url, 'r'))
		{
			return true;
		} else
		{
			return false;
		}
	}

?>
