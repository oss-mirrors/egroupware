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

	Header("Cache-Control: no-cache");
	Header("Pragma: no-cache");
	Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
  
	$phpgw_info["flags"] = array(
		'currentapp' => 'email', 
		'enable_network_class' => True, 
		'enable_nextmatchs_class' => True);

	include("../header.inc.php");

	@set_time_limit(0);

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_any_deleted' => 'index_any_deleted.tpl',
		'T_form_delmov_init' => 'index_form_delmov_init.tpl',
		'T_no_messages' => 'index_no_messages.tpl',
		'T_attach_clip' => 'index_attach_clip.tpl',
		'T_new_msg' => 'index_new_msg.tpl',
		'T_msg_list' => 'index_msg_list.tpl',
		'T_index_out' => 'index.tpl'
	));

// ----  Fill Some Important Variables  -----
	$svr_image_dir = PHPGW_IMAGES_DIR;
	$image_dir = PHPGW_IMAGES;

	// Abreviated Folder Name, NO namespace, NO delimiter
	$folder_short = $phpgw->msg->get_folder_short($phpgw->msg->folder);

// ---- Messages Sort Order  (AND ensure $phpgw->msg->sort and $phpgw->msg->order and $start have usable values) -----
	//$phpgw->msg->fill_sort_order_start();
	// MOVED to "begin_request" in msg class base

// ---- lang var for checkbox javascript  -----
	$t->set_var('select_msg',lang('Please select a message first'));

// ---- report on number of messages Deleted or Moved (if any)  -----
	if ($phpgw->msg->args['td'])
	{
		// report on number of messages DELETED (if any)
		if ($phpgw->msg->args['td'] == 1) 
		{
			$num_deleted = lang("1 message has been deleted",$phpgw->msg->args['td']);
		}
		else
		{
			$num_deleted = lang("x messages have been deleted",$phpgw->msg->args['td']);
		}
		// template only outputs if msgs were deleted, otherwise skipped
		$t->set_var('num_deleted',$num_deleted);
		$t->parse('V_any_deleted','T_any_deleted',True);
	}
	elseif ($phpgw->msg->args['tm'])
	{
		if ($phpgw->msg->args['tf'])
		{
			$_tf = $phpgw->msg->prep_folder_in($phpgw->msg->args['tf']);
		}
		else
		{
			$_tf = 'empty';
		}
		// report on number of messages MOVED (if any)
		if ($phpgw->msg->args['tm'] == 0) 
		{
			$num_moved = lang("Error moving messages to ").' '.$_tf;
		}
		elseif ($phpgw->msg->args['tm'] == 1)
		{
			$num_moved = lang("1 message has been moved to").' '.$_tf;
		}
		else
		{
			$num_moved = $phpgw->msg->args['tm'].' '.lang("messages have been moved to").' '.$_tf;
		}
		// template only outputs if msgs were moved, otherwise skipped
		$t->set_var('num_deleted',$num_moved);
		$t->parse('V_any_deleted','T_any_deleted',True);
	}
	else
	{
		// nothing deleted or moved, so template gets blank string
		$t->set_var('V_any_deleted','');
	}

// ---- SwitchTo Folder Listbox   -----
	if ($phpgw->msg->get_mailsvr_supports_folders())
	{
		// FUTURE: this will pick up the user option to show num unseen msgs in dropdown list
		//$listbox_show_unseen = True;
		$listbox_show_unseen = False;
		$switchbox_listbox = '<select name="folder" onChange="document.switchbox.submit()">'
				. '<option>' . lang('switch current folder to') . ':'
				. $phpgw->msg->all_folders_listbox('','','',$listbox_show_unseen)
				. '</select>';
	}
	else
	{
		$switchbox_listbox = '&nbsp';
	}

// ---- Folder Status Infomation   -----
	$mailbox_status = $phpgw->dcom->status(
					$phpgw->msg->mailsvr_stream,
					$phpgw->msg->get_mailsvr_callstr().$phpgw->msg->folder,
					SA_ALL);

// ----  Previous and Next arrows navigation  -----
	// nextmatches->left/right  vars:
	// a) script (filename like index.php)
	// b) start
	// c) total
	// d) extradata - in url format - "&var1=x&var2=y"
	$td_prev_arrows = $phpgw->nextmatchs->left('/'.$phpgw_info['flags']['currentapp'].'/index.php',
					$phpgw->msg->start,
					$mailbox_status->messages,
					 '&folder='.$phpgw->msg->prep_folder_out('')
					.'&sort='.$phpgw->msg->sort
					.'&order='.$phpgw->msg->order);

	$td_next_arrows = $phpgw->nextmatchs->right('/'.$phpgw_info['flags']['currentapp'].'/index.php',
					$phpgw->msg->start,
					$mailbox_status->messages,
					'&folder='.$phpgw->msg->prep_folder_out('')
					.'&sort='.$phpgw->msg->sort
					.'&order='.$phpgw->msg->order);

// ---- Control Bar =Row 1=   -----
	$t->set_var('ctrl_bar_back1',$phpgw_info["theme"]["row_on"]);
	// "accounts" switchbox
	// FUTURE
	// "sorting" switchbox
	$sort_selected[$phpgw->msg->sort] = " selected";
	$sortbox_select_options =
		 '<option value="0"' .$sort_selected[0] .'>'.lang("Email Date").'</option>' ."\r\n"
		.'<option value="1"' .$sort_selected[1] .'>'.lang("Arrival Date").'</option>' ."\r\n"
		.'<option value="2"' .$sort_selected[2] .'>'.lang("From").'</option>' ."\r\n"
		.'<option value="3"' .$sort_selected[3] .'>'.lang("Subject").'</option>' ."\r\n"
		.'<option value="6"' .$sort_selected[6] .'>'.lang("Size").'</option>' ."\r\n";
	$t->set_var('sortbox_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php',
					'folder='.$phpgw->msg->prep_folder_out('')));
	$t->set_var('sortbox_on_change','document.sortbox.submit()');
	$t->set_var('sortbox_select_name','sort');
	$t->set_var('sortbox_select_options',$sortbox_select_options);

	// "switch to" folder switchbox
	$t->set_var('switchbox_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php'));
	$t->set_var('switchbox_listbox',$switchbox_listbox);
	// navagation arrows
	$t->set_var('arrows_backcolor',$phpgw_info['theme']['bg_color']);
	$t->set_var('prev_arrows',$td_prev_arrows);
	$t->set_var('next_arrows',$td_next_arrows);

// ---- Control Bar =Row 2=   -----
	$t->set_var('ctrl_bar_back2',$phpgw_info["theme"]["row_off"]);
	$t->set_var('compose_txt',lang("compose"));
	$t->set_var('compose_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',"folder=".$phpgw->msg->prep_folder_out('')));
	$t->set_var('folders_txt',lang("folders"));
	if ($phpgw->msg->get_mailsvr_supports_folders())
	{
		$t->set_var('folders_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php'));
	}
	else
	{
		// doesn't support folders. just go to index page
		$t->set_var('folders_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'index.php'));
	}
	// "accounts" preferences FUTURE
	$t->set_var('accounts_txt',lang("accounts"));
	$t->set_var('accounts_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'index.php'));
	// "routing" preferences FUTURE
	$t->set_var('routing_txt',lang("routing"));
	$t->set_var('routing_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'index.php'));


// ---- Message Folder Stats Display  -----
	if ($mailbox_status->messages == 0)
	{
		$stats_saved = '-';
		$stats_new = '-';
		$stats_size = '-';
	}
	else
	{
		// TOTAL MESSAGES IN FOLDER
		$stats_saved = number_format($mailbox_status->messages);

		$msg_array = array();
		//$msg_array = $phpgw->dcom->sort($phpgw->msg->mailsvr_stream, $phpgw->msg->sort, $phpgw->msg->order);
		$msg_array = $phpgw->msg->get_message_list();

		// NUM NEW MESSAGES
		$stats_new = $mailbox_status->unseen;
		if ($stats_new == 0)
		{
			$stats_new = '0';
		}
		else
		{
			// put a comma between the thousands
			$stats_new = number_format($stats_new);
		}

		// SIZE OF FOLDER - total size of all emails added up
		// can take a long time if alot of mail is in the folder
		// FUTURE:  make it optional, this will pick up that option from the prefs
		//$stats_size_speed_skip = True;
		$stats_size_speed_skip = False;
		$stats_size_threshold = 1000;
		if (($mailbox_status->messages > $stats_size_threshold)
		&& ($stats_size_speed_skip == True))
		{
			$stats_size = 'speed skip';
		}
		else
		{
			$mailbox_detail = $phpgw->dcom->mailboxmsginfo($phpgw->msg->mailsvr_stream);
			$stats_size = $mailbox_detail->Size;
			// size is in bytes, format for KB or MB
			$stats_size = $phpgw->msg->format_byte_size($stats_size);
		}
	}

// ---- Folder Statistics Information Row  -----
	$t->set_var('stats_backcolor',$phpgw_info['theme']['em_folder']);
	$t->set_var('stats_font',$phpgw_info['theme']['font']);
	$t->set_var('stats_fontsize','+0');
	$t->set_var('stats_color',$phpgw_info['theme']['em_folder_text']);
	$t->set_var('stats_folder',$folder_short);
	$t->set_var('stats_saved',$stats_saved);
	$t->set_var('stats_new',$stats_new);
	$t->set_var('stats_size',$stats_size);
	$t->set_var('lang_new',lang('New'));
	$t->set_var('lang_total',lang('Total'));
	$t->set_var('lang_size',lang('Size'));
	$t->set_var('stats_first',$phpgw->msg->start + 1);
	// "last" can not be know until the calculations below
	//$t->set_var('stats_last',$phpgw->msg->start + $phpgw_info['user']['preferences']['common']['maxmatchs']);

// ----  Messages List Clickable Column Headers  -----
	/*
	// clickable column headers which change the sorting of the messages
	if ($phpgw->msg->newsmode)
	{
		// I think newsmode requires the "old way"
		$sizesort = lang("lines");
		$hdr_subject = $phpgw->nextmatchs->show_sort_order($phpgw->msg->sort,"3",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("subject"),"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_from = $phpgw->nextmatchs->show_sort_order($phpgw->msg->sort,"2",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("from"),"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_date = $phpgw->nextmatchs->show_sort_order($phpgw->msg->sort,"0",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("date"),"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_size = $phpgw->nextmatchs->show_sort_order($phpgw->msg->sort,"6",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$sizesort);
	}
	else
	{
		// for email
		$sizesort = lang("size");
		$hdr_subject = $phpgw->nextmatchs->show_sort_order_imap("3",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("subject"),"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_from = $phpgw->nextmatchs->show_sort_order_imap("2",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("from"),"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_date = $phpgw->nextmatchs->show_sort_order_imap("1",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("date"),"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_size = $phpgw->nextmatchs->show_sort_order_imap("6",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$sizesort);
	}
	*/
	$hdr_subject = lang("subject");
	$hdr_from = lang("from");
	$hdr_date = lang("date");
	$hdr_size = lang("size");

	$t->set_var('hdr_backcolor',$phpgw_info['theme']['th_bg']);
	$t->set_var('hdr_font',$phpgw_info['theme']['font']);
	$t->set_var('hdr_subject',$hdr_subject);
	$t->set_var('hdr_from',$hdr_from);
	$t->set_var('hdr_date',$hdr_date);
	$t->set_var('hdr_size',$hdr_size);


// ----  Form delmov Intialization  Setup  -----
	// ----  place in first checkbox cell of the messages list table, ONE TIME ONLY   -----
	$t->set_var('delmov_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/action.php'));
	$t->set_var('current_folder',$folder_short);
	$t->parse('V_form_delmov_init','T_form_delmov_init');
	$mlist_delmov_init = $t->get_var('V_form_delmov_init');	

// ----  New Message Indicator   -----
	$t->set_var('mlist_newmsg_char','*');
	$t->set_var('mlist_newmsg_color','#ff0000');
	$t->set_var('mlist_newmsg_txt',lang("New message"));

// ----  Init Vars Used In Messages List  -----
	$t->set_var('mlist_font',$phpgw_info['theme']['font']);
	//$t->set_var('images_dir',$phpgw_info['server']['images_dir']);
	$t->set_var('images_dir',$svr_image_dir);
	
	// prepare attachment paperclip image
	$t->parse('V_attach_clip','T_attach_clip');
	$mlist_attach = $t->get_var('V_attach_clip');
	// prepare new message character
	$t->parse('V_new_msg','T_new_msg');
	$mlist_new_msg = $t->get_var('V_new_msg');
	// initialize
	$t->set_var('V_msg_list',' ');

// ----  Zero Messages To List  -----
	if ($mailbox_status->messages == 0)
	{
		//if (!$mailbox)
		if ((!isset($phpgw->msg->mailsvr_stream))
		|| ($phpgw->msg->mailsvr_stream == ''))
		{
			$report_no_msgs = lang("Could not open this mailbox");
		}
		else
		{
			$report_no_msgs = lang("this folder is empty");
		}
		// this info for the stats row above
		$t->set_var('stats_last','0');
		// no messages to display, msgs list is just one row reporting this
		$t->set_var('report_no_msgs',$report_no_msgs);
		$t->set_var('mlist_delmov_init',$mlist_delmov_init);
		$t->set_var('mlist_backcolor',$phpgw_info["theme"]["row_on"]);
		// big Mr. Message List is just one row in this case
		$t->parse('V_msg_list','T_no_messages');
	}
// ----  Fill The Messages List  -----
	else
	{
		if ($mailbox_status->messages < $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
		{
			$totaltodisplay = $mailbox_status->messages;
		}
		elseif (($mailbox_status->messages - $phpgw->msg->start) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
		{
			$totaltodisplay = $phpgw->msg->start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
		}
		else
		{
			$totaltodisplay = $mailbox_status->messages;
		}
		// this info for the stats row above
		$t->set_var('stats_last',$totaltodisplay);

		for ($i=$phpgw->msg->start; $i < $totaltodisplay; $i++)
		{
			// place the delmov form header tags ONLY ONCE, blank string all subsequent loops
			$do_init_form = ($i == $phpgw->msg->start);

			// ROW BACK COLOR
			//$bg = (($i + 1)/2 == floor(($i + 1)/2)) ? $phpgw_info["theme"]["row_off"] : $phpgw_info["theme"]["row_on"];
			$bg = $phpgw->nextmatchs->alternate_row_color($bg);

			$struct = $phpgw->dcom->fetchstructure($phpgw->msg->mailsvr_stream, $msg_array[$i]);

			// SHOW ATTACHMENT CLIP ?
			$show_attach = has_real_attachment($struct);

			//$msg = $phpgw->dcom->header($mailbox, $msg_array[$i]);
			$msg = $phpgw->dcom->header($phpgw->msg->mailsvr_stream, $msg_array[$i]);
			
			// MESSAGE REFERENCE NUMBER
			$mlist_msg_num = $msg_array[$i];

			// SUBJECT
			$subject = $phpgw->msg->get_subject($msg,'');
			$subject_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',
				'folder='.$phpgw->msg->prep_folder_out('').'&msgnum='.$mlist_msg_num);

			// SIZE
			if ($phpgw->msg->newsmode)
			{
				$size = $msg->Size;
			}
			else
			{
				$ksize = round(10*($msg->Size/1024))/10;
				$size = $msg->Size > 1024 ? "$ksize k" : $msg->Size;
			}

			// SEEN OR UNSEEN/NEW
			if (($msg->Unseen == "U") || ($msg->Recent == "N"))
			{
				$show_newmsg = True;
			}
			else
			{
				$show_newmsg = False;
			}

			// FROM and REPLY TO  HANDLING
			if ($msg->reply_to[0])
			{
				$reply = $msg->reply_to[0];
			}
			else
			{
				$reply = $msg->from[0];
			}

			//$replyto = $phpgw->msg->make_rfc2822_address($reply);
			$replyto = $reply->mailbox.'@'.$reply->host;

			$from = $msg->from[0];
			if (!$from->personal)
			{
				// no "personal" info available, only can show plain address
				$personal = $from->mailbox.'@'.$from->host;
			}
			else
			{
				$personal = $phpgw->msg->decode_header_string($from->personal);
			}
			if ($personal == "@")
			{
				$personal = $replyto;
			}
			// display the "from" data according to user preferences
			// assumes user always wants "personal" shown, question is when to also show the plain address
			if (($phpgw_info['user']['preferences']['email']['show_addresses'] == 'from')
			&& ($personal != $from->mailbox.'@'.$from->host))
			{
				// user wants "personal" AND the plain address of who the email came from, in the "From"  column
				$display_address_from = '('.$from->mailbox.'@'.$from->host.')';
				$who_to = $from->mailbox.'@'.$from->host;
			}
			elseif (($phpgw_info['user']['preferences']['email']['show_addresses'] == 'replyto')
			&& ($personal != $from->mailbox.'@'.$from->host))
			{
				// user wants "personal" AND the plain address of the "ReplyTo" header, if available, in the "From" column
				$display_address_from = '&lt;'.$replyto.'&gt;';
				//$who_to = $replyto;
				$who_to = $from->mailbox.'@'.$from->host;
			}
			else
			{
				// user does not want to see any plain address, or "personal" was not available, so we show plain anyway
				$display_address_from = "";
				$who_to = $from->mailbox.'@'.$from->host;
			}

			$from_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',
				'folder='.$phpgw->msg->prep_folder_out('').'&to='.urlencode($who_to));
			if ($personal != $from->mailbox.'@'.$from->host)
			{
				$from_link = $from_link .'&personal='.urlencode($personal);
			}
			// this will be the href clickable text in the from column
			$from_name = $phpgw->msg->decode_header_string($personal);
			//echo "$display_address->from";

			// DATE
			$msg_date = $phpgw->common->show_date($msg->udate);

			// set up vars for the parsing
			if ($do_init_form)
			{
				$t->set_var('mlist_delmov_init',$mlist_delmov_init);
			}
			else
			{
				$t->set_var('mlist_delmov_init', '');
			}
			if ($show_newmsg)
			{
				$t->set_var('mlist_new_msg',$mlist_new_msg);
			}
			else
			{
				$t->set_var('mlist_new_msg','');
			}
			if ($show_attach)
			{
				$t->set_var('mlist_attach',$mlist_attach);
			}
			else
			{
				$t->set_var('mlist_attach','');
			}
			$t->set_var('mlist_msg_num',$mlist_msg_num);
			$t->set_var('mlist_backcolor',$bg);
			$t->set_var('mlist_subject',$subject);
			$t->set_var('mlist_subject_link',$subject_link);		
			$t->set_var('mlist_from',$from_name);
			$t->set_var('mlist_from_extra',$display_address_from);
			$t->set_var('mlist_reply_link',$from_link);
			$t->set_var('mlist_date',$msg_date);
			$t->set_var('mlist_size',$size);
			// fill this template, "true" means it's cumulative
			$t->parse('V_msg_list','T_msg_list',True);
			// end iterating through the messages to display
		}
	}

// ---- Delete/Move Folder Listbox  for Msg Table Footer -----
	if ($phpgw->msg->get_mailsvr_supports_folders())
	{
		$delmov_listbox = '<select name="tofolder" onChange="do_action(\'move\')">'
			. '<option>' . lang("move selected messages into") . ':'
			. $phpgw->msg->all_folders_listbox('','',$folder_short)
			. '</select>';
            
	}
	else
	{
		$delmov_listbox = '&nbsp;';
	}
	// preserving the current sort and order thru the delete process
	$t->set_var('current_sort',$phpgw->msg->sort);
	$t->set_var('current_order',$phpgw->msg->order);

// ----  Messages List Table Footer  -----
	$t->set_var('app_images',$image_dir);
	$t->set_var('ftr_backcolor',$phpgw_info['theme']['th_bg']);
	$t->set_var('ftr_font',$phpgw_info['theme']['font']);
	$t->set_var('delmov_button',lang("delete"));
	$t->set_var('delmov_listbox',$delmov_listbox);
	
// ----  Output the Template   -----
	$t->pparse('out','T_index_out');

	$phpgw->msg->end_request();

	$phpgw->common->phpgw_footer();
?>
