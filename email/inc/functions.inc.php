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
		global $phpgw, $phpgw_info, $PHP_SELF;  // This was a problem for me.
	}

// ----  Turn Off Magic Quotes Runtime    -----
	// magic_quotes_runtime (handles slashes when communicating with databases). PHP MANUAL:
	/*  If magic_quotes_runtime is enabled, most functions that return data from any sort of 
	  external source including databases and text files will have quotes escaped with a backslash. */
	set_magic_quotes_runtime(0);

// ----  Load "Preferences" from db (currently "phpgw_preferences")    -----
	// NOTE: Preferences *MAY* have the "custom email password" which is different than other passwords
	// because it is stored in the "Preferences" table, and may require special treatment
	$phpgw_info['user']['preferences'] = $phpgw->common->create_emailpreferences($phpgw_info['user']['preferences']);
	
	// NOTE: WORKAROUND FOR CUST EMAIL PASSWD BUG REQ'D msg->get_email_passwd() during LOGIN

// ----  Create the base email Class    -----
	$phpgw->msg = CreateObject("email.msg");
	$phpgw->msg->msg_common_();

// ----  Ensure certasin Defaults are Set, and some bug workarounds    -----
	if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "UWash" &&
	$phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" && @!$folder)
	{
		// Changed by skeeter 04 Jan 01
		// This was changed to give me access back to my folders.
		// Not sure what it would break if the user has a default folder preference set,
		// but will allow access to other folders now.
		//      $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") {
		$phpgw_info["user"]["preferences"]["email"]["folder"] = (@!$phpgw_info["user"]["preferences"]["email"]["folder"] ? "INBOX" : $phpgw_info["user"]["preferences"]["email"]["folder"]);
		//backward compatibility
		$folder = $phpgw_info["user"]["preferences"]["email"]["folder"];
	}

// ----  Ensure a Folder Variable exists, if not, set to INBOX (typical practice)   -----
	if(!$folder) $folder="INBOX";

// ----  What Process Called Us (many processes use this functions file)  -----
	// Its better then them using a ton of PHP errors.
	// Changed by Milosch on 3-26-2001 - This check was not working, and the code progressed to giving stream pointer errors
	// From the msg_imap class.  I tried to clean it up here so I could see what was happening.
	// -- (obviously, PHP_SELF is the built-in php variable = "filename on the currently executing script") --
	if (!$PHP_SELF) global $PHP_SELF;  // This was a problem for me.
	// were we called from the "main screen" a.k.a. "front page"
	$in_mainscreen = eregi($phpgw_info['server']['webserver_url'] . '/index.php',$PHP_SELF);
	// were we called from the preferences page
	$in_preferences = eregi("preferences",$PHP_SELF);

// ----  Connect To Mail Server ( only if NOT called from the Preferences Page)  -----
	if (!$in_preferences)
	{
		$mailbox = $phpgw->msg->login($folder); // Changed this to not try connection in prefs
	}

	//echo '<br>user_pass='.$phpgw_info['user']['passwd']
	//   .'<br>email_pass='.$phpgw_info['user']['preferences']['email']['passwd'].'<br><br>';
	//var_dump($phpgw_info['user']['preferences']['email']);
	//var_dump($phpgw_info['user']);

// ----  Error Msg And Exit If Mailbox Connection Not Established  -----
	if (!$mailbox && !($in_mainscreen || $in_preferences))
	{
		echo "<p><center><b>"
		  . lang("There was an error trying to connect to your mail server.<br>Please, check your username and password, or contact your admin.")
		  . "</b></center></p>";
		$phpgw->common->phpgw_exit(True);
	}

// ----  Various Functions Used To Support Email   -----
  function decode_header_string($string)
  {
	global $phpgw;

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
		if(!is_int($end)) {
			return $string;
		}
		$encoded_text = substr($search,0,$end);
		$rest = substr($string,(strlen($preceding.$charset.$encoding.$encoded_text)+6));
		if(strtoupper($encoding) == "Q")
		{
			$decoded = $phpgw->msg->qprint(str_replace("_"," ",$encoded_text));
		}
		if (strtoupper($encoding) == "B")
		{
			$decoded = urldecode(base64_decode($encoded_text));
		}
		return $preceding . $decoded . decode_header_string($rest);
	} 
	else
	return $string;
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
	global $phpgw, $phpgw_info;

	// construct the email server call string from the opening bracket "{"  to the closing bracket  "}"
	if ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'imap')
	{
		/* IMAP normal connection, No SSL */
		$server_call = '{' .$phpgw_info['user']['preferences']['email']['mail_server'] .':' .$phpgw_info['user']['preferences']['email']['mail_port'] .'}';
	}
	elseif ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'imaps')
	{
 		/* IMAP over SSL */
		$server_call = '{' .$phpgw_info['user']['preferences']['email']['mail_server'] .'/ssl/novalidate-cert:993}';
	}
	elseif ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'pop3s')
	{
		/* POP3 over SSL: */
		$server_call = '{' .$phpgw_info['user']['preferences']['email']['mail_server'] .'/pop3/ssl/novalidate-cert:995}';
	}
	elseif ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'pop3')
	{
		/* POP3 normal connection, No SSL  ( same string as normal imap above)  */
		$server_call = '{' .$phpgw_info['user']['preferences']['email']['mail_server'] .':' .$phpgw_info['user']['preferences']['email']['mail_port'] .'}';
	}
	else
	{
		//UNKNOWN SERVER in Preferences, return a default value that is likely to work
		// probably should raise some kind of error here
		$server_call = '{' .$phpgw_info['user']['preferences']['email']['mail_server'] .':' .$phpgw_info['user']['preferences']['email']['mail_port'] .'}';
	}
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
	global $phpgw, $phpgw_info;
	// UWash patched for Maildir style: $Maildir.Junque ?????
	// Cyrus and Courier style =" INBOX"
	// UWash style: "mail"

	if ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UW-Maildir')
	{
		if ( isset($phpgw_info['user']['preferences']['email']['mail_folder']) )
		{
			if ( empty($phpgw_info['user']['preferences']['email']['mail_folder']) )
			{
				// do we need a default value here?
				$name_space = '';
			}
			else
			{
				$name_space = $phpgw_info['user']['preferences']['email']['mail_folder'];
			}
		}
	}
	elseif ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'Cyrus')
	// ALSO works for Courier IMAP
	{
		$name_space = 'INBOX';
	}
	elseif ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
	{
		//$name_space = 'mail/';
		// delimiter "/" moved to get_mailsvr_delimiter()
		$name_space = 'mail';
	}
	else
	{
		// GENERIC IMAP NAMESPACE
		// imap servers usually use INBOX as their namespace
		// this is supposed to be discoverablewith the NAMESPACE command
		// see http://www.rfc-editor.org/rfc/rfc2342.txt
		// however as of PHP 4.0 this is not implemented
		$name_space = 'INBOX';
	}
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
	global $phpgw, $phpgw_info;
	// UWash style: "/"
	// all other imap servers *should* be "."

	if ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
	{
		$delimiter = '/';
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
	return $delimiter;
  }


/* * * * * * * * * * *
  *  get_folder_long
  *  will generate the long name of an imap folder name, works for
  *  imap: UW-Maildir, Cyrus, Courier
  *  Example (Cyrus or Courier):  INBOX.Templates
  *  Example (Cyrus only):  INBOX.drafts.rfc
  *  ????   Example (UW-Maildir only): /home/James.Drafts   ????
  * * * * * * *  * * * */
  function get_folder_long($feed_folder='INBOX')
  {
	global $phpgw, $phpgw_info;

	$folder = ensure_no_brackets($feed_folder);
	if ($folder == 'INBOX')
	{
		// INBOX is (always?) a special reserved word with nothing preceeding it in long or short form
		$folder_long = 'INBOX';
	}
	else
	{
		$name_space = get_mailsvr_namespace();
		$delimiter = get_mailsvr_delimiter();
		if (strstr($folder,"$name_space" ."$delimiter") == False)
		{
			$folder_long = "$name_space" ."$delimiter" ."$folder";
		}
		else
		{
			$folder_long = $folder;
		}
	}
	return trim($folder_long);
  }

  function get_folder_short($feed_folder='INBOX')
  {
	global $phpgw, $phpgw_info;
	// Example: "Sent"
	// Cyrus may support  "Sent.Today"

	$folder = ensure_no_brackets($feed_folder);
	if ($folder == 'INBOX')
	{
		// INBOX is (always?) a special reserved word with nothing preceeding it in long or short form
		$folder_short = 'INBOX';
	}
	else
	{
		$name_space = get_mailsvr_namespace();
		$delimiter = get_mailsvr_delimiter();
		if (strstr($folder,"$name_space" ."$delimiter") == False)
		{
			$folder_short = $folder;
		}
		else
		{
			$folder_short = strstr($folder,$delimiter);
			$folder_short = substr($folder_short, 1);
		}
	}
	return $folder_short;
  }


 function all_folders_listbox($mailbox,$pre_select="",$skip="")
  {
	global $phpgw, $phpgw_info;

	$outstr = '';
	if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"])
	{
		while($pref = each($phpgw_info["user"]["preferences"]["nntp"]))
		{
			$phpgw->db->query("SELECT name FROM newsgroups WHERE con=".$pref[0]);
			while($phpgw->db->next_record())
			{
				$outstr = $outstr .'<option value="' . urlencode($phpgw->db->f("name")) . '">' . $phpgw->db->f("name")
				  . '</option>';
			}
		}
	}
	elseif (($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'pop3')
	    && ($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'pop3s'))
	{
		// Establish Email Server Connectivity Conventions
		$server_str = get_mailsvr_callstr();
		$name_space = get_mailsvr_namespace();
		$delimiter = get_mailsvr_delimiter();
		if ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
		{
			$mailboxes = $phpgw->msg->listmailbox($mailbox, $server_str, "$name_space" ."$delimiter" ."*");
		} else {
			$mailboxes = $phpgw->msg->listmailbox($mailbox, $server_str, "$name_space" ."*");
		}

		// sort folder names 
		if (gettype($mailboxes) == 'array')
		{
			sort($mailboxes);
		}

		if($mailboxes)
		{
			$num_boxes = count($mailboxes);
			if ($name_space != 'INBOX')
			{
				$outstr = $outstr .'<option value="INBOX">INBOX</option>'; 
        		}
			for ($i=0; $i<$num_boxes;$i++)
			{
				$folder_short = get_folder_short($mailboxes[$i]);
				if ($folder_short == $pre_select)
				{
					$sel = ' selected';
				}
				else
				{
					$sel = '';
				}
				if ($folder_short != $skip)
				{
					$outstr = $outstr .'<option value="' .urlencode($folder_short) .'"'.$sel.'>' .$folder_short .'</option>';
					$outstr = $outstr ."\n";
				}
			}
		}
		else
		{
			$outstr = $outstr .'<option value="INBOX">INBOX</option>';
		}
	}
	return $outstr;
  }



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
	$finding = False;
	$struct_count = (!isset($struct->parts) || !$struct->parts ? 1 : count($struct->parts));
	for ($z = 0; $z < $struct_count; $z++)
	{
		$part = !isset($struct->parts[$z]) || !$struct->parts[$z] ? $struct : $struct->parts[$z];
		$att_name = get_att_name($part);

		if ($att_name != 'Unknown')
		{
			// if it has a name, it's an attachment
			$finding = True;
			break;
		}
		elseif ((isset($part->encoding)) && ($part->encoding) && ($part->encoding == ENCBASE64))
		{
			// some LAME MUA's allow attachments with NO name
			$finding = True;
			break;
		}
	}
	return $finding;
  }


  function format_byte_size($feed_size)
  {
	if ($feed_size < 999999)
	{
		$nice_size = round(10*($feed_size/1024))/10 .' k';
	} else {
		//  round to W.XYZ megs by rounding WX.YZ
		$nice_size = round($feed_size/(1024*100));
		// then bring it back one digit and add the MB string
		$nice_size = ($nice_size/10) .' MB';
	}
	return $nice_size;
  }


 function pgw_msg_struct($part, $parent_flat_idx, $feed_dumb_mime, $feed_i, $feed_loops, $feed_debth, $folder, $msgnum)
  {
	global $phpgw, $phpgw_info, $struct_not_set;

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
		$part_nice['id'] = $part->id;
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
	}
	//13:  ifdparameters : True if the dparameters array exists SKIPPED -  ifparameters is more useful (I think)
	//14:  dparameters : Disposition parameter array SKIPPED -  parameters is more useful (I think)
	// 15:  ifparameters : True if the parameters array exists (SKIP)
	// 16:  parameters : MIME parameters array  - this *may* have more than a single attribute / value pair  but I'm not sure
	$part_nice['ex_num_param_pairs'] = $struct_not_set; // CUSTOM/EXTRA: this may be good to know
	$part_nice['param_attribute'] = $struct_not_set;
	$part_nice['param_value'] = $struct_not_set;
	if ( (isset($part->ifparameters)) && ($part->ifparameters)
	&& (isset($part->parameters)) && ($part->parameters) )
	{
		// EXTRA: this is good to know
		$part_nice['ex_num_param_pairs'] = count($part->parameters); // CUSTOM/EXTRA: this may be good to know
		$part_params = $part->parameters[0];
		if ((isset($part_params->attribute) && ($part_params->attribute)))
		{
			$part_nice['param_attribute'] = $part_params->attribute;
		}
		if ((isset($part_params->value) && ($part_params->value)))
		{
			$part_nice['param_value'] = $part_params->value;
		}
		if ($part_nice['ex_num_param_pairs'] > 1)
		{
			$part_params = $part->parameters[1];
			if ((isset($part_params->attribute) && ($part_params->attribute)))
			{
				$part_nice['param_2_attribute'] = $part_params->attribute;
			}
			if ((isset($part_params->value) && ($part_params->value)))
			{
				$part_nice['param_2_value'] = $part_params->value;
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
	// Attachment Detection PART1 = Test For Files
	// non-file stuff like X-VCARD is tested for at a higher level
	// where the code can be more easily modified
	if ($part_nice['encoding'] == 'base64')
	{
		if (($part_nice['param_attribute'] == 'name') 
		  && ($part_nice['param_value'] != $struct_not_set))
		{
			$part_nice['ex_part_name'] = $part_nice['param_value'];
			// ALSO - this is a sign of a "REAL ATTACHMENT" like a file, image, etc...
			$part_nice['ex_has_attachment'] = True;
		}
		else
		{
			// base64 means this IS *some* kind of attachment
			$part_nice['ex_has_attachment'] = True;
			// BUT we have no idea of it's name, and *maybe* idea of it's content type (eg. name.gif = image/gif)
			// sometimes the name's extention is the only info we have, i.e. ".doc" implies a WORD file
			$part_nice['ex_part_name'] = 'no_name.att';
		}
	}
	else
	{
		// NO attachment here
		$part_nice['ex_part_name'] = 'unknown.html';
		$part_nice['ex_has_attachment'] = False;
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
	global $phpgw, $phpgw_info, $struct_not_set;

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
		if ($debug) { echo 'true: strlen(new_mime_dumb) = 1 ; FIRST debth level<br>'; }
		$exploded_mime_dumb[0] = (int)$new_mime_dumb;
	}
	else
	{
		if ($debug) { echo 'false: strlen(new_mime_dumb) = 1<br>'; }
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
			if ($debug) { echo 'dumbs_part_nice[i('.$i.')] = part_nice[flat_idx('.$flat_idx.')]<br>'; }
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
	global $phpgw, $phpgw_info, $struct_not_set;

	$click_info = Array();
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
		 'folder='.$folder .'&msgnum=' .$msgnum .'&part_no=' .$m_part_num_mime
		.'&type=' .$url_part_type .'&subtype=' .$url_part_subtype
		.'&name=' .$url_part_name .'&encoding=' .$url_part_encoding); 
	// Make CLICKABLE link directly to this attachment or part
	$href_part_name = decode_header_string($part_name);
	// ex_part_clickable
	$ex_part_clickable = '<a href="'.$ex_part_href.'">'.$href_part_name.'</a>';
	// put these two vars in an array, serialize it, and pass it back to the calling process
	$click_info[0] = $ex_part_href;
	$click_info[1] = $ex_part_clickable;
	return serialize($click_info);
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
			$att_name = decode_header_string($att_name);
		}
	}

	//    $jnk = "<a href=\"".$phpgw->link("get_attach.php","folder=".$phpgw_info["user"]["preferences"]["email"]["folder"]
	$jnk = "<a href=\"".$phpgw->link("/".$phpgw_info['flags']['currentapp']."/get_attach.php","folder=".$folder
	       ."&msgnum=$msgnum&part_no=$part_no&type=$mime_type"
	       ."&subtype=".$de_part->subtype."&name=$url_att_name"
	       ."&encoding=$mime_encoding")."\">$att_name</a>";
	return $jnk;
  }


  function inline_display($de_part, $part_no)
  {
	global $mailbox, $msgnum, $phpgw, $phpgw_info;
	$mime_type = get_mime_type($de_part);
	$mime_encoding = get_mime_encoding($de_part);

	$dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_no);

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
	global $phpgw;
	global $phpgw_info;

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
  function make_clickable($data)
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
		    "<a href=\"".$phpgw->link("/".$phpgw_info['flags']['currentapp']."/compose.php","folder=".urlencode($phpgw_info["user"]["preferences"]["email"]["folder"]))
		    ."&to=\\1\">\\1</a>", $line);

		$newText .= $line . "\n";
	}
	return $newText;
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
