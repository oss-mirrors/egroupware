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
		var $debug = 0;

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
			$GLOBALS['phpgw']->template->set_block('T_filters_blocks','B_account_and_or_ignore','V_account_and_or_ignore');
			$GLOBALS['phpgw']->template->set_block('T_filters_blocks','B_action_no_ignore','V_action_no_ignore');
			$GLOBALS['phpgw']->template->set_block('T_filters_blocks','B_action_with_ignore_me','V_action_with_ignore_me');
			$GLOBALS['phpgw']->template->set_block('T_filters_out','B_matches_row','V_matches_row');
			$GLOBALS['phpgw']->template->set_block('T_filters_out','B_actions_row','V_actions_row');
			
			// setup some form vars
			$form_edit_filter_btn_name = 'submit_filters';
			$form_edit_filter_action = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uifilters.filters_edit');
			$form_cancel_btn_name = 'filerpage_cancel';
			$form_cancel_action = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uifilters.filters_list');
			
			// make the filters object
			$this->bo = CreateObject("email.bofilters");
			$this->bo->submit_flag = $form_edit_filter_btn_name;
			$this->bo->distill_filter_args();
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
			
			
			// DEBUGGING
			if ($this->debug > 2) { echo 'uifilters.filters: HTTP_POST_VARS dump:<b>'."\r\n"; var_dump($GLOBALS['HTTP_POST_VARS']); echo '<br><br>'."\r\n"; }
			// dump submitted data for inspection
			//$show_data_dump = True;
			//$show_data_dump = False;
			$show_data_dump = ($this->debug > 2);
			if ($show_data_dump)
			{
				//raw HTTP_POST_VARS dump
				//echo 'uifilters.filters: HTTP_POST_VARS print_r dump (a):<b><pre>'."\r\n"; print_r($GLOBALS['HTTP_POST_VARS']); echo '</pre><br><br>'."\r\n";
				
				if  ((isset($GLOBALS['HTTP_POST_VARS'][$form_edit_filter_btn_name]))
				&& ($GLOBALS['HTTP_POST_VARS'][$form_edit_filter_btn_name] != ''))
				{
					$data_dump_info = 'uifilters.filters_edit: filter data WAS submitted';
				}
				elseif  ((isset($GLOBALS['HTTP_POST_VARS'][$form_cancel_btn_name]))
				&& ($GLOBALS['HTTP_POST_VARS'][$form_cancel_btn_name] != ''))
				{
					$data_dump_info = 'uifilters.filters_edit: cancel button was pressed';
				}
				else
				{
					$data_dump_info = 'uifilters.filters: NO filter data was submitted';
				}
			}
			else
			{
				$data_dump_info = 'uifilters.filters: data dump not set';
			}
			$GLOBALS['phpgw']->template->set_var('data_dump_info',$data_dump_info);
			
			
			$GLOBALS['phpgw']->template->set_var('form_edit_filter_action',$form_edit_filter_action);
			$GLOBALS['phpgw']->template->set_var('form_edit_filter_btn_name', $form_edit_filter_btn_name);
			$GLOBALS['phpgw']->template->set_var('form_cancel_action',$form_cancel_action);
			$GLOBALS['phpgw']->template->set_var('form_cancel_btn_name', $form_cancel_btn_name);
			
			$filters_txt = lang('EMail Filters');
			$GLOBALS['phpgw']->template->set_var('filters_txt',$filters_txt);
			
			// ---- Filter Number  ----
			// I assume we'll have more than one sieve script available to the user
			// of course, for now we have only one dummy slot
			$f_idx = '0';
			$GLOBALS['phpgw']->template->set_var('f_idx',$f_idx);
			
			
			// ----  Filter Name  ----
			// Assuming we'll allow more than one script, then the scripts must have names
			// pull the name from the database, else it's blank
			$filter_name = '';
			$GLOBALS['phpgw']->template->set_var('filter_name',$filter_name);
			
			$filter_name_box_name = 'filter_'.$f_idx.'[filtername]';
			$GLOBALS['phpgw']->template->set_var('filter_name_box_name',$filter_name_box_name);
			
			$GLOBALS['phpgw']->template->set_var('lang_name',lang('Filter Name'));
			$GLOBALS['phpgw']->template->set_var('lang_if_messages_match',lang('If Messages Match'));
			
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
			// I do NOT think Sieve lets you search the body - but I'm not sure
			$GLOBALS['phpgw']->template->set_var('lang_body',lang('Body &#040;extended sieve&#041;'));
			
			$GLOBALS['phpgw']->template->set_var('lang_contains',lang('Contains'));
			$GLOBALS['phpgw']->template->set_var('lang_notcontains',lang('Does Not Contain'));
			
			// ---  initially there will be 2 matches options rows  ---
			$num_matchrow_pairs = 2;
			for ($i=0; $i < $num_matchrow_pairs; $i++)
			{
				// 1st row
				// does NOT have the and/or combo box
				// so substitute "N/A" for "and" , also make "or" a "&nbsp;" so the combobox looks empty
				if ($i == 0)
				{
					// 1st row has an account combobox
					// anything not specified will be replace with a default value if the function has one for that param
					//$source_account_listbox_name = 'filter_'.$f_idx.'[match_'.(string)$i.'_source_account]';
					$source_account_listbox_name = 'filter_'.$f_idx.'[source_account]';
					$feed_args = Array(
						'pre_select_acctnum'	=> 0,
						'widget_name'			=> $source_account_listbox_name,
						'folder_key_name'		=> 'folder',
						'acctnum_key_name'		=> 'acctnum',
						'on_change'				=> ''
					);
					// get you custom built HTML combobox (a.k.a. selectbox) widget
					$GLOBALS['phpgw']->template->set_var('V_account_and_or_ignore', $GLOBALS['phpgw']->msg->all_ex_accounts_listbox($feed_args));
					
				}
				else
				{
					// 2nd row DOES have the and/or combo box with "not enabled"
					$andor_select_name = 'filter_'.$f_idx.'[match_'.(string)$i.'_andor]';
					$lang_ignore_me1 = lang('not used');
					$lang_and = lang('And');
					$lang_or = lang('Or');
					$GLOBALS['phpgw']->template->set_var('andor_select_name',$andor_select_name);
					$GLOBALS['phpgw']->template->set_var('lang_ignore_me1',$lang_ignore_me1);
					$GLOBALS['phpgw']->template->set_var('lang_and',$lang_and);
					$GLOBALS['phpgw']->template->set_var('lang_or',$lang_or);
					// 2nd row does NOT have s "source folder" combobox
					$GLOBALS['phpgw']->template->parse('V_account_and_or_ignore','B_account_and_or_ignore');	
				}
				// FIXME: select the correct AND/OR depending on the data from the database
				// FIXME: select the correct COMPARATOR depending on the data from the database
				// if there's existing match string data in the database, put it here
				$match_textbox_txt = '';
				$GLOBALS['phpgw']->template->set_var('match_textbox_txt',$match_textbox_txt);
				$GLOBALS['phpgw']->template->set_var('match_rownum',(string)$i);
				$GLOBALS['phpgw']->template->parse('V_matches_row','B_matches_row',True);	
			}
			
			//$GLOBALS['phpgw']->template->set_var('lang_more_choices',lang('More Choices'));
			//$GLOBALS['phpgw']->template->set_var('lang_fewer_choices',lang('Fewer Choices'));
			//$GLOBALS['phpgw']->template->set_var('lang_reset',lang('Reset'));
			
			$GLOBALS['phpgw']->template->set_var('lang_take_actions',lang('Then take these actions'));
			$GLOBALS['phpgw']->template->set_var('lang_or_enter_text',lang('or enter text'));	
			$GLOBALS['phpgw']->template->set_var('lang_stop_if_matched',lang('and stop filtering'));
			
			// ---typically we provide  2 action rows  ---
			$num_actionrows = 2;
			for ($i=0; $i < $num_actionrows; $i++)
			{
				$action_rownum = (string)$i;
				$actionbox_name = 'filter_'.$f_idx.'[action_'.$action_rownum.'_judgement]';
				$GLOBALS['phpgw']->template->set_var('actionbox_name',$actionbox_name);
				$GLOBALS['phpgw']->template->set_var('lang_ignore_me2',lang('not used'));
				$GLOBALS['phpgw']->template->set_var('lang_keep',lang('Keep'));
				$GLOBALS['phpgw']->template->set_var('lang_discard',lang('Discard'));
				$GLOBALS['phpgw']->template->set_var('lang_reject',lang('Reject'));
				$GLOBALS['phpgw']->template->set_var('lang_redirect',lang('Redirect'));
				$GLOBALS['phpgw']->template->set_var('lang_fileinto',lang('File into'));
				// 1st row does NOT have the IGNORE_ME option in the actionbox
				if ($i == 0)
				{
					$V_action_widget = $GLOBALS['phpgw']->template->parse('V_action_no_ignore','B_action_no_ignore');
				}
				else
				{
					$V_action_widget = $GLOBALS['phpgw']->template->parse('V_action_with_ignore_me','B_action_with_ignore_me');
				}
				$GLOBALS['phpgw']->template->set_var('V_action_widget',$V_action_widget);
				
				// --- Folders Listbox  ---
				// setup an dropdown listbox that is a list of all folders
				// digress:
				// 	in win32 this would be called an dropdown listbox with first row being editbox-like
				//	but in html we have to also show a seperate textbox to get the same functionality
				$folder_listbox_name = 'filter_'.$f_idx.'[action_'.$action_rownum.'_folder]';
				// do we want to show the number of new (unseen) messages in the listbox?
				//$listbox_show_unseen = True;
				$listbox_show_unseen = False;
				// for existing data, we must specify which folder was selected in the script
				$listbox_pre_select = '';
				// build the $feed_args array for the all_folders_listbox function
				// anything not specified will be replace with a default value if the function has one for that param
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
				// get you custom built HTML listbox (a.k.a. selectbox) widget
				$GLOBALS['phpgw']->template->set_var('folder_listbox', $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args));
				
				// --- Action Textbox ---
				// if the textbox has existing data, it gets filled here
				$action_textbox_txt = '';
				$GLOBALS['phpgw']->template->set_var('action_textbox_txt',$action_textbox_txt);
				// FIXME: check the checkbox "STOP" value depending on the data from the database
				$GLOBALS['phpgw']->template->set_var('action_rownum',$action_rownum);
				$GLOBALS['phpgw']->template->parse('V_actions_row','B_actions_row',True);	
			}
			
			
			//$GLOBALS['phpgw']->template->set_var('lang_more_actions',lang('More Actions'));
			//$GLOBALS['phpgw']->template->set_var('lang_fewer_actions',lang('Fewer Actions'));
			
			$GLOBALS['phpgw']->template->set_var('lang_submit',lang('Submit'));
			$GLOBALS['phpgw']->template->set_var('lang_clear',lang('Clear'));
			$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
			
			
			$GLOBALS['phpgw']->template->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
			$GLOBALS['phpgw']->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
			$GLOBALS['phpgw']->template->set_var('row_text',$GLOBALS['phpgw_info']['theme']['row_text']);
			
			
			
			
			
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