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

	Header('Cache-Control: no-cache');
	Header('Pragma: no-cache');
	Header('Expires: Sat, Jan 01 2000 01:01:01 GMT');

	$phpgw_info["flags"] = array(
		'currentapp'			=>	'email',
		'enable_network_class'		=>	True,
		'enable_nextmatchs_class'	=>	True
	);

	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_message_main' => 'message_main.tpl'
	));
	$t->set_block('T_message_main','B_cc_data','V_cc_data');
	$t->set_block('T_message_main','B_attach_list','V_attach_list');



// ----  Are We In Newsmode Or Not  -----
	if (isset($newsmode) && $newsmode == "on")
	{
		$phpgw_info['flags']['newsmode'] = True;
	}
	else
	{
		$phpgw_info['flags']['newsmode'] = False;
	}

// ----  Fill Some Important Variables  -----
	$image_dir = $phpgw->common->get_image_path($phpgw_info['flags']['currentapp']);
	$svr_image_dir = $phpgw_info['server']['images_dir'];
	$sm_envelope_img = img_maketag($image_dir.'/sm_envelope.gif',"Add to address book","8","10","0");
	$session_folder = 'folder='.urlencode($folder).'&msgnum=';
	$default_sorting = $phpgw_info['user']['preferences']['email']['default_sorting'];

// ----  Special X-phpGW-Type Message Flag  -----
	// is this still a planned feature?
	$application = '';
	$msgtype = $phpgw->msg->get_flag($mailbox,$msgnum,'X-phpGW-Type');
	if (!empty($msgtype))
	{
		$msg_type = explode(';',$msgtype);
		$application = substr($msg_type[0],1,strlen($msg_type[0])-2);
		echo '<center><h1>THIS IS A phpGroupWare-'.strtoupper($application).' EMAIL</h1><hr></center>'."\n";
		//	.'In the future, this will process a specially formated email msg.<hr></center>';
	}

	#set_time_limit(0);

// ----  General Information about The Message  -----
	$msg = $phpgw->msg->header($mailbox, $msgnum);
	$struct = $phpgw->msg->fetchstructure($mailbox, $msgnum);
	$totalmessages = $phpgw->msg->num_msg($mailbox);

	$subject = $phpgw->msg->get_subject($msg,'');
	$message_date = $phpgw->common->show_date($msg->udate);

	if (!$folder)
	{
		$folder = 'INBOX';
	}

// ----  What Folder To Return To  -----
        $lnk_goback_folder = href_maketag($phpgw->link('/email/index.php','folder='.urlencode($folder)),$folder);

// ----  Images and Hrefs For Reply, ReplyAll, Forward, and Delete  -----
        $reply_img = img_maketag($image_dir.'/sm_reply.gif',lang('reply'),'19','26','0');
	$reply_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=reply&folder='.urlencode($folder).'&msgnum='.$msgnum);
	$ilnk_reply = href_maketag($reply_url, $reply_img);

        $replyall_img = img_maketag($image_dir .'/sm_reply_all.gif',lang('reply all'),"19","26",'0');
	$replyall_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=replyall&folder='.urlencode($folder).'&msgnum='.$msgnum);
	$ilnk_replyall = href_maketag($replyall_url, $replyall_img);

	$forward_img = img_maketag($image_dir .'/sm_forward.gif',lang('forward'),"19","26",'0');
	$forward_url =  $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=forward&folder='.urlencode($folder).'&msgnum='.$msgnum);
	$ilnk_forward = href_maketag($forward_url, $forward_img);

	$delete_img = img_maketag($image_dir .'/sm_delete.gif',lang('delete'),"19","26",'0');
	$delete_url = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/action.php','what=delete&folder='.urlencode($folder).'&msgnum='.$msgnum);
	$ilnk_delete = href_maketag($delete_url, $delete_img);

	$t->set_var('theme_font',$phpgw_info['theme']['font']);
	$t->set_var('reply_btns_bkcolor',$phpgw_info['theme']['em_folder']);
	$t->set_var('reply_btns_text',$phpgw_info['theme']['em_folder_text']);
	$t->set_var('lnk_goback_folder',$lnk_goback_folder);
	$t->set_var('ilnk_reply',$ilnk_reply);
	$t->set_var('ilnk_replyall',$ilnk_replyall);
	$t->set_var('ilnk_forward',$ilnk_forward);
	$t->set_var('ilnk_delete',$ilnk_delete);

// ----  Go To Previous Message Handling  -----
	if ($msgnum != 1 || ($default_sorting == 'new_old' && $msgnum != $totalmeesages))
	{
		if ($default_sorting == 'new_old')
		{
			$pm = $msgnum + 1;
		}
		else
		{
			$pm = $msgnum - 1;
		}

		if ($default_sorting == 'new_old' && ($msgnum == $totalmessages && $msgnum != 1 || $totalmessages == 1))
		{
			$ilnk_prev_msg = img_maketag($svr_image_dir.'/left-grey.gif',"No Previous Message",'','','0');
		}
		else
		{
			$prev_msg_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',$session_folder.$pm);
			$prev_msg_img = img_maketag($svr_image_dir.'/left.gif',"Previous Message",'','','0');
			$ilnk_prev_msg = href_maketag($prev_msg_link,$prev_msg_img);
		}
	}
	else
	{
		$ilnk_prev_msg = img_maketag($svr_image_dir.'/left-grey.gif',"No Previous Message",'','','0');
	}

// ----  Go To Next Message Handling  -----
	if ($msgnum < $totalmessages || ($default_sorting == 'new_old' && $msgnum != 1))
	{
		if ($default_sorting == 'new_old')
		{
			$nm = $msgnum - 1;
		}
		else
		{
			$nm = $msgnum + 1;
		}

		if ($default_sorting == 'new_old' && $msgnum == 1 && $totalmessages != $msgnum)
		{
			$ilnk_next_msg = img_maketag($svr_image_dir.'/right-grey.gif',"No Next Message",'','','0');
		}
		else
		{
			$next_msg_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',$session_folder.$nm);
			$next_msg_img = img_maketag($svr_image_dir.'/right.gif',"Next Message",'','','0');
			$ilnk_next_msg = href_maketag($next_msg_link,$next_msg_img);
		}
	}
	else
	{
		$ilnk_next_msg = img_maketag($svr_image_dir.'/right-grey.gif',"No Next Message",'','','0');
	}

	$t->set_var('ilnk_prev_msg',$ilnk_prev_msg);
	$t->set_var('ilnk_next_msg',$ilnk_next_msg);

// ----  Labels and Colors for From, To, CC, Files, and Subject  -----
	$t->set_var('tofrom_labels_bkcolor', $phpgw_info['theme']['th_bg']);
	$t->set_var('tofrom_data_bkcolor', $phpgw_info['theme']['row_on']);

	$t->set_var('lang_from', lang('from'));
	$t->set_var('lang_to', lang('to'));
	$t->set_var('lang_cc', lang('cc'));
	$t->set_var('lang_date', lang('date'));
	$t->set_var('lang_files', lang('files'));
	$t->set_var('lang_subject', lang('subject'));

// ----  From: Message Data  -----
	// format "From" according to user preferences  ?
	// WHAT IS THIS PREF SUPPOSED TO DO ???
	$from = $msg->from[0];
	$personal = !isset($from->personal) || !$from->personal ? $from->mailbox.'@'.$from->host : $from->personal;
	if ($phpgw_info['user']['preferences']['email']['show_addresses'] != 'no' && ($personal != $from->mailbox.'@'.$from->host))
	{
		$display_address->from = '('.$from->mailbox.'@'.$from->host.')';
	}
	if ($msg->from)
	{

		$from_real_name = href_maketag($phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder).'&to='.urlencode($from->mailbox.'@'.$from->host)),
			decode_header_string($personal) );
		$from_raw_addy = trim($display_address->from);

		$from_addybook_add = href_maketag(
			$phpgw->link('/addressbook/add.php','add_email='.urlencode($from->mailbox.'@'.$from->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
			$sm_envelope_img
		);
	}
	else
	{
		$from_real_name = '';
		$from_raw_addy = lang('Undisclosed Sender');
		$from_addybook_add = '';
	}

	$t->set_var('from_real_name',$from_real_name);
	$t->set_var('from_raw_addy',$from_raw_addy);
	$t->set_var('from_addybook_add',$from_addybook_add);


// ----  To:  Message Data  -----
	if ($msg->to)
	{
		for ($i = 0; $i < count($msg->to); $i++)
		{
			$topeople = $msg->to[$i];
			$personal = !isset($topeople->personal) || !$topeople->personal ? $topeople->mailbox.'@'.$topeople->host : $topeople->personal;
			$personal = decode_header_string($personal);
			if ($phpgw_info['user']['preferences']['email']['show_addresses'] != 'no' && ($personal != $topeople->mailbox.'@'.$topeople->host))
			{
				$display_address->to = '('.$topeople->mailbox.'@'.$topeople->host.')';
			}

			$to_real_name = href_maketag(
				$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder).'&to='.$topeople->mailbox.'@'.$topeople->host),
				$personal
			);
			$to_raw_addy = trim($display_address->to);

			$to_addybook_add = href_maketag(
				$phpgw->link('/addressbook/add.php','add_email='.urlencode($topeople->mailbox.'@'.$topeople->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
				$sm_envelope_img
			);
			// assemble the string and store for later use
			$to_data_array[$i] = $to_real_name.' '.$to_raw_addy.' '.$to_addybook_add;
		}
		// throw a spacer comma in between addresses, if more than one
		$to_data_final = implode(', ',$to_data_array);
	}
	else
	{
		$to_data_final = lang('Undisclosed Recipients');
	}
	$t->set_var('to_data_final',$to_data_final);


// ----  Cc:  Message Data  -----
	if (isset($msg->cc) && count($msg->cc) > 0)
	{
		for ($i = 0; $i < count($msg->cc); $i++)
		{
			$ccpeople = $msg->cc[$i];
			$personal = !$ccpeople->personal ? $ccpeople->mailbox.'@'.$ccpeople->host : $ccpeople->personal;
			$personal = decode_header_string($personal);

			$cc_real_name = href_maketag($phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder)
				.'&to='.urlencode($ccpeople->mailbox.'@'.$ccpeople->host)),
				$personal
			);
			// we never show cc's raw address
			$cc_raw_addy = '';

			$cc_addybook_add = href_maketag(
				$phpgw->link('/addressbook/add.php','add_email='.urlencode($topeople->mailbox.'@'.$topeople->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)),
				$sm_envelope_img
			);
			// assemble the string and store for later use
			$cc_data_array[$i] = $cc_real_name.' '.$cc_raw_addy.' '.$cc_addybook_add;
		}
		// throw a spacer comma in between addresses, if more than one
		$cc_data_final = implode(', ',$cc_data_array);
		$t->set_var('cc_data_final',$cc_data_final);
		$t->parse('V_cc_data','B_cc_data');
	}
	else
	{
		$t->set_var('V_cc_data','');
	}

// ---- Message Date  (set above)  -----
	$t->set_var('message_date', $message_date);


// ---- Attachments List  -----
	$flag = 0;
	$struct_count = (!isset($struct->parts) || !$struct->parts ? 1 : count($struct->parts));
	for ($z = 0; $z < $struct_count; $z++)
	{
		$part = !isset($struct->parts[$z]) || !$struct->parts[$z] ? $struct : $struct->parts[$z];
		$att_name = get_att_name($part);

		if ($att_name != 'Unknown')
		{
			// if it has a name, it's an attachment
			$f_name[$flag] = attach_display($part, $z+1);
			$flag++;
		}
	}
	if ($flag != 0)
	{
		$list_of_files = implode(', ',$f_name);
		$t->set_var('list_of_files',$list_of_files);
		$t->parse('V_attach_list','B_attach_list');
	}
	else
	{
		$t->set_var('V_attach_list','');
	}

// ---- Message Subject  (set above)  -----
	$t->set_var('message_subject',$subject);

	$t->pparse('out','T_message_main');
	
// ---- STOPPED HERE - PHASE 2  -----


// ---- Message Content  -----
	$numparts = (!isset($struct->parts) || !$struct->parts ? 1 : count($struct->parts));
	echo '<!-- This message has '.$numparts.' part(s) -->'."\n";

	for ($i = 0; $i < $numparts; $i++)
	{
		$part = (!isset($struct->parts[$i]) || !$struct->parts[$i] ? $struct : $struct->parts[$i]);

		$att_name = get_att_name($part);
		if ($att_name == 'Unknown')
		{
			if (strtoupper(get_mime_type($part)) == 'MESSAGE')
			{
				inline_display($part, $i+1);
				echo "\n<p>";
			}
			else
			{
				inline_display($part, $i+1);
				echo "\n<p>";
			}
		}

		$mime_encoding = get_mime_encoding($part);
		if (($mime_encoding == 'base64') && ($part->subtype == 'JPEG' || $part->subtype == 'GIF' || $part->subtype == 'PJPEG'))
		{
			// we want to display images here, even though they are attachments.
			echo '<p>'.image_display($folder, $msgnum, $part, $i+1, $att_name)."<p>\n";
		}
	}
	echo '</td></tr>';
	if($application)
	{
		if(strstr($msgtype,'"; Id="'))
		{
			$msg_type = explode(';',$msgtype);
			$id_array = explode('=',$msg_type[2]);
			$calendar_id = intval(substr($id_array[1],1,strlen($id_array[1])-2));

			echo '<tr><td align="center">';
			$phpgw->common->hook_single('email',$application);
			echo '</td></tr>';
		}
	}
?>
</table>
<?php
	$phpgw->msg->close($mailbox); 
	$phpgw->common->phpgw_footer();
?>
