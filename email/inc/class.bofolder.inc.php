<?php
	/**************************************************************************\
	* phpGroupWare - email BO Class	for Folder Actions and List Display		*
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

	class bofolder
	{
		var $public_functions = array(
			'get_langed_labels'	=> True,
			'folder'		=> True,
			'folder_action'		=> True,
			'folder_data'		=> True
		);
		var $nextmatchs;
		var $index_base_link='';
		//var $debug = True;
		var $debug = False;
		var $xi;
		var $xml_functions = array();
		
		var $soap_functions = array(
			'get_langed_labels' => array(
				'in'  => array('int'),
				'out' => array('array')
			),
			'folder' => array(
				'in'  => array('int'),
				'out' => array('array')
			)
		);
		
		function bofolder()
		{
			
		}
		
		function get_langed_labels()
		{
			// ----  Langs  ----			
		}
		
		function folder($reuse_feed_args=array())
		{
			// attempt (or not) to reuse an existing mail_msg object, i.e. if one ALREADY exists before entering
			// FIXME:   What????   can pass a useful, existing object for us to use here
			//$attempt_reuse = True;
			$attempt_reuse = False;
			
			if ($this->debug) { echo 'ENTERING: email.bofolder.folder'.'<br>'; }
			if ($this->debug) { echo 'email.bofolder.folder: local var attempt_reuse=['.serialize($attempt_reuse).'] ; reuse_feed_args[] dump<pre>'; print_r($reuse_feed_args); echo '</pre>'; }
			// create class objects
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug) { echo 'email.bofolder.folder: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug) { echo 'email.bofolder.folder: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			// do we attempt to reuse the existing msg object?
			if ($attempt_reuse)
			{
				// no not create, we will reuse existing
				if ($this->debug) { echo 'email.bofolder.folder: reusing existing mail_msg login'.'<br>'; }
				// we need to feed the existing object some params begin_request uses to re-fill the msg->args[] data
				$args_array = Array();
				// any args passed in $args_array will override or replace any pre-existing arg value
				$args_array = $reuse_feed_args;
				// add this to keep the error checking code (below) happy
				$args_array['do_login'] = True;
			}
			else
			{
				if ($this->debug) { echo 'email.bofolder.folder: cannot or not trying to reusing existing'.'<br>'; }
				$args_array = Array();
				// should we log in or not
				$args_array['do_login'] = True;
			}
			
			// "start your engines"
			if ($this->debug == True) { echo 'email.bofolder.folder: call msg->begin_request with args array:<pre>'; print_r($args_array); echo '</pre>'; }
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			// error if login failed
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', folder()');
			}
			
			
			// ----  Create or Delete or Rename a Folder ?  ----
			// "folder_action()" handles checking if any action should be taken
			$this->folder_action();
			
			
			// ----  Get a List Of All Folders  AND Display them ----
			$this->folder_data();
			
			// end the email transaction
			$GLOBALS['phpgw']->msg->end_request();
		}
		
		
		
		function folder_action()
		{
			// ----  Create or Delete or Rename a Folder ?  ----
			if (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'create')
			|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'delete')
			|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename')
			|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'create_expert')
			|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'delete_expert')
			|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename_expert'))
			{
				// we have been requested to do a folder action
				
				// basic sanity check
				if ( ($GLOBALS['phpgw']->msg->get_isset_arg('["target_fldball"]["folder"]') == False)
				|| ($GLOBALS['phpgw']->msg->get_arg_value('["target_fldball"]["folder"]') == '') )
				{
					// Error Result Message
					$action_report = lang('Please type a folder name in the text box');
				}
				elseif ( (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename')
				  || ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename_expert'))
				&& (($GLOBALS['phpgw']->msg->get_isset_arg('["source_fldball"]["folder"]') == False)
				  || ($GLOBALS['phpgw']->msg->get_arg_value('["source_fldball"]["folder"]') == '')) )
				{
					// Error Result Message
					$action_report = lang('Please select a folder to rename');
				}
				else
				{
					$source_fldball = $GLOBALS['phpgw']->msg->get_arg_value('source_fldball');
					$target_fldball = $GLOBALS['phpgw']->msg->get_arg_value('target_fldball');
					
					//  ----  Establish Email Server Connectivity Conventions  ----
					$server_str = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_callstr');
					$name_space = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_namespace');
					$dot_or_slash = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_delimiter');
					
					// ---- Prep Target Folder
					// get rid of the escape \ that magic_quotes HTTP POST will add
					// " becomes \" and  '  becomes  \'  and  \  becomes \\
					$target_stripped = $GLOBALS['phpgw']->msg->stripslashes_gpc($target_fldball['folder']);
					$target_fldball['folder'] = $target_stripped;
					// == is that necessary ? == are folder names allowed with '  "  \  in them ? ===
					// rfc2060 does NOT prohibit them
					
					// obtain propper folder names
					// if this is a delete, the folder name will (should) already exist
					// although the user had to type in the folder name
					// for these actions,  the "expert" tag means:
					// "do not add the name space for me, I'm an expert and I know what I'm doing"
					if (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'create_expert')
					|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'delete_expert')
					|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename_expert'))
					{
						// other than stripslashes_gpc,  do nothing
						// the user is an expert, do not alter the phpgw->msg->get_arg_value('target_folder') name at all
					}
					else
					{
						// since the user is not an "expert", we properly prepare the folder name
						// see if the folder already exists in the folder lookup list
						// this would be the case if the user is deleting a folder
						$target_lookup = $GLOBALS['phpgw']->msg->folder_lookup('', $target_fldball['folder']);
						if ($target_lookup != '')
						{
							// phpgw->msg->get_arg_value('target_folder') returned an official long name from the lookup
							$target_fldball['folder'] = $target_lookup;
						}
						else
						{
							// the lookup failed, so this is not an existing folder
							// we have to add the namespace for the user
							$target_long = $GLOBALS['phpgw']->msg->get_folder_long($target_fldball['folder']);
							$target_fldball['folder'] = $target_long;
						}
					}
					
					// add server string to target folder
					$target_fldball['folder'] = $server_str.$target_fldball['folder'];
					
					// =====  NOTE:  maybe some "are you sure" code ????  =====
					if (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'create')
					|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'create_expert'))
					{
						$success = $GLOBALS['phpgw']->msg->phpgw_createmailbox($target_fldball);
					}
					elseif (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'delete')
					|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'delete_expert'))
					{
						$success = $GLOBALS['phpgw']->msg->phpgw_deletemailbox($target_fldball);
					}
					elseif (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename')
					|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename_expert'))
					{
						// phpgw->msg->get_arg_value('source_folder') is taken directly from the listbox, so it *should* be official long name already
						// but it does need to be prep'd in because we prep out the foldernames put in that listbox
						$source_preped = $GLOBALS['phpgw']->msg->prep_folder_in($source_fldball['folder']);
						$source_fldball['folder'] = $source_preped;
						// add server string to source folder
						$source_fldball['folder'] = $server_str.$source_fldball['folder'];
						$success = $GLOBALS['phpgw']->msg->phpgw_renamemailbox($source_fldball, $target_fldball);
					}
					
					// Result Message
					if (($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename')
					|| ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'rename_expert'))
					{
						$action_report =
							$GLOBALS['phpgw']->msg->get_arg_value('action') .' '.lang('folder').' '.$GLOBALS['phpgw']->msg->get_arg_value('source_folder')
							.' '.lang('to').' '.$GLOBALS['phpgw']->msg->get_arg_value('target_folder') .' <br>'
							.lang('result').' : ';
					}
					else
					{
						$action_report = $GLOBALS['phpgw']->msg->get_arg_value('action').' '.lang('folder').' '.$GLOBALS['phpgw']->msg->get_arg_value('target_folder').' <br>'
						.lang('result').' : ';
					}
					// did it work or not
					if ($success)
					{
						// assemble some feedback to show
						$action_report = $action_report .lang('OK');
					}
					else
					{
						$imap_err = $GLOBALS['phpgw']->msg->phpgw_server_last_error();
						if ($imap_err == '')
						{
							$imap_err = lang('unknown error');
						}
						// assemble some feedback to show the user about this error
						$action_report = $action_report .$imap_err;
					}
				}
			}
			else
			{
				// we were NOT requested to do a folder action
				// we did not have the key data needed describing the desired action
				$action_report = '';
				$success = False;
			}
			
			// save the "action_report" to the $this->xi[] data array
			$this->xi['action_report'] = $action_report;
			
			// we may have been  called externally, return this action report
			//return $action_report;
			// we may have been  called externally, return if we succeeded or not
			return $success;
		}




		function folder_data()
		{
			//  ----  Establish Email Server Connectivity Conventions  ----
			$server_str = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_callstr');
			$name_space = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_namespace');
			$dot_or_slash = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_delimiter');
			
			// ----  Get a List Of All Folders  AND Display them ----
			$folder_list = $GLOBALS['phpgw']->msg->get_folder_list('');
			if ($this->debug) { echo 'email.bofolder.folder_data: $folder_list[] dump:<pre>'; print_r($folder_list); echo '</pre>'; }
			
			$this->xi['folder_list_display'] = array();
			for ($i=0; $i<count($folder_list);$i++)
			{
				$folder_long = $folder_list[$i]['folder_long'];
				$folder_short = $folder_list[$i]['folder_short'];
				
				// SA_ALL gets the stats for the number of:  messages, recent, unseen, uidnext, uidvalidity
				//$mailbox_status = $GLOBALS['phpgw']->msg->dcom->status($GLOBALS['phpgw']->msg->get_mailsvr_stream(),"$server_str"."$folder_long",SA_ALL);
				$mailbox_status = $GLOBALS['phpgw']->msg->phpgw_status("$folder_long");
				//$folder_info = array();
				//$folder_info = $GLOBALS['phpgw']->msg->get_folder_status_info();
				
				//debug
				//$real_long_name = $GLOBALS['phpgw']->msg->folder_lookup('',$folder_list[$i]['folder_short']);
				//if ($real_long_name != '')
				//{
				//	echo 'folder exists, official long name: '.$real_long_name.'<br>';
				//}
				
				$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
				$this->xi['folder_list_display'][$i]['list_backcolor'] = $tr_color;
				$this->xi['folder_list_display'][$i]['folder_link'] = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uiindex.index'
								.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out($folder_long)
								.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum());
				
				//if ((isset($GLOBALS['phpgw']->msg->get_arg_value('show_long')))
				if (($GLOBALS['phpgw']->msg->get_isset_arg('show_long') == True)
				&& ($GLOBALS['phpgw']->msg->get_arg_value('show_long') != ''))
				{
					$this->xi['folder_list_display'][$i]['folder_name'] = $folder_long;
				}
				else
				{
					$this->xi['folder_list_display'][$i]['folder_name'] = $folder_short;
				}
				//$this->xi['folder_list_display'][$i]['folder_name'] = $folder_list[$i]['folder_long']);
				//$this->xi['folder_list_display'][$i]['folder_name'] = $GLOBALS['phpgw']->msg->htmlspecialchars_encode($folder_long));
				
				$this->xi['folder_list_display'][$i]['msgs_unseen'] = number_format($mailbox_status->unseen);
				//$this->xi['folder_list_display'][$i]['msgs_unseen'] = number_format($folder_info['number_new']));
				//$this->xi['folder_list_display'][$i]['msgs_total'] = $total_msgs);
				$this->xi['folder_list_display'][$i]['msgs_total'] = number_format($mailbox_status->messages);
				//$this->xi['folder_list_display'][$i]['msgs_total'] = number_format($folder_info['number_all']));
			}
			if ($this->debug) { echo 'email.bofolder.folder_data: $this->xi[folder_list_display] dump:<pre>'; print_r($this->xi['folder_list_display']); echo '</pre>'; }
			
			// information for target folder for create and delete, where no "source_fldball" is present
			// because you are NOT manipulating an *existing* folder
			$this->xi['hiddenvar_target_acctnum_name'] = 'target_fldball[acctnum]';
			$this->xi['hiddenvar_target_acctnum_value'] = (string)$GLOBALS['phpgw']->msg->get_acctnum();
			$this->xi['target_fldball_boxname'] = 'target_fldball[folder]';
			
			// make your HTML listbox of all folders
			// FUTURE: $show_num_new value should be picked up from the users preferences (need to add this pref)
			//$show_num_new = True;
			$show_num_new = False;
			// build the $feed_args array for the all_folders_listbox function
			// anything not specified will be replace with a default value if the function has one for that param
			$feed_args = Array(
				'mailsvr_stream'	=> '',
				'pre_select_folder'	=> '',
				'skip_folder'		=> '',
				'show_num_new'		=> $show_num_new,
				'widget_name'		=> 'source_fldball_fake_uri',
				'folder_key_name'	=> 'folder',
				'acctnum_key_name'	=> 'acctnum',
				'on_change'		=> '',
				'first_line_txt'	=> lang('choose for rename')
			);
			// get you custom built HTML listbox (a.k.a. selectbox) widget
			$this->xi['all_folders_listbox'] = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);
			
			// ----  Set Up Form Variables  ---
			$this->xi['form_action'] = $GLOBALS['phpgw']->link(
					'/index.php',
					'menuaction=email.uifolder.folder');
			//$GLOBALS['phpgw']->template->set_var('all_folders_listbox',$GLOBALS['phpgw']->msg->all_folders_listbox('','','',False));
			//$GLOBALS['phpgw']->template->set_var('select_name_rename','source_folder');
			
			$this->xi['form_create_txt'] = lang('Create a folder');
			$this->xi['form_delete_txt'] = lang('Delete a folder');
			$this->xi['form_rename_txt'] = lang('Rename a folder');
			$this->xi['form_create_expert_txt'] = lang('Create (expert)');
			$this->xi['form_delete_expert_txt'] = lang('Delete (expert)');
			$this->xi['form_rename_expert_txt'] = lang('Rename (expert)');
			$this->xi['form_submit_txt'] = lang("submit");
			
			// ----  Set Up Other Variables  ---	
			$this->xi['title_backcolor'] = $GLOBALS['phpgw_info']['theme']['em_folder'];
			$this->xi['title_textcolor'] = $GLOBALS['phpgw_info']['theme']['em_folder_text'];
			$this->xi['title_text'] = lang('Folder Maintenance');
			$this->xi['label_name_text'] = lang('Folder name');
			//$this->xi['label_messages_text'] = lang('Messages');
			$this->xi['label_new_text'] = lang('New');
			$this->xi['label_total_text'] = lang('Total');
			
			$this->xi['view_long_txt'] = lang('long names');
			//$this->xi['view_long_lnk'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/folder.php?show_long=1');
			$this->xi['view_long_lnk'] = $GLOBALS['phpgw']->link(
							'/index.php',
							'menuaction=email.uifolder.folder'
							.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
							.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum()
							.'&show_long=1');
							
			$this->xi['view_short_txt'] = lang('short names');
			//$this->xi['view_short_lnk'] = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/folder.php');
			$this->xi['view_short_lnk'] = $GLOBALS['phpgw']->link(
							'/index.php',
							'menuaction=email.uifolder.folder'
							.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out()
							.'&fldball[acctnum]='.$GLOBALS['phpgw']->msg->get_acctnum());
			
			$this->xi['the_font'] = $GLOBALS['phpgw_info']['theme']['font'];
			$this->xi['th_backcolor'] = $GLOBALS['phpgw_info']['theme']['th_bg'];
			
		}	
	
	}
?>
