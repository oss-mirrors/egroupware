<?php
	/**************************************************************************\
	* phpGroupWare - email BO Class	for Message Lists				*
	* http://www.phpgroupware.org							*
	* Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
	* xml-rpc and soap code template by Milosch and others				*
	* --------------------------------------------							*
	*  This program is free software; you can redistribute it and/or modify it		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your		*
	*  option) any later version.								*
	\**************************************************************************/
	
	/* $Id$ */
	
	class boindex
	{
		var $public_functions = array(
			'get_langed_labels'	=> True,
			'index_data'		=> True,
			'mlist_data'		=> True
		);
		var $nextmatchs;
		//var $debug_index_data = True;
		var $debug_index_data = False;
		var $xi;
		var $xml_functions = array();
		
		var $soap_functions = array(
			'get_langed_labels' => array(
				'in'  => array('int'),
				'out' => array('array')
			),
			'index_data' => array(
				'in'  => array('int'),
				'out' => array('array')
			),
			'mlist_data' => array(
				'in'  => array('struct'),
				'out' => array('struct')
			)
		);
		
		function boindex()
		{
			
		}
		
		// not used yet
		function add_vcard()
		{
			if($uploadedfile == 'none' || $uploadedfile == '')
			{
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uivcard.in&action=GetFile'));
			}
			else
			{
				$uploaddir = $GLOBALS['phpgw_info']['server']['temp_dir'] . SEP;
				
				srand((double)microtime()*1000000);
				$random_number = rand(100000000,999999999);
				$newfilename = md5("$uploadedfile, $uploadedfile_name, "
					. time() . getenv("REMOTE_ADDR") . $random_number );
				
				copy($uploadedfile, $uploaddir . $newfilename);
				$ftp = fopen($uploaddir . $newfilename . '.info','w');
				fputs($ftp,"$uploadedfile_type\n$uploadedfile_name\n");
				fclose($ftp);
				
				$filename = $uploaddir . $newfilename;
				
				$vcard = CreateObject('phpgwapi.vcard');
				$entry = $vcard->in_file($filename);
				/* _debug_array($entry);exit; */
				$entry['owner'] = $GLOBALS['phpgw_info']['user']['account_id'];
				$entry['access'] = 'private';
				$entry['tid'] = 'n';
				/* _debug_array($entry);exit; */
				$this->so->add_entry($entry);
				$ab_id = $this->get_lastid();
				
				/* Delete the temp file. */
				unlink($filename);
				unlink($filename . '.info');
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiaddressbook.view&ab_id=' . $ab_id));
			}
		}
		
		function get_langed_labels()
		{
			// ----  Langs  ----
			// lang var for checkbox javascript  -----
			/*
			$this->xi['select_msg'] = lang('Please select a message first');
			$this->xi['first_line_txt'] = lang('switch current folder to');
			$this->xi['compose_txt'] = lang('Compose New');
			$this->xi['folders_txt1'] = lang('Folders');
			$this->xi['folders_txt2'] = lang('Manage Folders');
			$this->xi['email_prefs_txt'] = lang('Email Preferences');
			$this->xi['filters_txt'] = lang('EMail Filters');
			$this->xi['accounts_txt'] = lang('Manage Accounts');
			// some langs for the sort by box
			$this->xi['lang_sort_by'] = lang('Sort By');
			$this->xi['lang_email_date'] = lang('Email Date');
			$this->xi['lang_arrival_date'] = lang('Arrival Date');
			$this->xi['lang_from'] = lang('From');
			$this->xi['lang_subject'] = lang('Subject');
			$this->xi['lang_size'] = lang('Size');
			// folder stats Information bar
			$this->xi['lang_new'] = lang('New');
			$this->xi['lang_new2'] = lang('New Messages');
			$this->xi['lang_total'] = lang('Total');
			$this->xi['lang_total2'] = lang('Total Messages');
			//$this->xi['lang_size'] = lang('Size');
			$this->xi['lang_size2'] = lang('Folder Size');
			$this->xi['stats_to_txt'] = lang('to');
			$this->xi['lang_get_size'] = lang('get size');
			// initialize the lang'd header names
			//$this->xi['lang_from'] = lang('from');
			$this->xi['lang_date'] = lang('date');
			$this->xi['lang_lines'] = lang('lines');
			$this->xi['lang_counld_not_open'] = lang('Could not open this mailbox');
			$this->xi['lang_empty_folder'] = lang('this folder is empty');
			$this->xi['lang_delete'] = lang('delete');
			$this->xi['mlist_attach_txt'] = lang('file');
			*/
			
			$lang_strings = array(
				'select_msg'		=> lang('Please select a message first'),
				'first_line_txt'	=> lang('switch current folder to'),
				'compose_txt'		=> lang('Compose New'),
				'folders_txt1'		=> lang('Folders'),
				'folders_txt2'		=> lang('Manage Folders'),
				'email_prefs_txt'	=> lang('Email Preferences'),
				'filters_txt'		=> lang('EMail Filters'),
				'accounts_txt'		=> lang('Manage Accounts'),
				// some langs for the sort by box
				'lang_sort_by'		=> lang('Sort By'),
				'lang_email_date'	=> lang('Email Date'),
				'lang_arrival_date'	=> lang('Arrival Date'),
				'lang_from'		=> lang('From'),
				'lang_subject'		=> lang('Subject'),
				'lang_size'		=> lang('Size'),
				// folder stats Information bar
				'lang_new'		=> lang('New'),
				'lang_new2'		=> lang('New Messages'),
				'lang_total'		=> lang('Total'),
				'lang_total2'		=> lang('Total Messages'),
				'lang_size2'		=> lang('Folder Size'),
				'stats_to_txt'		=> lang('to'),
				'lang_get_size'		=> lang('get size'),
				'lang_date'		=> lang('date'),
				'lang_lines'		=> lang('lines'),
				'lang_counld_not_open'	=> lang('Could not open this mailbox'),
				'lang_empty_folder'	=> lang('this folder is empty'),
				'lang_delete'		=> lang('delete'),
				'mlist_attach_txt'	=> lang('file')
			);
			// put these into $this->xi[] array
			while(list($key,$value) = each($lang_strings))
			{
				$this->xi[$key] = $lang_strings[$key];
			}
			// optional return value, primarily for external clients, we need only fill $this->xi[]
			return $lang_strings;
		}


		function index_data($reuse_feed_args=array())
		{
			// attempt (or not) to reuse an existing mail_msg object, i.e. if one ALREADY exists before entering
			// this function. As of Dec 14, 2001 only class.boaction can pass a useful, existing object for us to use here
			$attempt_reuse = True;
			//$attempt_reuse = False;
			
			if ($this->debug_index_data == True) { echo 'ENTERING: email.boindex: index_data'.'<br>'; }
			if ($this->debug_index_data == True) { echo 'email.boindex.index_data: local var attempt_reuse=['.serialize($attempt_reuse).'] ; reuse_feed_args[] dump<pre>'; print_r($reuse_feed_args); echo '</pre>'; }
			// create class objects
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug_index_data) { echo 'email.boindex.index_data: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug_index_data) { echo 'email.boindex.index_data: is_object: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			// do we attempt to reuse the existing msg object?
			if ((is_object($GLOBALS['phpgw']->msg))
			&& ($attempt_reuse == True))
			{
				// no not create, we will reuse existing
				if ($this->debug_index_data == True) { echo 'email.boindex.index_data: reusing existing mail_msg object'.'<br>'; }
				// we need to feed the existing object some params begin_request uses to re-fill the msg->args[] data
				$args_array = Array();
				// any args passed in $args_array will override or replace any pre-existing arg value
				$args_array = $reuse_feed_args;
				// add this to keep the error checking code (below) happy
				$args_array['do_login'] = True;
			}
			else
			{
				if ($this->debug_index_data == True) { echo 'email.boindex.index_data: cannot or not trying to reusing existing'.'<br>'; }
				$args_array = Array();
				// should we log in or not
				$args_array['do_login'] = True;
			}
			
			// "start your engines"
			if ($this->debug_index_data == True) { echo 'email.boindex.index_data: call msg->begin_request with args array:<pre>'; print_r($args_array); echo '</pre>'; }
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			// error if login failed
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', index_data()');
			}
			
			// if auto-refresh is in use, it will need to know what to refresh, use with ->link to get full http string
			$GLOBALS['phpgw_info']['flags']['email_refresh_uri'] = $GLOBALS['phpgw']->msg->get_arg_value('index_refresh_uri');
			// any app may use these lang'd labels in their email UI
			$this->get_langed_labels();
			// ---  this->xi ("eXternal Interface") is an array that will hold ALL data necessary for an index page
			// if we just moved or deleted messages, make a report string
			$this->xi['report_this'] = $GLOBALS['phpgw']->msg->report_moved_or_deleted();
			// font size options
			$font_size = Array (
				0 => '-5',
				1 => '-4',
				2 => '-3',
				3 => '-2',
				4 => '-1',
				5 => '0',
				6 => '1',
				7 => '2',
				8 => '3',
				9 => '4',
				10 => '5'
			);
			// some fonts and font sizes
			$this->xi['ctrl_bar_font'] = $GLOBALS['phpgw_info']['theme']['font'];
			$this->xi['ctrl_bar_font_size'] = $font_size[4];
			$this->xi['stats_font'] = $GLOBALS['phpgw_info']['theme']['font'];
			$this->xi['stats_font_size'] = $font_size[7];
			$this->xi['mlist_font'] = $GLOBALS['phpgw_info']['theme']['font'];
			$this->xi['mlist_font_size'] = $font_size[7];
			$this->xi['mlist_font_size_sm'] = $font_size[6];
			$this->xi['hdr_font'] = $GLOBALS['phpgw_info']['theme']['font'];
			$this->xi['hdr_font_size'] = $font_size[7];
			$this->xi['hdr_font_size_sm'] = $font_size[6];
			$this->xi['ftr_font']	= $GLOBALS['phpgw_info']['theme']['font'];
			
			
			// establish all manner of important data
			// can not put acctnum=X here because any single peice of data may apply to a different account
			$this->xi['svr_image_dir'] = PHPGW_IMAGES_DIR;
			$this->xi['image_dir'] = PHPGW_IMAGES;
			$this->xi['current_sort'] = $GLOBALS['phpgw']->msg->get_arg_value('sort');
			$this->xi['current_order'] = $GLOBALS['phpgw']->msg->get_arg_value('order');
			$this->xi['current_start'] = $GLOBALS['phpgw']->msg->get_arg_value('start');
			$this->xi['current_fldball_fake_uri'] =	 '&folder='.$GLOBALS['phpgw']->msg->prep_folder_out()
								.'&acctnum='.$GLOBALS['phpgw']->msg->get_acctnum();
			$this->xi['show_num_new'] = False;
			
			$this->xi['mailsvr_supports_folders'] = $GLOBALS['phpgw']->msg->get_mailsvr_supports_folders();
			// if using folders, make a HTML comobox used to switch folders
			if ($this->xi['mailsvr_supports_folders'])
			{
				$feed_args = Array(
					'mailsvr_stream'	=> '',
					'pre_select_folder'	=> '',
					'skip_folder'		=> '',
					'show_num_new'		=> $this->xi['show_num_new'],
					'widget_name'		=> 'fldball_fake_uri',
					'folder_key_name'	=> 'folder',
					'acctnum_key_name'	=> 'acctnum',
					'on_change'		=> 'document.switchbox.submit()',
					'first_line_txt'	=> $this->xi['first_line_txt']
				);
				$this->xi['switchbox_listbox'] = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);
			}
			else
			{
				$this->xi['switchbox_listbox'] = '&nbsp';
			}
			$this->xi['switchbox_frm_name'] = 'switchbox';
			
			// switchbox will itself contain "fake_uri" embedded data which includes the applicable account number for the folder
			$this->xi['switchbox_action'] = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uiindex.index');
			// get statistics an the current folder
			$this->xi['folder_info'] = array();
			$this->xi['folder_info'] = $GLOBALS['phpgw']->msg->get_folder_status_info();
			$this->xi['arrows_form_action'] = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uiindex.index');
			$this->xi['arrows_form_name'] = 'arrownav';
			$this->xi['arrows_backcolor'] = $GLOBALS['phpgw_info']['theme']['row_off'];
			//$this->xi['arrows_td_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
			$this->xi['arrows_td_backcolor'] = '';
			$nav_args = Array (
				'start'		=> $GLOBALS['phpgw']->msg->get_arg_value('start'),
				'total'		=> $this->xi['folder_info']['number_all'],
				'cmd_prefix'	=> 'do_navigate(\'',
				'cmd_suffix'	=> '\')'
			);
			$arrows_links = array();
			$arrows_links = $this->nextmatchs->nav_left_right_imap($nav_args);
			$this->xi['first_page'] = $arrows_links['first_page'];
			$this->xi['prev_page'] = $arrows_links['prev_page'];
			$this->xi['next_page'] = $arrows_links['next_page'];
			$this->xi['last_page'] = $arrows_links['last_page'];
			
			$this->xi['td_prev_arrows'] = $this->nextmatchs->left(
								'/index.php',
								$GLOBALS['phpgw']->msg->get_arg_value('start'),
								$this->xi['folder_info']['number_all'],
								 '&menuaction=email.uiindex.index'
								.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
								.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum()
								.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
								.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order'));
			
			$this->xi['td_next_arrows'] = $this->nextmatchs->right(
								'/index.php',
								$GLOBALS['phpgw']->msg->get_arg_value('start'),
								$this->xi['folder_info']['number_all'],
								 '&menuaction=email.uiindex.index'
								.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
								.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum()
								.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
								.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order'));
			
			$this->xi['ctrl_bar_back2'] = $GLOBALS['phpgw_info']['theme']['row_off'];
			$this->xi['compose_link'] = $GLOBALS['phpgw']->link(
								'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/compose.php',
								// this data tells us where to return to after sending a message
								'fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
								.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum()
								.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
								.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
								.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start'));
			
			if ($this->xi['mailsvr_supports_folders'])
			{
				$this->xi['folders_link'] = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uifolder.folder'
								// going to the folder list page, we only need log into the INBOX folder
								.'&fldball[folder]='.'INBOX'
								.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum());
				
				$this->xi['folders_href'] = '<a href="'.$this->xi['folders_link'].'">'.$this->xi['folders_txt2'].'</a>';
				
				$folders_btn_js = 'window.location=\''.$this->xi['folders_link'].'\'';
				$this->xi['folders_btn'] = '<input type="button" name="folder_link_btn" value="'.$this->xi['folders_txt1'].'" onClick="'.$folders_btn_js.'">';
			}
			else
			{
				$this->xi['folders_href'] = '&nbsp;';
				$this->xi['folders_btn'] = '&nbsp;';
			}
			// FIXME
			$this->xi['email_prefs_link'] = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uipreferences.preferences'
								.'&acctnum='.$GLOBALS['phpgw']->msg->get_acctnum());
			
			$this->xi['filters_link'] = $GLOBALS['phpgw']->link(
								'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/filters.php'
								);
			
			$this->xi['filters_href'] = '<a href="'.$this->xi['filters_link'].'">'.$this->xi['filters_txt'].'</a>';
			// FIXME
			// multiple account maintenance - not yet implemented
			$this->xi['accounts_link'] = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uiindex.index');
			
			$this->xi['ctrl_bar_back1'] = $GLOBALS['phpgw_info']['theme']['row_on'];
			
			$sort_selected = Array(
				0 => '',
				1 => '',
				2 => '',
				3 => '',
				6 => ''
			);
			$sort_selected[$GLOBALS['phpgw']->msg->get_arg_value('sort')] = " selected";
			$this->xi['sortbox_select_options'] =
				 '<option value="0"' .$sort_selected[0] .'>'.$this->xi['lang_email_date'].'</option>' ."\r\n"
				.'<option value="1"' .$sort_selected[1] .'>'.$this->xi['lang_arrival_date'].'</option>' ."\r\n"
				.'<option value="2"' .$sort_selected[2] .'>'.$this->xi['lang_from'].'</option>' ."\r\n"
				.'<option value="3"' .$sort_selected[3] .'>'.$this->xi['lang_subject'].'</option>' ."\r\n"
				.'<option value="6"' .$sort_selected[6] .'>'.$this->xi['lang_size'].'</option>' ."\r\n";
			
			$this->xi['sortbox_action'] = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uiindex.index');
			$this->xi['sortbox_on_change'] = 'document.sortbox.submit()';
			$this->xi['sortbox_select_name'] = 'sort';
			
			if ($this->xi['folder_info']['number_all'] == 0)
			{
				$this->xi['stats_saved'] = '-';
				$this->xi['stats_new'] = '-';
				$this->xi['stats_size'] = '-';
			}
			else
			{
				$this->xi['stats_saved'] = number_format($this->xi['folder_info']['number_all']);
				$this->xi['stats_new'] = $this->xi['folder_info']['number_new'];
				if ($this->xi['stats_new'] == 0)
				{
					$this->xi['stats_new'] = '0';
				}
				else
				{
					$this->xi['stats_new'] = number_format($this->xi['stats_new']);
				}
				$size_report_args['allow_stats_size_speed_skip'] = True;
				$size_report_args['stats_size_threshold'] = 100;
				$size_report_args['number_all'] = $this->xi['folder_info']['number_all'];
				$this->xi['stats_size'] = $GLOBALS['phpgw']->msg->report_total_foldersize($size_report_args);		
			}
			
			$this->xi['stats_backcolor'] = $GLOBALS['phpgw_info']['theme']['em_folder'];
			$this->xi['stats_color'] = $GLOBALS['phpgw_info']['theme']['em_folder_text'];
			$this->xi['stats_folder'] = $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->get_arg_value('folder'));
			$this->xi['stats_first'] = $GLOBALS['phpgw']->msg->get_arg_value('start') + 1;
			// toggle the "get folder size" button or link, if getting that size was skipped as a time-saving measure
			if ($this->xi['stats_size'] == '')
			{
				$this->xi['force_showsize_flag'] = 'force_showsize';
				$this->xi['get_size_link'] = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uiindex.index'
								.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
								.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum()
								.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
								.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
								.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start')
								.'&'.$this->xi['force_showsize_flag'].'=1');
				$this->xi['frm_get_size_name'] = 'form_get_size';
				$this->xi['frm_get_size_action'] = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uiindex.index');
			}
			// column labels for the message list
			$flag_sort_pre = '* ';
			$flag_sort_post = ' *';
			switch ((int)$GLOBALS['phpgw']->msg->get_arg_value('sort'))
			{
				case 1 : $this->xi['lang_date'] = $flag_sort_pre .$this->xi['lang_date'] .$flag_sort_post; break;
				case 2 : $this->xi['lang_from'] = $flag_sort_pre .$this->xi['lang_from'] .$flag_sort_post; break;
				case 3 : $this->xi['lang_subject'] = $flag_sort_pre .$this->xi['lang_subject'] .$flag_sort_post; break;
				case 6 : $this->xi['lang_size'] = '*'.$this->xi['lang_size'].'*';
					 $this->xi['lang_lines'] = $this->xi['lang_lines'] .$flag_sort_post; break;
			}
			// default order is needed for the "nextmatchs" args, to know when to toggle this between normal and reverse
			if (($GLOBALS['phpgw']->msg->get_isset_pref('default_sorting'))
			  && ($GLOBALS['phpgw']->msg->get_pref_value('default_sorting') == 'new_old'))
			{
				$this->xi['default_order'] = 1;
			}
			else
			{
				$this->xi['default_order'] = 0;
			}
			// make these column labels into clickable HREF's for their 
			if (($GLOBALS['phpgw']->msg->get_isset_arg('newsmode') == True)
			&& ($GLOBALS['phpgw']->msg->get_arg_value('newsmode') == True))
			{
				$this->xi['hdr_subject'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->get_arg_value('sort'),'3',$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.'menuaction=email.uiindex.index',$this->xi['lang_subject'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_from'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->get_arg_value('sort'),'2',$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.'menuaction=email.uiindex.index',$this->xi['lang_from'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_date'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->get_arg_value('sort'),'1',$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.'menuaction=email.uiindex.index',$this->xi['lang_date'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_size'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->get_arg_value('sort'),'6',$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.'menuaction=email.uiindex.index',$this->xi['lang_lines'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
			}
			else
			{
				$this->xi['hdr_subject'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->get_arg_value('sort'),'3',$this->xi['default_order'],$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.'menuaction=email.uiindex.index',$this->xi['lang_subject'],
						'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
						.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum());
				$this->xi['hdr_from'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->get_arg_value('sort'),'2',$this->xi['default_order'],$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.'menuaction=email.uiindex.index',$this->xi['lang_from'],
						'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
						.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum());
				$this->xi['hdr_date'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->get_arg_value('sort'),'1',$this->xi['default_order'],$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.'menuaction=email.uiindex.index',$this->xi['lang_date'],
						'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
						.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum());
				$this->xi['hdr_size'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->get_arg_value('sort'),'6',$this->xi['default_order'],$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.'menuaction=email.uiindex.index',$this->xi['lang_size'],
						'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
						.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum());
			}
			$this->xi['hdr_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
			$this->xi['mlist_newmsg_char'] = '<strong>*</strong>';
			$this->xi['mlist_newmsg_color'] = '#ff0000';
			$this->xi['mlist_new_msg'] = '<font color="'.$this->xi['mlist_newmsg_color'].'">'.$this->xi['mlist_newmsg_char'].'</font>';
			$this->xi['mlist_attach'] =
				'<div align="right">'
					.'<img src="'.$this->xi['svr_image_dir'].'/attach.gif" alt="'.$this->xi['mlist_attach_txt'].'">'
				.'</div>';
			//$this->xi['mlist_checkbox_name'] = 'delmov_list_fake_uri[]';
			$this->xi['mlist_checkbox_name'] = 'delmov_list[]';
			
			// loop thru the messages and get the data that the UI will display
			if ($this->xi['folder_info']['number_all'] == 0)
			{
				$this->xi['msg_list_dsp'] = Array();
				$this->xi['stats_last'] = 0;
				$some_stream = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream');
				if ((!isset($some_stream))
				|| ($some_stream == ''))
				{
					$this->xi['report_no_msgs'] = $this->xi['lang_counld_not_open'];
				}
				else
				{
					$this->xi['report_no_msgs'] = $this->xi['lang_empty_folder'];
				}
			}
			else
			{
					$this->xi['msg_list_dsp'] = Array();
					// hi-level function assembles all data for each message we will display
					$this->xi['msg_list_dsp'] = $GLOBALS['phpgw']->msg->get_msg_list_display($this->xi['folder_info']);
					// after we know how many messages we will display, we make the "showing from X to X" string
					$this->xi['totaltodisplay'] = $GLOBALS['phpgw']->msg->get_arg_value('start') + count($this->xi['msg_list_dsp']);
					$this->xi['stats_last'] = $this->xi['totaltodisplay'];
			}
			// user may select individual messages to move, make combobox to select destination folder
			$this->xi['frm_delmov_action'] = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.boaction.delmov');
			$this->xi['frm_delmov_name'] = 'delmov';
			if ($this->xi['mailsvr_supports_folders'])
			{
				$feed_args = Array();
				$feed_args = Array(
					'mailsvr_stream'	=> '',
					'pre_select_folder'	=> '',
					'skip_folder'		=> $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->get_arg_value('folder')),
					'show_num_new'		=> $this->xi['show_num_new'],
					'widget_name'		=> 'to_fldball_fake_uri',
					'folder_key_name'	=> 'folder',
					'acctnum_key_name'	=> 'acctnum',
					'on_change'		=> 'do_action(\'move\')',
					'first_line_txt'	=> lang('move selected messages into')
				);
				$this->xi['delmov_listbox'] = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);
			}
			else
			{
				$this->xi['delmov_listbox'] = '&nbsp;';
			}
			
			$this->xi['ftr_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
		}
		
		
		
		
		
		
		
		
		
		function mlist_data()
		{
			// DISPLAY SEARCH RESULTS
			
			if ($this->debug_index_data == True) { echo 'ENTERING: email.boindex: mlist_data'.'<br>'; }
			if ($this->debug_index_data == True) { echo 'email.boindex.mlist_data: local var attempt_reuse=['.serialize($attempt_reuse).'] ; reuse_feed_args[] dump<pre>'; print_r($reuse_feed_args); echo '</pre>'; }
			// create class objects
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug_index_data) { echo 'email.boindex.mlist_data: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug_index_data) { echo 'email.boindex.mlist_data: is_object: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			$args_array = Array();
			// FUTURE: "folder" does not really matter, this may be a multi-folder mlist result set
			// should we log in
			$args_array['do_login'] = True;
			// "start your engines"
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			// error if login failed
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', mlist_data()');
			}
			// base http URI on which we will add other stuff down below
			$this->index_base_link = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'));
			// any app may use these lang'd labels in their email UI
			$this->get_langed_labels();
			// ---  this->xi ("eXternal Interface") is an array that will hold ALL data necessary for an index page
			// if we just moved or deleted messages, make a report string
			// NOTE: not yet implemented for mlists
			$this->xi['report_this'] = $GLOBALS['phpgw']->msg->report_moved_or_deleted();
			
			// some of the following may not be necessary
			
			// establish all manner of important data
			$this->xi['svr_image_dir'] = PHPGW_IMAGES_DIR;
			$this->xi['image_dir'] = PHPGW_IMAGES;
			$this->xi['current_sort'] = $GLOBALS['phpgw']->msg->get_arg_value('sort');
			$this->xi['current_order'] = $GLOBALS['phpgw']->msg->get_arg_value('order');
			$this->xi['current_start'] = $GLOBALS['phpgw']->msg->get_arg_value('start');
			$this->xi['current_folder'] = $GLOBALS['phpgw']->msg->prep_folder_out('');
			$this->xi['show_num_new'] = False;
			
			$this->xi['mailsvr_supports_folders'] = $GLOBALS['phpgw']->msg->get_mailsvr_supports_folders();
			// if using folders, make a HTML comobox used to switch folders
			// EXCEPT for mlists
			if (($this->xi['mailsvr_supports_folders'])
			&& ($GLOBALS['phpgw']->msg->get_isset_arg('mlist_set') == False))
			{
				$feed_args = Array(
					'mailsvr_stream'	=> '',
					'pre_select_folder'	=> '',
					'skip_folder'		=> '',
					'show_num_new'		=> $this->xi['show_num_new'],
					'widget_name'		=> 'folder',
					'on_change'		=> 'document.switchbox.submit()',
					'first_line_txt'	=> $this->xi['first_line_txt']
				);
				$this->xi['switchbox_listbox'] = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);
			}
			else
			{
				$this->xi['switchbox_listbox'] = '&nbsp';
			}
			$this->xi['switchbox_frm_name'] = 'switchbox';
			$this->xi['switchbox_action'] = $this->index_base_link;
			
			// get statistics an the current folder
			$this->xi['folder_info'] = array();
			//$this->xi['folder_info'] = $GLOBALS['phpgw']->msg->get_folder_status_info();
			// make a  FAKE  folder_info array to make things simple for get_msg_list_display
			$this->xi['folder_info']['is_imap'] = True;
			$this->xi['folder_info']['folder_checked'] = $GLOBALS['phpgw']->msg->get_arg_value('folder');
			$this->xi['folder_info']['alert_string'] = 'you have search results';
			$this->xi['folder_info']['number_new'] = count($GLOBALS['phpgw']->msg->get_arg_value('mlist_set'));
			$this->xi['folder_info']['number_all'] = count($GLOBALS['phpgw']->msg->get_arg_value('mlist_set'));
			// first, previous, next, last  page navagation arrows
			$this->xi['arrows_form_action'] = $this->index_base_link;
			$this->xi['arrows_form_name'] = 'arrownav';
			$this->xi['arrows_backcolor'] = $GLOBALS['phpgw_info']['theme']['row_off'];
			//$this->xi['arrows_td_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
			$this->xi['arrows_td_backcolor'] = '';
			$nav_args = Array (
				'start'		=> $GLOBALS['phpgw']->msg->get_arg_value('start'),
				// remember we made that "fake" folder_info data above
				'total'		=> $this->xi['folder_info']['number_all'],
				'cmd_prefix'	=> 'do_navigate(\'',
				'cmd_suffix'	=> '\')'
			);
			$arrows_links = array();
			$arrows_links = $this->nextmatchs->nav_left_right_imap($nav_args);
			$this->xi['first_page'] = $arrows_links['first_page'];
			$this->xi['prev_page'] = $arrows_links['prev_page'];
			$this->xi['next_page'] = $arrows_links['next_page'];
			$this->xi['last_page'] = $arrows_links['last_page'];
			
			// this OLD arows way will not work with mlist sets
			/*
			$this->xi['td_prev_arrows'] = $this->nextmatchs->left('/index.php',
							$GLOBALS['phpgw']->msg->get_arg_value('start'),
							$this->xi['folder_info']['number_all'],
							'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
							.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
							.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
							.'&'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction)');
			
			$this->xi['td_next_arrows'] = $this->nextmatchs->right('/index.php',
							$GLOBALS['phpgw']->msg->get_arg_value('start'),
							$this->xi['folder_info']['number_all'],
							'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
							.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
							.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
							.'&'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction)');
			*/
			$this->xi['td_prev_arrows'] = '';
			$this->xi['td_prev_arrows'] = '';
			
			$this->xi['ctrl_bar_back2'] = $GLOBALS['phpgw_info']['theme']['row_off'];
			$this->xi['compose_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/compose.php','folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
			if ($this->xi['mailsvr_supports_folders'])
			{
				//$this->xi['folders_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/folder.php');
				$this->xi['folders_link'] = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->get_arg_value('folder_menuaction'));
				$this->xi['folders_href'] = '<a href="'.$this->xi['folders_link'].'">'.$this->xi['folders_txt2'].'</a>';
		
				$folders_btn_js = 'window.location=\''.$this->xi['folders_link'].'\'';
				$this->xi['folders_btn'] = '<input type="button" name="folder_link_btn" value="'.$this->xi['folders_txt1'].'" onClick="'.$folders_btn_js.'">';
			}
			else
			{
				$this->xi['folders_href'] = '&nbsp;';
				$this->xi['folders_btn'] = '&nbsp;';
			}
			$this->xi['email_prefs_link'] = $GLOBALS['phpgw']->link('/index.php','menuaction=email.uipreferences.preferences');
			$this->xi['filters_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/filters.php');
			$this->xi['filters_href'] = '<a href="'.$this->xi['filters_link'].'">'.$this->xi['filters_txt'].'</a>';
			// multiple account maintenance - not yet implemented
			$this->xi['accounts_link'] = $this->index_base_link;
			
			$this->xi['ctrl_bar_back1'] = $GLOBALS['phpgw_info']['theme']['row_on'];
			
			// SORTBOX not yet supported in mlists
			$sort_selected = Array(
				0 => '',
				1 => '',
				2 => '',
				3 => '',
				6 => ''
			);
			$sort_selected[$GLOBALS['phpgw']->msg->get_arg_value('sort')] = " selected";
			$this->xi['sortbox_select_options'] =
				 '<option value="0"' .$sort_selected[0] .'>'.$this->xi['lang_email_date'].'</option>' ."\r\n"
				.'<option value="1"' .$sort_selected[1] .'>'.$this->xi['lang_arrival_date'].'</option>' ."\r\n"
				.'<option value="2"' .$sort_selected[2] .'>'.$this->xi['lang_from'].'</option>' ."\r\n"
				.'<option value="3"' .$sort_selected[3] .'>'.$this->xi['lang_subject'].'</option>' ."\r\n"
				.'<option value="6"' .$sort_selected[6] .'>'.$this->xi['lang_size'].'</option>' ."\r\n";
			
			$this->xi['sortbox_action'] = $this->index_base_link.'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('');
			$this->xi['sortbox_on_change'] = 'document.sortbox.submit()';
			$this->xi['sortbox_select_name'] = 'sort';
			
			// MLIST we wuld need to loop thru the mlist set to see what's new - Maybe Future
			$this->xi['stats_new'] = '==SEARCH RESULTS==';
			$this->xi['stats_saved'] = number_format($this->xi['folder_info']['number_all']);				
			//$this->xi['stats_size'] = 'SEARCH RESULT';
			/*
			if ($this->xi['folder_info']['number_all'] == 0)
			{
				$this->xi['stats_saved'] = '-';
				$this->xi['stats_new'] = '-';
				$this->xi['stats_size'] = '-';
			}
			else
			{
				$this->xi['stats_saved'] = number_format($this->xi['folder_info']['number_all']);				
				$this->xi['stats_new'] = $this->xi['folder_info']['number_new'];
				if ($this->xi['stats_new'] == 0)
				{
					$this->xi['stats_new'] = '0';
				}
				else
				{
					$this->xi['stats_new'] = number_format($this->xi['stats_new']);
				}
				$size_report_args['allow_stats_size_speed_skip'] = True;
				$size_report_args['stats_size_threshold'] = 100;
				$size_report_args['number_all'] = $this->xi['folder_info']['number_all'];
				$this->xi['stats_size'] = $GLOBALS['phpgw']->msg->report_total_foldersize($size_report_args);		
			}
			*/
			$this->xi['stats_backcolor'] = $GLOBALS['phpgw_info']['theme']['em_folder'];
			$this->xi['stats_color'] = $GLOBALS['phpgw_info']['theme']['em_folder_text'];
			$this->xi['stats_folder'] = $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->get_arg_value('folder'));
			$this->xi['stats_first'] = $GLOBALS['phpgw']->msg->get_arg_value('start') + 1;
			
			// NA for mlist
			$this->xi['get_size_link'] = '';
			/*
			// toggle the "get folder size" button or link, if getting that size was skipped as a time-saving measure
			if ($this->xi['stats_size'] == '')
			{
				$this->xi['force_showsize_flag'] = 'force_showsize';
				$this->xi['get_size_link'] =
					$this->index_base_link
					.'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
					.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
					.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
					.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start')
					.'&'.$this->xi['force_showsize_flag'].'=1';
				$this->xi['frm_get_size_name'] = 'form_get_size';
				$this->xi['frm_get_size_action'] = $this->index_base_link;
			}
			*/
			
			// NOT YET IMPLEMENTED for mlists
			// just fill in blank values
			$this->xi['default_order'] = 1;
			$this->xi['hdr_subject'] = $this->xi['lang_subject'];
			$this->xi['hdr_from'] = $this->xi['lang_from'];
			$this->xi['hdr_date'] = $this->xi['lang_date'];
			$this->xi['hdr_size'] = $this->xi['lang_size'];
			/*
			// column labels for the message list
			$flag_sort_pre = '* ';
			$flag_sort_post = ' *';
			switch ((int)$GLOBALS['phpgw']->msg->get_arg_value('sort'))
			{
				case 1 : $this->xi['lang_date'] = $flag_sort_pre .$this->xi['lang_date'] .$flag_sort_post; break;
				case 2 : $this->xi['lang_from'] = $flag_sort_pre .$this->xi['lang_from'] .$flag_sort_post; break;
				case 3 : $this->xi['lang_subject'] = $flag_sort_pre .$this->xi['lang_subject'] .$flag_sort_post; break;
				case 6 : $this->xi['lang_size'] = '*'.$this->xi['lang_size'].'*';
					 $this->xi['lang_lines'] = $this->xi['lang_lines'] .$flag_sort_post; break;
			}
			// default order is needed for the "nextmatchs" args, to know when to toggle this between normal and reverse
			if (($GLOBALS['phpgw']->msg->get_isset_pref('default_sorting']))
			  && ($GLOBALS['phpgw']->msg->get_pref_value('default_sorting') == 'new_old'))
			{
				$this->xi['default_order'] = 1;
			}
			else
			{
				$this->xi['default_order'] = 0;
			}
			// make these column labels into clickable HREF's for their 
			if (($GLOBALS['phpgw']->msg->get_isset_arg('newsmode'))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('newsmode') == True))
			{
				$this->xi['hdr_subject'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->get_arg_value('sort'),'3',$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'),$this->xi['lang_subject'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_from'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->get_arg_value('sort'),'2',$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'),$this->xi['lang_from'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_date'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->get_arg_value('sort'),'1',$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'),$this->xi['lang_date'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_size'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->get_arg_value('sort'),'6',$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'),$this->xi['lang_lines'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
			}
			else
			{
				$this->xi['hdr_subject'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->get_arg_value('sort'),'3',$this->xi['default_order'],$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'),$this->xi['lang_subject'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_from'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->get_arg_value('sort'),'2',$this->xi['default_order'],$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'),$this->xi['lang_from'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_date'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->get_arg_value('sort'),'1',$this->xi['default_order'],$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'),$this->xi['lang_date'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_size'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->get_arg_value('sort'),'6',$this->xi['default_order'],$GLOBALS['phpgw']->msg->get_arg_value('order'),'/index.php'.$GLOBALS['phpgw']->msg->get_arg_value('mlist_menuaction'),$this->xi['lang_size'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
			}
			*/
			
			$this->xi['hdr_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
			$this->xi['mlist_newmsg_char'] = '<strong>*</strong>';
			$this->xi['mlist_newmsg_color'] = '#ff0000';
			$this->xi['mlist_new_msg'] = '<font color="'.$this->xi['mlist_newmsg_color'].'">'.$this->xi['mlist_newmsg_char'].'</font>';
			$this->xi['mlist_attach'] =
				'<div align="right">'
					.'<img src="'.$this->xi['svr_image_dir'].'/attach.gif" alt="'.$this->xi['mlist_attach_txt'].'">'
				.'</div>';
			// loop thru the messages and get the data that the UI will display
			if ($this->xi['folder_info']['number_all'] == 0)
			{
				$this->xi['msg_list_dsp'] = Array();
				$this->xi['stats_last'] = 0;
				
				$some_stream = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream');
				if ((!isset($some_stream))
				|| ($some_stream == ''))
				{
					$this->xi['report_no_msgs'] = $this->xi['lang_counld_not_open'];
				}
				else
				{
					$this->xi['report_no_msgs'] = $this->xi['lang_empty_folder'];
				}
			}
			else
			{
					$this->xi['msg_list_dsp'] = Array();
					// hi-level function assembles all data for each message we will display
					// FEED IT THE MLIST SET TO DISPLAY
					$this->xi['msg_list_dsp'] = $GLOBALS['phpgw']->msg->get_msg_list_display(
												$this->xi['folder_info'],
												$GLOBALS['phpgw']->msg->get_arg_value('mlist_set')
					);
					// after we know how many messages we will display, we make the "showing from X to X" string
					$this->xi['totaltodisplay'] = $GLOBALS['phpgw']->msg->get_arg_value('start') + count($this->xi['msg_list_dsp']);
					$this->xi['stats_last'] = $this->xi['totaltodisplay'];
			}
			
			// NOT YET IMPLEMENTED IN MLIST
			// user may select individual messages to move, make combobox to select destination folder
			$this->xi['frm_delmov_action'] = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uiindex.index');
			$this->xi['frm_delmov_name'] = 'delmov';
			if ($this->xi['mailsvr_supports_folders'])
			{
				$feed_args = Array();
				$feed_args = Array(
					'mailsvr_stream'	=> '',
					'pre_select_folder'	=> '',
					'skip_folder'		=> $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->get_arg_value('folder')),
					'show_num_new'		=> $this->xi['show_num_new'],
					'widget_name'		=> 'tofolder',
					'on_change'		=> 'do_action(\'move\')',
					'first_line_txt'	=> lang('move selected messages into')
				);
				$this->xi['delmov_listbox'] = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);
			}
			else
			{
				$this->xi['delmov_listbox'] = '&nbsp;';
			}
			
			$this->xi['ftr_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
			if ($this->debug_index_data == True) { echo 'LEAVING: email.boindex: mlist_data'.'<br>'; }
		}
	
	
	}
?>
