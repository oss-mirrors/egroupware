<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail								*
  * http://www.phpgroupware.org							*
  * Based on Aeromail by Mark Cushman <mark@cushman.net>			*
  *          http://the.cushman.net/							*
  * --------------------------------------------						*
  *  This program is free software; you can redistribute it and/or modify it	*
  *  under the terms of the GNU General Public License as published by the	*
  *  Free Software Foundation; either version 2 of the License, or (at your		*
  *  option) any later version.								*
  \**************************************************************************/

	/* $Id$ */

	Header('Cache-Control: no-cache');
	Header('Pragma: no-cache');
	Header('Expires: Sat, Jan 01 2000 01:01:01 GMT');
  
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'email', 
		'enable_network_class' => True, 
		'enable_nextmatchs_class' => True);

	include('../header.inc.php');

	// time limit should be controlled elsewhere
	@set_time_limit(0);

// ----  Prepare Browser and Layout for Template File Name  -----
	// Layout Template from Preferences (used below)
	$my_layout = $GLOBALS['phpgw_info']['user']['preferences']['email']['layout'];
	// Browser the client is using (used below)
	$my_browser = $GLOBALS['phpgw']->msg->browser;
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
	// NEW: use API-like high level function for this:
	$report_this = $GLOBALS['phpgw']->msg->report_moved_or_deleted();
	// if no move nor delete occured, then $report_this will be empty
	if ($report_this != '')
	{
		// only parse the "report block" if there's something to report
		$t->set_var('report_this',$report_this);
		$t->parse('V_action_report','B_action_report');
	}
	else
	{
		// nothing deleted or moved, no need to parse the block
		// instead, give the block's target variable a blank string
		// so when the main template is filled, this "report block" will simply not show up.
		$t->set_var('V_action_report','');
	}

// ----  Fill Some Important Variables  -----
	$svr_image_dir = PHPGW_IMAGES_DIR;
	$image_dir = PHPGW_IMAGES;

	// lang var for checkbox javascript  -----
	$t->set_var('select_msg',lang('Please select a message first'));

// ----  Preserving Current Sort, Order, Start, and Folder where necessary  -----
	// use these as hidden vars in any form that requires preserving these vars
	// example: get size button, the delete button
	$t->set_var('current_sort',$GLOBALS['phpgw']->msg->sort);
	$t->set_var('current_order',$GLOBALS['phpgw']->msg->order);
	$t->set_var('current_start',$GLOBALS['phpgw']->msg->start);
	$t->set_var('current_folder',$GLOBALS['phpgw']->msg->prep_folder_out(''));

// ---- SwitchTo Folder Listbox   -----
	// this is used in several places in the index page
	if ($GLOBALS['phpgw']->msg->get_mailsvr_supports_folders())
	{
		// show num unseen msgs in dropdown list
		// FUTURE: $show_num_new value should be picked up from the users preferences (need to add this pref)
		//$show_num_new = True;
		$show_num_new = False;
		// build the $feed_args array for the all_folders_listbox function
		// anything not specified will be replace with a default value if the function has one for that param
		$feed_args = Array();
		$feed_args = Array(
			'mailsvr_stream'	=> '',
			'pre_select_folder'	=> '',
			'skip_folder'		=> '',
			'show_num_new'		=> $show_num_new,
			'widget_name'		=> 'folder',
			'on_change'		=> 'document.switchbox.submit()',
			'first_line_txt'	=> lang('switch current folder to')
		);
		// get you custom built HTML listbox (a.k.a. selectbox) widget
		$switchbox_listbox = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);
	}
	else
	{
		$switchbox_listbox = '&nbsp';
	}


// ---- Folder Status Infomation   -----
	// NEW: use API-like high level function for this:
	$folder_info = array();
	$folder_info = $GLOBALS['phpgw']->msg->folder_status_info();
	/* returns this array:
	folder_info['is_imap'] boolean - pop3 server do not know what is "new" or not, IMAP servers do
	folder_info['folder_checked'] string - the folder checked, as processed by the msg class, which may have done a lookup on the folder name
	folder_info['alert_string'] string - lang'd string to show the user about status of new messages in this folder
	folder_info['number_new'] integer - for IMAP: the number "recent" and/or "unseen"messages; for POP3: the total number of messages
	folder_info['number_all'] integer - for IMAP and POP3: the total number messages in the folder
	*/

// ----  Previous and Next arrows navigation  -----
	// nextmatches->left/right  vars:
	// a) script (filename like index.php)
	// b) start
	// c) total
	// d) extradata - in url format - "&var1=x&var2=y"
	$td_prev_arrows = $GLOBALS['phpgw']->nextmatchs->left('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
					$GLOBALS['phpgw']->msg->start,
					$folder_info['number_all'],
					 '&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
					.'&sort='.$GLOBALS['phpgw']->msg->sort
					.'&order='.$GLOBALS['phpgw']->msg->order);

	$td_next_arrows = $GLOBALS['phpgw']->nextmatchs->right('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
					$GLOBALS['phpgw']->msg->start,
					$folder_info['number_all'],
					'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
					.'&sort='.$GLOBALS['phpgw']->msg->sort
					.'&order='.$GLOBALS['phpgw']->msg->order);

// ---- Control Bar =Row 1=   -----
	$t->set_var('ctrl_bar_back2',$GLOBALS['phpgw_info']['theme']['row_off']);
	// Compose New
	$t->set_var('compose_txt',lang("Compose New"));
	$t->set_var('compose_link',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/compose.php','folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')));
	// Manage Folders
	$folders_txt1 = lang('Folders');
	$folders_txt2 = lang('Manage Folders');
	if ($GLOBALS['phpgw']->msg->get_mailsvr_supports_folders())
	{
		// for those templates (layouts) using an A HREF  link to the folders page
		$folders_link = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/folder.php');
		$folders_href = '<a href="'.$folders_link.'">'.$folders_txt2.'</a>';
		$t->set_var('folders_href',$folders_href);
		
		// for those templates using a BUTTON to get to the folders page
		$folders_btn_js = "window.location='$folders_link'";
		$folders_btn = '<input type="button" name="folder_link_btn" value="'.$folders_txt1.'" onClick="'.$folders_btn_js.'">';
		$t->set_var('folders_btn',$folders_btn);
	}
	else
	{
		// doesn't support folders. NO button, NO href, replace with nbsp
		$t->set_var('folders_href','&nbsp;');
		$t->set_var('folders_btn','&nbsp;');
	}
	// Email Preferences
	$t->set_var('email_prefs_txt',lang('Email Preferences'));
	$t->set_var('email_prefs_link',$GLOBALS['phpgw']->link('/index.php','menuaction=email.uipreferences.preferences'));
	// Mail Filters
	$filters_txt = lang('EMail Filters');
	$filters_link = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/filters.php');
	$filters_href = '<a href="'.$filters_link.'">'.$filters_txt.'</a>';
	$t->set_var('filters_href',$filters_href);

	// FUTURE: "accounts" preferences
	$t->set_var('accounts_txt',lang('Manage Accounts'));
	$t->set_var('accounts_link',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php'));

// ---- Control Bar =Row 2=   -----
	$t->set_var('ctrl_bar_back1',$GLOBALS['phpgw_info']['theme']['row_on']);
	// FUTURE: "accounts" switchbox
	// "sorting" switchbox
	$sort_selected[0] = '';
	$sort_selected[1] = '';
	$sort_selected[2] = '';
	$sort_selected[3] = '';
	$sort_selected[6] = '';
	$sort_selected[$GLOBALS['phpgw']->msg->sort] = " selected";
	$sortbox_select_options =
		 '<option value="0"' .$sort_selected[0] .'>'.lang("Email Date").'</option>' ."\r\n"
		.'<option value="1"' .$sort_selected[1] .'>'.lang("Arrival Date").'</option>' ."\r\n"
		.'<option value="2"' .$sort_selected[2] .'>'.lang("From").'</option>' ."\r\n"
		.'<option value="3"' .$sort_selected[3] .'>'.lang("Subject").'</option>' ."\r\n"
		.'<option value="6"' .$sort_selected[6] .'>'.lang("Size").'</option>' ."\r\n";

	$tpl_vars = Array(
		'sortbox_action'	=> $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
						'folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')),
		'sortbox_on_change'	=> 'document.sortbox.submit()',
		'sortbox_select_name'	=> 'sort',
		'sortbox_select_options'	=> $sortbox_select_options,
		'sortbox_sort_by_txt'	=> lang("Sort By"),
		// "switch to" folder switchbox
		'switchbox_action'	=> $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php'),
		'switchbox_listbox'	=> $switchbox_listbox,
		// navagation arrows
		'arrows_backcolor'	=> $GLOBALS['phpgw_info']['theme']['row_off'],
		'prev_arrows'		=> $td_prev_arrows,
		'next_arrows'		=> $td_next_arrows
	);
	$t->set_var($tpl_vars);

// ---- Message Folder Stats Display  -----
	if ($folder_info['number_all'] == 0)
	{
		$stats_saved = '-';
		$stats_new = '-';
		$stats_size = '-';
	}
	else
	{
		// TOTAL MESSAGES IN FOLDER
		$stats_saved = number_format($folder_info['number_all']);
		
		// NUM NEW MESSAGES
		$stats_new = $folder_info['number_new'];
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
		// function "report_total_foldersize($size_report_args)" takes 3 array keys as its args:
		//	$size_report_args['allow_stats_size_speed_skip']
		//	$size_report_args['stats_size_threshold']
		//	$size_report_args['number_all'] 
		// can take a long time if alot of mail is in the folder, and put unneeded load on the IMAP server
		// FUTURE:  make it optional, this will pick up that option from the prefs
		$size_report_args['allow_stats_size_speed_skip'] = True;
		//$size_report_args['allow_stats_size_speed_skip'] = False;
		// if the number of messages in the folder exceeds this number, then we skip getting the folder size
		$size_report_args['stats_size_threshold'] = 100;
		// thus the function needs to know how many total messages there are
		$size_report_args['number_all'] = $folder_info['number_all'];
		// get the data, if it's filled then it was OK to get the data and we indeed got valid data
		$stats_size = $GLOBALS['phpgw']->msg->report_total_foldersize($size_report_args);		
	}

// ---- Folder Statistics Information Row  -----
	$tpl_vars = Array(
		'stats_backcolor'	=> $GLOBALS['phpgw_info']['theme']['em_folder'],
		'stats_font'	=> $GLOBALS['phpgw_info']['theme']['font'],
		'stats_color'	=> $GLOBALS['phpgw_info']['theme']['em_folder_text'],
		'stats_folder'	=> $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->folder),
		'stats_saved'	=> $stats_saved,
		'stats_new'	=> $stats_new,
		'lang_new'	=> lang('New'),
		'lang_new2'	=> lang('New Messages'),
		'lang_total'	=> lang('Total'),
		'lang_total2'	=> lang('Total Messages'),
		'lang_size'	=> lang('Size'),
		'lang_size2'	=> lang('Folder Size'),
		'stats_to_txt'	=> lang('to'),
		'stats_first'	=> ($GLOBALS['phpgw']->msg->start + 1)
	);
	$t->set_var($tpl_vars);
	// "last" (stats_last) can not be know until the calculations below

	// FOLDER SIZE: either you show it or you are skipping it because of speed skip
	if ($stats_size != '')
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
		$get_size_link = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
					 'folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
					.'&sort='.$GLOBALS['phpgw']->msg->sort
					.'&order='.$GLOBALS['phpgw']->msg->order
					.'&start='.$GLOBALS['phpgw']->msg->start
					.'&'.$force_showsize_flag.'=1');
		
		$t->set_var('get_size_link',$get_size_link);
		// BUTTON: for templates using a button for this
		$t->set_var('frm_get_size_name','form_get_size');
		$t->set_var('frm_get_size_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php'));
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
	$lang_subject = lang('subject');
	$lang_from = lang('from');
	$lang_date = lang('date');
	$lang_size = lang('size');
	$lang_lines = lang('lines');
	// this code will apply the above indicator flag to the corresponding header name
	switch ((int)$GLOBALS['phpgw']->msg->sort)
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
	if ((isset($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting']))
	  && ($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting'] == 'new_old'))
	{
		$default_order = 1;
	}
	else
	{
		$default_order = 0;
	}

	// clickable column headers which change the sorting of the messages
	if ($GLOBALS['phpgw']->msg->newsmode)
	{
		// I think newsmode requires the "old way"
		$hdr_subject = $GLOBALS['phpgw']->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'3',$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$lang_subject,'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$hdr_from = $GLOBALS['phpgw']->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'2',$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$lang_from,'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$hdr_date = $GLOBALS['phpgw']->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'1',$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$lang_date,'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$hdr_size = $GLOBALS['phpgw']->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'6',$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$lang_lines,'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
	}
	else
	{
		// for email
		$hdr_subject = $GLOBALS['phpgw']->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'3',$default_order,$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$lang_subject,'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$hdr_from = $GLOBALS['phpgw']->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'2',$default_order,$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$lang_from,'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$hdr_date = $GLOBALS['phpgw']->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'1',$default_order,$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$lang_date,'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$hdr_size = $GLOBALS['phpgw']->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'6',$default_order,$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$lang_size,'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
	}

	$tpl_vars = Array(
		'hdr_backcolor'	=> $GLOBALS['phpgw_info']['theme']['th_bg'],
		'hdr_font'	=> $GLOBALS['phpgw_info']['theme']['font'],
		'hdr_subject'	=> $hdr_subject,
		'hdr_from'	=> $hdr_from,
		'hdr_date'	=> $hdr_date,
		'hdr_size'	=> $hdr_size
	);
	$t->set_var($tpl_vars);
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
	$t->set_var('delmov_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/action.php'));
	$t->parse('V_form_delmov_init','T_form_delmov_init');
	$mlist_delmov_init = $t->get_var('V_form_delmov_init');	

// ----  Init Some Basic Vars Used In Messages List  -----
	$t->set_var('mlist_font',$GLOBALS['phpgw_info']['theme']['font']);
	$t->set_var('images_dir',$svr_image_dir);
	
// ----  New Message Indicator  -----
	// to make things simpler, I made this a regular variable rather than a template file var
	$mlist_newmsg_char = '<strong>*</strong>';
	$mlist_newmsg_color = '#ff0000';
	$mlist_newmsg_txt = lang('New message');
	$mlist_new_msg = '<font color="'.$mlist_newmsg_color.'">'.$mlist_newmsg_char.'</font>';
	// this is for the bottom of the page where we explain that the red astrisk means a new message
	$t->set_var('mlist_newmsg_char',$mlist_newmsg_char);
	$t->set_var('mlist_newmsg_color',$mlist_newmsg_color);
	$t->set_var('mlist_newmsg_txt',$mlist_newmsg_txt);

// ----  Attachment Indicator  -----
	// to make things simpler, I made this a regular variable rather than a template file var
	$mlist_attach_txt = lang('file');
	$mlist_attach =
		'<div align="right">'
			.'<img src="'.$svr_image_dir.'/attach.gif" alt="'.$mlist_attach_txt.'">'
		.'</div>';

// ----  Zero Messages To List  -----
	if ($folder_info['number_all'] == 0)
	{
		if ((!isset($GLOBALS['phpgw']->msg->mailsvr_stream))
		|| ($GLOBALS['phpgw']->msg->mailsvr_stream == ''))
		{
			$report_no_msgs = lang('Could not open this mailbox');
		}
		else
		{
			$report_no_msgs = lang('this folder is empty');
		}
		// this info for the stats row above
		$t->set_var('stats_last','0');
		// no messages to display, msgs list is just one row reporting this
		$t->set_var('report_no_msgs',$report_no_msgs);
		$t->set_var('mlist_delmov_init',$mlist_delmov_init);
		$t->set_var('mlist_backcolor',$GLOBALS['phpgw_info']['theme']['row_on']);
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
		
		// generate a list of details about all the messages we are going to show
		$msg_list_dsp = Array();
		$msg_list_dsp = $GLOBALS['phpgw']->msg->get_msg_list_display($folder_info);
		$totaltodisplay = $GLOBALS['phpgw']->msg->start + count($msg_list_dsp);
		// this info for the stats row above
		$t->set_var('stats_last',$totaltodisplay);

		for ($i=0; $i < count($msg_list_dsp); $i++)
		{
			// set up vars for the template parsing
			if ($msg_list_dsp[$i]['first_item'])
			{
				$t->set_var('mlist_delmov_init',$mlist_delmov_init);
			}
			else
			{
				$t->set_var('mlist_delmov_init', '');
			}
			if ($msg_list_dsp[$i]['is_unseen'])
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
			if ($msg_list_dsp[$i]['has_attachment'])
			{
				$t->set_var('mlist_attach',$mlist_attach);
			}
			else
			{
				// put nbsp there so mozilla will at least show the back color for the cell
				$t->set_var('mlist_attach','&nbsp;');
			}
			$tpl_vars = Array(
				'mlist_msg_num'		=> $msg_list_dsp[$i]['msg_num'],
				'mlist_backcolor'	=> $msg_list_dsp[$i]['back_color'],
				'mlist_subject'		=> $msg_list_dsp[$i]['subject'],
				'mlist_subject_link'	=> $msg_list_dsp[$i]['subject_link'],
				'mlist_from'		=> $msg_list_dsp[$i]['from_name'],
				'mlist_from_extra'	=> $msg_list_dsp[$i]['display_address_from'],
				'mlist_reply_link'	=> $msg_list_dsp[$i]['from_link'],
				'mlist_date'		=> $msg_list_dsp[$i]['msg_date'],
				'mlist_size'		=> $msg_list_dsp[$i]['size']
			);
			$t->set_var($tpl_vars);

			// fill this template, "true" means it's cumulative
			$t->parse('V_msg_list','B_msg_list',True);
		}
		// end iterating through the messages to display
	}

// ---- Delete/Move Folder Listbox  for Msg Table Footer -----
	if ($GLOBALS['phpgw']->msg->get_mailsvr_supports_folders())
	{
		// show num unseen msgs in dropdown list
		// FUTURE: $show_num_new value should be picked up from the users preferences (need to add this pref)
		//$show_num_new = True;
		$show_num_new = False;
		// build the $feed_args array for the all_folders_listbox function
		// anything not specified will be replace with a default value if the function has one for that param
		$feed_args = Array();
		$feed_args = Array(
			'mailsvr_stream'	=> '',
			'pre_select_folder'	=> '',
			'skip_folder'		=> $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->folder),
			'show_num_new'		=> $show_num_new,
			'widget_name'		=> 'tofolder',
			'on_change'		=> 'do_action(\'move\')',
			'first_line_txt'	=> lang('move selected messages into')
		);
		// get you custom built HTML listbox (a.k.a. selectbox) widget
		$delmov_listbox = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);            
	}
	else
	{
		$delmov_listbox = '&nbsp;';
	}

// ----  Messages List Table Footer  -----
	$tpl_vars = Array(
		'app_images'		=> $image_dir,
		'ftr_backcolor'		=> $GLOBALS['phpgw_info']['theme']['th_bg'],
		'ftr_font'		=> $GLOBALS['phpgw_info']['theme']['font'],
		'delmov_button'		=> lang('delete'),
		'delmov_listbox'	=> $delmov_listbox
	);
	$t->set_var($tpl_vars);
	/*
	$t->set_var('app_images',$image_dir);
	$t->set_var('ftr_backcolor',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$t->set_var('ftr_font',$GLOBALS['phpgw_info']['theme']['font']);
	$t->set_var('delmov_button',lang('delete'));
	$t->set_var('delmov_listbox',$delmov_listbox);
	*/
// ----  Output the Template   -----
	$t->pparse('out','T_index_main');

	$GLOBALS['phpgw']->msg->end_request();

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
