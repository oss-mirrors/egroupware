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

	$d1 = strtolower(substr($phpgw_info["server"]["app_inc"],0,3));
	if($d1 == "htt" || $d1 == "ftp" )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$phpgw->common->phpgw_exit();
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
	//$debug_logins = False;
	
	// OK TO LOGIN pre-conditions
	// were we called from the main screen (user's home page)
	if (strstr($phpgw_info['server']['versions']['phpgwapi'], '0.9.12'))
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

	// send_message needs to access the mailserver to get parts sometimes, can't limit this here
	// AND ALSO  Do Not Login - if sent message will NOT be put in the "Sent" folder
	//if ( (eregi("^.*\/email\/send_message\.php.*$",$PHP_SELF))
	//&& ($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'imap')
	//&& ($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'imaps') )
	//{
	//	$login_allowed = False;
	//}

	/* // FINE TUNE THIS - TOO BROAD
	// AND ALSO  Do Not Login - if composing message when server is not IMAP/IMAPS
	if ( (eregi("^.*\/email\/compose\.php.*$",$PHP_SELF))
	&& ($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'imap')
	&& ($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'imaps') )
	{
		$login_allowed = False;
	}
	*/

	/*
	if ($debug_logins)
	{
		echo '<br>';
		echo 'PHP_SELF='.$PHP_SELF.'<br>';
		echo 'phpgw_info[server][webserver_url]='.$phpgw_info['server']['webserver_url'].'<br>';
		echo 'in_mainscreen='.serialize($in_mainscreen).'<br>';
		echo 'in_email='.serialize($in_email).'<br>';
		echo 'login_allowed='.serialize($login_allowed).'<br>';
		echo 'folder='.$folder.'<br>';
		echo 'get_mailsvr_callstr='.$phpgw->msg->get_mailsvr_callstr().'<br>';
		echo 'get_folder_long='.$phpgw->msg->get_folder_long($folder).'<br>';
	}
	*/

// ----  CONNECT TO MAILSERVER - IF IT'S OK  -------
	if ((($in_email) || ($in_mainscreen))
	&& ($login_allowed))
	{
		if ($debug_logins) {  echo 'CALL TO LOGIN IN FUNCTIONS.INC.PHP'.'<br>'.'userid='.$phpgw_info['user']['preferences']['email']['userid']; }


		// ----  Create the base email Msg Class    -----
		$phpgw->msg = CreateObject("email.mail_msg");
		$args_array = Array();
		$args_array['folder'] = $folder;
		$args_array['do_login'] = True;
		$args_array['sort'] = $sort;
		$args_array['order'] = $order;
		$args_array['start'] = $start;
		$args_array['newsmode'] = $newsmode;
		$args_array['td'] = $td;
		// this will obtain the email preferences from the db (currently "phpgw_preferences")
		// and prep the folder name, and login if desired, and set msg->mailsvr_stream
		$phpgw->msg->begin_request($args_array);

		// ----  Error Msg And Exit If Mailbox Connection Not Established  -----
		if (!$phpgw->msg->mailsvr_stream)
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
			$phpgw->common->phpgw_exit(True);
		}
	}
	else
	{
		// ----  Create the base email Msg Class    -----
		// but DO NOT login
		$phpgw->msg = CreateObject("email.mail_msg");
		$args_array = Array();
		$args_array['folder'] = '';
		$args_array['do_login'] = False;
		// this will obtain the email preferences from the db (currently "phpgw_preferences")
		$phpgw->msg->begin_request($args_array);
	}

	// get rid og the "folder" GPC variable - it is no longer needed
	//unset($folder);

	// backward compatibility with class nextmatches
	$folder = $this->folder;
	$sort = $this->sort;
	$order = $this->order;
	$start = $this->start;

	//echo '<br>user_pass='.$phpgw_info['user']['passwd']
	//   .'<br>email_pass='.$phpgw_info['user']['preferences']['email']['passwd'].'<br><br>';
	//var_dump($phpgw_info['user']['preferences']['email']);
	//var_dump($phpgw_info['user']);

// ----  Various Functions Used To Support Email   -----

  function get_mime_type($de_part)
  {
	$mime_type = "unknown";
	if (isset($de_part->type) && $de_part->type)
	{
		switch ($de_part->type)
		{
			case TYPETEXT:		$mime_type = "text"; break;
			case TYPEMESSAGE:	$mime_type = "message"; break;
			case TYPEAPPLICATION:	$mime_type = "application"; break;
			case TYPEAUDIO:		$mime_type = "audio"; break;
			case TYPEIMAGE:		$mime_type = "image"; break;
			case TYPEVIDEO:		$mime_type = "video"; break;
			case TYPEMODEL:		$mime_type = "model"; break;
			default:		$mime_type = "unknown";
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
			case ENCBASE64:		$mime_encoding = "base64"; break;
			case ENCQUOTEDPRINTABLE:	$mime_encoding = "qprint"; break;
			case ENCOTHER:		$mime_encoding = "other";  break;
			default:		$mime_encoding = "other";
		}
	}
	return $mime_encoding;
  }


  function get_att_name($de_part)
  {
	$att_name = "Unknown";
	if ($de_part->ifparameters)
	{
		for ($i = 0; $i < count($de_part->parameters); $i++) 
		{
			$param = $de_part->parameters[$i];
			if (strtoupper($param->attribute) == "NAME")
			{
				$att_name = $param->value;
				break;
			}
		}
	}
	// added by Angles: used for improperly formatted messages, RARELY needed, if at all
	if (trim($att_name) == '')
	{
		$att_name = "error_blank_name";
	}
	return $att_name;
  }

  function has_real_attachment($struct)
  {
	$haystack = serialize($struct);

	if (stristr($haystack, 's:9:"attribute";s:4:"name"'))
	{
		// param attribute "name"
		// s:9:"attribute";s:4:"name"
		return True;
	}
	elseif (stristr($haystack, 's:8:"encoding";i:3'))
	{
		// encoding is base 64
		// s:8:"encoding";i:3
		return True;
	}
	elseif (stristr($haystack, 's:11:"disposition";s:10:"attachment"'))
	{
		// header disposition calls itself "attachment"
		// s:11:"disposition";s:10:"attachment"
		return True;
	}
	elseif (stristr($haystack, 's:9:"attribute";s:8:"filename"'))
	{
		// another mime filename indicator
		// s:9:"attribute";s:8:"filename"
		return True;
	}
	else
	{
		return False;
	}
  }


 function pgw_msg_struct($part, $parent_flat_idx, $feed_dumb_mime, $feed_i, $feed_loops, $feed_debth, $folder, $msgnum)
  {
	global $phpgw, $phpgw_info;
	$struct_not_set = '-1';

	//echo 'BEGIN pgw_msg_struct<br>';
	//echo var_dump($part);
	//echo '<br>';
	
	// TRANSLATE PART STRUCTURE CONSTANTS INTO STRINGS OR TRUE/FALSE
	// see php manual page function.imap-fetchstructure.php

	// 1: TYPE
	$part_nice['type'] = $struct_not_set; // Default value if not filled
	if (isset($part->type) && $part->type)
	{
		switch ($part->type)
		{
			case TYPETEXT:		$part_type = "text"; break;
			case 1:			$part_type = "multipart"; break;
			case TYPEMESSAGE:	$part_type = "message"; break;
			case TYPEAPPLICATION:	$part_type = "application"; break;
			case TYPEAUDIO:		$part_type = "audio"; break;
			case TYPEIMAGE:		$part_type = "image"; break;
			case TYPEVIDEO:		$part_type = "video"; break;
			//case TYPEMODEL:		$part_type = "model"; break;
			// TYPEMODEL is not supported as of php v 4
			case 7:			$part_type = "other"; break;
			default:		$part_type = "unknown";
		}
		$part_nice['type'] = $part_type;
	}
		
	// 2: ENCODING
	$part_nice['encoding'] = $struct_not_set; // Default value if not filled
	if (isset($part->encoding) && $part->encoding)
	{
		switch ($part->encoding)
		{
			case ENC7BIT:		$part_encoding = "7bit"; break;
			case ENC8BIT:		$part_encoding = "8bit"; break;
			case ENCBINARY:		$part_encoding = "binary"; break;
			case ENCBASE64:		$part_encoding = "base64"; break;
			case ENCQUOTEDPRINTABLE:	$part_encoding = "qprint"; break;
			case ENCOTHER:		$part_encoding = "other";  break;
			default:		$part_encoding = "other";
		}
		$part_nice['encoding'] = $part_encoding;
	}
	// 3: IFSUBTYPE : true if there is a subtype string (SKIP)
	// 4: MIME subtype if the above is true, already in string form
	$part_nice['subtype'] = $struct_not_set; // Default value if not filled
	if ((isset($part->ifsubtype)) && ($part->ifsubtype)
	&& (isset($part->subtype)) && ($part->subtype) )
	{
		$part_nice['subtype'] = $part->subtype;
		// this header item is not case sensitive
		$part_nice['subtype'] = trim(strtolower($part_nice['subtype']));
	}
	//5: IFDESCRIPTION : true if there is a description string (SKIP)
	// 6: Content Description String, if the above is true
	$part_nice['description'] = $struct_not_set; // Default value if not filled
	if ((isset($part->ifdescription)) && ($part->ifdescription)
	&& (isset($part->description)) && ($part->description) )
	{
		$part_nice['description'] = $part->description;
	}
	// 7:  ifid : True if there is an identification string (SKIP)
	// 8: id : Identification string  , if the above is true
	$part_nice['id'] = $struct_not_set; // Default value if not filled
	if ( (isset($part->ifid)) && ($part->ifid)
	&& (isset($part->id)) && ($part->id) )
	{
		$part_nice['id'] = trim($part->id);
	}
	// 9: lines : Number of lines
	$part_nice['lines'] = $struct_not_set; // Default value if not filled
	if ((isset($part->lines)) && ($part->lines))
	{
		$part_nice['lines'] = $part->lines;
	}
	// 10:  bytes : Number of bytes
	$part_nice['bytes'] = $struct_not_set; // Default value if not filled
	if ((isset($part->bytes)) && ($part->bytes))
	{
		$part_nice['bytes'] = $part->bytes;
	}
	// 11:  ifdisposition : True if there is a disposition string (SKIP)
	// 12:  disposition : Disposition string  ,  if the above is true
	$part_nice['disposition'] = $struct_not_set; // Default value if not filled
	if ( (isset($part->ifdisposition)) && ($part->ifdisposition)
	&& (isset($part->disposition)) && ($part->disposition) )
	{
		$part_nice['disposition'] = $part->disposition;
		// this header item is not case sensitive
		$part_nice['disposition'] = trim(strtolower($part_nice['disposition']));
	}
	//13:  ifdparameters : True if the dparameters array exists SKIPPED -  ifparameters is more useful (I think)
	//14:  dparameters : Disposition parameter array SKIPPED -  parameters is more useful (I think)
	// 15:  ifparameters : True if the parameters array exists (SKIP)
	// 16:  parameters : MIME parameters array  - this *may* have more than a single attribute / value pair  but I'm not sure
	// ex_num_param_pairs defaults to 0 (no params)
	$part_nice['ex_num_param_pairs'] = 0;
	if ( (isset($part->ifparameters)) && ($part->ifparameters)
	&& (isset($part->parameters)) && ($part->parameters) )
	{
		// Custom/Extra Information (ex_):  ex_num_param_pairs
		$part_nice['ex_num_param_pairs'] = count($part->parameters);
		// capture data from all param attribute=value pairs
		for ($pairs = 0; $pairs < $part_nice['ex_num_param_pairs']; $pairs++)
		{
			$part_params = $part->parameters[$pairs];
			$part_nice['params'][$pairs]['attribute'] = $struct_not_set; // default / fallback
			if ((isset($part_params->attribute) && ($part_params->attribute)))
			{
				$part_nice['params'][$pairs]['attribute'] = $part_params->attribute;
				$part_nice['params'][$pairs]['attribute'] = trim(strtolower($part_nice['params'][$pairs]['attribute']));
			}
			$part_nice['params'][$pairs]['value'] = $struct_not_set; // default / fallback
			if ((isset($part_params->value) && ($part_params->value)))
			{
				$part_nice['params'][$pairs]['value'] = $part_params->value;
				// stuff like file names should retain their case
				//$part_nice['params'][$pairs]['value'] = strtolower($part_nice['params'][$pairs]['value']);
			}
		}
	}
	// 17:  parts : Array of objects describing each message part to this part
	// (i.e. embedded MIME part(s) within a wrapper MIME part)
	// key 'ex_' = CUSTOM/EXTRA information
	$part_nice['ex_num_subparts'] = $struct_not_set;
	$part_nice['subpart'] = Array();
	if (isset($part->parts) && $part->parts)
	{
		$num_subparts = count($part->parts);
		$part_nice['ex_num_subparts'] = $num_subparts;
		for ($p = 0; $p < $num_subparts; $p++)
		{
			$part_subpart = $part->parts[$p];
			$part_nice['subpart'][$p] = $part_subpart;
		}
	}
	// ADDITIONAL INFORMATION (often uses array key "ex_" )

	// NOTE: initially I wanted to treat base64 attachments with more "respect", but many other attachments are NOT
	// base64 encoded and are still attachments - if param_value NAME has a value, pretend it's an attachment
	// however, a base64 part IS an attachment even if it has no name, just make one up
	// also, if "disposition" header = "attachment", same thing, it's an attachment, and if no name is in the params, make one up

	// Fallback / Default: assume No Attachment here
	//$part_nice['ex_part_name'] = 'unknown.html';
	$part_nice['ex_part_name'] = 'attachment.txt';
	$part_nice['ex_attachment'] = False;
	
	// Attachment Detection PART1 = if a part has a NAME=FOO in the param pairs, then treat as an attachment
	if (($part_nice['ex_num_param_pairs'] > 0)
	&& ($part_nice['ex_attachment'] == False))
	{
		for ($p = 0; $p < $part_nice['ex_num_param_pairs']; $p++)
		{
			if (($part_nice['params'][$p]['attribute'] == 'name') 
			  && ($part_nice['params'][$p]['value'] != $struct_not_set))
			{
				$part_nice['ex_part_name'] = $part_nice['params'][$p]['value'];
				$part_nice['ex_attachment'] = True;
				break;
			}
		}
	}
	// Attachment Detection PART2 = if a part has encoding=base64 , then treat as an attachment
	if (($part_nice['encoding'] == 'base64')
	&& ($part_nice['ex_attachment'] == False))
	{
		// NOTE: if a part has a name in the params, the above code would have found it, so to get here means
		// we MUST have a base64 part with NO NAME - but it still should be treated as an attachment
		$part_nice['ex_attachment'] = True;
		// BUT we have no idea of it's name, and *maybe* no idea of it's content type (eg. name.gif = image/gif)
		// sometimes the name's extention is the only info we have, i.e. ".doc" implies a WORD file
		//$part_nice['ex_part_name'] = 'no_name.att';
	}
	// Attachment Detection PART3 = if "disposition" header has a value of "attachment" , then treat as an attachment
	// PROVIDED it is not type "message" - in that case the attachment is *inside* the message, not the message itself
	if (($part_nice['disposition'] == 'attachment')
	&& ($part_nice['type'] != 'message')
	&& ($part_nice['ex_attachment'] == False))
	{
		// NOTE: if a part has a name in the params, the above code would have found it, so to get here means
		// we MUST have a attachment with NO NAME - but it still should be treated as an attachment
		$part_nice['ex_attachment'] = True;
		// BUT we have no idea of it's name, and *maybe* no idea of it's content type (eg. name.gif = image/gif)
		// sometimes the name's extention is the only info we have, i.e. ".doc" implies a WORD file
		//$part_nice['ex_part_name'] = 'no_name.att';
	}

	// "dumb" mime part number based only on array position, will be made "smart" later
	$part_nice['ex_mime_number_dumb'] = $feed_dumb_mime;
	$part_nice['ex_parent_flat_idx'] = $parent_flat_idx;
	// Iteration Tracking
	$part_nice['ex_level_iteration'] = $feed_i;
	$part_nice['ex_level_max_loops'] = $feed_loops;
	$part_nice['ex_level_debth'] = $feed_debth;
	
	//echo 'BEGIN DUMP<br>';
	//echo var_dump($part_nice);
	//echo '<br>END DUMP<br>';
	
	return $part_nice;
  }


  function mime_number_smart($part_nice, $flat_idx, $new_mime_dumb)
  {
	global $phpgw, $phpgw_info;
	$struct_not_set = '-1';

	// ---- Construct a "Smart" mime number
	
	//$debug = True;
	$debug = False;
	//if (($flat_idx >= 25) && ($flat_idx <= 100))
	//{
	//	$debug = True;
	//}
	
	if ($debug) { echo 'ENTER mime_number_smart<br>'; }
	if ($debug) { echo 'fed var flat_idx: '. $flat_idx.'<br>'; }
	if ($debug) { echo 'fed var new_mime_dumb: '. $new_mime_dumb.'<br>'; }
	//error check
	if ($new_mime_dumb == $struct_not_set)
	{
		$smart_mime_number = 'error 1 in mime_number_smart';
		break;
	}

	// explode new_mime_dumb into an array
	$exploded_mime_dumb = Array();
	if (strlen($new_mime_dumb) == 1)
	{
		if ($debug) { echo 'strlen(new_mime_dumb) = 1 :: TRUE ; FIRST debth level<br>'; }
		$exploded_mime_dumb[0] = (int)$new_mime_dumb;
	}
	else
	{
		if ($debug) { echo 'strlen(new_mime_dumb) = 1 :: FALSE<br>'; }
		$exploded_mime_dumb = explode('.', $new_mime_dumb);
	}

	// cast all values in exploded_mime_dumb as integers
	for ($i = 0; $i < count($exploded_mime_dumb); $i++)
	{
		$exploded_mime_dumb[$i] = (int)$exploded_mime_dumb[$i];
	}
	if ($debug) { echo 'exploded_mime_dumb '.serialize($exploded_mime_dumb).'<br>'; }

	// make an array of all parts of this family tree,  from the current part (the outermost) to innermost (closest to debth level 1)
	$dumbs_part_nice = Array();
	//loop BACKWARDS
	for ($i = count($exploded_mime_dumb) - 1; $i > -1; $i--)
	{
		if ($debug) { echo 'exploded_mime_dumb reverse loop i=['.$i.']<br>'; }
		// is this the outermost (current) part ?
		if ($i == (count($exploded_mime_dumb) - 1))
		{
			$dumbs_part_nice[$i] = $part_nice[$flat_idx];
			if ($debug) { echo '(outermost/current part) dumbs_part_nice[i('.$i.')] = part_nice[flat_idx('.$flat_idx.')]<br>'; }
			//if ($debug) { echo ' - prev_parent_flat_idx: '.$prev_parent_flat_idx.'<br>'; }
		}
		else
		{
			$this_dumbs_idx = $dumbs_part_nice[$i+1]['ex_parent_flat_idx'];
			$dumbs_part_nice[$i] = $part_nice[$this_dumbs_idx];
			if ($debug) { echo 'dumbs_part_nice[i('.$i.')] = part_nice[this_dumbs_idx('.$this_dumbs_idx.')]<br>'; }
		}
	}
	//if ($debug) { echo 'dumbs_part_nice serialized: '.serialize($dumbs_part_nice) .'<br>'; }
	//if ($debug) { echo 'serialize exploded_mime_dumb: '.serialize($exploded_mime_dumb).'<br>'; }
	
	// NOTE:  Packagelist -> Container EXCEPTION Conversions
	// a.k.a "Exceptions for Less-Standart Subtypes"
	// are located in the analysis loop done that BEFORE you enter this function

	// Reconstruct the Dumb Mime Number string into a "SMART" Mime Number string
	// RULE:  Dumb Mime parts that have "m_description" = "packagelist" (i.e. it's a header part)
	//	should be ommitted when constructing the Smart Mime Number
	// WITH 2 EXCEPTIONS:
	//	(a) debth 1 parts that are "packagelist" *never* get altered in any way
	//	(b) outermost debth parts that are "packagelist" get a value of "0", not ommitted
	//	(c) for 2 "packagelist"s in sucession, the first one gets a "1", not ommitted

	// apply the rules
	$smart_mime_number_array = Array();
	for ($i = 0; $i < count($dumbs_part_nice); $i++)
	{
		if (((int)$dumbs_part_nice[$i]['ex_level_debth'] == 1)
		|| ($i == 0))
		{
			// debth 1 part numbers are never altered
			$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
		}
		// is this the outermost level (i.e. the last dumb mime number)
		elseif ($i == (count($exploded_mime_dumb) - 1))
		{
			// see outermost rule above
			if ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
			{
				// it gets a value of zero
				$smart_mime_number_array[$i] = 0;
			}
			else
			{
				// no need to change
				$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
			}
		}
		// we covered the exceptions, now apply the ommiting rule
		else
		{
			if ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
			{
				// mark this for later removal (ommition)
				$smart_mime_number_array[$i] = $struct_not_set;
			}
			else
			{
				// no need to change
				$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
			}
		}
	}
	
	// for 2 "packagelist"s in sucession, the first one gets a "1", not ommitted
	for ($i = 0; $i < count($dumbs_part_nice); $i++)
	{
		if (($i > 0) // not innermost
		&& ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
		&& ($dumbs_part_nice[$i-1]['m_description'] == 'packagelist'))
		{
			$smart_mime_number_array[$i-1] = 1;
		}
	}

	// make the "smart mime number" based on the info gathered and the above rules
	// as applied to the smart_mime_number_array
	$smart_mime_number = '';
	for ($i = 0; $i < count($smart_mime_number_array); $i++)
	{
		if ($smart_mime_number_array[$i] != $struct_not_set)
		{
			$smart_mime_number = $smart_mime_number . (string)$smart_mime_number_array[$i];
			// we  add a dot "." if this is not the outermost debth level
			if ($i != (count($smart_mime_number_array) - 1))
			{
				$smart_mime_number = $smart_mime_number . '.';
			}
		}
	}
	if ($debug) { echo 'FINAL smart_mime_number: '.$smart_mime_number.'<br><br>'; }
	return $smart_mime_number;
  }


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


  function make_part_clickable($part_nice, $folder, $msgnum)
  {
	global $phpgw, $phpgw_info;
	$struct_not_set = '-1';

	// Part Number used to request parts from the server
	$m_part_num_mime = $part_nice['m_part_num_mime'];

	$part_name = $part_nice['ex_part_name'];

	// make a URL to directly access this part
	if ($part_nice['type'] != $struct_not_set) {
		$url_part_type = $part_nice['type'];
	} else {
		$url_part_type = 'unknown';
	}
	if ($part_nice['subtype'] != $struct_not_set) {
		$url_part_subtype = $part_nice['subtype'];
	} else {
		$url_part_subtype = 'unknown';
	}
	if ($part_nice['encoding'] != $struct_not_set) {
		$url_part_encoding = $part_nice['encoding'];
	} else {
		$url_part_encoding = 'other';
	}
	// make a URL to directly access this part
	$url_part_name = urlencode($part_name);
	// ex_part_href
	$ex_part_href = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/get_attach.php',
		 'folder='.$phpgw->msg->prep_folder_out($folder) .'&msgnum=' .$msgnum .'&part_no=' .$m_part_num_mime
		.'&type=' .$url_part_type .'&subtype=' .$url_part_subtype
		.'&name=' .$url_part_name .'&encoding=' .$url_part_encoding); 
	// Make CLICKABLE link directly to this attachment or part
	$href_part_name = $phpgw->msg->decode_header_string($part_name);
	// ex_part_clickable
	$ex_part_clickable = '<a href="'.$ex_part_href.'">'.$href_part_name.'</a>';
	// put these two vars in an array, and pass it back to the calling process
	$click_info = Array();
	$click_info['part_href'] = $ex_part_href;
	$click_info['part_clickable'] = $ex_part_clickable;
	return $click_info;
  }

  function array_keys_str($my_array)
  {
	$all_keys = Array();
	$all_keys = array_keys($my_array);
	return implode(', ',$all_keys);
  }

  function section_sep($title, $str)
  {
	global $phpgw_info;
	$sep_str = 
	    '</td>'
	  . '<td bgcolor"' . $phpgw_info["theme"]["th_bg"] .'">'
		  . '<font size="2" face="' .$phpgw_info["theme"]["font"] .'">'
		  . '<b>'.$title.'</b>'.' :: ' .$str
	  . '</td>' . "\r\n"
	  //. '<td bgcolor="' .$phpgw_info["theme"]["row_on"] . '" width="570">'
	//	  . '<font size="2" face="' . $phpgw_info["theme"]["font"] .'">'.$str
	//  . '</td>'
	  . '<td>';
	return $sep_str;
  }



  function attach_display($de_part, $part_no)
  {
	global $msgnum, $phpgw, $phpgw_info, $folder;
	$mime_type = get_mime_type($de_part);  
	$mime_encoding = get_mime_encoding($de_part);

	$att_name = "unknown";

	for ($i = 0; $i < count($de_part->parameters); $i++)
	{
		$param = $de_part->parameters[$i];
		if (strtoupper($param->attribute) == "NAME")
		{
			$att_name = $param->value;
			$url_att_name = urlencode($att_name);
			$att_name = $phpgw->msg->decode_header_string($att_name);
		}
	}

	//    $jnk = "<a href=\"".$phpgw->link("get_attach.php","folder=".$phpgw_info["user"]["preferences"]["email"]["folder"]
	$jnk = "<a href=\"".$phpgw->link("/".$phpgw_info['flags']['currentapp']."/get_attach.php","folder=".$folder
	       ."&msgnum=$msgnum&part_no=$part_no&type=$mime_type"
	       ."&subtype=".$de_part->subtype."&name=$url_att_name"
	       ."&encoding=$mime_encoding")."\">$att_name</a>";
	return $jnk;
  }


  function inline_display($de_part, $part_no, $msgnum, $mailbox)
  {
	global  $phpgw, $phpgw_info;

	$mime_type = get_mime_type($de_part);
	$mime_encoding = get_mime_encoding($de_part);

	if (!$mailbox)
	{
		$mailbox = $phpgw->msg->mailsvr_stream;
	}
	$dsp = $phpgw->dcom->fetchbody($mailbox, $msgnum, $part_no);

	$tag = "pre";
	$jnk = $de_part->ifdisposition ? $de_part->disposition : "unknown";
	if ($mime_encoding == "qprint")
	{
		$dsp = $phpgw->msg->qprint($dsp);
		$tag = "tt";
	}

	// Thanks to Omer Uner Guclu <oquclu@superonline.com> for figuring out
	// a better way to do message wrapping

	if (strtoupper($de_part->subtype) == "PLAIN")
	{
		// nlbr and htmlentities functions are strip latin5 characters
		if (strtoupper(lang("charset")) <> "BIG5")
		{
			$dsp = $phpgw->strip_html($dsp);
		}
		$dsp = ereg_replace( "^","<p>",$dsp);
		$dsp = ereg_replace( "\n","<br>",$dsp);
		$dsp = ereg_replace( "$","</p>", $dsp);
		$dsp = make_clickable($dsp);
		echo "<table border=\"0\" align=\"left\" cellpadding=\"10\" width=\"80%\">"
		  ."<tr><td>$dsp</td></tr></table>";
	}
	elseif (strtoupper($de_part->subtype) == "HTML")
	{
		output_bound(lang("section").":" , "$mime_type/".strtolower($de_part->subtype));
		echo $dsp;
	}
	else
	{
		output_bound(lang("section").":" , "$mime_type/".strtolower($de_part->subtype));
		echo "<$tag>$dsp</$tag>\n";
	}
  }


  function output_bound($title, $str)
  {
	global $phpgw_info;
	echo "</td></tr></table>\n"
	  . "<table border=\"0\" cellpadding=\"4\" cellspacing=\"3\" "
	  . "width=\"700\">\n<tr><td bgcolor\"" . $phpgw_info["theme"]["th_bg"] . "\" " 
	  . "valign=\"top\"><font size=\"2\" face=\"" . $phpgw_info["theme"]["font"] . "\">"
	  . "<b>$title</b></td>\n<td bgcolor=\"" . $phpgw_info["theme"]["row_on"] . "\" "
	  . "width=\"570\"><font size=\"2\" face=\"" . $phpgw_info["theme"]["font"] . "\">"
	  . "$str</td></tr></table>\n<p>\n<table border=\"0\" cellpadding=\"2\" "
	  . "cellspacing=\"0\" width=\"100%\"><tr><td>";
  }

  function image_display($folder, $msgnum, $de_part, $part_no, $att_name) 
  {
	global $phpgw, $phpgw_info;

	output_bound(lang("image").":" , $att_name);
	$extra_parms = "folder=".urlencode($folder)."&m=".$msgnum
		. "&p=".$part_no."&s=".strtolower($de_part->subtype)."&n=".$att_name;
	if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"])
	{
		$extra_parms .= "&newsmode=on";
	}
	$view_link = $phpgw->link("/".$phpgw_info['flags']['currentapp']."/view_image.php",$extra_parms);
	echo "\n<img src=\"".$view_link."\">\n<p>\n";
  }

  // function make_clickable taken from text_to_links() in the SourceForge Snipplet Library
  // http://sourceforge.net/snippet/detail.php?type=snippet&id=100004
  // modified to make mailto: addresses compose in phpGW
  function make_clickable($data, $folder)
  {
	global $phpgw,$phpgw_info;

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
		    "<a href=\"".$phpgw->link("/".$phpgw_info['flags']['currentapp']."/compose.php","folder=".$phpgw->msg->prep_folder_out($folder))
		    ."&to=\\1\">\\1</a>", $line);

		$newText .= $line . "\n";
	}
	return $newText;
  }


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
		&& ($parts[scheme] == "file"))
		{
			return false;
		}
		// try to open the URL
		if (fopen($url, "r"))
		{
			return true;
		} else
		{
			return false;
		}
	}

?>
