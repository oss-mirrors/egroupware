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
		var $theme;
		var $nextmatchs;
		var $debug = 0;

		function uifilters()
		{
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$this->theme = $GLOBALS['phpgw_info']['theme'];
			// make the filters object
			$this->bo = CreateObject("email.bofilters");
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
			$GLOBALS['phpgw']->template->set_var('lang_flag',lang('Flag as important'));
			$GLOBALS['phpgw']->template->set_var('lang_ignore_me1',lang('not used'));
			$GLOBALS['phpgw']->template->set_var('lang_and',lang('And'));
			$GLOBALS['phpgw']->template->set_var('lang_or',lang('Or'));
			$GLOBALS['phpgw']->template->set_var('lang_submit',lang('Submit'));
			$GLOBALS['phpgw']->template->set_var('lang_clear',lang('Clear'));
			$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
			
			
			// get all filters
			$this->bo->read_filter_data_from_prefs();
			
			// ---- Filter Number  ----
			// what filter are we supposed to edit
			$filter_num = $this->bo->obtain_filer_num();
			$GLOBALS['phpgw']->template->set_var('filter_num',$filter_num);
			
			if ($this->debug > 2) { echo 'uifilters.filters: $this->bo->obtain_filer_num(): ['.$this->bo->obtain_filer_num().'] ; $this->bo->all_filters DUMP<pre>'; print_r($this->bo->all_filters); echo '</pre>'."\r\n"; }
			
			// setup some form vars
			//$form_edit_filter_action = $GLOBALS['phpgw']->link(
			//					'/index.php',
			//					'menuaction=email.uifilters.filters_edit');
			$form_edit_filter_action = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.bofilters.process_submitted_data');
			
			$form_cancel_action = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uifilters.filters_list');
			
			$apply_this_filter_url = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.bofilters.run_single_filter'
								.'&filter_num='.$filter_num);
			$apply_this_filter_href = '<a href="'.$apply_this_filter_url.'"><b>*APPLY*</b> This Filter</a>';
			
			$test_this_filter_url = $apply_this_filter_url.'&filter_test=1';
			$test_this_filter_href = '<a href="'.$test_this_filter_url.'">Test Run This Filter</a>';
			
			$GLOBALS['phpgw']->template->set_var('apply_this_filter_href',$apply_this_filter_href);
			$GLOBALS['phpgw']->template->set_var('test_this_filter_href',$test_this_filter_href);
			
			
			// does the data exist or is this a new filter
			/*
			if ((isset($this->bo->all_filters[$filter_num]))
			&& (isset($this->bo->all_filters[$filter_num]['source_accounts'])))
			{
				$filter_exists = True;
			}
			else
			{
				$filter_exists = False;
			}
			*/
			$filter_exists = $this->bo->filter_exists($filter_num);
			
			// ----  Filter Name  ----
			$filter_name_box_name = 'filtername';
			if ($filter_exists)
			{
				$filter_name_box_value = $this->bo->all_filters[$filter_num]['filtername'];
			}
			else
			{
				//$filter_name_box_value = 'Filter '.$filter_num;
				$filter_name_box_value = 'My Mail Filter';
			}
			
			$GLOBALS['phpgw']->template->set_var('filter_name_box_name',$filter_name_box_name);
			$GLOBALS['phpgw']->template->set_var('filter_name_box_value',$filter_name_box_value);
			
			// ----  source_account_listbox_name Selected logic ----
			if ($filter_exists)
			{
				$pre_select_multi = '';
				for ($i=0; $i < count($this->bo->all_filters[$filter_num]['source_accounts']); $i++)
				{
					$this_acct =  $this->bo->all_filters[$filter_num]['source_accounts'][$i]['acctnum'];
					// make a comma sep string of all source accounts, so we can make them selected
					//$pre_select_multi .= (string)$this_acct.', ';
					if ($pre_select_multi == '')
					{
						$pre_select_multi .= (string)$this_acct;
					}
					else
					{
						$pre_select_multi .= ', '.(string)$this_acct;
					}
				}
			}
			else
			{
				// preselect the default account
				$pre_select_multi = '0';
			}
			
			// ---  many email apps offer 2 matches options rows  ---
			// ---  others offer 1 match options row with the option of more ---
			// ---  for now we will offer 2 rows ---
			// because the IMAP search string for 2 items is not as comlicated as for 3 or 4
			$num_matchrow_pairs = 2;
			for ($i=0; $i < $num_matchrow_pairs; $i++)
			{
				if ($i == 0)
				{
					// 1st row has an account combobox
					//$source_account_listbox_name = 'filter_'.$filter_num.'[source_account]'
					// now that we use a multi select box, and php3 can only handle one sub element on POST
					// we have to put this outside the array that holds the other data
					// should we use checkboxes instead?
					$source_account_listbox_name = 'source_accounts[]';
					$feed_args = Array(
						'pre_select_acctnum'	=> '',
						'widget_name'			=> $source_account_listbox_name,
						'folder_key_name'		=> 'folder',
						'acctnum_key_name'		=> 'acctnum',
						'on_change'				=> '',
						'is_multiple'			=> True,
						'multiple_rows'			=> '4',
						//'show_status_is'		=> 'enabled,disabled'
						'show_status_is'		=> 'enabled',
						'pre_select_multi'		=> $pre_select_multi
					);
					// get you custom built HTML combobox (a.k.a. selectbox) widget
					$account_multi_box = $GLOBALS['phpgw']->msg->all_ex_accounts_listbox($feed_args);
					$GLOBALS['phpgw']->template->set_var('account_multi_box', $account_multi_box);
					$V_match_left_td = $GLOBALS['phpgw']->template->parse('V_match_account_box','B_match_account_box');	
				}
				else
				{
					// 2nd row has an and/or combo box with "not enabled" option for when you do not need the 2nd line
					$andor_select_name = 'match_'.(string)$i.'[andor]';
					// what to preselect
					$ignore_me_selected = '';
					$or_selected = '';
					$and_selected = '';
					// as our numbers of rows go beyond what the user previously set, there will bo no andor data
					if (!isset($this->bo->all_filters[$filter_num]['matches'][$i]['andor']))
					{
						$ignore_me_selected = ' selected';
					}
					elseif ($this->bo->all_filters[$filter_num]['matches'][$i]['andor'] == 'or')
					{
						$or_selected = ' selected';
					}
					elseif ($this->bo->all_filters[$filter_num]['matches'][$i]['andor'] == 'and')
					{
						$and_selected = ' selected';
					}
					else
					{
						$ignore_me_selected = ' selected';
					}
					$GLOBALS['phpgw']->template->set_var('andor_select_name',$andor_select_name);
					$GLOBALS['phpgw']->template->set_var('or_selected',$or_selected);
					$GLOBALS['phpgw']->template->set_var('and_selected',$and_selected);
					$GLOBALS['phpgw']->template->set_var('ignore_me_selected',$ignore_me_selected);
					$V_match_left_td = $GLOBALS['phpgw']->template->parse('V_match_and_or_ignore','B_match_and_or_ignore');	
				}
				// things both rows have
				$examine_selectbox_name = 'match_'.(string)$i.'[examine]';
				// what to preselect for "examine"
				$from_selected = '';
				$to_selected = '';
				$cc_selected = '';
				$sender_selected = '';
				$subject_selected = '';
				// as our numbers of rows go beyond what the user previously set, there will bo no data
				if ((!isset($this->bo->all_filters[$filter_num]['matches'][$i]['examine']))
				|| ($this->bo->all_filters[$filter_num]['matches'][$i]['examine'] == 'from'))
				{
					$from_selected = ' selected';
				}
				elseif ($this->bo->all_filters[$filter_num]['matches'][$i]['examine'] == 'to')
				{
					$to_selected = ' selected';
				}
				elseif ($this->bo->all_filters[$filter_num]['matches'][$i]['examine'] == 'cc')
				{
					$cc_selected = ' selected';
				}
				elseif ($this->bo->all_filters[$filter_num]['matches'][$i]['examine'] == 'sender')
				{
					$sender_selected = ' selected';
				}
				elseif ($this->bo->all_filters[$filter_num]['matches'][$i]['examine'] == 'subject')
				{
					$subject_selected = ' selected';
				}
				else
				{
					$from_selected = ' selected';
				}
				$GLOBALS['phpgw']->template->set_var('examine_selectbox_name',$examine_selectbox_name);
				$GLOBALS['phpgw']->template->set_var('from_selected',$from_selected);
				$GLOBALS['phpgw']->template->set_var('to_selected',$to_selected);
				$GLOBALS['phpgw']->template->set_var('cc_selected',$cc_selected);
				$GLOBALS['phpgw']->template->set_var('sender_selected',$sender_selected);
				$GLOBALS['phpgw']->template->set_var('subject_selected',$subject_selected);
				// COMPARATOR
				$comparator_selectbox_name = 'match_'.(string)$i.'[comparator]';
				$contains_selected = '';
				$notcontains_selected = '';
				if ((!isset($this->bo->all_filters[$filter_num]['matches'][$i]['comparator']))
				|| ($this->bo->all_filters[$filter_num]['matches'][$i]['comparator'] == 'contains'))
				{
					$contains_selected = ' selected';
				}
				elseif ($this->bo->all_filters[$filter_num]['matches'][$i]['comparator'] == 'notcontains')
				{
					$notcontains_selected = ' selected';
				}
				else
				{
					$contains_selected = ' selected';
				}
				$GLOBALS['phpgw']->template->set_var('comparator_selectbox_name',$comparator_selectbox_name);
				$GLOBALS['phpgw']->template->set_var('contains_selected',$contains_selected);
				$GLOBALS['phpgw']->template->set_var('notcontains_selected',$notcontains_selected);
				// MATCHTHIS
				$matchthis_textbox_name = 'match_'.(string)$i.'[matchthis]';
				$match_textbox_txt = '';
				if (isset($this->bo->all_filters[$filter_num]['matches'][$i]['matchthis']))
				{
					$match_textbox_txt = $this->bo->all_filters[$filter_num]['matches'][$i]['matchthis'];
				}
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
			// UPDATE: offer "flag as important" option, this could be a 2nd action
			// but that's not coded yet, so for NOW offer 1 row, in the FUTURE offer 2 rows
			$num_actionrows = 1;
			for ($i=0; $i < $num_actionrows; $i++)
			{
				$action_rownum = (string)$i;
				$actionbox_judgement_name = 'action_'.$action_rownum.'[judgement]';
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
				$folder_listbox_name = 'action_'.$action_rownum.'[folder]';
				$listbox_show_unseen = False;
				// for existing data, we must specify which folder was selected in the stored filter
				if ((!isset($this->bo->all_filters[$filter_num]['actions'][$i]['folder']))
				|| ($this->bo->all_filters[$filter_num]['actions'][$i]['folder'] == ''))
				{
					$pre_select_folder = '';
					$pre_select_folder_acctnum = '';
				}
				else
				{
					parse_str($this->bo->all_filters[$filter_num]['actions'][$i]['folder'], $parsed_folder);
					// note also that parse_str will urldecode the uri folder data
					$pre_select_folder = $parsed_folder['folder'];
					$pre_select_folder_acctnum = $parsed_folder['acctnum'];
					//echo '$pre_select_folder: ['.$pre_select_folder.'] ; pre_select_folder_acctnum ['.$pre_select_folder_acctnum.']';
				}
				$feed_args = Array(
					'mailsvr_stream'	=> '',
					'pre_select_folder'	=> $pre_select_folder,
					'pre_select_folder_acctnum' => $pre_select_folder_acctnum,
					'skip_folder'		=> '',
					'show_num_new'		=> $listbox_show_unseen,
					'widget_name'		=> $folder_listbox_name,
					'folder_key_name'	=> 'folder',
					'acctnum_key_name'	=> 'acctnum',
					'on_change'			=> '',
					'first_line_txt'	=> lang('if fileto then select destination folder')
				);
				$folder_listbox = $GLOBALS['phpgw']->msg->folders_mega_listbox($feed_args);
				// ACTIONTEXT
				$action_textbox_name = 'action_'.$action_rownum.'[actiontext]';	
				if ((!isset($this->bo->all_filters[$filter_num]['actions'][$i]['actiontext']))
				|| ($this->bo->all_filters[$filter_num]['actions'][$i]['actiontext'] == ''))
				{
					$action_textbox_txt = '';
				}
				else
				{
					$action_textbox_txt = $this->bo->all_filters[$filter_num]['actions'][$i]['actiontext'];
				}
				// STOP_FILTERING
				$stop_filtering_checkbox_name = 'action_'.$action_rownum.'[stop_filtering]';
				if ((!isset($this->bo->all_filters[$filter_num]['actions'][$i]['stop_filtering']))
				|| ($this->bo->all_filters[$filter_num]['actions'][$i]['stop_filtering'] == ''))
				{
					$stop_filtering_checkbox_checked = '';
				}
				else
				{
					$stop_filtering_checkbox_checked = 'checked';
				}
				
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
			
			$GLOBALS['phpgw']->template->set_var('body_bg_color',$this->theme['bg_color']);
			$GLOBALS['phpgw']->template->set_var('row_on',$this->theme['row_on']);
			$GLOBALS['phpgw']->template->set_var('row_off',$this->theme['row_off']);
			$GLOBALS['phpgw']->template->set_var('row_text',$this->theme['row_text']);
			
			
			
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
			
			$GLOBALS['phpgw']->template->pparse('out','T_filters_out');
			
			$GLOBALS['phpgw']->msg->end_request();
			
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
		}
		
		
		function filters_list()
		{
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			$GLOBALS['phpgw']->common->phpgw_header();
			
			$GLOBALS['phpgw']->template->set_file(
				Array(
					'T_filters_list'	=> 'filters_list.tpl'
				)
			);
			$GLOBALS['phpgw']->template->set_block('T_filters_list','B_filter_list_row','V_filter_list_row');
			
			$var = Array(
				'pref_errors'		=> '',
				'font'				=> $this->theme['font'],
				'tr_titles_color'	=> $this->theme['th_bg'],
				'page_title'		=> lang('E-Mail INBOX Filters List'),
				'filter_name_header' => lang('Filter [number] and Name'),
				'lang_move_up'		=> lang('Move Up'),
				'lang_move_down'		=> lang('Move Down'),
				'lang_edit'			=> lang('Edit'),
				'lang_delete'		=> lang('Delete')
			);
			$GLOBALS['phpgw']->template->set_var($var);
			
			$filters_list = array();
			// get all filters
			$filters_list = $this->bo->read_filter_data_from_prefs();
			
			
			if ($this->debug > 2) { echo 'email.uifilters.filters_list: $filters_list dump<pre>'; print_r($filters_list); echo '</pre>'; }
			
			$tr_color = $this->theme['row_off'];
			$loops = count($filters_list);
			if ($loops == 0)
			{
				$nothing = '&nbsp;';
				$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
				$GLOBALS['phpgw']->template->set_var('tr_color',$tr_color);
				$GLOBALS['phpgw']->template->set_var('filter_identity',$nothing);
				$GLOBALS['phpgw']->template->set_var('move_up_href',$nothing);
				$GLOBALS['phpgw']->template->set_var('move_down_href',$nothing);
				$GLOBALS['phpgw']->template->set_var('edit_href',$nothing);
				$GLOBALS['phpgw']->template->set_var('delete_href',$nothing);
				$GLOBALS['phpgw']->template->parse('V_filter_list_row','B_filter_list_row');
			}
			else
			{
				for($i=0; $i < $loops; $i++)
				{
					// add extra display and handling data
					$filters_list[$i]['display_string'] = '['.$i.'] '.$filters_list[$i]['filtername'];
					$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
					
					$filters_list[$i]['edit_url'] = $GLOBALS['phpgw']->link(
									'/index.php',
									 'menuaction=email.uifilters.filters_edit'
									.'&filter_num='.$i);
					$filters_list[$i]['edit_href'] = '<a href="'.$filters_list[$i]['edit_url'].'">'.lang('Edit').'</a>';
					
					$filters_list[$i]['delete_url'] = $GLOBALS['phpgw']->link(
									'/index.php',
									 'menuaction=email.bofilters.delete_filter'
									.'&filter_num='.$i);
					$filters_list[$i]['delete_href'] = '<a href="'.$filters_list[$i]['delete_url'].'">'.lang('Delete').'</a>';
					
					$GLOBALS['phpgw']->template->set_var('tr_color',$tr_color);
					$GLOBALS['phpgw']->template->set_var('filter_identity',$filters_list[$i]['display_string']);
					$GLOBALS['phpgw']->template->set_var('move_up_href',$filters_list[$i]['move_up_href']);
					$GLOBALS['phpgw']->template->set_var('move_down_href',$filters_list[$i]['move_down_href']);
					$GLOBALS['phpgw']->template->set_var('edit_href',$filters_list[$i]['edit_href']);
					$GLOBALS['phpgw']->template->set_var('delete_href',$filters_list[$i]['delete_href']);
					$GLOBALS['phpgw']->template->parse('V_filter_list_row','B_filter_list_row', True);
				}
			}
			$add_new_filter_url = $GLOBALS['phpgw']->link(
									'/index.php',
									 'menuaction=email.uifilters.filters_edit'
									.'&filter_num='.$this->bo->add_new_filter_token);
			$add_new_filter_href = '<a href="'.$add_new_filter_url.'">'.lang('New Filter').'</a>';
			$GLOBALS['phpgw']->template->set_var('add_new_filter_href',$add_new_filter_href);
			
			$done_url = $GLOBALS['phpgw']->link(
									'/preferences/index.php');
			$done_href = '<a href="'.$done_url.'">'.lang('Done').'</a>';
			$GLOBALS['phpgw']->template->set_var('done_href',$done_href);
			
			// TEST AND APPLY LINKS
			$run_all_filters_url = $GLOBALS['phpgw']->link(
									'/index.php',
									 'menuaction=email.bofilters.run_all_filters');
			$run_all_filters_href = '<a href="'.$run_all_filters_url.'">'.lang('Run ALL Filters').'</a>';
			$GLOBALS['phpgw']->template->set_var('run_all_filters_href',$run_all_filters_href);
			
			$test_all_filters_url = $run_all_filters_url.'&filter_test=1';
			$test_all_filters_href = '<a href="'.$test_all_filters_url.'">Test Run All Filters</a>';
			$GLOBALS['phpgw']->template->set_var('test_all_filters_href',$test_all_filters_href);
			
			// output the template
			$GLOBALS['phpgw']->template->pfp('out','T_filters_list');
		}
		
		
	}
?>