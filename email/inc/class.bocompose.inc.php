<?php
	/**************************************************************************\
	* phpGroupWare - email BO Class	for Folder Actions and List Display		*
	* http://www.phpgroupware.org							*
	* Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
	* --------------------------------------------							*
	*  This program is free software; you can redistribute it and/or modify it		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your		*
	*  option) any later version.								*
	\**************************************************************************/
	
	/* $Id$ */
	
	class bocompose
	{
		var $public_functions = array(
			'get_langed_labels'	=> True,
			'compose'		=> True
		);
		//var $debug = True;
		var $debug = False;
		var $xi;
		var $xml_functions = array();
		
		var $soap_functions = array(
			'get_langed_labels' => array(
				'in'  => array('int'),
				'out' => array('array')
			),
			'compose' => array(
				'in'  => array('array'),
				'out' => array('int')
			)
		);
		
		function bocompose()
		{
			
		}
		
		function compose()
		{
			$not_set = $GLOBALS['phpgw']->msg->not_set;
			
			// attempt (or not) to reuse an existing mail_msg object, i.e. if one ALREADY exists before entering
			//$attempt_reuse = True;
			$attempt_reuse = False;
			
			if ($this->debug) { echo 'ENTERING: email.bocompose.compose'.'<br>'; }
			if ($this->debug) { echo 'email.bocompose.compose: local var attempt_reuse=['.serialize($attempt_reuse).'] ; reuse_feed_args[] dump<pre>'; print_r($reuse_feed_args); echo '</pre>'; }
			// create class objects
			//$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug) { echo 'email.bocompose.compose: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug) { echo 'email.bocompose.compose: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			// do we attempt to reuse the existing msg object?
			if ($attempt_reuse)
			{
				// no not create, we will reuse existing
				if ($this->debug) { echo 'email.bocompose.compose: reusing existing mail_msg login'.'<br>'; }
				// we need to feed the existing object some params begin_request uses to re-fill the msg->args[] data
				$args_array = Array();
				// any args passed in $args_array will override or replace any pre-existing arg value
				$args_array = $reuse_feed_args;
				// add this to keep the error checking code (below) happy
				$args_array['do_login'] = True;
			}
			else
			{
				if ($this->debug) { echo 'email.bocompose.compose: cannot or not trying to reusing existing'.'<br>'; }
				$args_array = Array();
				// should we log in or not
				$args_array['do_login'] = True;
			}
			
			// "start your engines"
			if ($this->debug == True) { echo 'email.bocompose.compose: call msg->begin_request with args array:<pre>'; print_r($args_array); echo '</pre>'; }
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			// error if login failed
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', compose()');
			}
			
			// ---- BEGIN BO COMPOSE
			
			
			// ----  Handle Replying and Forwarding  -----
			if ($GLOBALS['phpgw']->msg->get_isset_arg('["msgball"]["msgnum"]'))
			{
				$msgball = $GLOBALS['phpgw']->msg->get_arg_value('msgball');
				$msg_headers = $GLOBALS['phpgw']->msg->phpgw_header($msgball);
				$msg_struct = $GLOBALS['phpgw']->msg->phpgw_fetchstructure($msgball);
				
				// ----  initial handling of Replying  -----
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
				elseif ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'replyall')
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
				
				// ... we just did initial processing of reply / replyall actions...
				// (processing for forwaring mail is further down)
				// so continue with reply / replyall processing ...
				
				if (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'reply')
				|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'replyall'))
				{
					// ----  Begin The Message Body  (of the body we are replying to) -----
					$who_wrote = $GLOBALS['phpgw']->msg->get_who_wrote($msg_headers);
					$lang_wrote = lang('wrote');
					$body =	 "\r\n"
						."\r\n"
						."\r\n".$who_wrote .' '.$lang_wrote.': '
						."\r\n".'>'
						."\r\n";
					
					// ----  Quoted Bodystring of Re: Message is the "First Presentable" part  -----
					// as determimed in class.bomessage and passed in the uri as "msgball[part_no]=X.X"
					// most emails have many MIME parts, some may actually be blank, we do not want to
					// reply to a blank part, that would look dumb and is not correct behavior. Instead, we want
					// to quote the first body port that has some text, which could be anywhere.
					// NOTE: we should ALWAYS get a "First Presentable" value from class.bomessage
					// if not (a rare and screwed up situation) then assume msgball[part_no]=1
					if ((!isset($msgball['part_no']))
					|| ($msgball['part_no'] == ''))
					{
						// this *should* never happen, we should always get a good "First Presentable"
						// value in $msgball['part_no'] , but we can assume the first part if not specified
						$msgball['part_no'] = '1';
					}
					
					$bodystring = $GLOBALS['phpgw']->msg->phpgw_fetchbody($msgball);
					// see if we have to un-do qprint (or other) encoding of the part we are about to quote
					if ($GLOBALS['phpgw']->msg->get_isset_arg('encoding'))
					{
						// see if we have to un-do qprint encoding (fairly common)
						if ($GLOBALS['phpgw']->msg->get_arg_value('encoding') == 'qprint')
						{
							$bodystring = $GLOBALS['phpgw']->msg->qprint($bodystring);
						}
						// *rare, maybe never seen* see if we have to un-do base64 encoding
						elseif ($GLOBALS['phpgw']->msg->get_arg_value('encoding') == 'qprint')
						{
							// a human readable body part (non-attachment) should NOT be base64 encoded
							// but you can never account for idiots
							$bodystring = $GLOBALS['phpgw']->msg->de_base64($bodystring);
						}
					}
					// "normalize" all line breaks into CRLF pairs
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
					trim($bodystring);
					
					// ----- Quote The Body You Are Replying To With ">"  ------
					$body_array = array();
					// we need *some* line breaks in the body so we know where to add the ">" quoting char(s)
					// some relatively short emails may not have any CRLF pairs, but may have a few real long lines
					//so, add linebreaks to the body if none are already existing
					if (!ereg("\r\n", $bodystring))
					{
						// aim for a 74-80 char line length
						$bodystring = $GLOBALS['phpgw']->msg->body_hard_wrap($bodystring, 74);
					}
					// explode into an array
					$body_array = explode("\r\n", $bodystring);
					// add the ">" quoting char to the beginning of each line
					// note, this *will* loop at least once assuming the body has one line at least
					// therefor the var "body" *will* get filled
					for ($bodyidx = 0; $bodyidx < count($body_array); ++$bodyidx)
					{
						// add the ">" so called "quoting" char to the original body text
						// NOTE: do NOT trim the LEFT part of the string, use RTRIM instead
						$this_line = '>' . rtrim($body_array[$bodyidx]) ."\r\n";
						$body .= $this_line;
					}
					
					// email needs to be sent with NO ENCODED HTML ENTITIES
					// it's up to the endusers MUA to handle any htmlspecialchars
					// as for 7-bit vs. 8-bit, we prefer to leave body chars as-is and send out as 8-bit mail
					// Later Note: see RFCs 2045-2049 for what MTA's (note "T") can and can not handle
					$body = $GLOBALS['phpgw']->msg->htmlspecialchars_decode($body);
				}
				elseif ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'forward')
				{
					// ----  initial Handling of Forwarding  -----
					
					// get information from the orig email
					$subject = $GLOBALS['phpgw']->msg->get_subject($msg_headers,'Fw: ');
					$fwd_info_from = $GLOBALS['phpgw']->msg->make_rfc2822_address($msg_headers->from[0]);
					$fwd_info_date = $GLOBALS['phpgw']->common->show_date($msg_headers->udate);
					$fwd_info_subject = $GLOBALS['phpgw']->msg->get_subject($msg_headers,'');
					
					$body = "\r\n"."\r\n"
						.'forward - original mail:'."\r\n"
						.'  From ' .$fwd_info_from ."\r\n"
						.'  Date ' .$fwd_info_date ."\r\n"
						.'  Subject ' .$fwd_info_subject ."\r\n";
					
					
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
				
				// so what goes in the to and cc box
				$to_box_value = $to;
				$cc_box_value = $cc;
			}
			else
			{
				// ----  Handle Compose (*not* a result of clicking Reply or Forward)  -----
				
				// No var msgball['msgnum']=X  means we were not called by the reply, replyall, or forward
				// this typically is only called when the user clicks on a mailto: link in an html document
				// this behavior defines what your "default mail app" is, i.e. what mail app is called when
				// the user clicks a "mailto:" link
				$mailto = $GLOBALS['phpgw']->msg->get_arg_value('mailto');
				$to = $GLOBALS['phpgw']->msg->get_arg_value('to');
				$personal = $GLOBALS['phpgw']->msg->get_arg_value('personal');
				
				if ($mailto)
				{
					$to_box_value = substr($mailto, 7, strlen($mailto));
				}
				// called from the message list (index.php), most likely,
				//  or from message.php if user clicked on an individual address in the to or cc fields
				elseif ((isset($to))
				&& ($to != '')
				&& (isset($personal))
				&& ($personal != '')
				&& (urldecode($personal) != urldecode($to)) )
				{
					$to = $GLOBALS['phpgw']->msg->stripslashes_gpc($to);
					$GLOBALS['phpgw']->msg->set_arg_value('to', $to);
					$personal = $GLOBALS['phpgw']->msg->stripslashes_gpc($personal);
					$GLOBALS['phpgw']->msg->set_arg_value('personal', $personal);
					$to_box_value = $GLOBALS['phpgw']->msg->htmlspecialchars_encode('"'.urldecode($personal).'" <'.urldecode($to).'>');
				}
				elseif ((isset($to))
				&& ($to != ''))
				{
					$to = $GLOBALS['phpgw']->msg->stripslashes_gpc($to);
					$GLOBALS['phpgw']->msg->set_arg_value('to', $to);
					$to_box_value = urldecode($to);
				}
				else
				{
					$to_box_value = '';
				}
			}
			
			// what value does the "Send" button need
			if ($GLOBALS['phpgw']->msg->get_isset_arg('msgball'))
			{
				// generally, msgball arg exists when reply,replyall, or forward is being done
				// if it exists, preserve (carry forward) its "folder" and "acctnum" values
				$send_btn_action = $GLOBALS['phpgw']->link(
						'/index.php',
						'menuaction=email.bosend.send'
						.'&action=forward'
						.'&'.$msgball['uri']
						// this is used to preserve these values when we return to folder list after the send
						.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
						.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
						.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start')
				);
				if (($GLOBALS['phpgw']->msg->get_isset_arg('action'))
				&& ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'forward')
				&& ($GLOBALS['phpgw']->msg->get_isset_arg('fwd_proc')))
				{
					$send_btn_action = $send_btn_action
						.'&fwd_proc='.$GLOBALS['phpgw']->msg->get_arg_value('fwd_proc');
				}
			}
			elseif ($GLOBALS['phpgw']->msg->get_isset_arg('fldball'))
			{
				// if fldball it exists, preserve (carry forward) its "folder" and "acctnum" values
				// generally, fldball arg exists only when NOT doing reply,replyall, or forward
				// because a msgball would be supplied in those cases.
				// when simply composing a message, the code that calls this compose page 
				// *should* generate and pass into here a fldball to hold the relevent 
				// fldball["acctnum"] value, and also the fldball["folder"] value will be used
				// to help us decide which page to display to the user after the Send button is clicked,
				// that is, what folder to return to in the uiindex page we goto after the send.
				// since we are not dealing with a specific message here, we will pass the data
				// on in the form of a fldball structure, which is more generic in nature in that
				// it never holds a "msgnum" value.
				$fldball = $GLOBALS['phpgw']->msg->get_arg_value('fldball');
				$send_btn_action = $GLOBALS['phpgw']->link(
						'/index.php',
						'menuaction=email.bosend.send'
						// this is used to preserve these values when we return to folder list after the send
						.'&fldball[folder]='.$fldball['folder']
						.'&fldball[acctnum]='.$fldball['acctnum']
						.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
						.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
						.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start')
				);
			}
			else
			{
				// no msgball, no fldball, so not doing a reply/replyall/forward , 
				// and probably the code forget to supply and pass into here the "acctnum"
				// and "folder" data, so we will use currently prevailing values, but this
				// is depreciated, fallback procedure that does not necessarily preserve and
				// pass on precise acctnum and folder value data
				$send_btn_action = $GLOBALS['phpgw']->link(
						'/index.php',
						'menuaction=email.bosend.send'
						// this is used to preserve these values when we return to folder list after the send
						.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
						.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum()
						.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
						.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
						.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start')
				);
			}
			
			
			$this->xi['send_btn_action'] = $send_btn_action;
			$this->xi['to_box_value'] = $to_box_value;
			$this->xi['cc_box_value'] = $cc_box_value;
			$this->xi['subject'] = $subject;
			$this->xi['body'] = $body;
			
			$this->xi['js_addylink'] = $GLOBALS['phpgw']->link(
				'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php');
			$this->xi['form1_name'] = 'doit';
			$this->xi['form1_method'] = 'POST';
			$this->xi['buttons_bgcolor'] = $GLOBALS['phpgw_info']['theme']['em_folder'];
			$this->xi['btn_addybook_type'] = 'button';
			$this->xi['btn_addybook_value'] = lang('addressbook');
			$this->xi['btn_addybook_onclick'] = 'addybook();';
			$this->xi['btn_send_type'] = 'submit';
			$this->xi['btn_send_value'] = lang('send');
			$this->xi['to_boxs_bgcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
			$this->xi['to_boxs_font'] = $GLOBALS['phpgw_info']['theme']['font'];
			$this->xi['to_box_desc'] = lang('to');
			$this->xi['to_box_name'] = 'to';
			$this->xi['cc_box_desc'] = lang('cc');
			$this->xi['cc_box_name'] = 'cc';
			$this->xi['subj_box_desc'] = lang('subject');
			$this->xi['subj_box_name'] = 'subject';
			$this->xi['checkbox_sig_desc'] = lang('Attach signature');
			$this->xi['checkbox_sig_name'] = 'attach_sig';
			$this->xi['checkbox_sig_value'] = 'true';
			
			$this->xi['attachfile_js_link'] = $GLOBALS['phpgw']->link(
				'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/attach_file.php');
			$this->xi['attachfile_js_text'] = lang('Attach file');
			$this->xi['body_box_name'] = 'body';
			
			if ($GLOBALS['phpgw']->msg->get_isset_pref('email_sig')
			&& ($GLOBALS['phpgw']->msg->get_pref_value('email_sig') != ''))
			{
				$this->xi['do_checkbox_sig'] = True;
			}
			else
			{
				$this->xi['do_checkbox_sig'] = False;
			}
			
		}
	}
?>
