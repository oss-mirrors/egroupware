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
	
// ---- initialize Main Array Data Holder  ----
	// this array will actually hold all the data for this page
	$xi = array();

// ----  Prepare Browser and Layout for Template File Name  -----
	// Layout Template from Preferences (used below)
	$xi['my_layout'] = $GLOBALS['phpgw_info']['user']['preferences']['email']['layout'];
	// Browser the client is using (used below)
	$xi['my_browser'] = $GLOBALS['phpgw']->msg->browser;
	// example: if browser=0 (no CSS) and layout pref = 1 (default) then template used is:
	// "index_main_b0_l1.tpl"

// ----  Load Template Files And Specify Template Blocks  -----
	$xi['tpl'] = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$xi['tpl']->set_file(array(		
		'T_form_delmov_init' => 'index_form_delmov_init.tpl',
		'T_index_main' => 'index_main_b'.$xi['my_browser'].'_l'.$xi['my_layout']. '.tpl'
	));
	$xi['tpl']->set_block('T_index_main','B_action_report','V_action_report');
	$xi['tpl']->set_block('T_index_main','B_show_size','V_show_size');
	$xi['tpl']->set_block('T_index_main','B_get_size','V_get_size');
	$xi['tpl']->set_block('T_index_main','B_no_messages','V_no_messages');
	$xi['tpl']->set_block('T_index_main','B_msg_list','V_msg_list');

// ----  Langs  ----
	// lang var for checkbox javascript  -----
	$xi['select_msg'] = lang('Please select a message first');
	$xi['first_line_txt'] = lang('switch current folder to');
	$xi['compose_txt'] = lang('Compose New');
	$xi['folders_txt1'] = lang('Folders');
	$xi['folders_txt2'] = lang('Manage Folders');
	$xi['email_prefs_txt'] = lang('Email Preferences');
	$xi['filters_txt'] = lang('EMail Filters');
	$xi['accounts_txt'] = lang('Manage Accounts');
	// some langs for the sort by box
	$xi['lang_sort_by'] = lang('Sort By');
	$xi['lang_email_date'] = lang('Email Date');
	$xi['lang_arrival_date'] = lang('Arrival Date');
	$xi['lang_from'] = lang('From');
	$xi['lang_subject'] = lang('Subject');
	$xi['lang_size'] = lang('Size');
	// folder stats Information bar
	$xi['lang_new'] = lang('New');
	$xi['lang_new2'] = lang('New Messages');
	$xi['lang_total'] = lang('Total');
	$xi['lang_total2'] = lang('Total Messages');
	//$xi['lang_size'] = lang('Size');
	$xi['lang_size2'] = lang('Folder Size');
	$xi['stats_to_txt'] = lang('to');
	$xi['lang_get_size'] = lang('get size');
	// initialize the lang'd header names
	//$xi['lang_from'] = lang('from');
	$xi['lang_date'] = lang('date');
	$xi['lang_lines'] = lang('lines');
	$xi['lang_counld_not_open'] = lang('Could not open this mailbox');
	$xi['lang_empty_folder'] = lang('this folder is empty');
	$xi['lang_delete'] = lang('delete');
	$xi['mlist_attach_txt'] = lang('file');
	$xi['mlist_newmsg_txt'] = lang('New message');


// ---- report on number of messages Deleted or Moved (if any)  -----
	// NEW: use API-like high level function for this:
	$xi['report_this'] = $GLOBALS['phpgw']->msg->report_moved_or_deleted();
	// if no move nor delete occured, then $xi['report_this'] will be empty

// ----  Fill Some Important Variables  -----
	$xi['svr_image_dir'] = PHPGW_IMAGES_DIR;
	$xi['image_dir'] = PHPGW_IMAGES;

// ----  Preserving Current Sort, Order, Start, and Folder where necessary  -----
	// use these as hidden vars in any form that requires preserving these vars
	// example: get size button, the delete button
	$xi['current_sort'] = $GLOBALS['phpgw']->msg->sort;
	$xi['current_order'] = $GLOBALS['phpgw']->msg->order;
	$xi['current_start'] = $GLOBALS['phpgw']->msg->start;
	$xi['current_folder'] = $GLOBALS['phpgw']->msg->prep_folder_out('');

// ---- Show Number of Unseen Msgs in Dropdown List  ----
	// should we show num unseen msgs next to each folder in the folder dropdown lists
	// this applies to each of the 2 folder listboxes on the index page
	// FUTURE: this value should be picked up from the users preferences (need to add this pref)
	//$xi['show_num_new'] = True;
	$xi['show_num_new'] = False;

// ---- SwitchTo Folder Listbox   -----
	$xi['mailsvr_supports_folders'] = $GLOBALS['phpgw']->msg->get_mailsvr_supports_folders();
	// this is used in several places in the index page
	if ($xi['mailsvr_supports_folders'])
	{
		// build the $feed_args array for the all_folders_listbox function
		// anything not specified will be replace with a default value if the function has one for that param
		$feed_args = Array(
			'mailsvr_stream'	=> '',
			'pre_select_folder'	=> '',
			'skip_folder'		=> '',
			'show_num_new'		=> $xi['show_num_new'],
			'widget_name'		=> 'folder',
			'on_change'		=> 'document.switchbox.submit()',
			'first_line_txt'	=> $xi['first_line_txt']
		);
		// get you custom built HTML listbox (a.k.a. selectbox) widget
		$xi['switchbox_listbox'] = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);
	}
	else
	{
		$xi['switchbox_listbox'] = '&nbsp';
	}


// ---- Folder Status Infomation   -----
	// NEW: use API-like high level function for this:
	$xi['folder_info'] = array();
	$xi['folder_info'] = $GLOBALS['phpgw']->msg->folder_status_info();
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
	$xi['td_prev_arrows'] = $GLOBALS['phpgw']->nextmatchs->left('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
					$GLOBALS['phpgw']->msg->start,
					$xi['folder_info']['number_all'],
					 '&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
					.'&sort='.$GLOBALS['phpgw']->msg->sort
					.'&order='.$GLOBALS['phpgw']->msg->order);

	$xi['td_next_arrows'] = $GLOBALS['phpgw']->nextmatchs->right('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
					$GLOBALS['phpgw']->msg->start,
					$xi['folder_info']['number_all'],
					'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
					.'&sort='.$GLOBALS['phpgw']->msg->sort
					.'&order='.$GLOBALS['phpgw']->msg->order);
	// navagation arrows
	$xi['arrows_backcolor'] = $GLOBALS['phpgw_info']['theme']['row_off'];

// ---- Control Bar =Row 1=   -----
	$xi['ctrl_bar_back2'] = $GLOBALS['phpgw_info']['theme']['row_off'];
	// Compose New
	$xi['compose_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/compose.php','folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
	// Manage Folders
	if ($xi['mailsvr_supports_folders'])
	{
		// for those templates (layouts) using an A HREF  link to the folders page
		$xi['folders_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/folder.php');
		$xi['folders_href'] = '<a href="'.$xi['folders_link'].'">'.$xi['folders_txt2'].'</a>';
		
		// for those templates using a BUTTON to get to the folders page
		$folders_btn_js = 'window.location=\''.$xi['folders_link'].'\'';
		$xi['folders_btn'] = '<input type="button" name="folder_link_btn" value="'.$xi['folders_txt1'].'" onClick="'.$folders_btn_js.'">';
	}
	else
	{
		// doesn't support folders. NO button, NO href, replace with nbsp
		$xi['folders_href'] = '&nbsp;';
		$xi['folders_btn'] = '&nbsp;';
	}
	// Email Preferences
	$xi['email_prefs_link'] = $GLOBALS['phpgw']->link('/index.php','menuaction=email.uipreferences.preferences');
	// Mail Filters
	$xi['filters_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/filters.php');
	$xi['filters_href'] = '<a href="'.$xi['filters_link'].'">'.$xi['filters_txt'].'</a>';

	// FUTURE: "accounts" preferences
	$xi['accounts_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php');

// ---- Control Bar =Row 2=   -----
	$xi['ctrl_bar_back1'] = $GLOBALS['phpgw_info']['theme']['row_on'];
	
	// FUTURE: "accounts" switchbox
	
	// "sorting" switchbox
	$sort_selected = Array(
		0 => '',
		1 => '',
		2 => '',
		3 => '',
		6 => ''
	);
	//$sort_selected[0] = '';
	//$sort_selected[1] = '';
	//$sort_selected[2] = '';
	//$sort_selected[3] = '';
	//$sort_selected[6] = '';
	$sort_selected[$GLOBALS['phpgw']->msg->sort] = " selected";
	$xi['sortbox_select_options'] =
		 '<option value="0"' .$sort_selected[0] .'>'.$xi['lang_email_date'].'</option>' ."\r\n"
		.'<option value="1"' .$sort_selected[1] .'>'.$xi['lang_arrival_date'].'</option>' ."\r\n"
		.'<option value="2"' .$sort_selected[2] .'>'.$xi['lang_from'].'</option>' ."\r\n"
		.'<option value="3"' .$sort_selected[3] .'>'.$xi['lang_subject'].'</option>' ."\r\n"
		.'<option value="6"' .$sort_selected[6] .'>'.$xi['lang_size'].'</option>' ."\r\n";

	$xi['sortbox_action'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
						'folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
	$xi['sortbox_on_change'] = 'document.sortbox.submit()';
	$xi['sortbox_select_name'] = 'sort';
	$xi['switchbox_action'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php');


// ---- Message Folder Stats Display  -----
	if ($xi['folder_info']['number_all'] == 0)
	{
		$xi['stats_saved'] = '-';
		$xi['stats_new'] = '-';
		$xi['stats_size'] = '-';
	}
	else
	{
		// TOTAL MESSAGES IN FOLDER
		$xi['stats_saved'] = number_format($xi['folder_info']['number_all']);
		
		// NUM NEW MESSAGES
		$xi['stats_new'] = $xi['folder_info']['number_new'];
		if ($xi['stats_new'] == 0)
		{
			$xi['stats_new'] = '0';
		}
		else
		{
			// put a comma between the thousands
			$xi['stats_new'] = number_format($xi['stats_new']);
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
		$size_report_args['number_all'] = $xi['folder_info']['number_all'];
		// get the data, if it's filled then it was OK to get the data and we indeed got valid data
		$xi['stats_size'] = $GLOBALS['phpgw']->msg->report_total_foldersize($size_report_args);		
	}

// ---- Folder Statistics Information Row  -----
	$xi['stats_backcolor'] = $GLOBALS['phpgw_info']['theme']['em_folder'];
	$xi['stats_font'] = $GLOBALS['phpgw_info']['theme']['font'];
	$xi['stats_color'] = $GLOBALS['phpgw_info']['theme']['em_folder_text'];
	$xi['stats_folder'] = $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->folder);
	$xi['stats_first'] = $GLOBALS['phpgw']->msg->start + 1;
	// "last" (stats_last) can not be know until the calculations below

	// FOLDER SIZE: either you show it or you are skipping it because of speed skip
	if ($xi['stats_size'] != '')
	{
		// when output the template down below, you will
		// show the size of the folder in the template
		// the other block (button to get folder size, "V_get_size") should be blank
	}
	else
	{
		//present a link or a button so the user can request showing the folder size
		$xi['force_showsize_flag'] = 'force_showsize';
		// LINK : for templates using an href link for this
		$xi['get_size_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
					 'folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
					.'&sort='.$GLOBALS['phpgw']->msg->sort
					.'&order='.$GLOBALS['phpgw']->msg->order
					.'&start='.$GLOBALS['phpgw']->msg->start
					.'&'.$xi['force_showsize_flag'].'=1');
		$xi['frm_get_size_name'] = 'form_get_size';
		$xi['frm_get_size_action'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php');
		// when output the template down below, you will
		// parse the appropriate block
		// the other block (the size info, "V_show_size") should be blank
	}

// ----  Messages List SORT Clickable Column Headers  -----
	// this is the indicator flag that will be applied to the header name that = current sort
	$flag_sort_pre = '* ';
	$flag_sort_post = ' *';
	// this code will apply the above indicator flag to the corresponding header name
	switch ((int)$GLOBALS['phpgw']->msg->sort)
	{
		case 1 : $xi['lang_date'] = $flag_sort_pre .$xi['lang_date'] .$flag_sort_post; break;
		case 2 : $xi['lang_from'] = $flag_sort_pre .$xi['lang_from'] .$flag_sort_post; break;
		case 3 : $xi['lang_subject'] = $flag_sort_pre .$xi['lang_subject'] .$flag_sort_post; break;
		case 6 : $xi['lang_size'] = '*'.$xi['lang_size'].'*';
			 $xi['lang_lines'] = $xi['lang_lines'] .$flag_sort_post; break;
	}
	
	// "show_sort_order_imap"
	// $old_sort : the current sort value
	// $new_sort : the sort value you want if you click on this
	// $xi['default_order'] : user's preference for ordering list items (force this when a new [different] sorting is requested)
	// $order : the current order (will be flipped if old_sort = new_sort)
	// script file name
	// Text the link will show
	// any extra stuff you want to pass, url style
	
	// get users default order preference (hi to lo OR lo to hi) as its use was noted above
	if ((isset($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting']))
	  && ($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting'] == 'new_old'))
	{
		$xi['default_order'] = 1;
	}
	else
	{
		$xi['default_order'] = 0;
	}

	// clickable column headers which change the sorting of the messages
	if ($GLOBALS['phpgw']->msg->newsmode)
	{
		// I think newsmode requires the "old way"
		$xi['hdr_subject'] = $GLOBALS['phpgw']->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'3',$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$xi['lang_subject'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$xi['hdr_from'] = $GLOBALS['phpgw']->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'2',$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$xi['lang_from'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$xi['hdr_date'] = $GLOBALS['phpgw']->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'1',$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$xi['lang_date'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$xi['hdr_size'] = $GLOBALS['phpgw']->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'6',$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$xi['lang_lines'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
	}
	else
	{
		// for email
		$xi['hdr_subject'] = $GLOBALS['phpgw']->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'3',$xi['default_order'],$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$xi['lang_subject'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$xi['hdr_from'] = $GLOBALS['phpgw']->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'2',$xi['default_order'],$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$xi['lang_from'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$xi['hdr_date'] = $GLOBALS['phpgw']->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'1',$xi['default_order'],$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$xi['lang_date'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
		$xi['hdr_size'] = $GLOBALS['phpgw']->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'6',$xi['default_order'],$GLOBALS['phpgw']->msg->order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',$xi['lang_size'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
	}
	$xi['hdr_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
	$xi['hdr_font'] = $GLOBALS['phpgw_info']['theme']['font'];

// ----  Init Some Basic Vars Used In Messages List  -----
	$xi['mlist_font'] = $GLOBALS['phpgw_info']['theme']['font'];
	// to make things simpler, I made this a regular variable rather than a template file var
	$xi['mlist_newmsg_char'] = '<strong>*</strong>';
	$xi['mlist_newmsg_color'] = '#ff0000';
	$xi['mlist_new_msg'] = '<font color="'.$xi['mlist_newmsg_color'].'">'.$xi['mlist_newmsg_char'].'</font>';
	// ----  Attachment Indicator  -----
	// to make things simpler, I made this a regular variable rather than a template file var
	$xi['mlist_attach'] =
		'<div align="right">'
			.'<img src="'.$xi['svr_image_dir'].'/attach.gif" alt="'.$xi['mlist_attach_txt'].'">'
		.'</div>';



// boindex_page stops at this line, below should be UI index page
// ----  Form delmov Intialization  Setup  -----
	// ----  place in first checkbox cell of the messages list table, ONE TIME ONLY   -----
	$xi['tpl']->set_var('delmov_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/action.php'));
	$xi['tpl']->parse('V_form_delmov_init','T_form_delmov_init');
	// NOTE this parsing here actually sets up a string we need below here
	// so these 3 tpl->  calls need to be here, can not be moved down with the other tpl-> calls
	$xi['mlist_delmov_init'] = $xi['tpl']->get_var('V_form_delmov_init');	

	$xi['tpl']->set_var('mlist_font',$xi['mlist_font']);
	$xi['tpl']->set_var('images_dir',$xi['svr_image_dir']);
	
// ----  New Message Indicator  -----
	// this is for the bottom of the page where we explain that the red astrisk means a new message
	$xi['tpl']->set_var('mlist_newmsg_char',$xi['mlist_newmsg_char']);
	$xi['tpl']->set_var('mlist_newmsg_color',$xi['mlist_newmsg_color']);
	$xi['tpl']->set_var('mlist_newmsg_txt',$xi['mlist_newmsg_txt']);


// BO takes below
// ----  Zero Messages To List  -----
	if ($xi['folder_info']['number_all'] == 0)
	{
		if ((!isset($GLOBALS['phpgw']->msg->mailsvr_stream))
		|| ($GLOBALS['phpgw']->msg->mailsvr_stream == ''))
		{
			$xi['report_no_msgs'] = $xi['lang_counld_not_open'];
		}
		else
		{
			$xi['report_no_msgs'] = $xi['lang_empty_folder'];
		}
// BO ends here, back to UI class
		// this info for the stats row above
		$xi['tpl']->set_var('stats_last','0');
		// no messages to display, msgs list is just one row reporting this
		$xi['tpl']->set_var('report_no_msgs',$xi['report_no_msgs']);
		$xi['tpl']->set_var('mlist_delmov_init',$xi['mlist_delmov_init']);
		$xi['tpl']->set_var('mlist_backcolor',$GLOBALS['phpgw_info']['theme']['row_on']);
		// big Mr. Message List is just one row in this case
		// a simple message saying the folder is empty
		$xi['tpl']->parse('V_no_messages','B_no_messages');
		// set the real message list block to empty, it's not used in this case
		$xi['tpl']->set_var('V_msg_list','');
	}
// ----  Fill The Messages List  -----
	else
	{
		// we have messages, so set the "no messages" block to nothing, we don't show it in this case
		$xi['tpl']->set_var('V_no_messages','');
		

// boindex_page holds the next 4 lines
		// generate a list of details about all the messages we are going to show
		$msg_list_dsp = Array();
		$msg_list_dsp = $GLOBALS['phpgw']->msg->get_msg_list_display($xi['folder_info']);
		$totaltodisplay = $GLOBALS['phpgw']->msg->start + count($msg_list_dsp);
		// this info for the stats row above
		$xi['stats_last'] = $totaltodisplay;
// boindex_page 4 lines ends here, back to UI index page


		$xi['tpl']->set_var('stats_last',$totaltodisplay);

		for ($i=0; $i < count($msg_list_dsp); $i++)
		{
			// set up vars for the template parsing
			if ($msg_list_dsp[$i]['first_item'])
			{
				$xi['tpl']->set_var('mlist_delmov_init',$xi['mlist_delmov_init']);
			}
			else
			{
				$xi['tpl']->set_var('mlist_delmov_init', '');
			}
			if ($msg_list_dsp[$i]['is_unseen'])
			{
				// this shows the red astrisk
				$xi['tpl']->set_var('mlist_new_msg',$xi['mlist_new_msg']);
				// for layout 2, this adds "strong" tags to bold the new message in this row
				$xi['tpl']->set_var('open_newbold','<strong>');
				$xi['tpl']->set_var('close_newbold','</strong>');
			}
			else
			{
				// show NO red astrisk
				$xi['tpl']->set_var('mlist_new_msg','&nbsp;');
				// include NO "strong" bold tags
				$xi['tpl']->set_var('open_newbold','');
				$xi['tpl']->set_var('close_newbold','');
			}
			if ($msg_list_dsp[$i]['has_attachment'])
			{
				$xi['tpl']->set_var('mlist_attach',$xi['mlist_attach']);
			}
			else
			{
				// put nbsp there so mozilla will at least show the back color for the cell
				$xi['tpl']->set_var('mlist_attach','&nbsp;');
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
			$xi['tpl']->set_var($tpl_vars);

			// fill this template, "true" means it's cumulative
			$xi['tpl']->parse('V_msg_list','B_msg_list',True);
		}
		// end iterating through the messages to display
	}


// bo index page starts again
// ---- Delete/Move Folder Listbox  for Msg Table Footer -----
	if ($xi['mailsvr_supports_folders'])
	{
		// build the $feed_args array for the all_folders_listbox function
		// anything not specified will be replace with a default value if the function has one for that param
		$feed_args = Array();
		$feed_args = Array(
			'mailsvr_stream'	=> '',
			'pre_select_folder'	=> '',
			'skip_folder'		=> $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->folder),
			'show_num_new'		=> $xi['show_num_new'],
			'widget_name'		=> 'tofolder',
			'on_change'		=> 'do_action(\'move\')',
			'first_line_txt'	=> lang('move selected messages into')
		);
		// get you custom built HTML listbox (a.k.a. selectbox) widget
		$xi['delmov_listbox'] = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);            
	}
	else
	{
		$xi['delmov_listbox'] = '&nbsp;';
	}


// ----  Messages List Table Footer  -----
	$xi['ftr_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
	$xi['ftr_font']	= $GLOBALS['phpgw_info']['theme']['font'];
// bo index page ends here, back to UI index page







// ----  Set All Template Vars Not Yet Set  -----
	if ($xi['report_this'] != '')
	{
		// only parse the "report block" if there's something to report
		$xi['tpl']->set_var('report_this',$xi['report_this']);
		$xi['tpl']->parse('V_action_report','B_action_report');
	}
	else
	{
		// nothing deleted or moved, no need to parse the block
		// instead, give the block's target variable a blank string
		// so when the main template is filled, this "report block" will simply not show up.
		$xi['tpl']->set_var('V_action_report','');
	}
	$tpl_vars = Array(
		'select_msg'	=> $xi['select_msg'],
		'current_sort'	=> $xi['current_sort'],
		'current_order'	=> $xi['current_order'],
		'current_start'	=> $xi['current_start'],
		'current_folder'	=> $xi['current_folder'],
		'ctrl_bar_back2'	=> $xi['ctrl_bar_back2'],
		'compose_txt'	=> $xi['compose_txt'],
		'compose_link'	=> $xi['compose_link'],
		'folders_href'	=> $xi['folders_href'],
		'folders_btn'	=> $xi['folders_btn'],
		'email_prefs_txt'	=> $xi['email_prefs_txt'],
		'email_prefs_link'	=> $xi['email_prefs_link'],
		'filters_href'	=> $xi['filters_href'],
		'accounts_txt'	=> $xi['accounts_txt'],
		'accounts_link'	=> $xi['accounts_link'],
		'ctrl_bar_back1'	=> $xi['ctrl_bar_back1'],
		'sortbox_action'	=> $xi['sortbox_action'],
		'sortbox_on_change'	=> $xi['sortbox_on_change'],
		'sortbox_select_name'	=> $xi['sortbox_select_name'],
		'sortbox_select_options' => $xi['sortbox_select_options'],
		'sortbox_sort_by_txt'	=> $xi['lang_sort_by'],
		'switchbox_action'	=> $xi['sortbox_action'],
		'switchbox_listbox'	=> $xi['switchbox_listbox'],
		'arrows_backcolor'	=> $xi['arrows_backcolor'],
		'prev_arrows'		=> $xi['td_prev_arrows'],
		'next_arrows'		=> $xi['td_next_arrows'],
		'stats_backcolor' => $xi['stats_backcolor'],
		'stats_font'	=> $xi['stats_font'],
		'stats_color'	=> $xi['stats_color'],
		'stats_folder'	=> $xi['stats_folder'],
		'stats_saved'	=> $xi['stats_saved'],
		'stats_new'	=> $xi['stats_new'],
		'lang_new'	=> $xi['lang_new'],
		'lang_new2'	=> $xi['lang_new2'],
		'lang_total'	=> $xi['lang_total'],
		'lang_total2'	=> $xi['lang_total2'],
		'lang_size'	=> $xi['lang_size'],
		'lang_size2'	=> $xi['lang_size2'],
		'stats_to_txt'	=> $xi['stats_to_txt'],
		'stats_first'	=> $xi['stats_first'],
		'hdr_backcolor'	=> $xi['hdr_backcolor'],
		'hdr_font'	=> $xi['hdr_font'],
		'hdr_subject'	=> $xi['hdr_subject'],
		'hdr_from'	=> $xi['hdr_from'],
		'hdr_date'	=> $xi['hdr_date'],
		'hdr_size'	=> $xi['hdr_size'],
		'app_images'		=> $xi['image_dir'],
		'ftr_backcolor'		=> $xi['ftr_backcolor'],
		'ftr_font'		=> $xi['ftr_font'],
		'delmov_button'		=> $xi['lang_delete'],
		'delmov_listbox'	=> $xi['delmov_listbox']
	);
	$xi['tpl']->set_var($tpl_vars);
	if ($xi['stats_size'] != '')
	{
		// show the size of the folder
		$xi['tpl']->set_var('stats_size',$xi['stats_size']);
		$xi['tpl']->parse('V_show_size','B_show_size');
		// the other block (button to get folder size, "V_get_size") should be blank
		$xi['tpl']->set_var('V_get_size','');
	}
	else
	{
		//present a link or a button so the user can request showing the folder size
		$xi['tpl']->set_var('get_size_link',$xi['get_size_link']);
		// BUTTON: for templates using a button for this
		$xi['tpl']->set_var('frm_get_size_name',$xi['frm_get_size_name']);
		$xi['tpl']->set_var('frm_get_size_action',$xi['frm_get_size_action']);
		$xi['tpl']->set_var('get_size_flag',$xi['force_showsize_flag']);
		$xi['tpl']->set_var('lang_get_size',$xi['lang_get_size']);
		// parse the appropriate block
		$xi['tpl']->parse('V_get_size','B_get_size');
		// the other block (the size info, "V_show_size") should be blank
		$xi['tpl']->set_var('V_show_size','');
	}
	/*
	// for those layouts that do not want these clickable headers, here are plain words
	$xi['tpl']->set_var('lang_subject',$xi['lang_subject']);
	$xi['tpl']->set_var('lang_from',$xi['lang_from']);
	$xi['tpl']->set_var('lang_date',$xi['lang_date']);
	$xi['tpl']->set_var('lang_size',$xi['lang_size']);
	$xi['tpl']->set_var('lang_lines',$xi['lang_size']);
	*/






// ----  Output the Template   -----
	$xi['tpl']->pparse('out','T_index_main');

	$GLOBALS['phpgw']->msg->end_request();

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
