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

	class uimessage
	{
		var $bo;		
		var $debug = 0;
		var $is_modular = False;

		var $public_functions = array(
			'message' => True,
			'get_is_modular' => True,
			'set_is_modular' => True
		);

		function uimessage()
		{
			
		}

		function get_is_modular()
		{
			return $this->is_modular;
		}
		
		function set_is_modular($feed_bool=False)
		{
			// is_bool() is in the php3 compat library
			if ((isset($feed_bool))
			&& (is_bool($feed_bool)))
			{
				// only change this if the arg is boolean
				$this->is_modular = $feed_bool;
			}
			return $this->is_modular;
		}
		
		function message($reuse_feed_args='')
		{
			if (empty($reuse_feed_args))
			{
				$reuse_feed_args = array();
			}
			
			$this->bo = CreateObject("email.bomessage");
			$this->bo->message_data($reuse_feed_args);
			
			if ($this->is_modular == True)
			{
				// we do NOT echo or print output any html, we are being used as a module by another app
				// all we do in this case is pass the parsed html to the calling app
			}
			else
			{
				// we are the BO and the UI, we take care of outputting the HTML to the client browser
				// NOW we can out the header, because "index_data()" filled this global
				//	$GLOBALS['phpgw_info']['flags']['email_refresh_uri']
				// which is needed to preserve folder and sort settings during the auto-refresh-ing
				// currently (Dec 6, 2001) that logic is in phpgwapi/inc/templates/idsociety/head.inc.php
				unset($GLOBALS['phpgw_info']['flags']['noheader']);
				unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
				$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
				$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
				$GLOBALS['phpgw']->common->phpgw_header();
				// NOTE: as of Dec 10, 2001 a call from menuaction defaults to NOT modular
				// HOWEVER still this class must NOT invoke $GLOBALS['phpgw']->common->phpgw_header()
				// even though we had to output the header (go figure... :)
			}
			
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
			
			if (!empty($this->xi['msgtype']))
			{
				$GLOBALS['phpgw']->template->set_var('application',$this->bo->xi['application']);
				$GLOBALS['phpgw']->template->parse('V_x-phpgw-type','B_x-phpgw-type');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('V_x-phpgw-type','');
			}
			
			$GLOBALS['phpgw']->template->set_var('ilnk_prev_msg',$this->bo->xi['ilnk_prev_msg']);
			$GLOBALS['phpgw']->template->set_var('ilnk_next_msg',$this->bo->xi['ilnk_next_msg']);
			
			// ----  Labels and Colors for From, To, CC, Files, and Subject  -----
			$GLOBALS['phpgw']->template->set_var('tofrom_labels_bkcolor',$this->bo->xi['tofrom_labels_bkcolor']);
			$GLOBALS['phpgw']->template->set_var('tofrom_data_bkcolor',$this->bo->xi['tofrom_data_bkcolor']);
			
			$GLOBALS['phpgw']->template->set_var('lang_from',$this->bo->xi['lang_from']);
			$GLOBALS['phpgw']->template->set_var('lang_to',$this->bo->xi['lang_to']);
			$GLOBALS['phpgw']->template->set_var('lang_cc',$this->bo->xi['lang_cc']);
			$GLOBALS['phpgw']->template->set_var('lang_date',$this->bo->xi['lang_date']);
			$GLOBALS['phpgw']->template->set_var('lang_files',$this->bo->xi['lang_files']);
			$GLOBALS['phpgw']->template->set_var('lang_subject',$this->bo->xi['lang_subject']);
			
			// ----  From:  Message Data  -----
			$GLOBALS['phpgw']->template->set_var('from_data_final',$this->bo->xi['from_data_final']);
			
			// ----  To:  Message Data  -----
			$GLOBALS['phpgw']->template->set_var('to_data_final',$this->bo->xi['to_data_final']);
			
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
			
			
			// ----  Images and Hrefs For Reply, ReplyAll, Forward, and Delete  -----
			$GLOBALS['phpgw']->template->set_var('theme_font',$this->bo->xi['theme_font']);
			$GLOBALS['phpgw']->template->set_var('theme_th_bg',$this->bo->xi['theme_th_bg']);
			$GLOBALS['phpgw']->template->set_var('theme_row_on',$this->bo->xi['theme_row_on']);
			$GLOBALS['phpgw']->template->set_var('reply_btns_bkcolor',$this->bo->xi['reply_btns_bkcolor']);
			$GLOBALS['phpgw']->template->set_var('reply_btns_text',$this->bo->xi['reply_btns_text']);
			$GLOBALS['phpgw']->template->set_var('lnk_goback_folder',$this->bo->xi['lnk_goback_folder']);
			$GLOBALS['phpgw']->template->set_var('ilnk_reply',$this->bo->xi['ilnk_reply']);
			$GLOBALS['phpgw']->template->set_var('ilnk_replyall',$this->bo->xi['ilnk_replyall']);
			$GLOBALS['phpgw']->template->set_var('ilnk_forward',$this->bo->xi['ilnk_forward']);
			$GLOBALS['phpgw']->template->set_var('ilnk_delete',$this->bo->xi['ilnk_delete']);
			
			
			// ---- DEBUG: Show Information About Each Part  -----
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
			
			// -----  SHOW MESSAGE  -------
			set_time_limit(120);
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
			set_time_limit(0);
			// by now it should be OK to close the stream
			$GLOBALS['phpgw']->msg->end_request();
			
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
		}
	}
?>
