<?php
	/**************************************************************************\
	* phpGroupWare - E-Mail								*
	* http://www.phpgroupware.org							*
	* Based on Aeromail by Mark Cushman <mark@cushman.net>			*
	*          http://the.cushman.net/							*
	* --------------------------------------------						*
	*  This program is free software; you can redistribute it and/or modify it 	*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your		*
	*  option) any later version.								*
	\**************************************************************************/

	/* $Id$ */

	$d1 = strtolower(substr(APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	
	// (angles) I think this is unnecessary with the new GLOBALS construct
	//if(floor(phpversion()) == 4)
	//{
	//	global $phpgw, $phpgw_info, $PHP_SELF;  // This was a problem for me (author unknown).
	//}

// ----  Turn Off Magic Quotes Runtime    -----
	/*
	@Discussion	Turn Off Magic Quotes Runtime
	magic_quotes_runtime essentially handles slashes when communicating with databases.
	PHP MANUAL says:
		If magic_quotes_runtime is enabled, most functions that return data from any sort of 
		external source including databases and text files will have quotes escaped with a backslash.
	this is undesirable - turn it off.
	@author	Angles
	*/
	set_magic_quotes_runtime(0);

// ----  Set Some Debug Flags ==  -----
	//$debug_logins = True;
	$debug_logins = False;
	
	//$debug_args_array = True;
	$debug_args_array = False;
	
// ----  == IS IT OK TO LOGIN To Mailserver ==  -----
	/*
	@Discussion	Is It OK to Login To The Server?
	Preferences page, Users home page, Addressbook page,
	none require an actual connection to a server, in fact a connection may not even
	be possible if preferences are not set or are set incorrectly
	@author	Angles
	*/
	// OK TO LOGIN pre-conditions
	// were we called from the main screen (user's home page)
	if (strstr($GLOBALS['phpgw_info']['server']['versions']['phpgwapi'], '0.9.12'))
	{
		// user's welcome page was called "index.php" in ver 0.9.12
		// perhaps still needed during the upgrade procedure - so keep this check
		$in_mainscreen = eregi("^.*\/index\.php.*$",$GLOBALS['PHP_SELF']);
	}
	else
	{
		// after version 0.9.13 users welcome page is "home.php"
		$in_mainscreen = eregi("^.*\/home\.php.*$",$GLOBALS['PHP_SELF']);
	}
	// were we in a typical email session
	$in_email = eregi("^.*\/email\/.*$",$GLOBALS['PHP_SELF']);
	
	// DO NOT LOGIN for these conditions  --------
	$login_allowed = True; // initialize
	
	// these files do not require login to email server, or have been replaced by n-tier classes.
	$no_login_check = Array(
		"attach_file\.php",
		"preferences\.php",
		"addressbook\.php",
		"folder\.php",
		//"filters\.php",
		"index\.php",
		"indexx\.php"
	);
	if ($debug_logins) { echo 'email functions.php: $no_login_check[]: '.serialize($no_login_check).'<br>'; }
	
	for ($i=0; $i<count($no_login_check); $i++)
	{
		$match_this = $no_login_check[$i];
		if (eregi("^.*\/email\/$match_this.*$",$GLOBALS['PHP_SELF']))
		{
			$login_allowed = False;
			break;
		}
	}

	// MORE Login Restrictions That Need Work - Disabled for now
	// send_message needs to access the mailserver to get parts sometimes, can't limit this here
	// AND ALSO  Do Not Login - if sent message will NOT be put in the "Sent" folder
	//if ( (eregi("^.*\/email\/send_message\.php.*$",$GLOBALS['PHP_SELF']))
	//&& ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] != 'imap')
	//&& ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] != 'imaps') )
	//{
	//	$login_allowed = False;
	//}

	/*
	// this this debug report looks to be somewhat dated
	if ($debug_logins)
	{
		echo '<br>';
		echo 'PHP_SELF='.$GLOBALS['PHP_SELF'].'<br>';
		echo 'phpgw_info[server][webserver_url]='.$GLOBALS['phpgw_info']['server']['webserver_url'].'<br>';
		echo 'in_mainscreen='.serialize($in_mainscreen).'<br>';
		echo 'in_email='.serialize($in_email).'<br>';
		echo 'login_allowed='.serialize($login_allowed).'<br>';
		echo 'folder='.$folder.'<br>';
		echo 'get_mailsvr_callstr='.$GLOBALS['phpgw']->msg->get_mailsvr_callstr().'<br>';
		echo 'get_folder_long='.$GLOBALS['phpgw']->msg->get_folder_long($folder).'<br>';
	}
	*/

	/*!
	@action	begin mail request
	@abstract	basic instructions for creating and initializing the mail_msg class
	@param	$this->args[]	array	see below for available array elements
	@param	$args_array	array	currently only 2 elements are available
		$args_array['folder']  string  default: 'INBOX'
			name of folder name to log into (i.e. open, select)
		$args_array['do_login']  boolean  default: True
			should the mail_msg class actually create a mail_dcom instance and then
			attempt to establish a connection to a server. In some cases, such as when
			setting preferences, this is not desirable (not possible before prefs are set, anyway)
			avoids delays and/or error messages of an unneeded server connection
	@result	none, this is an object
	@discussion	The mail_msg class is intended to hide the complex details of email requests
	from the developer, allowing almost anyone with little effort to include useful email functionality
	in their application. For this reason, the initial arguments that mail_msg class will look for are
	in two seperate structures. Param $args_array accepts only 2 elements, "folder" and "do_login"
	and represents the minimum amount of information the calling application need supply to
	the class to get something done. The other necessary data will be inferred of gathered from the
	preferences class.
	Alternatively, the $this->args[] array can hold quite a number of elements which can be used
	by the mail_msg class to accomplish more specific and/or more complicated mail requests.
	See below for the currently available array elements that mail_msg class will accept.
	Here are some simplified instructions on initializing and using the mail_msg class.
	
	----  INSTRUCTIONS:   -------
	1: create an instance of the mail_msg class
		$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
	2: (optional) if you want to pass some GPC type args to the class, put then in the
		$GLOBALS['phpgw']->msg->args[] array
		example: putting $GLOBALS['HTTP_POST_VARS'] and/or HTTP_GET_VARS
		data into said msg->args[] array is accomplished with this class function:
		$GLOBALS['phpgw']->msg->grab_class_args_gpc();
		Alternatively, if you are attempting to set email preferences, use this call:
		$GLOBALS['phpgw']->msg->grab_set_prefs_args_gpc();
		FUTURE USAGE:
			Those two class functions will have equivalent calls for external data feeds, like:
			$GLOBALS['phpgw']->msg->grab_class_args_xmlrpc();
			$GLOBALS['phpgw']->msg->grab_set_prefs_args_xmlrpc();
	3: create an array (example: $args_array[]) to be the sole argument to "begin_request()"
	4: there is 1 needed and 1 optional params:
		$args_array['do_login'] : boolean (necessary) : default: True
		$args_array['folder'] : string (defaults to 'INBOX' if not supplied)
	5: call "begin_request" with that args array as such:
		$GLOBALS['phpgw']->msg->begin_request($args_array);
	6: do something, like grab an email, list messages, check for new mail, etc...
			$inbox_data = Array();
			$inbox_data = $GLOBALS['phpgw']->msg->new_message_check();
	7: when you are done, end the request with this command:
		$GLOBALS['phpgw']->msg->end_request('');
	
	Simple Example:
		$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
		$args_array = Array();
		$args_array['folder'] = 'INBOX';
		$args_array['do_login'] = True;
		$GLOBALS['phpgw']->msg->begin_request($args_array);
		$inbox_data = Array();
		$inbox_data = $GLOBALS['phpgw']->msg->new_message_check();
		echo 'mail check says: '.$inbox_data['alert_string'];
		$GLOBALS['phpgw']->msg->end_request('');
	*/

	// ----  Create the mail_msg Class    -----
	//$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
	if (is_object($GLOBALS['phpgw']->msg))
	{
		if ($debug_logins) { echo 'email: functions.inc.php: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
	}
	else
	{
		if ($debug_logins) { echo 'email: functions.inc.php: is_object test: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
		$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
	}

	/*
	// DEPRECIATED - now in email.bopreferences
	// ----  HANDLE GETTING PREFERENCE GPC HTTP_POST_VARS ARGS  -------
	// setting prefs does not require a login, in fact you may not be able to login until you set
	// some basic prefs, so it makes sence to handle that here
	if (isset($GLOBALS['HTTP_POST_VARS']['submit_prefs']))
	{
		$GLOBALS['phpgw']->msg->grab_set_prefs_args_gpc();
	}
	*/
	
	// UNKNOWN if $totalerrors and $errors are still used or not
	// $args_array['totalerrors'] = $totalerrors;
	// $args_array['errors'] = $errors;

// ----  CONNECT TO MAILSERVER - IF IT'S OK  -------
	if ((($in_email) || ($in_mainscreen))
	&& ($login_allowed))
	{
		// this will expose sensitive data, beter to comment it out when not debugging
		//if ($debug_logins) {  echo 'CALL TO LOGIN IN FUNCTIONS.INC.PHP'.'<br>'.'userid='.$GLOBALS['phpgw_info']['user']['preferences']['email']['userid']; }
		
		/*
		// DEPRECIATED - not happens aytomatically in "->begin_request()"
		// ----  GRAB CLASS VARIABLES FROM HTTP POST OR GET GLOBALS  ------
		$GLOBALS['phpgw']->msg->grab_class_args_gpc();
		*/
		
		// ----  INITIALIZE SIMPLE REQUEST ARGS ARRAY HOLDER VARIABLE  -------
		// needed whether you intend to login or not
		$args_array = Array();
		// ====== ARGUMENTS THAT "BEGIN_REQUEST() ARGS_ARRAY TAKES  =====
		// (1) PRIMARY: $args_array["do_login"] = True/False
		// "Primary" means this arg value *should* be passed in the $args_array[] item
		// although it can be passed thru any supported means. "do_login" controls
		// whether class.mail_dcom  is created and whether a server login is attempted.
		// This can be undersirable if no email prefs exist or if a server connection is otherwise
		// not required or not desired.
		// (2) additionally, *any* known class arg can be passed in $args_array[] and theey will
		// OVERRIDE any previous arg value, such as gotten from the GPC vars in "->grab_class_args_*()"
		// Otherwise, just let the "->grab_class_args_*()" set these arg values.
		// NOTE: you can supply the "folder" and "do_login" values from any data source, xml-rpc is planned
		
		// (1) ----  do_login: true/false  ----
		// if true: class dcom is created and a login is attaemted, and a reopen to the "foler" var is attempted
		// if false: used for information only, such as to fill preferences for squirrelmail,
		//	or for the preferences page, where info necessary for logino may not yet be filled in
		$args_array['do_login'] = True;
				
		// "begin_request" will obtain the email preferences from the db
		// currently db table "phpgw_preferences", accessed via object $GLOBALS['phpgw']->preferences
		// and also containing integrated pref handling code in /mail/class.bopreferences.inc.php
		// "begin_request" will also attempt to obtain values for all known args, ex. "->grab_class_args_*()",
		// and will prepare those args, such as preping the folder name
		// and login (if "do_login" == true) and, if logged in, will set arg value "mailsvr_stream"
		
		// BEGIN the mail transaction REQUEST
		$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);

		// ----  Error Msg And Exit If Mailbox Connection Not Established  -----
		if (!$some_stream)
		{
			$GLOBALS['phpgw']->msg->login_error('email_(slash)_functions.inc.php');
			// this exits the script, calling $GLOBALS['phpgw']->common->phpgw_exit(True);
		}
	}
	else
	{
		// use the msg class BUT DO NOT login
		// ----  INITIALIZE ARGS ARRAY HOLDER VARIABLE  -------
		// needed whether you intend to login or not
		$args_array = Array();
		$args_array['do_login'] = False;
		// this will obtain the email preferences from the db (currently "phpgw_preferences")
		$GLOBALS['phpgw']->msg->begin_request($args_array);
	}

// ----  UN-INITIALIZE ARGS ARRAY HOLDER VARIABLE  -------
	// it's no longer needed
	$args_array = Array();


	// FROM HERE DOWN IS ALL DEPRECIATED CODE
// ----  Various Functions Used To Support Email   -----
	// note these are probably unused, because most (all ?) useful functions have migrated into mail_msg class

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

?>
