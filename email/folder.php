<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */
	
	if(empty($folder))
	{
		$folder='INBOX';
	}

	Header("Cache-Control: no-cache");
	Header("Pragma: no-cache");
	Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
  
	$phpgw_info["flags"] = array(
		'currentapp' => 'email', 
		'enable_network_class' => True, 
		'enable_nextmatchs_class' => True);
	
	if (isset($newsmode) && $newsmode == "on")
	{
		$phpgw_info['flags']['newsmode'] = True;
	}
	
	include("../header.inc.php");

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_folder_out' => 'folder.tpl'
	));

	$t->set_block('T_folder_out','B_folder_list','V_folder_list');
	$t->set_block('T_folder_out','B_action_report','V_action_report');

//  ----  Establish Email Server Connectivity Conventions  ----
	$server_str = get_mailsvr_callstr();
	$namespace_filter = get_mailsvr_namespace();
	$folder_long = get_folder_long($folder);
	$folder_short = get_folder_short($folder);


	/*
	//$full_str = $server_str .$folder_long;
	//$folder_short = get_folder_short($full_str);
	$t->set_var('debug_server_str',$server_str .$folder_long);
	$t->set_var('debug_filter',$namespace_filter);
	$t->set_var('debug_folder',$folder);
	$t->set_var('debug_folder_long',$folder_long);
	$t->set_var('debug_folder_short',$folder_short);
	*/

// ----  Create or Delete A Folder  ----
	if (($action == 'create') || ($action == 'delete'))
	{
		// maybe some "are you sure" code
		$folder_long = get_folder_long($target_folder);
		$folder_short = get_folder_short($target_folder);
		
		if ($action == 'create')
		{
			$phpgw->msg->createmailbox($mailbox, "$server_str"."$folder_long");
		}
		else if ($action == 'delete')
		{
			$phpgw->msg->deletemailbox($mailbox, "$server_str"."$folder_long");
		}

		// Result Message
		$action_report = $action .' folder "' .$folder_short .'": ';
		$imap_err = imap_last_error();
		if ($imap_err == '')
		{
			$action_report = $action_report .' OK';
		}
		else
		{
			$action_report = $action_report .$imap_err;
		}
		$t->set_var('action_report',$action_report);
		$t->parse('V_action_report','B_action_report');
	}
	else
	{
		$t->set_var('V_action_report','');
	}

	$mailboxes = $phpgw->msg->listmailbox($mailbox, $server_str, $namespace_filter .'*');

	// sort folder names 
	if (gettype($mailboxes) == 'array')
	{
		sort($mailboxes);
	}
	
	if ($mailboxes)
	{
		for ($i=0; $i<count($mailboxes);$i++)
		{
			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

			$phpgw->msg->reopen($mailbox, $mailboxes[$i]);
			$folder_long = get_folder_long($mailboxes[$i]);
			$folder_short = get_folder_short($mailboxes[$i]);

			$mailbox_status = $phpgw->msg->status($mailbox,$server_str .$folder_long,SA_UNSEEN);

			$t->set_var('list_backcolor',$tr_color);
			$t->set_var('folder_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder=' .urlencode($folder_short)));
			$t->set_var('folder_name',$folder_short);
			$t->set_var('msgs_unseen',$mailbox_status->unseen);
			$t->set_var('msgs_total',$phpgw->msg->num_msg($mailbox));
			$t->parse('V_folder_list','B_folder_list',True);
		}
	}
	else
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

		$mailbox_status = $phpgw->msg->status($mailbox,$server_str.'INBOX',SA_UNSEEN);

		$t->set_var('list_backcolor',$tr_color);
		$t->set_var('folder_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder=INBOX'));
		$t->set_var('folder_name','INBOX');
		$t->set_var('msgs_unseen',$mailbox_status->unseen);
		$t->set_var('msgs_total',$phpgw->msg->num_msg($mailbox));
		$t->parse('V_folder_list','B_folder_list',True);
	}

// ----  Set Up Form Variables  ---
	$t->set_var('form_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php'));
	// FIXME  needs lang
	$t->set_var('form_create_txt','Create a folder');
	$t->set_var('form_delete_txt','Delete a folder');
	$t->set_var('form_submit_txt',lang("submit"));

// ----  Set Up Other Variables  ---	
	$t->set_var('title_backcolor',$phpgw_info['theme']['em_folder']);
	$t->set_var('title_textcolor',$phpgw_info['theme']['em_folder_text']);
	// FIXME  needs lang
	$t->set_var('title_text','Folder Maintenance');
	$t->set_var('the_font',$phpgw_info['theme']['font']);
	$t->set_var('th_backcolor',$phpgw_info['theme']['th_bg']);
	
	

	$t->pparse('out','T_folder_out');

	$phpgw->msg->close($mailbox);

	$phpgw->common->phpgw_footer();
?>
