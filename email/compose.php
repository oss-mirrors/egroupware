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

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_compose_out' => 'compose.tpl'
	));
	$t->set_block('T_compose_out','B_checkbox_sig','V_checkbox_sig');

// ----  Handle Replying and Forwarding  -----
	if ($msgnum)
	{
		$msg = $phpgw->msg->header($mailbox, $msgnum);
		$struct = $phpgw->msg->fetchstructure($mailbox, $msgnum);
		if ($action == 'reply')
		{
			if ($msg->reply_to[0])
			{
				$reply = $msg->reply_to[0];
			}
			else
			{
				$reply = $msg->from[0];
			}
			$to = $reply->mailbox.'@'.$reply->host;
			$subject = $phpgw->msg->get_subject($msg,'Re: ');
		}
		if ($action == 'replyall')
		{
			if ($msg->to)
			{
				for ($i = 0; $i < count($msg->to); $i++)
				{
					$topeople = $msg->to[$i];
					$tolist[$i] = "$topeople->mailbox@$topeople->host";
				}
				$from = $msg->from[0];
				$to = "$from->mailbox@$from->host, " . implode(", ", $tolist);
			}

			if ($msg->cc)
			{
				for ($i = 0; $i < count($msg->cc); $i++)
				{
					$ccpeople = $msg->cc[$i];
					$cclist[$i] = "$ccpeople->mailbox@$ccpeople->host";	
				}
				$cc = implode(", ", $cclist);
			}
			$subject = $phpgw->msg->get_subject($msg,'Re: ');
		}

		if ($action == 'forward')
		{
			$subject = $phpgw->msg->get_subject($msg,'Fw: ');
		}

		// ----  Begin The Message Body  (of Fw or Re Body) -----
		$who_wrote = $phpgw->msg->get_who_wrote($msg);
		$lang_wrote = 'wrote';
		$body = "\n\n\n" .$who_wrote .' '. $lang_wrote .": \n>\n";

		
		// ----  Quoted Bodystring of Fw: or Re: Message is "First Presentable" from message.php  -----
		// passed in the uri as "part_no"
		// FUTURE: Forward needs entirely different handling
		if (isset($part_no)
		&& ($part_no != '')
		&& (($action == 'reply') || ($action == 'replyall')))
		{
			$bodystring = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_no);
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
					$body .= '>' . $body_array[$bodyidx];
					$body = chop ($body);
					$body .= "\n";
				}
			}
			trim ($body);
			// new - testing this
			// I think the email needs to be sent out as if it were PLAIN text
			// NO ENCODED HTML ENTITIES should be sent over the wire
			// it's up to the endusers MUA to handle any htmlspecialchars
			$body = $phpgw->msg->htmlspecialchars_decode($body);
		}
		// ----  Process Multiple Body Parts (if necessary)  of Fw or Re Body  "the OLD WAY" -----
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
					$bodystring = $phpgw->msg->fetchbody($mailbox, $msgnum, $i+1);
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
							$body .= '>' . $body_array[$bodyidx];
							$body = chop ($body);
							$body .= "\n";
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
	}

	$t->set_var('js_addylink',$phpgw->link("/".$phpgw_info['flags']['currentapp'].'/addressbook.php'));
	$t->set_var('form1_name','doit');
	$t->set_var('form1_action',$phpgw->link("/".$phpgw_info['flags']['currentapp']."/send_message.php"));
	$t->set_var('form1_method','POST');
	$t->set_var('hidden1_name','return');
	$t->set_var('hidden1_value',$folder);

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
	if ($mailto)
	{
		$to_box_value = substr($mailto, 7, strlen($mailto));
	}
	else
	{
		$to_box_value = $to;
	}
	$t->set_var('to_box_value',$to_box_value);
	$t->set_var('cc_box_desc',lang("cc"));
	$t->set_var('cc_box_name','cc');
	$t->set_var('cc_box_value',$cc);
	$t->set_var('subj_box_desc',lang("subject"));
	$t->set_var('subj_box_name','subject');
	$t->set_var('subj_box_value',$subject);
	$t->set_var('checkbox_sig_desc',lang("Attach signature"));
	$t->set_var('checkbox_sig_name','attach_sig');
	$t->set_var('checkbox_sig_value','true');
	if (isset($phpgw_info["user"]["preferences"]["email"]["email_sig"])
	&& ($phpgw_info["user"]["preferences"]["email"]["email_sig"] != ''))
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
 
	$phpgw->common->phpgw_footer();
?>
