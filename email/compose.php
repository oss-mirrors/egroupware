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

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'email',
		'enable_network_class' => True
	);
	include('../header.inc.php');
	$not_set = $GLOBALS['phpgw']->msg->not_set;

	$GLOBALS['phpgw']->template->set_file(
		Array(
			'T_compose_out' => 'compose.tpl'
		)
	);
	$GLOBALS['phpgw']->template->set_block('T_compose_out','B_checkbox_sig','V_checkbox_sig');

// ----  Handle Replying and Forwarding  -----
	if ($GLOBALS['phpgw']->msg->get_arg_value('msgnum'))
	{
		//$msg = $GLOBALS['phpgw']->dcom->header($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream'), $GLOBALS['phpgw']->msg->get_arg_value('msgnum'));
		$msg_headers = $GLOBALS['phpgw']->msg->phpgw_header('');
		//$struct = $GLOBALS['phpgw']->dcom->fetchstructure($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream'), $GLOBALS['phpgw']->msg->get_arg_value('msgnum'));
		$msg_struct = $GLOBALS['phpgw']->msg->phpgw_fetchstructure('');

		if ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'reply')
		{
			// if "Reply-To" is specified, use it, or else use the "from" address as the address to reply to
			if ($msg_headers->reply_to[0])
			{
				$reply = $msg_headers->reply_to[0];
			}
			else
			{
				$reply = $msg_headers->from[0];
			}
			$to = $GLOBALS['phpgw']->msg->make_rfc2822_address($reply);
			$subject = $GLOBALS['phpgw']->msg->get_subject($msg_headers,'Re: ');
		}
		if ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'replyall')
		{
			if ($msg_headers->to)
			{
				$from = $msg_headers->from[0];
				$from_plain = $from->mailbox.'@'.$from->host;
				// if from and reply-to are the same plain email address, use from instead, it usually has "personal" info
				if ($msg_headers->reply_to[0])
				{
					$reply_to = $msg_headers->reply_to[0];
					$reply_to_plain = $reply_to->mailbox.'@'.$reply_to->host;
					if ($reply_to_plain != $from_plain)
					{
						$my_reply = $reply_to;
					}
					else
					{
						// we don't need reply-to then
						$my_reply = $from;
					}
				}
				else
				{
					$my_reply = $from;
				}
				for ($i = 0; $i < count($msg_headers->to); $i++)
				{
					$topeople = $msg_headers->to[$i];
					$tolist[$i] = $GLOBALS['phpgw']->msg->make_rfc2822_address($topeople);
				}
				// these spaces after the comma will be taken out in send_message, they are only for user readability here
				$to = implode(", ", $tolist);
				// add $from_or_reply_to to the $to string
				$my_reply_plain = $my_reply->mailbox.'@'.$my_reply->host;
				
				// sometimes, the "To:" and the "Reply-To: / From" are the same, such as with mailing lists
				if (!ereg(".*$my_reply_plain.*", $to))
				{
					// it's ok to add $from_or_reply_to, it is not a duplicate
					$my_reply_addr_spec = $GLOBALS['phpgw']->msg->make_rfc2822_address($my_reply);
					$to = $my_reply_addr_spec.', '.$to;
				}
				/*// RFC2822 leaves the following as an option:
				// use the "from" addy in replyall even if "reply-to" was specified
				if (($reply_to != '') && ($reply_to_plain != ''))
				{
					// this means reply-to is not the same as From
					// sometimes, the "Reply-To:" may be duplicated in the To headers
					if (!ereg(".*$reply_to_plain.*", $to))
					{
						// it's ok to add $reply_to, it is not a duplicate
						$reply_to_addr_spec = $GLOBALS['phpgw']->msg->make_rfc2822_address($reply_to);
						$to = $reply_to_addr_spec.', '.$to;
					}
				}
				*/
			}
			if ($msg_headers->cc)
			{
				for ($i = 0; $i < count($msg_headers->cc); $i++)
				{
					$ccpeople = $msg_headers->cc[$i];
					$cclist[$i] = $GLOBALS['phpgw']->msg->make_rfc2822_address($ccpeople);
				}
				// these spaces after the comma will be taken out in send_message, they are only for user readability here
				$cc = implode(", ", $cclist);
			}
			$subject = $GLOBALS['phpgw']->msg->get_subject($msg_headers,'Re: ');
		}

		// ----  Begin The Message Body  (of Fw or Re Body) -----
		$who_wrote = $GLOBALS['phpgw']->msg->get_who_wrote($msg_headers);
		$lang_wrote = lang('wrote');
		$body = "\r\n"."\r\n"."\r\n" .$who_wrote .' '. $lang_wrote .': '."\r\n".'>'."\r\n";

		
		// ----  Quoted Bodystring of Fw: or Re: Message is "First Presentable" from message.php  -----
		// passed in the uri as "part_no"
		// FUTURE: Forward needs entirely different handling
		if (($GLOBALS['phpgw']->msg->get_isset_arg('part_no'))
		&& ($GLOBALS['phpgw']->msg->get_arg_value('part_no') != '')
		&& (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'reply')
		  || ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'replyall')))
		{
			//$bodystring = $GLOBALS['phpgw']->dcom->fetchbody($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream'), $GLOBALS['phpgw']->msg->get_arg_value('msgnum'), $GLOBALS['phpgw']->msg->get_arg_value('part_no'));
			$bodystring = $GLOBALS['phpgw']->msg->phpgw_fetchbody($GLOBALS['phpgw']->msg->get_arg_value('part_no'));
			// see if we have to un-do qprint encoding
			if ((($GLOBALS['phpgw']->msg->get_isset_arg('encoding')))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('encoding') == 'qprint'))
			{
				$bodystring = $GLOBALS['phpgw']->msg->qprint($bodystring);
			}
			$bodystring = $GLOBALS['phpgw']->msg->normalize_crlf($bodystring);

			// ----- Remove Email "Personal Signature" from Quoted Body  -----
			// RFC's unofficially suggest you remove the "personal signature" before quoting the body
			// a standard sig begins with "-- CRFL", that's [dash][dash][space][CRLF]
			// and *should* be no more than 4 lines in length, followed by a CFLF
			//$bodystring = preg_replace("/--\s{0,1}\r\n.{1,}\r\n\r\n/smx", "BLAA", $bodystring);
			//$bodystring = preg_replace("/--\s{0,1}\r\n(.{1,}\r\n){1,5}/smx", "", $bodystring);
			// sig = "dash dash space CRLF (anything and CRLF) repeated 1 to 5 times"
			//$bodystring = preg_replace("/--\s{0,1}\r\n.(?!>)(.{1,}\r\n){1,5}/smx", "", $bodystring);
			$bodystring = preg_replace("/\r\n[-]{2}\s{0,1}\r\n\w.{0,}\r\n(.{1,}\r\n){0,4}/", "\r\n", $bodystring);
			// sig = "CRLF dash dash space(0or1) CRLF anyWordChar anything CRLF (anything and CRLF) repeated 0 to 4 times"

			//now is a good time to trim the body
			//trim($body);
			trim($bodystring);

			// ----- Quote The Body You Are Replying To With >  ------
			$body_array = array();
			if (!ereg("\r\n", $bodystring))
			{
				$bodystring = $GLOBALS['phpgw']->msg->body_hard_wrap($bodystring, 74);
			}
			$body_array = explode("\r\n", $bodystring);
			$bodycount = count ($body_array);
			for ($bodyidx = 0; $bodyidx < ($bodycount); ++$bodyidx)
			{
				// I think the email needs to be sent out as if it were PLAIN text
				// i.e. with NO ENCODED HTML ENTITIES, so use > instead of $gt; 
				// it's up to the endusers MUA to handle any htmlspecialchars
				//$this_line = '>' . trim($body_array[$bodyidx]) ."\r\n";
				// NOTE: I see NO reason to trim the LEFT part of the string, use RTRIM instead
				$this_line = '>' . rtrim($body_array[$bodyidx]) ."\r\n";
				$body .= $this_line;
			}
			
			// new - testing this
			// I think the email needs to be sent out as if it were PLAIN text
			// NO ENCODED HTML ENTITIES should be sent over the wire
			// it's up to the endusers MUA to handle any htmlspecialchars
			// Later Note: see RFCs 2045-2049 for what MTA's (note "T") can and can not handle
			$body = $GLOBALS['phpgw']->msg->htmlspecialchars_decode($body);
		}
		elseif ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'forward')
		{
			// ----- get information from the orig email  --------
			$subject = $phpgw->msg->get_subject($msg_headers,'Fw: ');
			$fwd_info_from = $GLOBALS['phpgw']->msg->make_rfc2822_address($msg_headers->from[0]);
			$fwd_info_date = $GLOBALS['phpgw']->common->show_date($msg_headers->udate);
			$fwd_info_subject = $GLOBALS['phpgw']->msg->get_subject($msg_headers,'');
			
			//$body = " \r\n"." \r\n"
			$body = "\r\n"."\r\n"
				.'forward - original mail:'."\r\n"
				.' From ' .$fwd_info_from ."\r\n"
				.' Date ' .$fwd_info_date ."\r\n"
				.' Subject ' .$fwd_info_subject ."\r\n";
			

			//$body = "\r\n"."\r\n".'forwarded mail'."\r\n";
			
			/*
			$part_nice = pgw_msg_struct($msg_struct, $not_set, '1', 1, 1, 1, $GLOBALS['phpgw']->msg->get_arg_value('folder'), $GLOBALS['phpgw']->msg->get_arg_value('msgnum'));
			// see if one of the params if the boundry
			$part_nice['boundary'] = $not_set;  // initialize
			for ($p = 0; $p < $part_nice['ex_num_param_pairs']; $p++)
			{
				//echo '<br>params['.$p.']: '.$part_nice['params'][$p]['attribute'].'='.$part_nice['params'][$p]['value'] .'<br>';
				if (($part_nice['params'][$p]['attribute'] == 'boundary') 
				  && ($part_nice['params'][$p]['value'] != $not_set))
				{
					$part_nice['boundary'] = $part_nice['params'][$p]['value'];
					break;
				}
			}
			echo '<br>part_nice[boundary] ' .$part_nice['boundary'] .'<br>';
			//echo '<br>part_nice: <br>' .$GLOBALS['phpgw']->msg->htmlspecialchars_encode(serialize($part_nice)) .'<br>';
			*/

			/*
			$orig_boundary = '';
			// we are going to re-use the original message's mime boundry from the main headers
			//$orig_headers = $GLOBALS['phpgw']->dcom->fetchheader($mailbox, $GLOBALS['phpgw']->msg->get_arg_value('msgnum'));
			$orig_headers = $GLOBALS['phpgw']->dcom->fetchheader($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream'), $GLOBALS['phpgw']->msg->get_arg_value('msgnum'));
			$did_match = preg_match('/(boundary=["]?)(.*)(["]?.*(\r|\n))/ix', $orig_headers, $reg_matches);
			if (($did_match) && (isset($reg_matches[1])) && (isset($reg_matches[2]))
			&& (stristr($reg_matches[1], 'boundary')) && ($reg_matches[2] != ''))
			{
				$orig_boundary = trim($reg_matches[2]);
				if ($orig_boundary[strlen($orig_boundary)-1] == '"')
				{
					$grab_to = strlen($orig_boundary) - 1;
					$orig_boundary = substr($orig_boundary, 0, $grab_to);
				}
			}
			
			//echo '<br>orig_headers <br><pre>' .$GLOBALS['phpgw']->msg->htmlspecialchars_encode($orig_headers) .'</pre><br>';
			//echo '<br>reg_matches ' .serialize($reg_matches) .'<br>';
			//echo '<br>orig_boundary ' .$orig_boundary .'<br>';
			//echo '<br>struct: <br>' .$GLOBALS['phpgw']->msg->htmlspecialchars_encode(serialize($msg_struct)) .'<br>';
			*/
			
		}
		// ----  "the OLD WAY": Process Multiple Body Parts (if necessary)  of Fw or Re Body   -----
		// IS THIS STILL USED ?????
		elseif (!$msg_struct->parts)
		{
			$numparts = '1';
		}
		else
		{
			$numparts = count($msg_struct->parts);
		}
		for ($i = 0; $i < $numparts; $i++)
		{
			if (!$msg_struct->parts[$i])
			{
				$part = $msg_struct;
			}
			else
			{
				$part = $msg_struct->parts[$i];
			}
			if (get_att_name($part) == 'Unknown')
			{
				if (strtoupper($part->subtype) == 'PLAIN')
				{
					//$bodystring = $GLOBALS['phpgw']->dcom->fetchbody($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream'), $GLOBALS['phpgw']->msg->get_arg_value('msgnum'), $i+1);
					$bodystring = $GLOBALS['phpgw']->msg->phpgw_fetchbody($i+1);
					$body_array = array();
					$body_array = explode("\n", $bodystring);
					$bodycount = count ($body_array);
					for ($bodyidx = 0; $bodyidx < ($bodycount -1); ++$bodyidx)
					{
						if ($body_array[$bodyidx] != "\r")
						{
							//$body .= "&gt;" . $body_array[$bodyidx];
							// I think the email needs to be sent out as if it were PLAIN text
							// i.e. with NO ENCODED HTML ENTITIES, so use > instead of $rt; 
							// it's up to the endusers MUA to handle any htmlspecialchars
							//$this_line = '>' . trim($body_array[$bodyidx]) ."\r\n";
							// NOTE: I see NO reason to trim the LEFT part of the string, use RTRIM instead
							$this_line = '>' . rtrim($body_array[$bodyidx]) ."\r\n";
							$body .= $this_line;
						}
					}
					trim ($body);
					// I think the email needs to be sent out as if it were PLAIN text
					// NO ENCODED HTML ENTITIES should be sent over the wire
					// it's up to the endusers MUA to handle any htmlspecialchars
					$body = $GLOBALS['phpgw']->msg->htmlspecialchars_decode($body);
				}
			}
		}
		// so what goes in the to and cc box
		$to_box_value = $to;
		$cc_box_value = $cc;
	}
	else
	{
		// no var $phpgw->msg->msgnum  means we were not called by the reply, replyall, or forward
		// this typically is only called when the user clicks on a mailto: link in an html document
		// this behavior defines what your "default mail app" is, i.e. what mail app is called when
		// the user clicks a "mailto:" link
		if ($GLOBALS['phpgw']->msg->get_arg_value('mailto'))
		{
			$to_box_value = substr($GLOBALS['phpgw']->msg->get_arg_value('mailto'), 7, strlen($GLOBALS['phpgw']->msg->get_arg_value('mailto')));
		}
		// called from the message list (index.php), most likely,
		//  or from message.php if user clicked on an individual address in the to or cc fields
		elseif ((($GLOBALS['phpgw']->msg->get_isset_arg('to')))
		&& ($GLOBALS['phpgw']->msg->get_arg_value('to') != '')
		&& (($GLOBALS['phpgw']->msg->get_isset_arg('personal')))
		&& ($GLOBALS['phpgw']->msg->get_arg_value('personal') != '')
		&& (urldecode($GLOBALS['phpgw']->msg->get_arg_value('personal')) != urldecode($GLOBALS['phpgw']->msg->get_arg_value('to'))) )
		{
			$GLOBALS['phpgw']->msg->set_arg_value('to', $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('to')));
			$GLOBALS['phpgw']->msg->set_arg_value('personal', $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('personal')));
			$to_box_value = $GLOBALS['phpgw']->msg->htmlspecialchars_encode('"'.urldecode($GLOBALS['phpgw']->msg->get_arg_value('personal')).'" <'.urldecode($GLOBALS['phpgw']->msg->get_arg_value('to')).'>');
		}
		elseif ((($GLOBALS['phpgw']->msg->get_isset_arg('to')))
		&& ($GLOBALS['phpgw']->msg->get_arg_value('to') != ''))
		{
			$GLOBALS['phpgw']->msg->set_arg_value('to', $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('to')));
			$to_box_value = urldecode($GLOBALS['phpgw']->msg->get_arg_value('to'));
		}
		else
		{
			$to_box_value = '';
		}
	}

	if ((($GLOBALS['phpgw']->msg->get_isset_arg('action')))
	&& ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'forward'))
	{
		//$send_btn_action = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/send_message.php',
		//	'action=forward&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('').'&msgnum='.$GLOBALS['phpgw']->msg->get_arg_value('msgnum'));
		$send_btn_action = $GLOBALS['phpgw']->link('/index.php',
				 $GLOBALS['phpgw']->msg->get_arg_value('send_menuaction')
				.'$action=forward'
				.'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
				.'&msgnum='.$GLOBALS['phpgw']->msg->get_arg_value('msgnum'));
		if (($GLOBALS['phpgw']->msg->get_isset_arg('fwd_proc')))
		{
			$send_btn_action = $send_btn_action .'&fwd_proc='.$GLOBALS['phpgw']->msg->get_arg_value('fwd_proc');
		}
	}
	else
	{
		//$send_btn_action = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/send_message.php');
		$send_btn_action = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->get_arg_value('send_menuaction'));
	}
	
	$tpl_vars = Array(
		'js_addylink'	=> $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php'),
		'form1_name'	=> 'doit',
		'form1_action'	=> $send_btn_action,
		'form1_method'	=> 'POST',
		'hidden1_name'	=> 'return',
		'hidden1_value'	=> $GLOBALS['phpgw']->msg->prep_folder_out(''),

		'buttons_bgcolor'	=> $GLOBALS['phpgw_info']['theme']['em_folder'],
		'btn_addybook_type'	=> 'button',
		'btn_addybook_value'	=> lang('addressbook'),
		'btn_addybook_onclick'	=> 'addybook();',
		'btn_send_type'		=> 'submit',
		'btn_send_value'	=> lang('send'),

		'to_boxs_bgcolor'	=> $GLOBALS['phpgw_info']['theme']['th_bg'],
		'to_boxs_font'		=> $GLOBALS['phpgw_info']['theme']['font'],
		'to_box_desc'		=> lang('to'),
		'to_box_name'		=> 'to',
		// to_box_value set above
		'to_box_value'		=> $to_box_value,
		'cc_box_desc'		=> lang('cc'),
		'cc_box_name'		=> 'cc',
		//'cc_box_value'		=> $cc,
		'cc_box_value'		=> $cc_box_value,
		'subj_box_desc'		=> lang('subject'),
		'subj_box_name'		=> 'subject',
		'subj_box_value'	=> $subject,
		'checkbox_sig_desc'	=> lang('Attach signature'),
		'checkbox_sig_name'	=> 'attach_sig',
		'checkbox_sig_value'	=> 'true'
	);
	$GLOBALS['phpgw']->template->set_var($tpl_vars);
	if ($GLOBALS['phpgw']->msg->get_isset_pref('email_sig')
	&& ($GLOBALS['phpgw']->msg->get_pref_value('email_sig') != ''))
	{
		$GLOBALS['phpgw']->template->parse('V_checkbox_sig','B_checkbox_sig');
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('V_checkbox_sig','');
	}
	$tpl_vars = Array(
		'attachfile_js_link'	=> $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/attach_file.php'),
		'attachfile_js_text'	=> lang('Attach file'),
		'body_box_name'		=> 'body',
		'body_box_value'	=> $body
	);
	$GLOBALS['phpgw']->template->set_var($tpl_vars);
	$GLOBALS['phpgw']->template->pparse('out','T_compose_out');

	$GLOBALS['phpgw']->msg->end_request();
 
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
