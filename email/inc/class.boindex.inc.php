<?php
  /**************************************************************************\
  * phpGroupWare - email BO Class	for Message Lists				*
  * http://www.phpgroupware.org							*
  * Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
  * xml-rpc and soap code template by Milosch
  * --------------------------------------------						*
  *  This program is free software; you can redistribute it and/or modify it	*
  *  under the terms of the GNU General Public License as published by the	*
  *  Free Software Foundation; either version 2 of the License, or (at your		*
  *  option) any later version.								*
  \**************************************************************************/

  /* $Id$ */

	class boindex
	{
		var $public_functions = array(
			'report_moved_or_deleted'	=> True,
			'get_mailsvr_supports_folders'	=> True,
			'folder_status_info'		=> True,
			'report_total_foldersize'	=> True,
			'get_msg_list_display'		=> True,
			'get_langed_labels'		=> True,
			'prep_folder_out'		=> True
		);
		var $base_link='';
		var $debug = False;
		var $xi;
		var $xml_functions = array();
		
		var $soap_functions = array(
			'report_moved_or_deleted' => array(
				'in'  => array('int'),
				'out' => array('array')
			),
			'get_mailsvr_supports_folders' => array(
				'in'  => array('int'),
				'out' => array('int')
			),
			'folder_status_info' => array(
				'in'  => array('string'),
				'out' => array('array')
			),
			'report_total_foldersize' => array(
				'in'  => array('struct'),
				'out' => array('int')
			),
			'get_msg_list_display' => array(
				'in'  => array('struct','struct'),
				'out' => array('array')
			),
			'get_langed_labels' => array(
				'in'  => array('int'),
				'out' => array('array')
			),
			'prep_folder_out' => array(
				'in'  => array('string'),
				'out' => array('string')
			)
		);
		
		function boindex()
		{
			
		}
		
		function list_methods($_type='xmlrpc')
		{
			/*
			  This handles introspection or discovery by the logged in client,
			  in which case the input might be an array.  The server always calls
			  this function to fill the server dispatch map using a string.
			*/
			if (is_array($_type))
			{
				$_type = $_type['type'] ? $_type['type'] : $_type[0];
			}
			switch($_type)
			{
				case 'xmlrpc':
					$xml_functions = array(
						'report_moved_or_deleted' => array(
							'function'  => 'report_moved_or_deleted',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Report on moved or deleted messages.')
						),
						'get_mailsvr_supports_folders' => array(
							'function'  => 'get_mailsvr_supports_folders',
							'signature' =>  array(array(xmlrpcInt,xmlrpcBoolean)),
							'docstring' => lang('Are folders supported, IMAP, on the users email server.')
						),
						'folder_status_info' => array(
							'function'  => 'folder_status_info',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Simple new message check, also reports other data.')
						),
						'report_total_foldersize' => array(
							'function'  => 'report_total_foldersize',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Total of all message sizes in a folder, may be time consuming.')
						),
						'get_msg_list_display' => array(
							'function'  => 'get_msg_list_display',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Get a processed list of messages with diaplayable data.')
						),
						'get_msg_list_display' => array(
							'function'  => 'get_msg_list_display',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Get a processed list of messages with diaplayable data.')
						),
						'get_langed_labels' => array(
							'function'  => 'get_langed_labels',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Client can use these language processed labels in a remote UI.')
						),
						'list_methods' => array(
							'function'  => 'list_methods',
							'signature' => array(array(xmlrpcStruct,xmlrpcString)),
							'docstring' => lang('Read this list of methods.')
						)
					);
					return $xml_functions;
					break;
				case 'soap':
					return $this->soap_functions;
					break;
				default:
					return array();
					break;
			}
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
		
		function login_error()
		{
			$imap_err = imap_last_error();
			if ($imap_err == '')
			{
				$error_report = lang('No Error Returned From Server');
			}
			else
			{
				$error_report = $imap_err;
			}
			// this should be templated
			echo "<p><center><b>"
			  . lang("There was an error trying to connect to your mail server.<br>Please, check your username and password, or contact your admin.")
			  ."<br>source: email class.boindes.inc.php"
			  ."<br>imap_last_error: ".$error_report
			  . "</b></center></p>";
			$GLOBALS['phpgw']->common->phpgw_exit(True);
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


		function index_data()
		{
			// create class objects
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			$GLOBALS['phpgw']->msg->grab_class_args_gpc();
			// 2 args needed for "begin_request()"  (another 1 arg not yet used)
			$args_array = Array();
			// (1) folder (if specified) - can be left empty or unset, mail_msg will then assume INBOX
			if (isset($GLOBALS['HTTP_POST_VARS']['folder']))
			{
				$args_array['folder'] = $GLOBALS['HTTP_POST_VARS']['folder'];
			}
			elseif (isset($GLOBALS['HTTP_GET_VARS']['folder']))
			{
				$args_array['folder'] = $GLOBALS['HTTP_GET_VARS']['folder'];
			}
			// (2) should we log in
			$args_array['do_login'] = True;
			// (3) NOT IMPLEMENTED YET  -- newsmode
			$args_array['newsmode'] = False;
			// "start your engines"
			$GLOBALS['phpgw']->msg->begin_request($args_array);
			// error if login failed
			if (($args_array['do_login'] == True)
			&& (!$GLOBALS['phpgw']->msg->mailsvr_stream))
			{
				$this->login_error();
			}
			// base http URI on which we will add other stuff down below
			$this->base_link = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->index_menuaction);
			// if auto-refresh is in use, it will need to know what to refresh, use with ->link to get full http string
			$GLOBALS['phpgw_info']['flags']['email_refresh_uri'] = $GLOBALS['phpgw']->msg->index_refresh_uri;
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
			$this->xi['svr_image_dir'] = PHPGW_IMAGES_DIR;
			$this->xi['image_dir'] = PHPGW_IMAGES;
			$this->xi['current_sort'] = $GLOBALS['phpgw']->msg->sort;
			$this->xi['current_order'] = $GLOBALS['phpgw']->msg->order;
			$this->xi['current_start'] = $GLOBALS['phpgw']->msg->start;
			$this->xi['current_folder'] = $GLOBALS['phpgw']->msg->prep_folder_out('');
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
			$this->xi['switchbox_action'] = $this->base_link;
			
			// get statistics an the current folder
			$this->xi['folder_info'] = array();
			$this->xi['folder_info'] = $GLOBALS['phpgw']->msg->folder_status_info();
			// first, previous, next, last  page navagation arrows
			$this->xi['arrows_form_action'] = $this->base_link;
			$this->xi['arrows_form_name'] = 'arrownav';
			$this->xi['arrows_backcolor'] = $GLOBALS['phpgw_info']['theme']['row_off'];
			//$this->xi['arrows_td_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
			$this->xi['arrows_td_backcolor'] = '';
			$nav_args = Array (
				'start'		=> $GLOBALS['phpgw']->msg->start,
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
			
			$this->xi['td_prev_arrows'] = $this->nextmatchs->left('/index.php',
							$GLOBALS['phpgw']->msg->start,
							$this->xi['folder_info']['number_all'],
							'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
							.'&sort='.$GLOBALS['phpgw']->msg->sort
							.'&order='.$GLOBALS['phpgw']->msg->order
							.'&'.$GLOBALS['phpgw']->msg->index_menuaction);
			
			$this->xi['td_next_arrows'] = $this->nextmatchs->right('/index.php',
							$GLOBALS['phpgw']->msg->start,
							$this->xi['folder_info']['number_all'],
							'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
							.'&sort='.$GLOBALS['phpgw']->msg->sort
							.'&order='.$GLOBALS['phpgw']->msg->order
							.'&'.$GLOBALS['phpgw']->msg->index_menuaction);
			
			$this->xi['ctrl_bar_back2'] = $GLOBALS['phpgw_info']['theme']['row_off'];
			$this->xi['compose_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/compose.php','folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
			if ($this->xi['mailsvr_supports_folders'])
			{
				$this->xi['folders_link'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/folder.php');
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
			$this->xi['accounts_link'] = $this->base_link;
			
			$this->xi['ctrl_bar_back1'] = $GLOBALS['phpgw_info']['theme']['row_on'];
			
			$sort_selected = Array(
				0 => '',
				1 => '',
				2 => '',
				3 => '',
				6 => ''
			);
			$sort_selected[$GLOBALS['phpgw']->msg->sort] = " selected";
			$this->xi['sortbox_select_options'] =
				 '<option value="0"' .$sort_selected[0] .'>'.$this->xi['lang_email_date'].'</option>' ."\r\n"
				.'<option value="1"' .$sort_selected[1] .'>'.$this->xi['lang_arrival_date'].'</option>' ."\r\n"
				.'<option value="2"' .$sort_selected[2] .'>'.$this->xi['lang_from'].'</option>' ."\r\n"
				.'<option value="3"' .$sort_selected[3] .'>'.$this->xi['lang_subject'].'</option>' ."\r\n"
				.'<option value="6"' .$sort_selected[6] .'>'.$this->xi['lang_size'].'</option>' ."\r\n";
			
			$this->xi['sortbox_action'] = $this->base_link.'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('');
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
			$this->xi['stats_folder'] = $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->folder);
			$this->xi['stats_first'] = $GLOBALS['phpgw']->msg->start + 1;
			// toggle the "get folder size" button or link, if getting that size was skipped as a time-saving measure
			if ($this->xi['stats_size'] == '')
			{
				$this->xi['force_showsize_flag'] = 'force_showsize';
				$this->xi['get_size_link'] =
					$this->base_link
					.'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
					.'&sort='.$GLOBALS['phpgw']->msg->sort
					.'&order='.$GLOBALS['phpgw']->msg->order
					.'&start='.$GLOBALS['phpgw']->msg->start
					.'&'.$this->xi['force_showsize_flag'].'=1';
				$this->xi['frm_get_size_name'] = 'form_get_size';
				$this->xi['frm_get_size_action'] = $this->base_link;
			}
			// column labels for the message list
			$flag_sort_pre = '* ';
			$flag_sort_post = ' *';
			switch ((int)$GLOBALS['phpgw']->msg->sort)
			{
				case 1 : $this->xi['lang_date'] = $flag_sort_pre .$this->xi['lang_date'] .$flag_sort_post; break;
				case 2 : $this->xi['lang_from'] = $flag_sort_pre .$this->xi['lang_from'] .$flag_sort_post; break;
				case 3 : $this->xi['lang_subject'] = $flag_sort_pre .$this->xi['lang_subject'] .$flag_sort_post; break;
				case 6 : $this->xi['lang_size'] = '*'.$this->xi['lang_size'].'*';
					 $this->xi['lang_lines'] = $this->xi['lang_lines'] .$flag_sort_post; break;
			}
			// default order is needed for the "nextmatchs" args, to know when to toggle this between normal and reverse
			if ((isset($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting']))
			  && ($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting'] == 'new_old'))
			{
				$this->xi['default_order'] = 1;
			}
			else
			{
				$this->xi['default_order'] = 0;
			}
			// make these column labels into clickable HREF's for their 
			if ($GLOBALS['phpgw']->msg->newsmode)
			{
				$this->xi['hdr_subject'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'3',$GLOBALS['phpgw']->msg->order,'/index.php'.$GLOBALS['phpgw']->msg->index_menuaction,$this->xi['lang_subject'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_from'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'2',$GLOBALS['phpgw']->msg->order,'/index.php'.$GLOBALS['phpgw']->msg->index_menuaction,$this->xi['lang_from'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_date'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'1',$GLOBALS['phpgw']->msg->order,'/index.php'.$GLOBALS['phpgw']->msg->index_menuaction,$this->xi['lang_date'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_size'] = $this->nextmatchs->show_sort_order($GLOBALS['phpgw']->msg->sort,'6',$GLOBALS['phpgw']->msg->order,'/index.php'.$GLOBALS['phpgw']->msg->index_menuaction,$this->xi['lang_lines'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
			}
			else
			{
				$this->xi['hdr_subject'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'3',$this->xi['default_order'],$GLOBALS['phpgw']->msg->order,'/index.php'.$GLOBALS['phpgw']->msg->index_menuaction,$this->xi['lang_subject'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_from'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'2',$this->xi['default_order'],$GLOBALS['phpgw']->msg->order,'/index.php'.$GLOBALS['phpgw']->msg->index_menuaction,$this->xi['lang_from'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_date'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'1',$this->xi['default_order'],$GLOBALS['phpgw']->msg->order,'/index.php'.$GLOBALS['phpgw']->msg->index_menuaction,$this->xi['lang_date'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
				$this->xi['hdr_size'] = $this->nextmatchs->show_sort_order_imap($GLOBALS['phpgw']->msg->sort,'6',$this->xi['default_order'],$GLOBALS['phpgw']->msg->order,'/index.php'.$GLOBALS['phpgw']->msg->index_menuaction,$this->xi['lang_size'],'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out(''));
			}
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
				
				if ((!isset($GLOBALS['phpgw']->msg->mailsvr_stream))
				|| ($GLOBALS['phpgw']->msg->mailsvr_stream == ''))
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
					$this->xi['totaltodisplay'] = $GLOBALS['phpgw']->msg->start + count($this->xi['msg_list_dsp']);
					$this->xi['stats_last'] = $this->xi['totaltodisplay'];
			}
			// user may select individual messages to move, make combobox to select destination folder
			$this->xi['frm_delmov_action'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/action.php');
			$this->xi['frm_delmov_name'] = 'delmov';
			if ($this->xi['mailsvr_supports_folders'])
			{
				$feed_args = Array();
				$feed_args = Array(
					'mailsvr_stream'	=> '',
					'pre_select_folder'	=> '',
					'skip_folder'		=> $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->folder),
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
		}
	}
?>
