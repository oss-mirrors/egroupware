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

	// time limit should be controlled elsewhere
	@set_time_limit(0);

// ----  Prepare Browser and Layout for Template File Name  -----
	// Layout Template from Preferences (used below)
	$my_layout = $phpgw_info['user']['preferences']['email']['layout'];
	// Browser the client is using (used below)
	$my_browser = $phpgw->msg->browser;
	// example: if browser=0 (no CSS) and layout pref = 1 (default) then template used is:
	// "index_main_b0_l1.tpl"

// ----  Load Template Files And Specify Template Blocks  -----
	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_form_delmov_init' => 'index_form_delmov_init.tpl',
		'T_index_main' => 'index_main_b'.$my_browser.'_l'.$my_layout. '.tpl'
	));
	$t->set_block('T_index_main','B_action_report','V_action_report');
	$t->set_block('T_index_main','B_show_size','V_show_size');
	$t->set_block('T_index_main','B_get_size','V_get_size');
	$t->set_block('T_index_main','B_no_messages','V_no_messages');
	$t->set_block('T_index_main','B_msg_list','V_msg_list');

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
		$t->set_var('report_this',$num_deleted);
		$t->parse('V_action_report','B_action_report');
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
		$t->set_var('report_this',$num_moved);
		$t->parse('V_action_report','B_action_report');
	}
	else
	{
		// nothing deleted or moved, so template gets blank string
		$t->set_var('V_action_report','');
	}

// ----  Fill Some Important Variables  -----
	$svr_image_dir = PHPGW_IMAGES_DIR;
	$image_dir = PHPGW_IMAGES;

	// Abreviated Folder Name, NO namespace, NO delimiter
	$folder_short = $phpgw->msg->get_folder_short($phpgw->msg->folder);

	// lang var for checkbox javascript  -----
	$t->set_var('select_msg',lang('Please select a message first'));

// ----  Preserving Current Sort, Order, Start, and Folder where necessary  -----
	// use these as hidden vars in any form that requires preserving these vars
	// example: get size button, the delete button
	$t->set_var('current_sort',$phpgw->msg->sort);
	$t->set_var('current_order',$phpgw->msg->order);
	$t->set_var('current_start',$phpgw->msg->start);
	$t->set_var('current_folder',$phpgw->msg->prep_folder_out(''));

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
	$sort_selected[0] = '';
	$sort_selected[1] = '';
	$sort_selected[2] = '';
	$sort_selected[3] = '';
	$sort_selected[6] = '';
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
	//$t->set_var('arrows_backcolor',$phpgw_info['theme']['bg_color']);
	$t->set_var('arrows_backcolor',$phpgw_info['theme']['row_off']);
	$t->set_var('prev_arrows',$td_prev_arrows);
	$t->set_var('next_arrows',$td_next_arrows);

// ---- Control Bar =Row 2=   -----
	$t->set_var('ctrl_bar_back2',$phpgw_info["theme"]["row_off"]);
	$t->set_var('compose_txt',lang("Compose New"));
	$t->set_var('compose_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',"folder=".$phpgw->msg->prep_folder_out('')));
	$t->set_var('folders_txt',lang("Manage Folders"));
	if ($phpgw->msg->get_mailsvr_supports_folders())
	{
		$t->set_var('folders_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php'));
		$t->set_var('folders_btn_js','window.location='."'".$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php')."'".'"');
	}
	else
	{
		// doesn't support folders. just go to index page
		$t->set_var('folders_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'index.php'));
		$t->set_var('folders_btn_js','window.location='."'".$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php')."'".'"');
	}
	// go directly to email prefs page
	$t->set_var('email_prefs_txt',lang("Email Preferences"));
	$t->set_var('email_prefs_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/preferences.php'));
	// "accounts" preferences FUTURE
	$t->set_var('accounts_txt',lang("Manage Accounts"));
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

		// SHOW SIZE OF FOLDER OPTION
		// total size of all emails in this folder added up
		// can take a long time if alot of mail is in the folder, and put unneeded load on the IMAP server
		// FUTURE:  make it optional, this will pick up that option from the prefs
		$allow_stats_size_speed_skip = True;
		//$allow_stats_size_speed_skip = False;

		// if the number of messahes in the folder exceeds this number, then we skip getting the folder size
		$stats_size_threshold = 100;

		// determine if we should show the folder size
		if ((isset($phpgw->msg->args['force_showsize']))
		&& ($phpgw->msg->args['force_showsize'] != ''))
		{
			// user has requested override of this speed skip option
			$do_show_size = True;
		}
		elseif (($allow_stats_size_speed_skip == True)
		&& ($mailbox_status->messages > $stats_size_threshold))
		{
			// spped skip option is enabled and number messages exceeds skip threshold
			$do_show_size = False;
			$stats_size = '';
		}
		else
		{
			// if either of those are not met, just show the size of the folder
			$do_show_size = True;

		}

		// if we should show the folder size, get that data now
		if ($do_show_size)
		{
			$do_show_size = True;
			$mailbox_detail = $phpgw->dcom->mailboxmsginfo($phpgw->msg->mailsvr_stream);
			$stats_size = $mailbox_detail->Size;
			// size is in bytes, format for KB or MB
			$stats_size = $phpgw->msg->format_byte_size($stats_size);
		}
	}

// ---- Folder Statistics Information Row  -----
	$t->set_var('stats_backcolor',$phpgw_info['theme']['em_folder']);
	//$t->set_var('stats_backcolor','#000000');
	$t->set_var('stats_font',$phpgw_info['theme']['font']);
	$t->set_var('stats_fontsize','+0');
	$t->set_var('stats_color',$phpgw_info['theme']['em_folder_text']);
	$t->set_var('stats_folder',$folder_short);
	$t->set_var('stats_saved',$stats_saved);
	$t->set_var('stats_new',$stats_new);
	$t->set_var('lang_new',lang('New'));
	$t->set_var('lang_new2',lang('New Messages'));
	$t->set_var('lang_total',lang('Total'));
	$t->set_var('lang_total2',lang('Total Messages'));
	$t->set_var('lang_size',lang('Size'));
	$t->set_var('lang_size2',lang('Folder Size'));
	$t->set_var('stats_first',$phpgw->msg->start + 1);
	// "last" can not be know until the calculations below

	//$t->set_var('stats_last',$phpgw->msg->start + $phpgw_info['user']['preferences']['common']['maxmatchs']);
	// FOLDER SIZE: either you show it or you are skipping it because of speed skip
	if ($do_show_size == True)
	{
		// show the size of the folder
		$t->set_var('stats_size',$stats_size);
		$t->parse('V_show_size','B_show_size');
		// the other block (button to get folder size, "V_get_size") should be blank
		$t->set_var('V_get_size','');
	}
	else
	{
		//present a link or a button so the user can request showing the folder size
		$force_showsize_flag = 'force_showsize';
		// LINK : for templates using an href link for this
		$get_size_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php',
					 'folder='.$phpgw->msg->prep_folder_out('')
					.'&sort='.$phpgw->msg->sort
					.'&order='.$phpgw->msg->order
					.'&start='.$phpgw->msg->start
					.'&'.$force_showsize_flag.'=1');
		$t->set_var('get_size_link',$get_size_link);
		// BUTTON: for templates using a button for this
		$t->set_var('frm_get_size_name','form_get_size');
		$t->set_var('frm_get_size_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php'));
		$t->set_var('get_size_flag',$force_showsize_flag);
		$t->set_var('lang_get_size',lang('get size'));
		// parse the appropriate block
		$t->parse('V_get_size','B_get_size');
		// the other block (the size info, "V_show_size") should be blank
		$t->set_var('V_show_size','');
	}

// ----  Messages List SORT Clickable Column Headers  -----
	// this is the indicator flag that will be applied to the header name that = current sort
	$flag_sort_pre = '* ';
	$flag_sort_post = ' *';
	// initialize the lang'd header names
	$lang_subject = lang("subject");
	$lang_from = lang("from");
	$lang_date = lang("date");
	$lang_size = lang("size");
	$lang_lines = lang("lines");
	// this code will apply the above indicator flag to the corresponding header name
	switch ((int)$phpgw->msg->sort)
	{
		case 1 : $lang_date = $flag_sort_pre .$lang_date .$flag_sort_post; break;
		case 2 : $lang_from = $flag_sort_pre .$lang_from .$flag_sort_post; break;
		case 3 : $lang_subject = $flag_sort_pre .$lang_subject .$flag_sort_post; break;
		case 6 : $lang_size = '*'.$lang_size.'*';
			 $lang_lines = $lang_lines .$flag_sort_post; break;
	}
	
	// "show_sort_order_imap"
	// $old_sort : the current sort value
	// $new_sort : the sort value you want if you click on this
	// $default_order : user's preference for ordering list items (force this when a new [different] sorting is requested)
	// $order : the current order (will be flipped if old_sort = new_sort)
	// script file name
	// Text the link will show
	// any extra stuff you want to pass, url style
	
	// get users default order preference (hi to lo OR lo to hi)
	if ((isset($phpgw_info["user"]["preferences"]["email"]["default_sorting"]))
	  && ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old"))
	{
		$default_order = 1;
	}
	else
	{
		$default_order = 0;
	}

	// clickable column headers which change the sorting of the messages
	if ($phpgw->msg->newsmode)
	{
		// I think newsmode requires the "old way"
		$hdr_subject = $phpgw->nextmatchs->show_sort_order($phpgw->msg->sort,"3",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_subject,"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_from = $phpgw->nextmatchs->show_sort_order($phpgw->msg->sort,"2",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_from,"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_date = $phpgw->nextmatchs->show_sort_order($phpgw->msg->sort,"1",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_date,"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_size = $phpgw->nextmatchs->show_sort_order($phpgw->msg->sort,"6",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_lines,"&folder=".$phpgw->msg->prep_folder_out(''));
	}
	else
	{
		// for email
		//$hdr_subject = $phpgw->nextmatchs->show_sort_order_imap("3",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_subject,"&folder=".$phpgw->msg->prep_folder_out('').'&sort=3&order='.$phpgw->msg->order);
		//$hdr_from = $phpgw->nextmatchs->show_sort_order_imap("2",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_from,"&folder=".$phpgw->msg->prep_folder_out('').'&sort=2&order='.$phpgw->msg->order);
		//$hdr_date = $phpgw->nextmatchs->show_sort_order_imap("1",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_date,"&folder=".$phpgw->msg->prep_folder_out('').'&sort=1&order='.$phpgw->msg->order);
		//$hdr_size = $phpgw->nextmatchs->show_sort_order_imap("6",$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_size,"&folder=".$phpgw->msg->prep_folder_out('').'&sort=6&order='.$phpgw->msg->order);
		// for email
		$hdr_subject = $phpgw->nextmatchs->show_sort_order_imap($phpgw->msg->sort,"3",$default_order,$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_subject,"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_from = $phpgw->nextmatchs->show_sort_order_imap($phpgw->msg->sort,"2",$default_order,$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_from,"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_date = $phpgw->nextmatchs->show_sort_order_imap($phpgw->msg->sort,"1",$default_order,$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_date,"&folder=".$phpgw->msg->prep_folder_out(''));
		$hdr_size = $phpgw->nextmatchs->show_sort_order_imap($phpgw->msg->sort,"6",$default_order,$phpgw->msg->order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$lang_size,"&folder=".$phpgw->msg->prep_folder_out(''));
	}

	$t->set_var('hdr_backcolor',$phpgw_info['theme']['th_bg']);
	$t->set_var('hdr_font',$phpgw_info['theme']['font']);
	$t->set_var('hdr_subject',$hdr_subject);
	$t->set_var('hdr_from',$hdr_from);
	$t->set_var('hdr_date',$hdr_date);
	$t->set_var('hdr_size',$hdr_size);
	/*
	// for those layouts that do not want these clickable headers, here are plain words
	$t->set_var('lang_subject',$lang_subject);
	$t->set_var('lang_from',$lang_from);
	$t->set_var('lang_date',$lang_date);
	$t->set_var('lang_size',$lang_size);
	$t->set_var('lang_lines',$lang_size);
	*/

// ----  Form delmov Intialization  Setup  -----
	// ----  place in first checkbox cell of the messages list table, ONE TIME ONLY   -----
	$t->set_var('delmov_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/action.php'));
	$t->parse('V_form_delmov_init','T_form_delmov_init');
	$mlist_delmov_init = $t->get_var('V_form_delmov_init');	

// ----  Init Some Basic Vars Used In Messages List  -----
	$t->set_var('mlist_font',$phpgw_info['theme']['font']);
	$t->set_var('images_dir',$svr_image_dir);
	
// ----  New Message Indicator  -----
	// to make things simpler, I made this a regular variable rather than a template file var
	$mlist_newmsg_char = '<strong>*</strong>';
	$mlist_newmsg_color = '#ff0000';
	$mlist_newmsg_txt = lang("New message");
	$mlist_new_msg = '<font color="'.$mlist_newmsg_color.'">'.$mlist_newmsg_char.'</font>';
	// this is for the bottom of the page where we explain that the red astrisk means a new message
	$t->set_var('mlist_newmsg_char',$mlist_newmsg_char);
	$t->set_var('mlist_newmsg_color',$mlist_newmsg_color);
	$t->set_var('mlist_newmsg_txt',$mlist_newmsg_txt);

// ----  Attachment Indicator  -----
	// to make things simpler, I made this a regular variable rather than a template file var
	$mlist_attach_txt = lang("file");
	$mlist_attach =
		'<div align="right">'
			.'<img src="'.$svr_image_dir.'/attach.gif" alt="'.$mlist_attach_txt.'">'
		.'</div>';

// initialize (is this necessary?)
	$t->set_var('V_msg_list','');

// ----  Zero Messages To List  -----
	if ($mailbox_status->messages == 0)
	{
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
		// a simple message saying the folder is empty
		$t->parse('V_no_messages','B_no_messages');
		// set the real message list block to empty, it's not used in this case
		$t->set_var('V_msg_list','');
	}
// ----  Fill The Messages List  -----
	else
	{
		// we have messages, so set the "no messages" block to nothing, we don't show it in this case
		$t->set_var('V_no_messages','');

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
			// if it's a long plain addresswith no spaces, then add a space to the TD can wrap the text
			if ((!strstr($from_name, " "))
			&& (strstr($from_name, "@")))
			{
				$from_name = str_replace('@',' @',$from_name);
			}

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
				// this shows the red astrisk
				$t->set_var('mlist_new_msg',$mlist_new_msg);
				// for layout 2, this adds "strong" tags to bold the new message in this row
				$t->set_var('open_newbold','<strong>');
				$t->set_var('close_newbold','</strong>');
			}
			else
			{
				// show NO red astrisk
				$t->set_var('mlist_new_msg','&nbsp;');
				// include NO "strong" bold tags
				$t->set_var('open_newbold','');
				$t->set_var('close_newbold','');
			}
			if ($show_attach)
			{
				$t->set_var('mlist_attach',$mlist_attach);
			}
			else
			{
				//$t->set_var('mlist_attach','');
				$t->set_var('mlist_attach','&nbsp;');
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
			$t->parse('V_msg_list','B_msg_list',True);
			// end iterating through the messages to display
		}
	}

// ---- Delete/Move Folder Listbox  for Msg Table Footer -----
	if ($phpgw->msg->get_mailsvr_supports_folders())
	{
		$delmov_listbox =
			 '<select name="tofolder" onChange="do_action(\'move\')">'
			 	.'<option>' . lang("move selected messages into").':'
			 	.$phpgw->msg->all_folders_listbox('','',$folder_short)
			.'</select>';
            
	}
	else
	{
		$delmov_listbox = '&nbsp;';
	}

// ----  Messages List Table Footer  -----
	$t->set_var('app_images',$image_dir);
	$t->set_var('ftr_backcolor',$phpgw_info['theme']['th_bg']);
	$t->set_var('ftr_font',$phpgw_info['theme']['font']);
	$t->set_var('delmov_button',lang("delete"));
	$t->set_var('delmov_listbox',$delmov_listbox);
	
// ----  Output the Template   -----
	$t->pparse('out','T_index_main');

	$phpgw->msg->end_request();

	$phpgw->common->phpgw_footer();
?>
