<?php
  /**************************************************************************\
  * phpGroupWare - email UI Class for Message Lists				*
  * http://www.phpgroupware.org							*
  * Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
  * --------------------------------------------						*
  *  This program is free software; you can redistribute it and/or modify it 	*
  *  under the terms of the GNU General Public License as published by the	*
  *  Free Software Foundation; either version 2 of the License, or (at your  	*
  *  option) any later version.								*
  \**************************************************************************/

  /* $Id$ */

	class uiindex
	{
		var $template;
		var $bo;		
		var $debug = False;

		var $public_functions = array(
			'index' => True
		);

		function uiindex()
		{
			
		}

		function index()
		{
			$this->bo = CreateObject("email.boindex");
			$this->bo->index_data();
			// NOW we can out the header, because "index_data()" filled this global
			//	$GLOBALS['phpgw_info']['flags']['email_refresh_uri']
			// which is needed to preserve folder and sort settings during the auto-refresh-ing
			// currently (Dec 6, 2001) that logic is in phpgwapi/inc/templates/idsociety/head.inc.php
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			
			$this->bo->xi['my_layout'] = $GLOBALS['phpgw_info']['user']['preferences']['email']['layout'];
			$this->bo->xi['my_browser'] = $GLOBALS['phpgw']->msg->browser;
			
			$this->template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$this->template->set_file(array(		
				//'T_form_delmov_init' => 'index_form_delmov_init.tpl',
				'T_index_blocks' => 'index_blocks.tpl',
				'T_index_main' => 'index_main_b'.$this->bo->xi['my_browser'].'_l'.$this->bo->xi['my_layout']. '.tpl'
			));
			$this->template->set_block('T_index_main','B_action_report','V_action_report');
			$this->template->set_block('T_index_main','B_show_size','V_show_size');
			$this->template->set_block('T_index_main','B_get_size','V_get_size');
			$this->template->set_block('T_index_main','B_no_messages','V_no_messages');
			$this->template->set_block('T_index_main','B_msg_list','V_msg_list');
			$this->template->set_block('T_index_blocks','B_mlist_form_init','V_mlist_form_init');
			$this->template->set_block('T_index_blocks','B_arrows_form_table','V_arrows_form_table');
			
			$this->template->set_var('frm_delmov_action',$this->bo->xi['frm_delmov_action']);
			$this->template->set_var('frm_delmov_name',$this->bo->xi['frm_delmov_name']);
			$this->template->parse('V_mlist_form_init','B_mlist_form_init');
			$this->bo->xi['V_mlist_form_init'] = $this->template->get_var('V_mlist_form_init');	
			$tpl_vars = Array(
				// fonts and font sizes
				'ctrl_bar_font'		=> $this->bo->xi['ctrl_bar_font'],
				'ctrl_bar_font_size'	=> $this->bo->xi['ctrl_bar_font_size'],
				'mlist_font'		=> $this->bo->xi['mlist_font'],
				'mlist_font_size'	=> $this->bo->xi['mlist_font_size'],
				'mlist_font_size_sm'	=> $this->bo->xi['mlist_font_size_sm'],
				'stats_font'		=> $this->bo->xi['stats_font'],
				'stats_font_size'	=> $this->bo->xi['stats_font_size'],
				'hdr_font'		=> $this->bo->xi['hdr_font'],
				'hdr_font_size'		=> $this->bo->xi['hdr_font_size'],
				'hdr_font_size_sm'	=> $this->bo->xi['hdr_font_size_sm'],
				'ftr_font'		=> $this->bo->xi['ftr_font'],
				// other message list stuff, we parse the mlist block before the rest of the tpl vars are needed			
				'mlist_newmsg_char'	=> $this->bo->xi['mlist_newmsg_char'],
				'mlist_newmsg_color'	=> $this->bo->xi['mlist_newmsg_color'],
				'mlist_newmsg_txt'	=> $this->bo->xi['mlist_newmsg_txt'],
				'images_dir'		=> $this->bo->xi['svr_image_dir']
			);
			$this->template->set_var($tpl_vars);
			
			if ($this->bo->xi['folder_info']['number_all'] == 0)
			{
				$tpl_vars = Array(
					'stats_last'		=> '0',
					'report_no_msgs'	=> $this->bo->xi['report_no_msgs'],
					'V_mlist_form_init'	=> $this->bo->xi['V_mlist_form_init'],
					'mlist_backcolor'	=> $GLOBALS['phpgw_info']['theme']['row_on']
				);
				$this->template->set_var($tpl_vars);
				$this->template->parse('V_no_messages','B_no_messages');
				$this->template->set_var('V_msg_list','');
			}
			else
			{
				$this->template->set_var('V_no_messages','');
				
				$this->template->set_var('stats_last',$this->bo->xi['totaltodisplay']);
				
				for ($i=0; $i < count($this->bo->xi['msg_list_dsp']); $i++)
				{
					if ($this->bo->xi['msg_list_dsp'][$i]['first_item'])
					{
						$this->template->set_var('V_mlist_form_init',$this->bo->xi['V_mlist_form_init']);
					}
					else
					{
						$this->template->set_var('V_mlist_form_init', '');
					}
					if ($this->bo->xi['msg_list_dsp'][$i]['is_unseen'])
					{
						$this->template->set_var('mlist_new_msg',$this->bo->xi['mlist_new_msg']);
						$this->template->set_var('open_newbold','<strong>');
						$this->template->set_var('close_newbold','</strong>');
					}
					else
					{
						$this->template->set_var('mlist_new_msg','&nbsp;');
						$this->template->set_var('open_newbold','');
						$this->template->set_var('close_newbold','');
					}
					if ($this->bo->xi['msg_list_dsp'][$i]['has_attachment'])
					{
						$this->template->set_var('mlist_attach',$this->bo->xi['mlist_attach']);
					}
					else
					{
						$this->template->set_var('mlist_attach','&nbsp;');
					}
					$tpl_vars = Array(
						'mlist_msg_num'		=> $this->bo->xi['msg_list_dsp'][$i]['msg_num'],
						'mlist_backcolor'	=> $this->bo->xi['msg_list_dsp'][$i]['back_color'],
						'mlist_subject'		=> $this->bo->xi['msg_list_dsp'][$i]['subject'],
						'mlist_subject_link'	=> $this->bo->xi['msg_list_dsp'][$i]['subject_link'],
						'mlist_from'		=> $this->bo->xi['msg_list_dsp'][$i]['from_name'],
						'mlist_from_extra'	=> $this->bo->xi['msg_list_dsp'][$i]['display_address_from'],
						'mlist_reply_link'	=> $this->bo->xi['msg_list_dsp'][$i]['from_link'],
						'mlist_date'		=> $this->bo->xi['msg_list_dsp'][$i]['msg_date'],
						'mlist_size'		=> $this->bo->xi['msg_list_dsp'][$i]['size']
					);
					$this->template->set_var($tpl_vars);
					$this->template->parse('V_msg_list','B_msg_list',True);
				}
			}


			if ($this->bo->xi['report_this'] != '')
			{
				$this->template->set_var('report_this',$this->bo->xi['report_this']);
				$this->template->parse('V_action_report','B_action_report');
			}
			else
			{
				$this->template->set_var('V_action_report','');
			}
			$tpl_vars = Array(
				'select_msg'	=> $this->bo->xi['select_msg'],
				'current_sort'	=> $this->bo->xi['current_sort'],
				'current_order'	=> $this->bo->xi['current_order'],
				'current_start'	=> $this->bo->xi['current_start'],
				'current_folder'	=> $this->bo->xi['current_folder'],
				'ctrl_bar_back2'	=> $this->bo->xi['ctrl_bar_back2'],
				'compose_txt'	=> $this->bo->xi['compose_txt'],
				'compose_link'	=> $this->bo->xi['compose_link'],
				'folders_href'	=> $this->bo->xi['folders_href'],
				'folders_btn'	=> $this->bo->xi['folders_btn'],
				'email_prefs_txt'	=> $this->bo->xi['email_prefs_txt'],
				'email_prefs_link'	=> $this->bo->xi['email_prefs_link'],
				'filters_href'	=> $this->bo->xi['filters_href'],
				'accounts_txt'	=> $this->bo->xi['accounts_txt'],
				'accounts_link'	=> $this->bo->xi['accounts_link'],
				'ctrl_bar_back1'	=> $this->bo->xi['ctrl_bar_back1'],
				'sortbox_action'	=> $this->bo->xi['sortbox_action'],
				'switchbox_frm_name'	=> $this->bo->xi['switchbox_frm_name'],
				'sortbox_on_change'	=> $this->bo->xi['sortbox_on_change'],
				'sortbox_select_name'	=> $this->bo->xi['sortbox_select_name'],
				'sortbox_select_options' => $this->bo->xi['sortbox_select_options'],
				'sortbox_sort_by_txt'	=> $this->bo->xi['lang_sort_by'],
				'switchbox_action'	=> $this->bo->xi['switchbox_action'],
				'switchbox_listbox'	=> $this->bo->xi['switchbox_listbox'],
				// old version of first prev next last arrowsm for "layout 1"
				'prev_arrows'		=> $this->bo->xi['td_prev_arrows'],
				'next_arrows'		=> $this->bo->xi['td_next_arrows'],
				'arrows_backcolor'	=> $this->bo->xi['arrows_backcolor'],
				'arrows_td_backcolor'	=> $this->bo->xi['arrows_td_backcolor'],
				// part of new first prev next last arrows data block for "layout 2"
				'arrows_form_action'	=> $this->bo->xi['arrows_form_action'],
				'arrows_form_name'	=> $this->bo->xi['arrows_form_name'],
				'first_page'	=> $this->bo->xi['first_page'],
				'prev_page'	=> $this->bo->xi['prev_page'],
				'next_page'	=> $this->bo->xi['next_page'],
				'last_page'	=> $this->bo->xi['last_page'],
				'stats_backcolor' => $this->bo->xi['stats_backcolor'],
				'stats_color'	=> $this->bo->xi['stats_color'],
				'stats_folder'	=> $this->bo->xi['stats_folder'],
				'stats_saved'	=> $this->bo->xi['stats_saved'],
				'stats_new'	=> $this->bo->xi['stats_new'],
				'lang_new'	=> $this->bo->xi['lang_new'],
				'lang_new2'	=> $this->bo->xi['lang_new2'],
				'lang_total'	=> $this->bo->xi['lang_total'],
				'lang_total2'	=> $this->bo->xi['lang_total2'],
				'lang_size'	=> $this->bo->xi['lang_size'],
				'lang_size2'	=> $this->bo->xi['lang_size2'],
				'stats_to_txt'	=> $this->bo->xi['stats_to_txt'],
				'stats_first'	=> $this->bo->xi['stats_first'],
				'hdr_backcolor'	=> $this->bo->xi['hdr_backcolor'],
				'hdr_subject'	=> $this->bo->xi['hdr_subject'],
				'hdr_from'	=> $this->bo->xi['hdr_from'],
				'hdr_date'	=> $this->bo->xi['hdr_date'],
				'hdr_size'	=> $this->bo->xi['hdr_size'],
				'app_images'		=> $this->bo->xi['image_dir'],
				'ftr_backcolor'		=> $this->bo->xi['ftr_backcolor'],
				'delmov_button'		=> $this->bo->xi['lang_delete'],
				// "delmov_action" was filled above when we parsed that block
				'delmov_listbox'	=> $this->bo->xi['delmov_listbox']
			);
			$this->template->set_var($tpl_vars);
			// make the first prev next last arrows
			$this->template->parse('V_arrows_form_table','B_arrows_form_table');			
			if ($this->bo->xi['stats_size'] != '')
			{
				$this->template->set_var('stats_size',$this->bo->xi['stats_size']);
				$this->template->parse('V_show_size','B_show_size');
				$this->template->set_var('V_get_size','');
			}
			else
			{
				$this->template->set_var('get_size_link',$this->bo->xi['get_size_link']);
				$this->template->set_var('frm_get_size_name',$this->bo->xi['frm_get_size_name']);
				$this->template->set_var('frm_get_size_action',$this->bo->xi['frm_get_size_action']);
				$this->template->set_var('get_size_flag',$this->bo->xi['force_showsize_flag']);
				$this->template->set_var('lang_get_size',$this->bo->xi['lang_get_size']);
				$this->template->parse('V_get_size','B_get_size');
				$this->template->set_var('V_show_size','');
			}

			$this->template->pparse('out','T_index_main');
			$GLOBALS['phpgw']->msg->end_request();
		}
	}
?>
