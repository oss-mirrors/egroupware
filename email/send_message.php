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
		//'enable_send_class'	=> True,
		//'use_send_2822'		=> False,
		'enable_send_class'	=> False,
		'use_send_2822'		=> True,
		'noheader'		=> True,
		'nonavbar'		=> True
	);
	
	$phpgw_info['flags'] = $phpgw_flags;
	include('../header.inc.php');

	$struct_not_set = '-1';

//  -------  Init Array Structure For Outgoing Mail  -----------
	$mail_out = Array();
	$mail_out['to'] = Array();
	$mail_out['cc'] = Array();
	$mail_out['bcc'] = Array();
	$mail_out['mta_to'] = Array();
	$mail_out['message_id'] = $phpgw->msg->make_message_id();
	$mail_out['main_headers'] = Array();
	$mail_out['body'] = Array();
	$mail_out['is_multipart'] = False;
	$mail_out['num_attachments'] = 0;
	$mail_out['boundary'] = $phpgw->msg->make_boundary();
	$mail_out['whitespace'] = chr(9);
	$mail_out['is_forward'] = False;
	$mail_out['fwd_proc'] = '';
	// this array gets filled with functiuon "make_rfc_addy_array", but it will have only 1 numbered array, $mail_out['from'][0]
	$mail_out['from'] = Array();
	// this array gets filled with functiuon "make_rfc_addy_array", but it will have only 1 numbered array, $mail_out['sender'][0]
	$mail_out['sender'] = Array();
	$mail_out['mymachine'] = $phpgw_info['server']['hostname'];
	$mail_out['charset'] = '';

//  -------  Start Filling Array Structure For Outgoing Mail  -----------
	$mail_out['from'] = unserialize($phpgw->msg->make_rfc_addy_array('"'.$phpgw_info['user']['fullname'].'" <'.$phpgw_info['user']['preferences']['email']['address'].'>'));
	if (isset($sender) && ($sender))
	{
		$mail_out['sender'] = unserialize($phpgw->msg->make_rfc_addy_array($sender));
	}
	if (lang('charset') != '')
	{
		$mail_out['charset'] = lang('charset');
	}
	else
	{
		$mail_out['charset'] = 'US-ASCII';
	}
	
// ----  Forwarding Detection  -----
	if ((isset($action))
	&& ($action == 'forward'))
	{
		$mail_out['is_forward'] = True;
	}
	if ((isset($fwd_proc))
	&& ($fwd_proc != ''))
	{
		$mail_out['fwd_proc'] = $fwd_proc;
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
	$to = $phpgw->msg->stripslashes_gpc($to);
	$cc = $phpgw->msg->stripslashes_gpc($cc);
	$body = $phpgw->msg->stripslashes_gpc(trim($body));
	$subject = $phpgw->msg->stripslashes_gpc($subject);


// ----  DE-code HTML SpecialChars in the body   -----
	// THIS NEEDS TO BE CHANGED WHEN MULTIPLE PART FORWARDS ARE ENABLED
	// BECAUSE WE CAN ONLY ALTER THE 1ST PART, I.E. THE PART THE USER JUST TYPED IN
	/*  // I think the email needs to be sent out as if it were PLAIN text (at least the part we are handling here)
	// i.e. with NO ENCODED HTML ENTITIES, so use > instead of $rt; and " instead of &quot; . etc...
	// it's up to the endusers MUA to handle any htmlspecialchars, whether to encode them or leave as it, the MUA should decide  */
	$body = $phpgw->msg->htmlspecialchars_decode($body);

// ----  Add Email Sig to Body   -----
	if (($phpgw_info['user']['preferences']['email']['email_sig'])
	&& ($attach_sig)
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
		$mail_out['to'] = unserialize($phpgw->msg->make_rfc_addy_array($to));
		// this will make a simple comma seperated string of the plain addresses
		$mta_to = $phpgw->msg->addy_array_to_str($mail_out['to'], False);
	}
	if ($cc)
	{
		$mail_out['cc'] = unserialize($phpgw->msg->make_rfc_addy_array($cc));
		$mta_to .= ',' .$phpgw->msg->addy_array_to_str($mail_out['cc'], False);
	}
	// now make mta_to an array because we will loop through it in class send_2822
	$mail_out['mta_to'] = explode(',', $mta_to);

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


// ----  Send The Email  == via API CLASS SEND == -----
	if ($phpgw_info['flags']['use_send_2822'] == False)
	{
		// ----  Prepare Body for RFC821 Compliance  -----
		$body = $phpgw->msg->normalize_crlf($body);
		
		// thanks to: Squirrelmail <Luke Ehresman> http://www.squirrelmail.org
		// In order to remove the problem of users not able to create
		// messages with "." on a blank line, RFC821 has made provision  in section 4.5.2 (Transparency). 
		$body = ereg_replace("\n\.", "\n\.\.", $body);
		$body = ereg_replace("^\.", "\.\.", $body);
	
		// ----  Attachment Handling  -----
		$upload_dir = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'];
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
					
						// what boundary do we use?
						if ($total_files >= $num_expected)
						{
							// the "final" boundary
							$mess_boundary = '--Message-Boundary--';
						}
						else
						{
							/* // attachments have their own boundary preceeding them (see below)
							// do not add another one between attachments
							// ( i.e. this particular code loop should not put 2 boundary strings, like it was doing, inbetween each part )
							// or else MUAs will not see the later attachments 
							// all boundary strings are have 2 dashes "--" added to their begining
							// and the FINAL boundary string (after all other parts) ALSO has 
							// 2 dashes "--" tacked on tho the end of it, very important !! */
							$mess_boundary = '';
						}
						//echo 'tot: '.$total_files .' expext: '.$num_expected; // for debugging

						//set_magic_quotes_runtime(0);   MOVED to email/inc/functions.inc.php
						$fh = fopen($upload_dir.SEP.$file,'rb');
						// $rawfile = fread($fh,$size);
						// chunk split will use /r/n as linebreaks by default
						$encoded_attach = chunk_split(base64_encode(fread($fh,$size)));
						fclose($fh);
						//set_magic_quotes_runtime(get_magic_quotes_gpc()); // LEAVE IT OFF

						$body .= "\r\n".'--Message-Boundary'."\r\n"
							. 'Content-type: '.$content_type.'; name="'.$content_name.'"'."\r\n"
							. 'Content-Transfer-Encoding: BASE64'."\r\n"
							. 'Content-disposition: attachment; filename="'.$content_name.'"'."\r\n"."\r\n"
							. $encoded_attach .$mess_boundary ."\r\n";
						unlink($upload_dir.SEP.$file);

						unlink($upload_dir.SEP.$file.'.info');
					}
				}
			}
			rmdir($upload_dir);
		}

		// ----  Format To and CC   -----
		if (count($mail_out['to']) > 0)
		{
			$old_to = $phpgw->msg->addy_array_to_str($mail_out['to'], False);
		}
		if (count($mail_out['cc']) > 0)
		{
			$old_cc = $phpgw->msg->addy_array_to_str($mail_out['cc'], False);
		}
	
		// ----  Send It   -----
		$rc = $phpgw->send->msg('email', $old_to, $subject, $body, '', $old_cc, $bcc);
		
		if ($rc)
		{
			//header('Location: '.$phpgw->link('index.php','cd=13&folder='.urlencode($return)));
			$return = ereg_replace ("^\r\n", '', $return);
			header('Location: '.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder='.urlencode($return)));

		}
		else
		{
			echo 'Your message could <B>not</B> be sent!<BR>'."\n"
			. 'The mail server returned:<BR>'
				. "err_code: '".$phpgw->send_2822->err['code']."';<BR>"
				. "err_msg: '".htmlspecialchars($phpgw->send_2822->err['msg'])."';<BR>\n"
				. "err_desc: '".$phpgw->err['desc']."'.<P>\n"
				. 'To go back to the msg list, click <a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','cd=13&folder='.urlencode($return)).'">here</a>';
		}
	}
	else
	{
// ----  Send The Email  ==  via CLASS SEND_2822  == -----
		// USE CLASS SEND_2822
		$phpgw->send_2822 = CreateObject("email.send_2822");

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
			if ((isset($msgtype)) && ($msgtype))
			{
				// "folded header" opens with a "whitespace"
				$mail_out['body'][0]['mime_headers'][$m_line] = '  phpgw-type="'.$msgtype.'"';
				$m_line++;
			}
			// if we need to do 7bit, then we must qprint the body
			// also, the top most level encoding can not be less restrictive than any embedded part's encoding
			// 7bit is more restrictive than 8 bit
			//$mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: 8bit';
			//$m_line++;
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
		// we can not "pushdown" a multipart/mixed original mail, it must be encaposulated
		if (($mail_out['is_forward'] == True)
		&& ($mail_out['fwd_proc'] == 'pushdown'))
		{
			$msg = $phpgw->msg->header($mailbox, $msgnum);
			$struct = $phpgw->msg->fetchstructure($mailbox, $msgnum);
			
			$mail_out['fwd_info'] = pgw_msg_struct($struct, $struct_not_set, '1', 1, 1, 1, urldecode($folder), $msgnum);
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
			$body_part_num++;
			$mail_out['body'][$body_part_num]['mime_headers'] = Array();
			$mail_out['body'][$body_part_num]['mime_body'] = Array();
			
			// ----  General Information about The Original Message  -----
			$msg = $phpgw->msg->header($mailbox, $msgnum);
			$struct = $phpgw->msg->fetchstructure($mailbox, $msgnum);
			// use the "pgw_msg_struct" function to get the orig message main header info
			$mail_out['fwd_info'] = pgw_msg_struct($struct, $struct_not_set, '1', 1, 1, 1, urldecode($folder), $msgnum);
			// add some more info
			$mail_out['fwd_info']['from'] = $phpgw->msg->make_rfc2822_address($msg->from[0]);
			$mail_out['fwd_info']['date'] = $phpgw->common->show_date($msg->udate);
			$mail_out['fwd_info']['subject'] = $phpgw->msg->get_subject($msg,'');
			
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
			$fwd_this = $phpgw->msg->get_body($mailbox, $msgnum);
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
			$fwd_this['sub_header'] = trim($phpgw->msg->fetchheader($mailbox, $msgnum));
			$fwd_this['sub_header'] = $phpgw->msg->normalize_crlf($fwd_this['sub_header']);
			
			// CLENSE headers of offensive artifacts that can confuse dumb MUAs
			$fwd_this['sub_header'] = preg_replace("/^[>]{0,1}From\s.{1,}\r\n/i", "", $fwd_this['sub_header']);
			$fwd_this['sub_header'] = preg_replace("/Received:\s(.{1,}\r\n\s){0,6}.{1,}\r\n(?!\s)/m", "", $fwd_this['sub_header']);
			$fwd_this['sub_header'] = preg_replace("/.{0,3}Return-Path.*\r\n/m", "", $fwd_this['sub_header']);
			$fwd_this['sub_header'] = trim($fwd_this['sub_header']);
			
			// get the body
			$fwd_this['sub_body'] = trim($phpgw->msg->get_body($mailbox, $msgnum));
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
						/ /  * * * * MOVE THIS INTO SEND_2822 PROC * * * * *
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
		if (isset($sender) && ($sender))
		{
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
		// RFC2822: date *should* be local time with the correct offset, but this is problematic on many machines
		$mail_out['main_headers'][$hdr_line] = 		'Date: '.gmdate('D, d M Y H:i:s').' +0000';
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
			//$mail_out['main_headers'][$hdr_line] =	'Content-Transfer-Encoding: 8bit';
			//$hdr_line++;
			// FUTURE: Content-Transfer-Encoding:  Needs To Match What is In the Body, i.e. may be qprint
			$mail_out['main_headers'][$hdr_line] =	'Content-Disposition: inline';
			$hdr_line++;
			// Content-Description: this is not really a "technical" header
			// it can be used to inform the person reading some summary info
			//$header .= 'Content-description: Mail message body'."\r\n";
		}

		// finish off the main headers
		if (!empty($msgtype))
		{
			$mail_out['main_headers'][$hdr_line] = 	'X-phpGW-Type: '.$msgtype;
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
		$returnccode = $phpgw->send_2822->smail_2822($mail_out);

		
		//  -------  Put in "Sent" Folder, if Applicable  -------
		if (($returnccode == True)
		&& ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'imap')
		&& ($phpgw_info['user']['apps']['email'])
		&& (is_object($phpgw->msg)) )
		{
			$sent_folder_header = '';
			$sent_folder_body = '';
			// --- Assemble the Mail Into A String ----
			for ($i=0; $i<count($mail_out['main_headers']); $i++)
			{
				$sent_folder_header .= $mail_out['main_headers'][$i]."\r\n";
			}
			// this CRLF terminates the header, signals the body will follow next (ONE CRLF ONLY)
			$sent_folder_header .= "\r\n";
			// now we can go to deliver the body!
			for ($part_num=0; $part_num<count($mail_out['body']); $part_num++)
			{
				// mime headers for this mime part (if any)
				if (($mail_out['is_multipart'] == True)
				|| ($mail_out['is_forward'] == True))
				{
					for ($i=0; $i<count($mail_out['body'][$part_num]['mime_headers']); $i++)
					{
						$this_line = rtrim($this_line = $mail_out['body'][$part_num]['mime_headers'][$i])."\r\n";
						$sent_folder_body .= $this_line;
					}
					// a space needs to seperate the mime part headers from the mime part content
					$sent_folder_body .= "\r\n";
				}
				
				
				// the part itself
				for ($i=0; $i<count($mail_out['body'][$part_num]['mime_body']); $i++)
				{
					$this_line = rtrim($mail_out['body'][$part_num]['mime_body'][$i])."\r\n";
					if (trim($this_line) == ".")
					{
						// rfc2822 escape the "special" single dot line into a double dot line
						$this_line = "." .$this_line;
					}
					$sent_folder_body .= $this_line;
				}
				// this space will seperate this part from any following parts that may be coming
				$sent_folder_body .= "\r\n";
			}
			// at the end of a multipart email, we need to add the "final" boundary
			if (($mail_out['is_multipart'] == True)
			|| ($mail_out['is_forward'] == True))
			{
				// attachments / parts have their own boundary preceeding them in their mime headers
				// this is: "--"boundary
				// all boundary strings are have 2 dashes "--" added to their begining
				// and the FINAL boundary string (after all other parts) ALSO has 
				// 2 dashes "--" tacked on tho the end of it, very important !! 
				//   the first or last \r\n is *probably* not necessary
				$final_boundary = '--' .$mail_out['boundary'].'--'."\r\n";
				$sent_folder_body .= $final_boundary;
				// another blank line
				$sent_folder_body .= "\r\n";
			}
			// --- Put This in the User's Sent Folder  -----
			$stream = $phpgw->msg->login('Sent');
			$phpgw->msg->append($stream, 'Sent', $sent_folder_header, $sent_folder_body, "\\Seen");
			$phpgw->msg->close($stream);
		}
		
		// -----  Cleanup  -------
		unset($mail_out);

		// ----  Error Report and Redirect   -----
		if ($returnccode)
		{
			//header('Location: '.$phpgw->link('index.php','cd=13&folder='.urlencode($return)));
			$return = ereg_replace ("^\r\n", '', $return);
			header('Location: '.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder='.urlencode($return)));
		}
		else
		{
			echo 'Your message could <B>not</B> be sent!<BR>'."\r\n"
			. 'The mail server returned:<BR>'
				. "err_code: '".$phpgw->send_2822->err['code']."';<BR>"
				. "err_msg: '".htmlspecialchars($phpgw->send_2822->err['msg'])."';<BR>\r\n"
				. "err_desc: '".$phpgw->err['desc']."'.<P>\r\n"
				. 'To go back to the msg list, click <a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','cd=13&folder='.urlencode($return)).'">here</a>';
		}
	}

	$phpgw->common->phpgw_footer();
?>
