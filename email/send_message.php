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

	$phpgw_flags = Array(
		'currentapp'		=> 'email',
		'enable_network_class'	=> True,
		'noheader'		=> True,
		'nonavbar'		=> True
	);
	
	$phpgw_info['flags'] = $phpgw_flags;
	include('../header.inc.php');

	$struct_not_set = $phpgw->msg->not_set;

//  -------  This will be called just before leaving this page, to clear / unset variables / objects -----------
	function send_message_cleanup()
	{
		global $phpgw, $mail_out;
		
		//echo 'send_message cleanup';
		$phpgw->msg->end_request();
		// note: the next lines can be removed since php takes care of memory management
		unset($mail_out);
		unset($phpgw->mail_send);
	}

	/*
	// -- debug ----
	$to = $phpgw->msg->stripslashes_gpc($to);
	$mail_out['to'] = $phpgw->msg->make_rfc_addy_array($to);
	$cc = $phpgw->msg->stripslashes_gpc($cc);
	$mail_out['cc'] = $phpgw->msg->make_rfc_addy_array($cc);
	
	//echo '<br> var dump $mail_out[to] <br>';
	//var_dump($mail_out['to']);
	
	//send_message_cleanup($mailbox);
	send_message_cleanup();
	$phpgw->common->phpgw_footer();
	exit;
	// end ----  debug ------
	*/

//  -------  Init Array Structure For Outgoing Mail  -----------
	$mail_out = Array();
	$mail_out['to'] = Array();
	$mail_out['cc'] = Array();
	$mail_out['bcc'] = Array();
	$mail_out['mta_to'] = Array();
	$mail_out['mta_from'] = '<'.trim($phpgw_info['user']['preferences']['email']['address']).'>';
	$mail_out['mta_elho_domain'] = '';
	$mail_out['message_id'] = $phpgw->msg->make_message_id();
	$mail_out['boundary'] = $phpgw->msg->make_boundary();
	$mail_out['date'] = '';
	$mail_out['main_headers'] = Array();
	$mail_out['body'] = Array();
	$mail_out['is_multipart'] = False;
	$mail_out['num_attachments'] = 0;
	$mail_out['whitespace'] = chr(9);
	$mail_out['is_forward'] = False;
	$mail_out['fwd_proc'] = '';
	// this array gets filled with functiuon "make_rfc_addy_array", but it will have only 1 numbered array, $mail_out['from'][0]
	// note that sending it through make_rfc_addy_array will ensure correct formatting of non us-ascii chars (if any) in the use's fullname
	$mail_out['from'] = Array();
	$mail_out['from'] = $phpgw->msg->make_rfc_addy_array('"'.$phpgw_info['user']['fullname'].'" <'.$phpgw_info['user']['preferences']['email']['address'].'>');
	// this array gets filled with functiuon "make_rfc_addy_array", but it will have only 1 numbered array, $mail_out['sender'][0]
	$mail_out['sender'] = Array();
	$mail_out['charset'] = '';
	$mail_out['msgtype'] = '';

//  -------  Start Filling Array Structure For Outgoing Mail  -----------
	// -----  x-phpgw custom message type RPC-like flag  ------
	// RARELY USED, maybe NEVER used, most implementation code for this is commented out
	if ((isset($phpgw->msg->args['msgtype'])) && ($phpgw->msg->args['msgtype'] != ''))
	{
		// convert script GPC args into useful mail_out structure information
		$mail_out['msgtype'] = $phpgw->msg->args['msgtype'];
		// after this, ONLY USE $mail_out structure for this
	}
	// -----  CHARSET  -----
	if (lang('charset') != '')
	{
		$mail_out['charset'] = lang('charset');
	}
	else
	{
		$mail_out['charset'] = 'US-ASCII';
	}
	// -----  SENDER  -----
	// rfc2822 - sender is only used if some one NOT the author (ex. the author's secretary) is sending the authors email
	if (isset($phpgw->msg->args['sender']) && ($phpgw->msg->args['sender'] != ''))
	{
		// convert script GPC args into useful mail_out structure information
		$mail_out['sender'] = $phpgw->msg->make_rfc_addy_array($phpgw->msg->args['sender']);
		// after this, ONLY USE $mail_out structure for this
	}
	// -----  DATE  -----
	// RFC2822: date *should* be local time with the correct offset, but this is problematic on many Linux boxen
	// FUTURE: figure out a host independant way of getting the correct rfc time and TZ offset
	$mail_out['date'] = gmdate('D, d M Y H:i:s').' +0000';
	// -----  MYMACHINE - The MTA HELO/ELHO DOMAIN ARG  -----
	// rfc2821 sect 4.1.1.1 - almost always the Fully Qualified Domain Name of the SMTP client maching
	// rarely, when the maching has dynamic FQD or no reverse mapping is available, *should* be "address leteral" (see sect 4.1.3)
	$mail_out['mta_elho_mymachine'] = trim($phpgw_info['server']['hostname']);

// ----  Forwarding Detection  -----
	if ((isset($phpgw->msg->args['action']))
	&& ($phpgw->msg->args['action'] == 'forward'))
	{
		// convert script GPC args into useful mail_out structure information
		$mail_out['is_forward'] = True;
		// after this, ONLY USE $mail_out structure for this
	}
	if ((isset($phpgw->msg->args['fwd_proc']))
	&& ($phpgw->msg->args['fwd_proc'] != ''))
	{
		// convert script GPC args into useful mail_out structure information
		$mail_out['fwd_proc'] = $phpgw->msg->args['fwd_proc'];
		// after this, ONLY USE $mail_out structure for this
	}

// ----  Attachment Detection  -----
	$upload_dir = $phpgw->msg->att_files_dir;
	if (file_exists($upload_dir))
	{
		@set_time_limit(0);
		// how many attachments do we need to process?
		$dh = opendir($upload_dir);
		$num_expected = 0;
		while ($file = readdir($dh))
		{
			if (($file != '.')
			&& ($file != '..')
			&& (ereg("\.info",$file)))
			{
				$num_expected++;
			}
		}
		closedir($dh);
		if ($num_expected > 0)
		{
			$mail_out['num_attachments'] = $num_expected;
			$mail_out['is_multipart'] = True;
		}
	}

//  ------  get rid of the escape \ that magic_quotes (if enabled) HTTP POST will add, " becomes \" and  '  becomes  \'
	// convert script GPC args into useful mail_out structure information
	$to = $phpgw->msg->stripslashes_gpc($phpgw->msg->args['to']);
	$cc = $phpgw->msg->stripslashes_gpc($phpgw->msg->args['cc']);
	$body = $phpgw->msg->stripslashes_gpc(trim($phpgw->msg->args['body']));
	$subject = $phpgw->msg->stripslashes_gpc($phpgw->msg->args['subject']);
	// after this,  do NOT use the args for these anymore

	// since arg "body" *may* be huge (and is now in local var $body), lets clear it now
	$phpgw->msg->args['body'] = '';

// ----  DE-code HTML SpecialChars in the body   -----
	// THIS NEEDS TO BE CHANGED WHEN MULTIPLE PART FORWARDS ARE ENABLED
	// BECAUSE WE CAN ONLY ALTER THE 1ST PART, I.E. THE PART THE USER JUST TYPED IN
	/*  // I think the email needs to be sent out as if it were PLAIN text (at least the part we are handling here)
	// i.e. with NO ENCODED HTML ENTITIES, so use > instead of $rt; and " instead of &quot; . etc...
	// it's up to the endusers MUA to handle any htmlspecialchars, whether to encode them or leave as it, the MUA should decide  */
	$body = $phpgw->msg->htmlspecialchars_decode($body);

// ----  Add Email Sig to Body   -----
	if ((isset($phpgw_info['user']['preferences']['email']['email_sig']))
	&& ($phpgw_info['user']['preferences']['email']['email_sig'] != '')
	&& (isset($phpgw->msg->args['attach_sig']))
	&& ($phpgw->msg->args['attach_sig'] != '')
	// ONLY ADD SIG IF USER PUTS TEXT IN THE BODY
	//&& (strlen(trim($body)) > 3))
	&& ($mail_out['is_forward'] == False))
	{
		$user_sig = $phpgw_info['user']['preferences']['email']['email_sig'];
		// html_quotes_decode may be obsoleted someday:  workaround for a preferences database issue (<=pgpgw ver 0.9.13)
		$user_sig = $phpgw->msg->html_quotes_decode($user_sig);
		$body = $body ."\r\n" .'-- '."\r\n" .$user_sig ."\r\n";
	}

// ----  Ensure To: and CC:  and BCC: are properly formatted   -----
	if ($to)
	{
		// mail_out[to] is an array of addresses, each has properties [plain] and [personal]
		$mail_out['to'] = $phpgw->msg->make_rfc_addy_array($to);
		// this will make a simple comma seperated string of the plain addresses (False sets the "include_personal" arg)
		$mta_to = $phpgw->msg->addy_array_to_str($mail_out['to'], False);
	}
	if ($cc)
	{
		$mail_out['cc'] = $phpgw->msg->make_rfc_addy_array($cc);
		$mta_to .= ',' .$phpgw->msg->addy_array_to_str($mail_out['cc'], False);
	}
	// now make mta_to an array because we will loop through it in class mail_send
	$mail_out['mta_to'] = explode(',', $mta_to);
	
	// RFC2821 - RCPT TO: args (email addresses) should be enclosed in brackets
	// when we constructed the $mail_out['mta_to'] var, we set "include_personal" to False, so this array has only "plain" email addys
	for ($i=0; $i<count($mail_out['mta_to']); $i++)
	{
		if (!preg_match('/^<.*>$/', $mail_out['mta_to'][$i]))
		{
			$mail_out['mta_to'][$i] = '<'.$mail_out['mta_to'][$i].'>';
		}
	}

	/*
	// ===== DEBUG =====	
	echo '<br>';
	//$dubug_info = $to;
	//$dubug_info = ereg_replace("\r\n.", "CRLF_WSP", $dubug_info);
	//$dubug_info = ereg_replace("\r\n", "CRLF", $dubug_info);
	//$dubug_info = ereg_replace(" ", "SP", $dubug_info);
	//$dubug_info = $phpgw->msg->htmlspecialchars_encode($dubug_info);
	//echo serialize($dubug_info);

	//$to = $phpgw->msg->addy_array_to_str($to, True);
	//echo 'to including personal: '.$phpgw->msg->htmlspecialchars_encode($to).'<br>';

	echo '<br> var dump mail_out <br>';
	var_dump($mail_out);
	//echo '<br> var dump cc <br>';
	//var_dump($cc);
	echo '<br>';

	$phpgw->common->phpgw_footer();
	exit;
	// ===== DEBUG ===== 
	*/

// ----  Send The Email  ==  via CLASS MAIL SEND 2822  == -----
	// USE CLASS MAIL SEND 2822
	$phpgw->mail_send = CreateObject("email.mail_send");
	$phpgw->mail_send->send_init();
	// do we need to retain a copy of the sent message for the "Sent" folder?
	if($phpgw_info['user']['preferences']['email']['use_sent_folder'])
	{
		$phpgw->mail_send->retain_copy = True;
	}

	// initialize structure for 1st part
	$body_part_num = 0;
	$mail_out['body'][$body_part_num]['mime_headers'] = Array();
	$mail_out['body'][$body_part_num]['mime_body'] = Array();

	// -----  ADD 1st PART's MIME HEADERS  (if necessary)  -------
	if (($mail_out['is_multipart'] == True)
	|| ($mail_out['is_forward'] == True))
	{
		// --- Add Mime Part Header to the First Body Part
		// this part _should_ be text
		$m_line = 0;
		$mail_out['body'][0]['mime_headers'][$m_line] = 'This is a multipart message in MIME format';
		$m_line++;
		$mail_out['body'][0]['mime_headers'][$m_line] = "\r\n";
		$m_line++;
		$mail_out['body'][0]['mime_headers'][$m_line] = '--' .$mail_out['boundary'];
		$m_line++;
		$mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Type: text/plain; charset="'.$mail_out['charset'].'"';
		$m_line++;
		if ($mail_out['msgtype'] != '')
		{
			// "folded header" opens with a "whitespace"
			$mail_out['body'][0]['mime_headers'][$m_line] = '  phpgw-type="'.$mail_out['msgtype'].'"';
			$m_line++;
		}
		// 7 BIT vs. 8 BIT Content-Transfer-Encoding
		// 7 bit means that no chars > 127 can be in the email, or else MTA's will get confused
		// if you really want to enforce 7 bit you should qprint encode the email body
		// however, if you are forwarding via MIME encapsulation then I do not believe it's cool to alter 
		// the original message's content by qprinting it if it was not already qprinted
		// in which case you should send it 8 bit instead.
		// ALSO, the top most level encoding can not be less restrictive than any embedded part's encoding
		// 7bit is more restrictive than 8 bit
		// OPTIONS:
		// 1) send it out with no encoding header - against RFC's but the MTA will probably put it there for you
		// 2) do a scan for chars > 127, if so, send 8 bit and hope the MTA can handle 8 bit
		// 3) scan for > 127 then qprint what we can (not embeded) then send out 7 bit
		// 4) listen to the initial string from the MTA indicating if it can handle MIME8BIT
		// 5) just send it out 8 bit and hope for the best (for now do this)
		//$mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: 7bit';
		//$m_line++;
		$mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: 8bit';
		$m_line++;
		$mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Disposition: inline';
		$m_line++;
	}

	// -----  MAIN BODY PART (1st Part)  ------
	// Explode Body into Array of strings
	$body = $phpgw->msg->normalize_crlf($body);
	$mail_out['body'][$body_part_num]['mime_body'] = explode ("\r\n",$body);
	//$mail_out['body'][$body_part_num]['mime_body'] = $phpgw->msg->explode_linebreaks(trim($body));
	// for no real reason, I add a CRLF to the end of the body
	//$mail_out['body'][$body_part_num]['mime_body'][count($mail_out['body'][$body_part_num]['mime_body'])] = " \r\n";
	// since var $body *may* be huge, lets clear it now
	$body = '';

	// -----  FORWARD HANDLING  ------
	// Sanity Check - we can not "pushdown" a multipart/mixed original mail, it must be encaposulated
	if (($mail_out['is_forward'] == True)
	&& ($mail_out['fwd_proc'] == 'pushdown'))
	{
		//$msg_headers = $phpgw->dcom->header($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
		$msg_headers = $phpgw->msg->phpgw_header('');
		//$msg_struct = $phpgw->dcom->fetchstructure($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
		$msg_struct = $phpgw->msg->phpgw_fetchstructure('');

		$mail_out['fwd_info'] = pgw_msg_struct($msg_struct, $struct_not_set, '1', 1, 1, 1, $phpgw->msg->folder, $phpgw->msg->msgnum);
		if (($mail_out['fwd_info']['type'] == 'multipart')
		|| ($mail_out['fwd_info']['subtype'] == 'mixed'))
		{
			$mail_out['fwd_proc'] = 'encapsulate';
		}
	}

	// Add Forwarded Mail as An Additional Encapsulated "message/rfc822" MIME Part
	if (($mail_out['is_forward'] == True)
	&& ($mail_out['fwd_proc'] == 'pushdown'))
	{
		// -----   INCOMPLETE CODE HERE  --------
		$body_part_num++;
		$mail_out['body'][$body_part_num]['mime_headers'] = Array();
		$mail_out['body'][$body_part_num]['mime_body'] = Array();

		// ----  General Information about The Original Message  -----
		//$msg_headers = $phpgw->dcom->header($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
		$msg_headers = $phpgw->msg->phpgw_header('');
		//$msg_struct = $phpgw->dcom->fetchstructure($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
		$msg_struct = $phpgw->msg->phpgw_fetchstructure('');

		// use the "pgw_msg_struct" function to get the orig message main header info
		$mail_out['fwd_info'] = pgw_msg_struct($msg_struct, $struct_not_set, '1', 1, 1, 1, $phpgw->msg->folder, $phpgw->msg->msgnum);
		// add some more info
		$mail_out['fwd_info']['from'] = $phpgw->msg->make_rfc2822_address($msg_headers->from[0]);
		$mail_out['fwd_info']['date'] = $phpgw->common->show_date($msg_headers->udate);
		$mail_out['fwd_info']['subject'] = $phpgw->msg->get_subject($msg_headers,'');

		// normalize data to rfc2046 defaults, in the event data is not provided
		if ($mail_out['fwd_info']['type'] == $struct_not_set)
		{
			$mail_out['fwd_info']['type'] = 'text';
		}
		if ($mail_out['fwd_info']['subtype'] == $struct_not_set)
		{
			$mail_out['fwd_info']['subtype'] = 'plain';
		}
		if ($mail_out['fwd_info']['disposition'] == $struct_not_set)
		{
			$mail_out['fwd_info']['disposition'] = 'inline';
		}

		$mail_out['fwd_info']['boundary'] = $struct_not_set;
		for ($p = 0; $p < $part_nice['ex_num_param_pairs']; $p++)
		{
			//echo '<br>params['.$p.']: '.$part_nice['params'][$p]['attribute'].'='.$part_nice['params'][$p]['value'] .'<br>';
			if (($part_nice['params'][$p]['attribute'] == 'boundary') 
			  && ($part_nice['params'][$p]['value'] != $struct_not_set))
			{
				$mail_out['fwd_info']['boundary'] = $part_nice['params'][$p]['value'];
				break;
			}
		}
		if ($mail_out['fwd_info']['boundary'] != $struct_not_set)
		{
			// original email ALREADY HAS a boundary., so use it!
			$mail_out['boundary'] = $mail_out['fwd_info']['boundary'];
		}
		//echo '<br>part_nice[boundary] ' .$mail_out['fwd_info']['boundary'] .'<br>';
		//echo '<br>part_nice: <br>' .$phpgw->msg->htmlspecialchars_encode(serialize($mail_out)) .'<br>';

		// prepare the mime part headers
		// original body gets pushed down one part, i.e. was part 1, now is part 2
		$m_line = 0;
		$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = '--' .$mail_out['boundary'];
		$m_line++;
		$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Type: '.$mail_out['fwd_info']['type'].'/'.$mail_out['fwd_info']['subtype'].';';
		$m_line++;
		if ($mail_out['fwd_info']['encoding'] != 'other')
		{
			$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: '.$mail_out['fwd_info']['encoding'];
			$m_line++;			
		}
		for ($p = 0; $p < $part_nice['ex_num_param_pairs']; $p++)
		{
			//echo '<br>params['.$p.']: '.$part_nice['params'][$p]['attribute'].'='.$part_nice['params'][$p]['value'] .'<br>';
			if ($part_nice['params'][$p]['attribute'] != 'boundary') 
			{
				$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = '  '.$part_nice['params'][$p]['attribute'].'="'.$part_nice['params'][$p]['value'].'"';
				$m_line++;
			}
		}
		$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Disposition: '.$mail_out['fwd_info']['disposition'];
		$m_line++;

		// dump the original BODY (with out its headers) here
		//$fwd_this = $phpgw->dcom->get_body($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum);
		$fwd_this = $phpgw->msg->phpgw_body();
		// Explode Body into Array of strings
		$mail_out['body'][$body_part_num]['mime_body'] = $phpgw->msg->explode_linebreaks(trim($fwd_this));
		$fwd_this = '';		
	}
	elseif (($mail_out['is_forward'] == True)
	&& ($mail_out['fwd_proc'] == 'encapsulate'))
	{
		// generate the message/rfc822 part that is the container for the forwarded mail
		$body_part_num++;
		$mail_out['body'][$body_part_num]['mime_headers'] = Array();
		$mail_out['body'][$body_part_num]['mime_body'] = Array();

		// mime headers define this as a message/rfc822 part
		// following RFC2046 recommendations
		$mail_out['body'][$body_part_num]['mime_headers'][0] = '--' .$mail_out['boundary'];
		$mail_out['body'][$body_part_num]['mime_headers'][1] = 'Content-Type: message/rfc822'.';';
		$mail_out['body'][$body_part_num]['mime_headers'][2] = 'Content-Disposition: inline';

		// DUMP the original message verbatim into this part's "body" - i.e. encapsulate the original mail
		//$fwd_this['sub_header'] = trim($phpgw->dcom->fetchheader($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum));
		$fwd_this['sub_header'] = trim($phpgw->msg->phpgw_fetchheader(''));
		$fwd_this['sub_header'] = $phpgw->msg->normalize_crlf($fwd_this['sub_header']);

		// CLENSE headers of offensive artifacts that can confuse dumb MUAs
		$fwd_this['sub_header'] = preg_replace("/^[>]{0,1}From\s.{1,}\r\n/i", "", $fwd_this['sub_header']);
		$fwd_this['sub_header'] = preg_replace("/Received:\s(.{1,}\r\n\s){0,6}.{1,}\r\n(?!\s)/m", "", $fwd_this['sub_header']);
		$fwd_this['sub_header'] = preg_replace("/.{0,3}Return-Path.*\r\n/m", "", $fwd_this['sub_header']);
		$fwd_this['sub_header'] = trim($fwd_this['sub_header']);

		// get the body
		//$fwd_this['sub_body'] = trim($phpgw->dcom->get_body($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum));
		$fwd_this['sub_body'] = trim($phpgw->msg->phpgw_body());
		//$fwd_this['sub_body'] = $phpgw->msg->normalize_crlf($fwd_this['sub_body']);


		// Make Sure ALL INLINE BOUNDARY strings actually have CRLF CRLF preceeding them
		// ---- not yet complete ----
		$char_quot = '"';
		preg_match("/boundary=[$char_quot]{0,1}.*[$char_quot]{0,1}\r\n/",$fwd_this['sub_header'],$fwd_this['matches']);
		if (stristr($fwd_this['matches'][0], 'boundary='))
		{
			$fwd_this['boundaries'] = trim($fwd_this['matches'][0]);
			$fwd_this['boundaries'] = str_replace('boundary=', '', $fwd_this['boundaries']);
			$fwd_this['boundaries'] = str_replace('"', '', $fwd_this['boundaries']);
			$this_boundary = $fwd_this['boundaries'];
			//$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}$this_boundary/m", "\r\n\r\n".'DASHDASH'.$this_boundary, $fwd_this['sub_body']);
			//$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}$this_boundary/m", "\r\n\r\n".'DASHDASH'.$this_boundary, $fwd_this['sub_body']);
			//$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}".$this_boundary."[-]{0,2}/m", "\r\n\r\n".'DASHDASH'.$this_boundary, $fwd_this['sub_body']);
			//$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}$this_boundary/m", "\r\n\r\n".'DASHDASH'.$this_boundary, $fwd_this['sub_body']);
			$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}".$this_boundary."/m", "\r\n\r\n".'--'.$this_boundary, $fwd_this['sub_body']);
			$dash_dash = '--';
			$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}$dash_dash$this_boundary$dash_dash/", "\r\n\r\n".'--'.$this_boundary.'--', $fwd_this['sub_body']);
			$fwd_this['sub_body'] = trim($fwd_this['sub_body']);
		}


		// assemble it and add the blank line that seperates the headers from the body
		$fwd_this['processed'] = $fwd_this['sub_header']."\r\n"."\r\n".$fwd_this['sub_body'];


		/*
		//echo 'fwd_this[sub_header]: <br><pre>'.$phpgw->msg->htmlspecialchars_encode($fwd_this['sub_header']).'</pre><br>';
		//echo 'fwd_this[matches]: <br><pre>'.$phpgw->msg->htmlspecialchars_encode(serialize($fwd_this['matches'])).'</pre><br>';
		//echo 'fwd_this[boundaries]: <br><pre>'.$phpgw->msg->htmlspecialchars_encode($fwd_this['boundaries']).'</pre><br>';
		//echo '=== var dump    fwd_this <br><pre>';
		//var_dump($fwd_this);
		//echo '</pre><br>';			
		echo 'fwd_this[processed]: <br><pre>'.$phpgw->msg->htmlspecialchars_encode($fwd_this['processed']).'</pre><br>';
		unset($fwd_this);
		exit;
		*/


		// Explode Body into Array of strings
		//$fwd_this['processed'] = $phpgw->msg->normalize_crlf($fwd_this['processed']);
		//$mail_out['body'][$body_part_num]['mime_body'] = explode("\r\n", $fwd_this['processed']);
		$mail_out['body'][$body_part_num]['mime_body'] = $phpgw->msg->explode_linebreaks(trim($fwd_this['processed']));
		unset($fwd_this);
	}

	/*
	// ===== DEBUG =====	
	echo '<br>';
	echo '<br>=== mail_out ===<br>';
	$dubug_info = serialize($mail_out);
	$dubug_info = $phpgw->msg->htmlspecialchars_encode($dubug_info);
	echo $dubug_info;
	echo '<br>';
		
	$phpgw->common->phpgw_footer();
	exit;
	// ===== DEBUG ===== 
	*/


	// ---  ATTACHMENTS -- Add each of them as an additional mime part ---
	if ($mail_out['num_attachments'] > 0)
	{
		@set_time_limit(0);
		// process (encode) attachments and add to the email body
		$total_files = 0;
		$dh = opendir($upload_dir);
		while ($file = readdir($dh))
		{
			if (($file != '.')
			&& ($file != '..'))
			{
				if (! ereg("\.info",$file))
				{
					$total_files++;
					$size = filesize($upload_dir.SEP.$file);

					$info_file = $upload_dir.SEP.$file.'.info';
					$file_info = file($info_file);
					$content_type = trim($file_info[0]);
					$content_name = trim($file_info[1]);

					$body_part_num++;
					$mail_out['body'][$body_part_num]['mime_headers'] = Array();
					$mail_out['body'][$body_part_num]['mime_body'] = Array();

					$m_line = 0;
					$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = '--' .$mail_out['boundary'];
					$m_line++;
					$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Type: '.$content_type.'; name="'.$content_name.'"';
					$m_line++;
					$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: base64';
					$m_line++;
					$mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Disposition: attachment; filename="'.$content_name.'"';

					// get the file and base 64 encode it
					$fh = fopen($upload_dir.SEP.$file,'rb');
					// $rawfile = fread($fh,$size);
					$b64_part = chunk_split(base64_encode(fread($fh,$size)));
					$mail_out['body'][$body_part_num]['mime_body'] = explode("\r\n", $b64_part);
					$b64_part = '';
					fclose($fh);

					/*
					/ /  * * * * MOVE THIS INTO MAIL SEND 2822 PROC * * * * *
					// IF LAST PART - GIVE THE "FINAL" boundary
					if ($total_files >= $num_expected)
					{
						// attachments (parts) have their own boundary preceeding them (see below)
						// this is: "--"boundary
						// all boundary strings are have 2 dashes "--" added to their begining
						// and the FINAL boundary string (after all other parts) ALSO has 
						// 2 dashes "--" tacked on tho the end of it, very important !! 
						// the next available array number
						$m_line = count($mail_out['body'][$body_part_num]['mime_body']);
						$mail_out['body'][$body_part_num]['mime_body'][$m_line] = '--' .$mail_out['boundary'].'--';
					}
					//echo 'tot: '.$total_files .' expext: '.$num_expected; // for debugging
					*/

					// delete the temp file (the attachment)
					unlink($upload_dir.SEP.$file);
					// delete the other temp file (the .info file)
					unlink($upload_dir.SEP.$file.'.info');
				}
			}
		}
		// get rid of the temp dir we used for the above
		rmdir($upload_dir);
	}

	// --- MAIN HEADERS  -------
	$hdr_line = 0;
	$mail_out['main_headers'][$hdr_line] = 		'From: '.$phpgw->msg->addy_array_to_str($mail_out['from']);
	$hdr_line++;
	if (count($mail_out['sender'] > 0))
	{
		// rfc2822 - sender is only used if some one NOT the author (ex. the author's secretary) is sending the authors email
		// $mail_out['sender'] is initialized as an empty array in the begining of this file
		// after that, it would be filled if the argument "sender" was passed to the script,
		// then it would have been converted to the appropriate format and put in the $mail_out['sender'] array
		$mail_out['main_headers'][$hdr_line] = 	'Sender: '.$phpgw->msg->addy_array_to_str($mail_out['sender']);
		$hdr_line++;
	}
	//$mail_out['main_headers'][$hdr_line] = 		'Reply-To: '.$phpgw->msg->addy_array_to_str($mail_out['from']);
	//$hdr_line++;
	$mail_out['main_headers'][$hdr_line] = 		'To: '.$phpgw->msg->addy_array_to_str($mail_out['to']);
	$hdr_line++;
	if (count($mail_out['cc']) > 0)
	{
		$mail_out['main_headers'][$hdr_line] = 	'Cc: '.$phpgw->msg->addy_array_to_str($mail_out['cc']);
		$hdr_line++;
	}
	$mail_out['main_headers'][$hdr_line] = 		'Subject: '.$subject;
	$hdr_line++;
	$mail_out['main_headers'][$hdr_line] = 		'Date: '.$mail_out['date'];
	$hdr_line++;
	$mail_out['main_headers'][$hdr_line] = 		'Message-ID: '.$mail_out['message_id'];
	$hdr_line++;
	// RFC2045 REQUIRES this header in even if no embedded mime parts are in the body
	// MTA's, MUA's *should* assume the following as default (RFC2045) if not included
	$mail_out['main_headers'][$hdr_line] = 		'MIME-Version: 1.0';
	$hdr_line++;

	if (($mail_out['is_multipart'] == True)
	|| ($mail_out['is_forward'] == True))
	{
		// THIS MAIL INCLUDES EMBEDED MIME PARTS
		$mail_out['main_headers'][$hdr_line] =	'Content-Type: multipart/mixed;';
		$hdr_line++;
		$mail_out['main_headers'][$hdr_line] =	$mail_out['whitespace'].'boundary="'.$mail_out['boundary'].'"';
		$hdr_line++;
	}
	else
	{
		// NO MIME SUBPARTS - SIMPLE 1 PART MAIL 
		// headers = mime part 0 and  body = mime part 1
		$mail_out['main_headers'][$hdr_line] =	'Content-Type: text/plain;';
		$hdr_line++;
		$mail_out['main_headers'][$hdr_line] =	$mail_out['whitespace'].'charset="'.$mail_out['charset'].'"';
		$hdr_line++;
		// RFC2045 - the next line is *assumed* as default 7bit if it is not included
		// BUT 7bit requires qprinting the body, so let's use 8bit here
		// FUTURE: Content-Transfer-Encoding:  Needs To Match What is In the Body, i.e. may be qprint
		// for now send out as 8 bit and hope for the best (see notes above)
		//$mail_out['main_headers'][$hdr_line] =	'Content-Transfer-Encoding: 7bit';
		//$hdr_line++;
		$mail_out['main_headers'][$hdr_line] =	'Content-Transfer-Encoding: 8bit';
		$hdr_line++;
	
		$mail_out['main_headers'][$hdr_line] =	'Content-Disposition: inline';
		$hdr_line++;
		// Content-Description: this is not really a "technical" header
		// it can be used to inform the person reading some summary info
		//$header .= 'Content-description: Mail message body'."\r\n";
	}

	// finish off the main headers
	if ($mail_out['msgtype'] != '')
	{
		$mail_out['main_headers'][$hdr_line] = 	'X-phpGW-Type: '.$mail_out['msgtype'];
		$hdr_line++;
	}
	$mail_out['main_headers'][$hdr_line] = 	'X-Mailer: phpGroupWare (http://www.phpgroupware.org)';
	$hdr_line++;

	/*
	// ===== DEBUG =====	
	echo '<br>';
	echo '<br>=== mail_out ===<br>';
	$dubug_info = serialize($mail_out);
	$dubug_info = $phpgw->msg->htmlspecialchars_encode($dubug_info);
	echo $dubug_info;
	echo '<br>';
	// ===== DEBUG ===== 
	*/

	// ----  Send It   -----
	$returnccode = $phpgw->mail_send->smail_2822($mail_out);

	/*
	// ===== DEBUG =====	
	echo '<br>';
	echo 'retain_copy: '.serialize($phpgw->mail_send->retain_copy);
	echo '<br>=== POST SEND ===<br>';
	echo '<pre>'.$phpgw->msg->htmlspecialchars_encode($phpgw->mail_send->assembled_copy).'</pre>';
	echo '<br>';
	// ===== DEBUG ===== 
	*/
	

	//  -------  Put in "Sent" Folder, if Applicable  -------
	$skip_this = False;
	//$skip_this = True;

	if (($skip_this == False)
	&& ($returnccode)
	&& ($phpgw_info['user']['preferences']['email']['use_sent_folder']))
	{
		//echo 'ENTERING SENT FOLDER CODE';

		// note: what format should these folder name options (sent and trash) be held in
		// i.e. long or short name form, in the prefs database
		//$sent_folder_name = $phpgw->msg->get_folder_short($phpgw_info['user']['preferences']['email']['sent_folder_name']);
		$sent_folder_name = $phpgw_info['user']['preferences']['email']['sent_folder_name'];

		// NOTE: should we use the existing mailbox stream or initiate a new one just for the append?
		// using a NEW stream *seems* faster, but not sure ???
		/*
		if ((!isset($phpgw->msg->mailsvr_stream))
		|| ($phpgw->msg->mailsvr_stream == ''))
		{
			$stream = $phpgw->dcom->login('INBOX');
			// note: "append" will CHECK  to make sure this folder exists, and try to create it if it does not
			// also note, make sure there is a \r\n CRLF empty last line sequence so Cyrus will be happy
			$phpgw->dcom->append($stream, $sent_folder_name, $phpgw->mail_send->assembled_copy."\r\n", "\\Seen");
			$phpgw->dcom->close($stream);
		}
		else
		{
			// note: "append" will CHECK  to make sure this folder exists, and try to create it if it does not
			// also note, make sure there is a \r\n CRLF empty last line sequence so Cyrus will be happy
			//$phpgw->dcom->append($phpgw->msg->mailsvr_stream,
			$phpgw->msg->phpgw_append($sent_folder_name,
						$phpgw->mail_send->assembled_copy."\r\n",
						"\\Seen");
			//echo 'used existing stream for trash folder';
		//}
		*/

		if ((isset($phpgw->msg->mailsvr_stream))
		&& ($phpgw->msg->mailsvr_stream != ''))
		{
			// note: "append" will CHECK  to make sure this folder exists, and try to create it if it does not
			// also note, make sure there is a \r\n CRLF empty last line sequence so Cyrus will be happy
			//$phpgw->dcom->append($phpgw->msg->mailsvr_stream,
			//echo 'using existing stream for sent folder append<br>';
			$success = $phpgw->msg->phpgw_append($sent_folder_name,
							$phpgw->mail_send->assembled_copy."\r\n",
							"\\Seen");
			//if ($success) { echo 'append to sent OK<br>'; }
			//else { echo 'append to sent FAILED<br>'; echo 'imap_last_error: '.imap_last_error().'<br>'; }
		}
		else
		{
			//echo 'NO STREAM available for sent folder append<br>';
		}

	}

	// ----  Redirect on Success, else show Error Report   -----
	if ($returnccode)
	{
		// Success
		if ($phpgw->mail_send->get_svr_response)
		{
			// for debugging
			$return = trim($return);
			echo '<strong>Here is the communication from the MTA</strong><br><br>'."\r\n";
			echo '<pre>';
			echo $phpgw->msg->htmlspecialchars_encode($phpgw->mail_send->svr_response);
			echo '</pre>';
			echo 'To go back to the msg list, click <a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','cd=13&folder='.urlencode($return)).'">here</a><br>';
			//send_message_cleanup($mailbox);
			send_message_cleanup();
		}
		else
		{
			//header('Location: '.$phpgw->link('index.php','cd=13&folder='.urlencode($return)));
			//$return = ereg_replace ("^\r\n", '', $return);
			// unset some vars (is this necessary?)
			//send_message_cleanup($mailbox);
			send_message_cleanup();
			// what folder to go back to (the one we came from)
			$return = trim($return);
			// redirect the browser to the index page for the appropriate folder
			header('Location: '.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder='.urlencode($return)));
		}
	}
	else
	{
		// ERROR - mail NOT sent
		$return = trim($return);
		echo 'Your message could <B>not</B> be sent!<BR>'."\r\n"
		. 'The mail server returned:<BR>'
			. "err_code: '".$phpgw->mail_send->err['code']."';<BR>"
			. "err_msg: '".htmlspecialchars($phpgw->mail_send->err['msg'])."';<BR>\r\n"
			. "err_desc: '".$phpgw->err['desc']."'.<P>\r\n"
			. 'To go back to the msg list, click <a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','cd=13&folder='.urlencode($return)).'">here</a>';
		//send_message_cleanup($mailbox);
		send_message_cleanup();
	}

	$phpgw->common->phpgw_footer();
?>
