<?php
	/**************************************************************************\
	* phpGroupWare - email UI Class for Message Lists				*
	* http://www.phpgroupware.org							*
	* Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
	* --------------------------------------------							*
	*  This program is free software; you can redistribute it and/or modify it 		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your  		*
	*  option) any later version.								*
	\**************************************************************************/

	/* $Id$ */

	/*!
	@class uimessage
	@abstract ?
	*/
	class uimessage
	{
		var $bo;
		var $widgets;
		var $debug = 0;

		var $public_functions = array(
			'message' => True
		);

		function uimessage()
		{
			return;
		}

		/*!
		@function message
		@abstract ?
		*/
		function message()
		{
			$this->bo = CreateObject("email.bomessage");
			$this->bo->message_data();
			
			// NOW we can out the header, because "index_data()" filled this global
			//	$GLOBALS['phpgw_info']['flags']['email_refresh_uri']
			// which is needed to preserve folder and sort settings during the auto-refresh-ing
			// currently (Dec 6, 2001) that logic is in phpgwapi/inc/templates/idsociety/head.inc.php
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			$GLOBALS['phpgw']->common->phpgw_header();
			// HOWEVER still this class must NOT invoke $GLOBALS['phpgw']->common->phpgw_header()
			// even though we had to output the header (go figure... :)
			
			// ---- BEGIN UIMESSAGE
			$GLOBALS['phpgw']->template->set_file(array(
				'T_message_main' => 'message_main.tpl',
				'T_message_echo_dump' => 'message_echo_dump.tpl'
			));
			$GLOBALS['phpgw']->template->set_block('T_message_main','B_x-phpgw-type','V_x-phpgw-type');
			$GLOBALS['phpgw']->template->set_block('T_message_main','B_cc_data','V_cc_data');
			$GLOBALS['phpgw']->template->set_block('T_message_main','B_attach_list','V_attach_list');
			$GLOBALS['phpgw']->template->set_block('T_message_main','B_debug_parts','V_debug_parts');
			$GLOBALS['phpgw']->template->set_block('T_message_main','B_display_part','V_display_part');
			$GLOBALS['phpgw']->template->set_block('T_message_echo_dump','B_setup_echo_dump','V_setup_echo_dump');
			$GLOBALS['phpgw']->template->set_block('T_message_echo_dump','B_done_echo_dump','V_done_echo_dump');
			
			//= = = = TOOLBAR WIDGET = = = 
			$this->widgets = CreateObject('email.html_widgets');
			$GLOBALS['phpgw']->template->set_var('widget_toolbar',$this->widgets->get_toolbar());
			
			if (!empty($this->xi['msgtype']))
			{
				$GLOBALS['phpgw']->template->set_var('application',$this->bo->xi['application']);
				$GLOBALS['phpgw']->template->parse('V_x-phpgw-type','B_x-phpgw-type');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('V_x-phpgw-type','');
			}
			
			//  ----  TOOL BAR / MENU BAR ----
			$tpl_vars = Array(
				//'accounts_label'		=> $this->bo->xi['accounts_label'],
				//'ctrl_bar_font'			=> $this->bo->xi['ctrl_bar_font'],
				//'ctrl_bar_font_size'	=> $this->bo->xi['ctrl_bar_font_size'],
				//'ctrl_bar_back1'		=> $this->bo->xi['ctrl_bar_back1'],
				
				'bar_back1'		=> '',
				//'bar_back1'		=> $GLOBALS['phpgw_info']['theme']['table_bg'],
				//'bar_back1'		=> $GLOBALS['phpgw_info']['theme']['bg08'],
				
				// ---- account switchbox  ----
				//'acctbox_listbox'		=> $this->bo->xi['acctbox_listbox'],
				//'ilnk_accounts'			=> $this->bo->xi['ilnk_accounts'],
				//'acctbox_frm_name'		=> $this->bo->xi['acctbox_frm_name'],
				//'acctbox_action'		=> $this->bo->xi['acctbox_action'],
				// ---- Move Message Box  ----
				'move_current_sort'		=> $this->bo->xi['move_current_sort'],
				'move_current_order'	=> $this->bo->xi['move_current_order'],
				'move_current_start'	=> $this->bo->xi['move_current_start'],
				'mlist_checkbox_name'	=> $this->bo->xi['mlist_checkbox_name'],
						
				'mlist_embedded_uri'	=> $this->bo->xi['mlist_embedded_uri'],
				'frm_delmov_action'		=> $this->bo->xi['frm_delmov_action'],
				'frm_delmov_name'		=> $this->bo->xi['frm_delmov_name'],
				'delmov_listbox'		=> $this->bo->xi['delmov_listbox'],
				'move_postmove_goto_name'	=> $this->bo->xi['move_postmove_goto_name'],
				'move_postmove_goto_value'	=> $this->bo->xi['move_postmove_goto_value'],
				
				'ilnk_prev_msg'			=> $this->bo->xi['ilnk_prev_msg'],
				'ilnk_next_msg'			=> $this->bo->xi['ilnk_next_msg'],
				
				// ----  Labels and Colors for From, To, CC, Files, and Subject  -----
				'tofrom_labels_bkcolor'	=> $this->bo->xi['tofrom_labels_bkcolor'],
				'tofrom_data_bkcolor'	=> $this->bo->xi['tofrom_data_bkcolor'],
				
				'lang_from'		=> $this->bo->xi['lang_from'],
				'lang_to'		=> $this->bo->xi['lang_to'],
				'lang_cc'		=> $this->bo->xi['lang_cc'],
				'lang_date'		=> $this->bo->xi['lang_date'],
				'lang_files'	=> $this->bo->xi['lang_files'],
				'lang_subject'	=> $this->bo->xi['lang_subject'],
				
				// ----  From:  Message Data  -----
				'from_data_final'		=> $this->bo->xi['from_data_final'],
				// ----  To:  Message Data  -----
				'to_data_final'			=> $this->bo->xi['to_data_final']
			);
			$GLOBALS['phpgw']->template->set_var($tpl_vars);
			
			// ----  Cc:  Message Data  -----
			//if (isset($msg_headers->cc) && count($msg_headers->cc) > 0)
			//	$this->bo->xi['
			if ( (isset($this->bo->xi['cc_data_final']))
			&& ($this->bo->xi['cc_data_final'] != '') )
			{
				$GLOBALS['phpgw']->template->set_var('cc_data_final',$this->bo->xi['cc_data_final']);
				$GLOBALS['phpgw']->template->parse('V_cc_data','B_cc_data');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('V_cc_data','');
			}
			
			// ---- Message Date  (set above)  -----
			$GLOBALS['phpgw']->template->set_var('message_date',$this->bo->xi['message_date']);
			// ---- Message Subject  (set above)  -----
			$GLOBALS['phpgw']->template->set_var('message_subject',$this->bo->xi['message_subject']);
			
			// ---- Attachments List  -----
			if ($this->bo->xi['list_of_files'] != '')
			{
				$GLOBALS['phpgw']->template->set_var('list_of_files',$this->bo->xi['list_of_files']);
				$GLOBALS['phpgw']->template->parse('V_attach_list','B_attach_list');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('V_attach_list','');
			}
			
			
			$tpl_vars = Array(
				// ----  Images and Hrefs For Reply, ReplyAll, Forward, and Delete  -----
				'theme_font'		=> $this->bo->xi['theme_font'],
				'theme_th_bg'		=> $this->bo->xi['theme_th_bg'],
				'theme_row_on'		=> $this->bo->xi['theme_row_on'],
				'reply_btns_bkcolor' => $this->bo->xi['reply_btns_bkcolor'],
				'reply_btns_text'	=> $this->bo->xi['reply_btns_text'],
				
				'lnk_goback_folder'	=> $this->bo->xi['lnk_goback_folder'],
				'ilnk_reply'		=> $this->bo->xi['ilnk_reply'],
				'ilnk_replyall'		=> $this->bo->xi['ilnk_replyall'],
				'ilnk_forward'		=> $this->bo->xi['ilnk_forward'],
				'ilnk_delete'		=> $this->bo->xi['ilnk_delete']
			);
			$GLOBALS['phpgw']->template->set_var($tpl_vars);
			
			
			// ---- DEBUG: Show Information About Each Part  -----
			//  the debug output needs updating
			if ($this->bo->debug > 0)
			{
				$GLOBALS['phpgw']->template->set_var('msg_body_info',$this->bo->xi['msg_body_info']);
				$GLOBALS['phpgw']->template->parse('V_debug_parts','B_debug_parts');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('V_debug_parts','');
			}
			
			// -----  Message_Display Template Handles it from here  -------
			$GLOBALS['phpgw']->template->set_var('theme_font',$this->bo->xi['theme_font']);
			$GLOBALS['phpgw']->template->set_var('theme_th_bg',$this->bo->xi['theme_th_bg']);
			$GLOBALS['phpgw']->template->set_var('theme_row_on',$this->bo->xi['theme_row_on']);
				
			// ----  so called "TOOLBAR" between the msg header data and the message siaplay
			$GLOBALS['phpgw']->template->set_var('view_option',$this->bo->xi['view_option']);
			$GLOBALS['phpgw']->template->set_var('view_headers_href',$this->bo->xi['view_headers_href']);
			$GLOBALS['phpgw']->template->set_var('view_raw_message_href',$this->bo->xi['view_raw_message_href']);
			
			// -----  SHOW MESSAGE  -------
			//@set_time_limit(120);
			$count_part_nice = count($this->bo->part_nice);
			for ($i = 0; $i < $count_part_nice; $i++)
			{
				if ($this->bo->part_nice[$i]['d_instructions'] == 'show')
				{
					$GLOBALS['phpgw']->template->set_var('title_text',$this->bo->part_nice[$i]['title_text']);
					$GLOBALS['phpgw']->template->set_var('display_str',$this->bo->part_nice[$i]['display_str']);
					$GLOBALS['phpgw']->template->set_var('message_body',$this->bo->part_nice[$i]['message_body']);
					$GLOBALS['phpgw']->template->parse('V_display_part','B_display_part', True);
				}
				elseif ($this->bo->part_nice[$i]['d_instructions'] == 'echo_out')
				{
					// output a blank message body, we'll use an alternate method below
					$GLOBALS['phpgw']->template->set_var('V_display_part','');
					// -----  Finished With Message_Mail Template, Output It
					$GLOBALS['phpgw']->template->pfp('out','T_message_main');
					
					// -----  Prepare a Table for this Echo Dump
					$GLOBALS['phpgw']->template->set_var('title_text',$this->bo->part_nice[$i]['title_text']);
					$GLOBALS['phpgw']->template->set_var('display_str',$this->bo->part_nice[$i]['display_str']);
					$GLOBALS['phpgw']->template->parse('V_setup_echo_dump','B_setup_echo_dump');
					$GLOBALS['phpgw']->template->set_var('V_done_echo_dump','');
					$GLOBALS['phpgw']->template->pfp('out','T_message_echo_dump');
					
					// -----  Prepare $msgball data for phpgw_fetchbody()
					$msgball = $GLOBALS['phpgw']->msg->get_arg_value('msgball');
					$msgball['part_no'] = $this->bo->part_nice[$i]['m_part_num_mime'];
					
					// -----  Echo This Data Directly to the Client
					// since the php version of this of b0rked for large msgs, perhaps use sockets code?
					echo '<pre>';
					echo $GLOBALS['phpgw']->msg->phpgw_fetchbody($msgball);
					echo '</pre>';
					// -----  Close Table
					$GLOBALS['phpgw']->template->set_var('V_setup_echo_dump','');
					$GLOBALS['phpgw']->template->parse('V_done_echo_dump','B_done_echo_dump');
					$GLOBALS['phpgw']->template->pfp('out','T_message_echo_dump');
					
					//  = = = =  = =======  CLEANUP AND EXIT PAGE ======= = = = = = =
					$did_echo_dump = True;
					break;
				}
			}
			//@set_time_limit(0);
			
			if ((isset($did_echo_dump))
			&& ($did_echo_dump == True))
			{
				// DO NOTHING!
				// echo dump already outputted the template
			}
			else
			{
				$GLOBALS['phpgw']->template->pfp('out','T_message_main');
				//$GLOBALS['phpgw']->common->phpgw_footer();
			}
			// tell apache to release emeory back to the system on script end
			//apache_child_terminate();

			// close down ALL mailserver streams
			$GLOBALS['phpgw']->msg->end_request();
			// destroy the object
			$GLOBALS['phpgw']->msg = '';
			unset($GLOBALS['phpgw']->msg);
		}
	}
?>
