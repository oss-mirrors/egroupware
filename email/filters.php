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
	
	// setup some form vars
	$form1_submit_btn_name = 'submit_filters';;
	$form1_action = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/filters.php');
	$form2_submit_btn_name = 'filerpage_cancel';
	$form2_action = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/filters.php');
	
	// make the filters object
	$GLOBALS['phpgw']->filter = CreateObject("email.mail_filters");
	$GLOBALS['phpgw']->filter->submit_flag = $form1_submit_btn_name;
	$GLOBALS['phpgw']->filter->distill_filter_args();
	$mlist_html = '';
	if (count($GLOBALS['phpgw']->filter->filters) > 0)
	{
		echo 'filters.php: attempting imap query on submited data<br>'."\r\n";
		//$GLOBALS['phpgw']->filter->sieve_to_imap_string();
		$GLOBALS['phpgw']->filter->do_imap_search();
		//echo 'message list print_r dump:<b><pre>'."\r\n"; print_r($GLOBALS['phpgw']->filter->result_set_mlist); echo '</pre><br><br>'."\r\n";
		$GLOBALS['phpgw']->filter->make_mlist_box();
		$mlist_html = 
			'<table border="0" cellpadding="4" cellspacing="1" width="90%" align="center">'."\r\n"
			.$GLOBALS['phpgw']->filter->finished_mlist."\r\n"
			.'</table>'."\r\n"
			.'<p>&nbsp;</p>'."\r\n"
			.$GLOBALS['phpgw']->filter->submit_mlist_to_class_form
			.'<p>&nbsp;</p>'."\r\n";

	}
	$t->set_var('V_mlist_html',$mlist_html);
	
	
	// DEBUGGING
	//echo 'filters.php: HTTP_POST_VARS dump:<b>'."\r\n"; var_dump($GLOBALS['HTTP_POST_VARS']); echo '<br><br>'."\r\n";
	// dump submitted data for inspection
	$show_data_dump = True;
	//$show_data_dump = False;
	if ($show_data_dump)
	{
		//raw HTTP_POST_VARS dump
		//echo 'filters.php: HTTP_POST_VARS print_r dump (a):<b><pre>'."\r\n"; print_r($GLOBALS['HTTP_POST_VARS']); echo '</pre><br><br>'."\r\n";
		
		if  ((isset($GLOBALS['HTTP_POST_VARS'][$form1_submit_btn_name]))
		&& ($GLOBALS['HTTP_POST_VARS'][$form1_submit_btn_name] != ''))
		{
			$data_dump_info = 'filters.php: filter data WAS submitted';
		}
		elseif  ((isset($GLOBALS['HTTP_POST_VARS'][$form2_submit_btn_name]))
		&& ($GLOBALS['HTTP_POST_VARS'][$form2_submit_btn_name] != ''))
		{
			$data_dump_info = 'filters.php: cancel button was pressed';
		}
		else
		{
			$data_dump_info = 'filters.php: NO filter data was submitted';
		}
	}
	else
	{
		$data_dump_info = 'filters.php: data dump not set';
	}
	$t->set_var('data_dump_info',$data_dump_info);


	$t->set_var('form1_action',$form1_action);
	$t->set_var('form1_submit_btn_name', $form1_submit_btn_name);
	$t->set_var('form2_action',$form2_action);
	$t->set_var('form2_submit_btn_name', $form2_submit_btn_name);

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

	$not_available_yet = ' &#040;NA&#041;';
	$t->set_var('lang_from',lang('From Address'));
	$t->set_var('lang_to',lang('To Address'));
	$t->set_var('lang_cc',lang('CC Address'));
	$t->set_var('lang_bcc',lang('Bcc Address'));
	$t->set_var('lang_recipient',lang('Recipient').$not_available_yet);
	$t->set_var('lang_sender',lang('Sender').$not_available_yet);
	$t->set_var('lang_subject',lang('Subject'));
	$t->set_var('lang_header',lang('Header Field').$not_available_yet);
	$t->set_var('lang_size_larger',lang('Size Larger Than'.$not_available_yet));
	$t->set_var('lang_size_smaller',lang('Size Smaller Than'.$not_available_yet));
	$t->set_var('lang_allmessages',lang('All Messages'.$not_available_yet));
	// I do NOT think Sieve lets you search the body - but I'm not sure
	$t->set_var('lang_body',lang('Body &#040;extended sieve&#041;'));

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
			// 1st row DOES have s "source folder" combobox
			// anything not specified will be replace with a default value if the function has one for that param
			//$source_folder_listbox_name = 'filter_'.$f_idx.'[match_'.(string)$i.'_source_folder]';
			$source_folder_listbox_name = 'filter_'.$f_idx.'[source_folder]';
			$feed_args = Array(
				'mailsvr_stream'	=> '',
				'pre_select_folder'	=> 'INBOX',
				'skip_folder'		=> '',
				'show_num_new'		=> False,
				'widget_name'		=> $source_folder_listbox_name,
				'on_change'		=> '',
				'first_line_txt'	=> lang('search this folder')
			);
			// get you custom built HTML listbox (a.k.a. selectbox) widget
			$t->set_var('source_folder_or_andor', $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args));
		}
		else
		{
			// 2nd row DOES have the and/or combo box
			$andor_select_name = 'filter_'.$f_idx.'[match_'.(string)$i.'_andor]';
			$lang_and = lang('And');
			$lang_or = lang('Or');
			$t->set_var('lang_and',$lang_and);
			$t->set_var('lang_or',$lang_or);
			// 2nd row does NOT have s "source folder" combobox
			$andor_select_widget = 
				'<select name="'.$andor_select_name.'">'."\r\n"
					.'<option value="or" selected>'.$lang_or.'</option>'."\r\n"
					.'<option value="and">'.$lang_and.'</option>'."\r\n"
				.'</select>'."\r\n";
			$t->set_var('source_folder_or_andor', $andor_select_widget);
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

	$t->pparse('out','T_filters_out');

	$GLOBALS['phpgw']->msg->end_request();

	$GLOBALS['phpgw']->common->phpgw_footer();
?>