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

	$phpgw_info['flags'] = array(
		'currentapp' => 'email',
		'enable_network_class' => True
	);
	include('../header.inc.php');
	$struct_not_set = '-1';
	//$phpgw->msg->mailsvr_stream

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_compose_out' => 'compose.tpl'
	));
	$t->set_block('T_compose_out','B_checkbox_sig','V_checkbox_sig');

// ----  Handle Replying and Forwarding  -----
	if ($msgnum)
	{
		$msg = $phpgw->dcom->header($phpgw->msg->mailsvr_stream, $msgnum);
		$struct = $phpgw->dcom->fetchstructure($phpgw->msg->mailsvr_stream, $msgnum);

		if ($action == 'reply')
		{
			// if "Reply-To" is specified, use it, or else use the "from" address as the address to reply to
			if ($msg->reply_to[0])
			{
				$reply = $msg->reply_to[0];
			}
			else
			{
				$reply = $msg->from[0];
			}
			$to = $phpgw->msg->make_rfc2822_address($reply);
			$subject = $phpgw->msg->get_subject($msg,'Re: ');
		}
		if ($action == 'replyall')
		{
			if ($msg->to)
			{
				$from = $msg->from[0];
				$from_plain = $from->mailbox.'@'.$from->host;
				// if from and reply-to are the same plain email address, use from instead, it usually has "personal" info
				if ($msg->reply_to[0])
				{
					$reply_to = $msg->reply_to[0];
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
				for ($i = 0; $i < count($msg->to); $i++)
				{
					$topeople = $msg->to[$i];
					$tolist[$i] = $phpgw->msg->make_rfc2822_address($topeople);
				}
				// these spaces after the comma will be taken out in send_message, they are only for user readability here
				$to = implode(", ", $tolist);
				// add $from_or_reply_to to the $to string
				$my_reply_plain = $my_reply->mailbox.'@'.$my_reply->host;
				
				// sometimes, the "To:" and the "Reply-To: / From" are the same, such as with mailing lists
				if (!ereg(".*$my_reply_plain.*", $to))
				{
					// it's ok to add $from_or_reply_to, it is not a duplicate
					$my_reply_addr_spec = $phpgw->msg->make_rfc2822_address($my_reply);
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
						$reply_to_addr_spec = $phpgw->msg->make_rfc2822_address($reply_to);
						$to = $reply_to_addr_spec.', '.$to;
					}
				}
				*/
			}
			if ($msg->cc)
			{
				for ($i = 0; $i < count($msg->cc); $i++)
				{
					$ccpeople = $msg->cc[$i];
					$cclist[$i] = $phpgw->msg->make_rfc2822_address($ccpeople);
				}
				// these spaces after the comma will be taken out in send_message, they are only for user readability here
				$cc = implode(", ", $cclist);
			}
			$subject = $phpgw->msg->get_subject($msg,'Re: ');
		}

		// ----  Begin The Message Body  (of Fw or Re Body) -----
		$who_wrote = $phpgw->msg->get_who_wrote($msg);
		$lang_wrote = 'wrote';
		$body = "\r\n"."\r\n"."\r\n" .$who_wrote .' '. $lang_wrote .': '."\r\n".'>'."\r\n";

		
		// ----  Quoted Bodystring of Fw: or Re: Message is "First Presentable" from message.php  -----
		// passed in the uri as "part_no"
		// FUTURE: Forward needs entirely different handling
		if (isset($part_no)
		&& ($part_no != '')
		&& (($action == 'reply') || ($action == 'replyall')))
		{
			//$bodystring = $phpgw->dcom->fetchbody($mailbox, $msgnum, $part_no);
			$bodystring = $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $msgnum, $part_no);
			// see if we have to un-do qprint encoding
			if ((isset($encoding))
			&& ($encoding == 'qprint'))
			{
				$bodystring = $phpgw->msg->qprint($bodystring);
			}
			$bodystring = $phpgw->msg->normalize_crlf($bodystring);

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
				$bodystring = $phpgw->msg->body_hard_wrap($bodystring, 74);
			}
			$body_array = explode("\r\n", $bodystring);
			$bodycount = count ($body_array);
			for ($bodyidx = 0; $bodyidx < ($bodycount); ++$bodyidx)
			{
				// I think the email needs to be sent out as if it were PLAIN text
				// i.e. with NO ENCODED HTML ENTITIES, so use > instead of $gt; 
				// it's up to the endusers MUA to handle any htmlspecialchars
				$this_line = '>' . trim($body_array[$bodyidx]) ."\r\n";
				$body .= $this_line;
			}
			
			// new - testing this
			// I think the email needs to be sent out as if it were PLAIN text
			// NO ENCODED HTML ENTITIES should be sent over the wire
			// it's up to the endusers MUA to handle any htmlspecialchars
			// Later Note: see RFCs 2045-2049 for what MTA's (note "T") can and can not handle
			$body = $phpgw->msg->htmlspecialchars_decode($body);
		}
		elseif ($action == 'forward')
		{
			// ----- get information from the orig email  --------
			$subject = $phpgw->msg->get_subject($msg,'Fw: ');
			$fwd_info_from = $phpgw->msg->make_rfc2822_address($msg->from[0]);
			$fwd_info_date = $phpgw->common->show_date($msg->udate);
			$fwd_info_subject = $phpgw->msg->get_subject($msg,'');
			
			
			$body = " \r\n"." \r\n"
				.'forward - original mail:'."\r\n"
				.' From ' .$fwd_info_from ."\r\n"
				.' Date ' .$fwd_info_date ."\r\n"
				.' Subject ' .$fwd_info_subject ."\r\n";
			

			//$body = "\r\n"."\r\n".'forwarded mail'."\r\n";
			
			/*
			$part_nice = pgw_msg_struct($struct, $struct_not_set, '1', 1, 1, 1, $phpgw->msg->folder, $msgnum);
			// see if one of the params if the boundry
			$part_nice['boundary'] = $struct_not_set;  // initialize
			for ($p = 0; $p < $part_nice['ex_num_param_pairs']; $p++)
			{
				//echo '<br>params['.$p.']: '.$part_nice['params'][$p]['attribute'].'='.$part_nice['params'][$p]['value'] .'<br>';
				if (($part_nice['params'][$p]['attribute'] == 'boundary') 
				  && ($part_nice['params'][$p]['value'] != $struct_not_set))
				{
					$part_nice['boundary'] = $part_nice['params'][$p]['value'];
					break;
				}
			}
			echo '<br>part_nice[boundary] ' .$part_nice['boundary'] .'<br>';
			//echo '<br>part_nice: <br>' .$phpgw->msg->htmlspecialchars_encode(serialize($part_nice)) .'<br>';
			*/

			/*
			$orig_boundary = '';
			// we are going to re-use the original message's mime boundry from the main headers
			//$orig_headers = $phpgw->dcom->fetchheader($mailbox, $msgnum);
			$orig_headers = $phpgw->dcom->fetchheader($phpgw->msg->mailsvr_stream, $msgnum);
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
			
			//echo '<br>orig_headers <br><pre>' .$phpgw->msg->htmlspecialchars_encode($orig_headers) .'</pre><br>';
			//echo '<br>reg_matches ' .serialize($reg_matches) .'<br>';
			//echo '<br>orig_boundary ' .$orig_boundary .'<br>';
			//echo '<br>struct: <br>' .$phpgw->msg->htmlspecialchars_encode(serialize($struct)) .'<br>';
			*/
			
		}
		// ----  "the OLD WAY": Process Multiple Body Parts (if necessary)  of Fw or Re Body   -----
		elseif (!$struct->parts)
		{
			$numparts = "1";
		}
		else
		{
			$numparts = count($struct->parts);
		}
		for ($i = 0; $i < $numparts; $i++)
		{
			if (!$struct->parts[$i])
			{
				$part = $struct;
			}
			else
			{
				$part = $struct->parts[$i];
			}
			if (get_att_name($part) == "Unknown")
			{
				if (strtoupper($part->subtype) == 'PLAIN')
				{
					$bodystring = $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $msgnum, $i+1);
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
							$this_line = '>' . trim($body_array[$bodyidx]) ."\r\n";
							$body .= $this_line;
						}
					}
					trim ($body);
					// I think the email needs to be sent out as if it were PLAIN text
					// NO ENCODED HTML ENTITIES should be sent over the wire
					// it's up to the endusers MUA to handle any htmlspecialchars
					$body = $phpgw->msg->htmlspecialchars_decode($body);
				}
			}
		}
		// so what goes in the to and cc box
		$to_box_value = $to;
		$cc_box_value = $cc;
	}
	else
	{
		// no var $msgnum  means we were not called by the reply, replyall, or forward
		// i do NOT what page calls this page with the var mailto in the url
		if ($mailto)
		{
			$to_box_value = substr($mailto, 7, strlen($mailto));
		}
		// called fromthe message list, most likely
		elseif ((isset($to)) && ($to) && (isset($personal)) && ($personal)
		&& (urldecode($personal) != urldecode($to)) )
		{
			$to = $phpgw->msg->stripslashes_gpc($to);
			$personal = $phpgw->msg->stripslashes_gpc($personal);
			$to_box_value = $phpgw->msg->htmlspecialchars_encode('"'.urldecode($personal).'" <'.urldecode($to).'>');
		}
		elseif ((isset($to)) && ($to))
		{
			$to = $phpgw->msg->stripslashes_gpc($to);
			$to_box_value = urldecode($to);
		}
		else
		{
			$to_box_value = '';
		}
	}

	if ((isset($action))
	&& ($action == 'forward'))
	{
		$send_btn_action = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/send_message.php',
			'action=forward&folder='.$phpgw->msg->prep_folder_out('').'&msgnum='.$msgnum);
		if (isset($fwd_proc))
		{
			$send_btn_action = $send_btn_action .'&fwd_proc='.$fwd_proc;
		}
	}
	else
	{
		$send_btn_action = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/send_message.php');
	}
	
	$t->set_var('js_addylink',$phpgw->link("/".$phpgw_info['flags']['currentapp'].'/addressbook.php'));
	$t->set_var('form1_name','doit');
	$t->set_var('form1_action',$send_btn_action);
	$t->set_var('form1_method','POST');
	$t->set_var('hidden1_name','return');
	$t->set_var('hidden1_value',$phpgw->msg->prep_folder_out(""));

	$t->set_var('buttons_bgcolor',$phpgw_info["theme"]["em_folder"]);
	$t->set_var('btn_addybook_type','button');
	$t->set_var('btn_addybook_value',lang("addressbook"));
	$t->set_var('btn_addybook_onclick','addybook();');
	$t->set_var('btn_send_type','submit');
	$t->set_var('btn_send_value',lang("send"));

	$t->set_var('to_boxs_bgcolor',$phpgw_info["theme"]["th_bg"]);
	$t->set_var('to_boxs_font',$phpgw_info["theme"]["font"]);
	$t->set_var('to_box_desc',lang("to"));
	$t->set_var('to_box_name','to');
	// to_box_value set above
	$t->set_var('to_box_value',$to_box_value);
	$t->set_var('cc_box_desc',lang("cc"));
	$t->set_var('cc_box_name','cc');
	//$t->set_var('cc_box_value',$cc);
	$t->set_var('cc_box_value',$cc_box_value);
	$t->set_var('subj_box_desc',lang("subject"));
	$t->set_var('subj_box_name','subject');
	$t->set_var('subj_box_value',$subject);
	$t->set_var('checkbox_sig_desc',lang("Attach signature"));
	$t->set_var('checkbox_sig_name','attach_sig');
	$t->set_var('checkbox_sig_value','true');
	if (isset($phpgw_info['user']['preferences']['email']['email_sig'])
	&& ($phpgw_info['user']['preferences']['email']['email_sig'] != ''))
	{
		$t->parse('V_checkbox_sig','B_checkbox_sig');
	}
	else
	{
		$t->set_var('V_checkbox_sig','');
	}
	$t->set_var('attachfile_js_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/attach_file.php'));
	$t->set_var('attachfile_js_text',lang("Attach file"));
	$t->set_var('body_box_name','body');
	$t->set_var('body_box_value',$body);

	$t->pparse('out','T_compose_out');

	$phpgw->msg->end_request();
 
	$phpgw->common->phpgw_footer();
?>
