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

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(
		Array(
			'T_filters_out' => 'filters.tpl'
		)
	);
	$t->set_block('T_filters_out','B_matches_row','V_matches_row');
	$t->set_block('T_filters_out','B_actions_row','V_actions_row');

	// DEBUGGING
	// dump submitted data for inspection
	$show_data_dump = True;
	//$show_data_dump = False;
	
	if ($show_data_dump)
	{
		if  ((isset($GLOBALS['HTTP_POST_VARS']['submit']))
		&& ($GLOBALS['HTTP_POST_VARS']['submit'] != '')
		&& (isset($GLOBALS['HTTP_POST_VARS']['filter_0']))
		&& ($GLOBALS['HTTP_POST_VARS']['filter_0'] != '')
		)
		{
			var_dump($GLOBALS['HTTP_POST_VARS']['filter_0']);
			//$data_dump = $GLOBALS['phpgw']->msg->htmlspecialchars_encode(serialize($GLOBALS['HTTP_POST_VARS']));
			//$data_dump = $GLOBALS['phpgw']->msg->body_hard_wrap($data_dump, 76);
			$data_dump = 'data should be above this table';
			$t->set_var('data_dump',$data_dump);
		}
		else
		{
			$t->set_var('data_dump','no data was submitted');
		}
	}
	else
	{
		$t->set_var('data_dump','data dump not set');
	}

	// setup the form
	$form1_action = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/filters.php');
	$t->set_var('form1_action',$form1_action);
	$form2_action = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/filters.php');
	$t->set_var('form2_action',$form1_action);

	$filters_txt = lang('EMail Filters');
	$t->set_var('filters_txt',$filters_txt);
	
	// ---- Filter Number  ----
	// I assume we'll have more than one sieve script available to the user
	// of course, for now we have only one dummy slot
	$f_idx = '0';
	$t->set_var('f_idx',$f_idx);
	
	
	// ----  Filter Name  ----
	// Assuming we'll allow more than one script, then the scripts must have names
	// pull the name from the database, else it's blank
	$filter_name = '';
	$t->set_var('filter_name',$filter_name);

	$t->set_var('lang_name',lang('Name'));
	$t->set_var('lang_if_messages_match',lang('If Messages Match'));

	$t->set_var('lang_from',lang('From Address'));
	$t->set_var('lang_to',lang('To Address'));
	$t->set_var('lang_cc',lang('CC Address'));
	$t->set_var('lang_bcc',lang('Bcc Address'));
	$t->set_var('lang_recipient',lang('Recipient'));
	$t->set_var('lang_sender',lang('Sender'));
	$t->set_var('lang_subject',lang('Subject'));
	$t->set_var('lang_header',lang('Header Field'));
	$t->set_var('lang_size',lang('Size'));
	$t->set_var('lang_allmessages',lang('All Messages'));

	$t->set_var('lang_contains',lang('Contains'));
	$t->set_var('lang_notcontains',lang('Does Not Contain'));

	// ---  initially there will be 2 matches options rows  ---
	$num_matchrows = 2;
	for ($i=0; $i < $num_matchrows; $i++)
	{
		// 1st row
		// does NOT have the and/or combo box
		// so substitute "N/A" for "and" , also make "or" a "&nbsp;" so the combobox looks empty
		if ($i == 0)
		{
			$t->set_var('lang_and','N&#47;A');
			$t->set_var('lang_or','&nbsp;');
		}
		else
		{
			// 2nd row DOES have the and/or combo box
			$t->set_var('lang_and',lang('And'));
			$t->set_var('lang_or',lang('Or'));
		}
		// FIXME: select the correct AND/OR depending on the data from the database
		// FIXME: select the correct COMPARATOR depending on the data from the database
		// if there's existing match string data in the database, put it here
		$match_textbox_txt = '';
		$t->set_var('match_textbox_txt',$match_textbox_txt);
		$t->set_var('match_rownum',(string)$i);
		$t->parse('V_matches_row','B_matches_row',True);	
	}

	$t->set_var('lang_more_choices',lang('More Choices'));
	$t->set_var('lang_fewer_choices',lang('Fewer Choices'));
	$t->set_var('lang_reset',lang('Reset'));
	
	$t->set_var('lang_take_actions',lang('Then take these actions'));

	$t->set_var('lang_keep',lang('Keep'));
	$t->set_var('lang_discard',lang('Discard'));
	$t->set_var('lang_reject',lang('Reject'));
	$t->set_var('lang_redirect',lang('Redirect'));
	$t->set_var('lang_fileinto',lang('File into'));
	
	$t->set_var('lang_or_enter_text',lang('or enter text'));	


	// ---  initially there will be 2 action rows  ---
	// Note: at this point, i do NOT see how Sieve allows multiple actions,
	// but the Mulburry screenshots show multiple action rows in their docs
	$num_actionrows = 2;
	for ($i=0; $i < $num_actionrows; $i++)
	{
		$action_rownum = (string)$i;
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
			'on_change'		=> '',
			'first_line_txt'	=> lang('if fileto then select destination folder')
		);
		// get you custom built HTML listbox (a.k.a. selectbox) widget
		$t->set_var('folder_listbox', $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args));
		
		// --- Action Textbox ---
		// if the textbox has existing data, it gets filled here
		$action_textbox_txt = '';
		$t->set_var('action_textbox_txt',$action_textbox_txt);
		// FIXME: check the checkbox "STOP" value depending on the data from the database
		$t->set_var('action_rownum',$action_rownum);
		$t->parse('V_actions_row','B_actions_row',True);	
	}


	$t->set_var('lang_more_actions',lang('More Actions'));
	$t->set_var('lang_fewer_actions',lang('Fewer Actions'));
	$t->set_var('lang_stop_if_matched',lang('Stop if Matched'));

	$t->set_var('lang_submit',lang('Submit'));
	$t->set_var('lang_clear',lang('Clear'));
	$t->set_var('lang_cancel',lang('Cancel'));


	$t->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
	$t->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
	$t->set_var('row_text',$GLOBALS['phpgw_info']['theme']['row_text']);


	$t->pparse('out','T_filters_out');

	$GLOBALS['phpgw']->msg->end_request();

	$GLOBALS['phpgw']->common->phpgw_footer();
?>