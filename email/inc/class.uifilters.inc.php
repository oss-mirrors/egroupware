<?php
	/**************************************************************************\
	* phpGroupWare - Sieve Email Filters and Search Mode				*
	* http://www.phpgroupware.org							*
	* Written by Angelo (Angles) Puglisi						*
	* --------------------------------------------							*
	*  This program is free software; you can redistribute it and/or modify it		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your		*
	*  option) any later version.								*
	\**************************************************************************/

	/* $Id$ */

	class uifilters
	{
		var $public_functions = array(
			'filters_list' => True,
			'filters_edit' => True
		);
		var $bo;		
		var $debug = 3;

		function uifilters()
		{
			
		}
		
		function filters_list()
		{			
			// FUTURE
			// for now, pass off to edit so we at leadt show something
			if ($this->debug > 0) { echo 'uifilters.filters_list: function not coded yet, pass off to $this->filters_edit()<br>'."\r\n"; }
			$this->filters_edit();
			
		}
		
		function filters_edit()
		{			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			$GLOBALS['phpgw']->common->phpgw_header();
			
			
			$GLOBALS['phpgw']->template->set_file(
				Array(
					'T_filters_out' => 'filters.tpl',
					'T_filters_blocks' => 'filters_blocks.tpl'
				)
			);
			$GLOBALS['phpgw']->template->set_block('T_filters_blocks','B_match_account_box','V_match_account_box');
			$GLOBALS['phpgw']->template->set_block('T_filters_blocks','B_match_and_or_ignore','V_match_and_or_ignore');
			$GLOBALS['phpgw']->template->set_block('T_filters_blocks','B_action_no_ignore','V_action_no_ignore');
			$GLOBALS['phpgw']->template->set_block('T_filters_blocks','B_action_with_ignore_me','V_action_with_ignore_me');
			$GLOBALS['phpgw']->template->set_block('T_filters_out','B_matches_row','V_matches_row');
			$GLOBALS['phpgw']->template->set_block('T_filters_out','B_actions_row','V_actions_row');
			
			//  ---- LANGS  ----
			$GLOBALS['phpgw']->template->set_var('lang_email_filters',lang('EMail Filters'));
			$GLOBALS['phpgw']->template->set_var('lang_filter_name',lang('Filter Name'));
			$GLOBALS['phpgw']->template->set_var('lang_filter_number',lang('Filter Number'));			
			$GLOBALS['phpgw']->template->set_var('lang_if_messages_match',lang('If Messages Match'));
			$GLOBALS['phpgw']->template->set_var('lang_inbox_for_account',lang('Filter INBOX for accounts'));
			$not_available_yet = ' &#040;NA&#041;';
			$GLOBALS['phpgw']->template->set_var('lang_from',lang('From Address'));
			$GLOBALS['phpgw']->template->set_var('lang_to',lang('To Address'));
			$GLOBALS['phpgw']->template->set_var('lang_cc',lang('CC Address'));
			$GLOBALS['phpgw']->template->set_var('lang_bcc',lang('Bcc Address'));
			$GLOBALS['phpgw']->template->set_var('lang_recipient',lang('Recipient').$not_available_yet);
			$GLOBALS['phpgw']->template->set_var('lang_sender',lang('Sender').$not_available_yet);
			$GLOBALS['phpgw']->template->set_var('lang_subject',lang('Subject'));
			$GLOBALS['phpgw']->template->set_var('lang_header',lang('Header Field').$not_available_yet);
			$GLOBALS['phpgw']->template->set_var('lang_size_larger',lang('Size Larger Than'.$not_available_yet));
			$GLOBALS['phpgw']->template->set_var('lang_size_smaller',lang('Size Smaller Than'.$not_available_yet));
			$GLOBALS['phpgw']->template->set_var('lang_allmessages',lang('All Messages'.$not_available_yet));
			$GLOBALS['phpgw']->template->set_var('lang_body',lang('Body'));
			$GLOBALS['phpgw']->template->set_var('lang_contains',lang('Contains'));
			$GLOBALS['phpgw']->template->set_var('lang_notcontains',lang('Does Not Contain'));
			$GLOBALS['phpgw']->template->set_var('lang_take_actions',lang('Then do this'));
			$GLOBALS['phpgw']->template->set_var('lang_or_enter_text',lang('or enter text'));	
			$GLOBALS['phpgw']->template->set_var('lang_stop_if_matched',lang('and stop filtering'));
			$GLOBALS['phpgw']->template->set_var('lang_ignore_me2',lang('not used'));
			$GLOBALS['phpgw']->template->set_var('lang_keep',lang('Keep'));
			$GLOBALS['phpgw']->template->set_var('lang_discard',lang('Discard'));
			$GLOBALS['phpgw']->template->set_var('lang_reject',lang('Reject'));
			$GLOBALS['phpgw']->template->set_var('lang_redirect',lang('Redirect'));
			$GLOBALS['phpgw']->template->set_var('lang_fileinto',lang('File into'));
			$GLOBALS['phpgw']->template->set_var('lang_ignore_me1',lang('not used'));
			$GLOBALS['phpgw']->template->set_var('lang_and',lang('And'));
			$GLOBALS['phpgw']->template->set_var('lang_or',lang('Or'));
			$GLOBALS['phpgw']->template->set_var('lang_submit',lang('Submit'));
			$GLOBALS['phpgw']->template->set_var('lang_clear',lang('Clear'));
			$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
			
			
			// DEBUGGING
			if ($this->debug > 2) { echo 'uifilters.filters: HTTP_POST_VARS dump:<b>'."\r\n"; var_dump($GLOBALS['HTTP_POST_VARS']); echo '<br><br>'."\r\n"; }			
			
			
			// THIS WILL BE MOVED
			// make the filters object
			$this->bo = CreateObject("email.bofilters");
			$this->bo->distill_filter_args();
			
			
			// setup some form vars
			$form_edit_filter_action = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uifilters.filters_edit');
			
			$form_cancel_action = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uifilters.filters_list');
			
			// ---- Filter Number  ----
			// for now we have only one filter
			$filternum = 0;
			$GLOBALS['phpgw']->template->set_var('filternum',$filternum);
			
			// ----  Filter Name  ----
			$filter_name_box_name = 'filter_'.$filternum.'[filtername]';
			$filter_name_box_value = '';
			
			$GLOBALS['phpgw']->template->set_var('filter_name_box_name',$filter_name_box_name);
			$GLOBALS['phpgw']->template->set_var('filter_name_box_value',$filter_name_box_value);
			
			// ---  many email apps offer 2 matches options rows  ---
			// ---  others offer 1 match options row with the option of more ---
			// ---  for now we will offer 4 rows ---
			$num_matchrow_pairs = 4;
			for ($i=0; $i < $num_matchrow_pairs; $i++)
			{
				if ($i == 0)
				{
					// 1st row has an account combobox
					//$source_account_listbox_name = 'filter_'.$filternum.'[source_account]'
					// now that we use a multi select box, and php3 can only handle one sub element on POST
					// we have to put this outside the array that holds the other data
					// should we use checkboxes instead?
					$source_account_listbox_name = 'filter_'.$filternum.'_source_accounts[]';
					$feed_args = Array(
						'pre_select_acctnum'	=> 0,
						'widget_name'			=> $source_account_listbox_name,
						'folder_key_name'		=> 'folder',
						'acctnum_key_name'		=> 'acctnum',
						'on_change'				=> '',
						'is_multiple'			=> True,
						'multiple_rows'			=> '4',
						//'show_status_is'		=> 'enabled,disabled'
						'show_status_is'		=> 'enabled'
					);
					// get you custom built HTML combobox (a.k.a. selectbox) widget
					$account_multi_box = $GLOBALS['phpgw']->msg->all_ex_accounts_listbox($feed_args);
					$GLOBALS['phpgw']->template->set_var('account_multi_box', $account_multi_box);
					$V_match_left_td = $GLOBALS['phpgw']->template->parse('V_match_account_box','B_match_account_box');	
				}
				else
				{
					// 2nd row has an and/or combo box with "not enabled" option for when you do not need the 2nd line
					$andor_select_name = 'filter_'.$filternum.'[match_'.(string)$i.'_andor]';
					$GLOBALS['phpgw']->template->set_var('andor_select_name',$andor_select_name);
					$V_match_left_td = $GLOBALS['phpgw']->template->parse('V_match_and_or_ignore','B_match_and_or_ignore');	
				}
				// things both rows have
				$examine_selectbox_name = 'filter_'.$filternum.'[match_'.(string)$i.'_examine]';
				$comparator_selectbox_name = 'filter_'.$filternum.'[match_'.(string)$i.'_comparator]';
				$matchthis_textbox_name = 'filter_'.$filternum.'[match_'.(string)$i.'_matchthis]';
				$match_textbox_txt = '';
				
				$GLOBALS['phpgw']->template->set_var('examine_selectbox_name',$examine_selectbox_name);
				$GLOBALS['phpgw']->template->set_var('comparator_selectbox_name',$comparator_selectbox_name);
				$GLOBALS['phpgw']->template->set_var('matchthis_textbox_name',$matchthis_textbox_name);
				$GLOBALS['phpgw']->template->set_var('match_textbox_txt',$match_textbox_txt);
				$GLOBALS['phpgw']->template->set_var('V_match_left_td',$V_match_left_td);
				$GLOBALS['phpgw']->template->parse('V_matches_row','B_matches_row',True);	
			}
			
			// ----  Action Row(s)  ----
			// Mulberry;s Sieve filters provide 2 action rows
			// I'm not sure how the first action still allows for a second action
			// for ex. if you "fileinto" a folder, what would the second action be? Delete it? doesn't make sense
			// with evolution, the second action could be "scoring", but we don't have scoring
			// so for now, offer ONE action row
			$num_actionrows = 1;
			for ($i=0; $i < $num_actionrows; $i++)
			{
				$action_rownum = (string)$i;
				$actionbox_judgement_name = 'filter_'.$filternum.'[action_'.$action_rownum.'_judgement]';
				$GLOBALS['phpgw']->template->set_var('actionbox_judgement_name',$actionbox_judgement_name);
				// 1st row does NOT have the IGNORE_ME option in the actionbox
				if ($i == 0)
				{
					$V_action_widget = $GLOBALS['phpgw']->template->parse('V_action_no_ignore','B_action_no_ignore');
				}
				else
				{
					$V_action_widget = $GLOBALS['phpgw']->template->parse('V_action_with_ignore_me','B_action_with_ignore_me');
				}
				
				// --- Folders Listbox  ---
				$folder_listbox_name = 'filter_'.$filternum.'[action_'.$action_rownum.'_folder]';
				$listbox_show_unseen = False;
				// for existing data, we must specify which folder was selected in the script
				$listbox_pre_select = '';
				$feed_args = Array(
					'mailsvr_stream'	=> '',
					'pre_select_folder'	=> $listbox_pre_select,
					'skip_folder'		=> '',
					'show_num_new'		=> $listbox_show_unseen,
					'widget_name'		=> $folder_listbox_name,
					'folder_key_name'	=> 'folder',
					'acctnum_key_name'	=> 'acctnum',
					'on_change'			=> '',
					'first_line_txt'	=> lang('if fileto then select destination folder')
				);
				$folder_listbox = $GLOBALS['phpgw']->msg->folders_mega_listbox($feed_args);
				
				$action_textbox_name = 'filter_'.$filternum.'[action_'.$action_rownum.'_actiontext]';				
				$action_textbox_txt = '';
				
				$stop_filtering_checkbox_name = 'filter_'.$filternum.'[action_'.$action_rownum.'_stop_filtering]';
				$stop_filtering_checkbox_checked = '';
				
				$GLOBALS['phpgw']->template->set_var('V_action_widget',$V_action_widget);
				$GLOBALS['phpgw']->template->set_var('folder_listbox', $folder_listbox);
				$GLOBALS['phpgw']->template->set_var('action_textbox_name',$action_textbox_name);
				$GLOBALS['phpgw']->template->set_var('action_textbox_txt',$action_textbox_txt);
				$GLOBALS['phpgw']->template->set_var('stop_filtering_checkbox_name',$stop_filtering_checkbox_name);
				$GLOBALS['phpgw']->template->set_var('stop_filtering_checkbox_checked',$stop_filtering_checkbox_checked);
				$GLOBALS['phpgw']->template->parse('V_actions_row','B_actions_row',True);	
			}
			
			$GLOBALS['phpgw']->template->set_var('form_edit_filter_action',$form_edit_filter_action);
			$GLOBALS['phpgw']->template->set_var('form_cancel_action',$form_cancel_action);
			
			$GLOBALS['phpgw']->template->set_var('body_bg_color',$GLOBALS['phpgw_info']['theme']['bg_color']);
			$GLOBALS['phpgw']->template->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
			$GLOBALS['phpgw']->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
			$GLOBALS['phpgw']->template->set_var('row_text',$GLOBALS['phpgw_info']['theme']['row_text']);
			
			// debugging result list
			$mlist_html = '';
			if (count($this->bo->filters) > 0)
			{
				
				if ($this->debug > 1) { echo 'uifilters.filters_edit: count($this->bo->filters): ['.count($this->bo->filters).'] ; <br>'."\r\n"; }
				//$this->bo->sieve_to_imap_string();
				$this->bo->do_imap_search();
				//if ($this->debug > 0) { echo 'message list print_r dump:<b><pre>'."\r\n"; print_r($this->bo->result_set_mlist); echo '</pre><br><br>'."\r\n"; }
				$this->bo->make_mlist_box();
				$mlist_html = 
					'<table border="0" cellpadding="4" cellspacing="1" width="90%" align="center">'."\r\n"
					.$this->bo->finished_mlist."\r\n"
					.'</table>'."\r\n"
					.'<p>&nbsp;</p>'."\r\n"
					.$this->bo->submit_mlist_to_class_form
					.'<p>&nbsp;</p>'."\r\n";
			
			}
			$GLOBALS['phpgw']->template->set_var('V_mlist_html',$mlist_html);
			
			
			// GENERAL INFO
			//echo 'get_loaded_extensions returns:<br><pre>'; print_r(get_loaded_extensions()); echo '</pre>';
			//echo 'phpinfo returns:<br><pre>'; print_r(phpinfo()); echo '</pre>';
			/*
			echo 'SA_MESSAGES: ['.(string)SA_MESSAGES.']<br>'."\r\n";
			echo 'SA_RECENT: ['.(string)SA_RECENT.']<br>'."\r\n";
			echo 'SA_UNSEEN: ['.(string)SA_UNSEEN.']<br>'."\r\n";
			echo 'SA_UIDNEXT: ['.(string)SA_UIDNEXT.']<br>'."\r\n";
			echo 'SA_UIDVALIDITY: ['.(string)SA_UIDVALIDITY.']<br>'."\r\n";
			echo 'SA_ALL: ['.(string)SA_ALL.']<br>'."\r\n";
			
			echo 'SORTDATE: ['.(string)SORTDATE.']<br>'."\r\n";
			echo 'SORTARRIVAL: ['.(string)SORTARRIVAL.']<br>'."\r\n";
			echo 'SORTFROM: ['.(string)SORTFROM.']<br>'."\r\n";
			echo 'SORTSUBJECT: ['.(string)SORTSUBJECT.']<br>'."\r\n";
			echo 'SORTTO: ['.(string)SORTTO.']<br>'."\r\n";
			echo 'SORTCC: ['.(string)SORTCC.']<br>'."\r\n";
			echo 'SORTSIZE: ['.(string)SORTSIZE.']<br>'."\r\n";
			
			echo 'TYPETEXT: ['.(string)TYPETEXT.']<br>'."\r\n";
			echo 'TYPEMULTIPART: ['.(string)TYPEMULTIPART.']<br>'."\r\n";
			echo 'TYPEMESSAGE: ['.(string)TYPEMESSAGE.']<br>'."\r\n";
			echo 'TYPEAPPLICATION: ['.(string)TYPEAPPLICATION.']<br>'."\r\n";
			echo 'TYPEAUDIO: ['.(string)TYPEAUDIO.']<br>'."\r\n";
			echo 'TYPEIMAGE: ['.(string)TYPEIMAGE.']<br>'."\r\n";
			echo 'TYPEVIDEO: ['.(string)TYPEVIDEO.']<br>'."\r\n";
			echo 'TYPEOTHER: ['.(string)TYPEOTHER.']<br>'."\r\n";
			echo 'TYPEMODEL: ['.(string)TYPEMODEL.']<br>'."\r\n";
			
			echo 'ENC7BIT: ['.(string)ENC7BIT.']<br>'."\r\n";
			echo 'ENC8BIT: ['.(string)ENC8BIT.']<br>'."\r\n";
			echo 'ENCBINARY: ['.(string)ENCBINARY.']<br>'."\r\n";
			echo 'ENCBASE64: ['.(string)ENCBASE64.']<br>'."\r\n";
			echo 'ENCQUOTEDPRINTABLE: ['.(string)ENCQUOTEDPRINTABLE.']<br>'."\r\n";
			echo 'ENCOTHER: ['.(string)ENCOTHER.']<br>'."\r\n";
			echo 'ENCUU: ['.(string)ENCUU.']<br>'."\r\n";
			
			echo 'FT_UID: ['.(string)FT_UID.']<br>'."\r\n";
			echo 'FT_PEEK: ['.(string)FT_PEEK.']<br>'."\r\n";
			echo 'FT_NOT: ['.(string)FT_NOT.']<br>'."\r\n";
			echo 'FT_INTERNAL: ['.(string)FT_INTERNAL.']<br>'."\r\n";
			echo 'FT_PREFETCHTEXT: ['.(string)FT_PREFETCHTEXT.']<br>'."\r\n";
  
			echo 'SE_UID: ['.(string)SE_UID.']<br>'."\r\n";
			echo 'SE_FREE: ['.(string)SE_FREE.']<br>'."\r\n";
			echo 'SE_NOPREFETCH: ['.(string)SE_NOPREFETCH.']<br>'."\r\n";
			*/
			
			$GLOBALS['phpgw']->template->pparse('out','T_filters_out');
			
			$GLOBALS['phpgw']->msg->end_request();
		
		}
	}
?>