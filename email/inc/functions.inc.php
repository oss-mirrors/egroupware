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
	$no_login_check = Array();
	// these files do not require login to email server
	$no_login_check[0] = "preferences\.php";
	$no_login_check[1] = "attach_file\.php";
	$no_login_check[2] = "addressbook\.php";
	//$no_login_check[3] = "filters\.php";
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
	$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");

	// ----  HANDLE SETTING PREFERENCE GPC HTTP_POST_VARS ARGS  -------
	// setting prefs does not require a login, in fact you may not be able to login until you set
	// some basic prefs, so it makes sence to handle that here
	if (isset($GLOBALS['HTTP_POST_VARS']['submit_prefs']))
	{
		$GLOBALS['phpgw']->msg->grab_set_prefs_args_gpc();
	}
	
	// UNKNOWN if $totalerrors and $errors are still used or not
	// $args_array['totalerrors'] = $totalerrors;
	// $args_array['errors'] = $errors;

// ----  CONNECT TO MAILSERVER - IF IT'S OK  -------
	if ((($in_email) || ($in_mainscreen))
	&& ($login_allowed))
	{
		// this will expose sensitive data, beter to comment it out when not debugging
		//if ($debug_logins) {  echo 'CALL TO LOGIN IN FUNCTIONS.INC.PHP'.'<br>'.'userid='.$GLOBALS['phpgw_info']['user']['preferences']['email']['userid']; }
		
		// ----  GRAB CLASS VARIABLES FROM HTTP POST OR GET GLOBALS  ------
		$GLOBALS['phpgw']->msg->grab_class_args_gpc();
		
		// ----  INITIALIZE SIMPLE REQUEST ARGS ARRAY HOLDER VARIABLE  -------
		// needed whether you intend to login or not
		$args_array = Array();
		// ====== 3 ARGUMENTS THAT "BEGIN_REQUEST() ARGS_ARRAY TAKES  =====
		// there are 2 necessary args to pass, the 3rd (newsmode) is for FUTURE USE
		// these next 2 are really all you need to use this class
		// NOTE: you can supply the "folder" and "do_login" values from any data source, xml-rpc is planned

		// (1) ----  folder: string  ----
		// used in almost every procedure, IMAP can be logged into only one folder at a time
		if (isset($GLOBALS['HTTP_POST_VARS']['folder']))
		{
			// folder is not meant to be in class args[] array
			// instead, it should be fed as an agrument to begin_request, it will be processed there
			$args_array['folder'] = $GLOBALS['HTTP_POST_VARS']['folder'];
		}
		// also may be in the URI
		elseif (isset($GLOBALS['HTTP_GET_VARS']['folder']))
		{
			$args_array['folder'] = $GLOBALS['HTTP_GET_VARS']['folder'];
		}
		
		// (2) ----  do_login: true/false  ----
		// if true: class dcom is created and a login is attaemted, and a reopen to the "foler" var is attempted
		// if false: used for information only, such as to fill preferences for squirrelmail,
		//	or for the preferences page, where info necessary for logino may not yet be filled in
		$args_array['do_login'] = True;
		
		// (3) ----  newsmode: true/false  ----  NOT IMPLEMENTED YET
		// lastly, the third applicatble arg to begin request is "newsmode" which is not yet developed
		$args_array['newsmode'] = False; // NOT IMPLEMENTED YET
		
		// "begin_request" will obtain the email preferences from the db (currently "phpgw_preferences")
		// and prep the folder name, and login if desired, and set msg->mailsvr_stream
		
		// BEGIN the mail transaction REQUEST
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
	// note these are probably unused, because most (all ?) useful functions have migrated into mail_msg class

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
?>
