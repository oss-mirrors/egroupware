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
	$server_str = $phpgw->msg->get_mailsvr_callstr();
	$name_space = $phpgw->msg->get_mailsvr_namespace();
	$dot_or_slash = $phpgw->msg->get_mailsvr_delimiter();

	$folder_long = $phpgw->msg->get_folder_long($folder);
	$folder_short = $phpgw->msg->get_folder_short($folder);
	/*
	//$full_str = $server_str .$folder_long;
	//$folder_short = $phpgw->msg->get_folder_short($full_str);
	$t->set_var('debug_server_str',$server_str .$folder_long);
	$t->set_var('debug_namespace',$phpgw->msg->get_mailsvr_namespace());
	$t->set_var('debug_delimiter',$phpgw->msg->get_mailsvr_delimiter());
	$t->set_var('debug_folder',$folder);
	$t->set_var('debug_folder_long',$folder_long);
	$t->set_var('debug_folder_short',$folder_short);
	*/

// ----  Create or Delete A Folder  ----
	if (($action == 'create') || ($action == 'delete'))
	{
		// basic sanity check
		if ($target_folder == '')
		{
			// Result Message
			// FIXME needs lang
			$action_report = 'Please type a folder name in the text box';		
		}
		else
		{
			// maybe some "are you sure" code
			$folder_long = $phpgw->msg->get_folder_long($target_folder);
			$folder_short = $phpgw->msg->get_folder_short($target_folder);
		
			if ($action == 'create')
			{
				$phpgw->dcom->createmailbox($mailbox, "$server_str"."$folder_long");
			}
			else if ($action == 'delete')
			{
				$phpgw->dcom->deletemailbox($mailbox, "$server_str"."$folder_long");
			}

			// Result Message
			$action_report = $action .' folder "' .$folder_short .'": ';
			$imap_err = imap_last_error();
			if ($imap_err == '')
			{
				$action_report = $action_report .'OK';
			}
			else
			{
				$action_report = $action_report .$imap_err;
			}
		}
		$t->set_var('action_report',$action_report);
		$t->parse('V_action_report','B_action_report');
	}
	else
	{
		$t->set_var('V_action_report','');
	}


// ----  Get a List Of All Folders  ----
	if ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
	{
		// last arg may be "mail/*" which will NOT list the INBOX with the folder list
		// however, we have no choice since w/o the delimiter "email*" we get NOTHING
		$mailboxes = $phpgw->dcom->listmailbox($mailbox, $server_str, "$name_space" ."$dot_or_slash" ."*");
	}
	else
	{
		// the last arg is typically "INBOX*" which will include the inbox in the list of folders
		// wheres adding the delimiter "INBOX.*" will NOT include the INBOX in the list of folders
		// so - it's safe to include the delimiter here, but the INBOX will not be included in the list
		// this is typically the ONLY TIME you would ever *not* use the delimiter between the namespace and what comes after it
		$mailboxes = $phpgw->dcom->listmailbox($mailbox, $server_str, "$name_space" ."*");
	}

	// sort folder names 
	if (gettype($mailboxes) == 'array')
	{
		sort($mailboxes);
	}
	
	if ($mailboxes)
	{
		for ($i=0; $i<count($mailboxes);$i++)
		{
			/*
			if (($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
			&& (strstr($mailboxes[$i],"/.")) )
			{
				// {serverstring}~/. indicates this is a hidden file in the users home directory
				// $server_str."/."
				// any pattern matching "/." for UWash is NOT an MBOX
				// DO NOTHING - this is not an MBOX file
			}
			else
			*/
			if ($phpgw->msg->is_imap_folder($mailboxes[$i]))
			{
				$folder_long = $phpgw->msg->get_folder_long($mailboxes[$i]);
				$folder_short = $phpgw->msg->get_folder_short($mailboxes[$i]);
				$phpgw->dcom->reopen($mailbox, $mailboxes[$i]);
				$mailbox_status = $phpgw->dcom->status($mailbox,$server_str .$folder_long,SA_UNSEEN);

				$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
				$t->set_var('list_backcolor',$tr_color);
				$t->set_var('folder_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder=' .urlencode($folder_short)));
				$t->set_var('folder_name',$folder_short);
				$t->set_var('msgs_unseen',$mailbox_status->unseen);
				$t->set_var('msgs_total',$phpgw->dcom->num_msg($mailbox));
				$t->parse('V_folder_list','B_folder_list',True);
			}
		}
	}
	else
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

		$mailbox_status = $phpgw->dcom->status($mailbox,$server_str.'INBOX',SA_UNSEEN);

		$t->set_var('list_backcolor',$tr_color);
		$t->set_var('folder_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder=INBOX'));
		$t->set_var('folder_name','INBOX');
		$t->set_var('msgs_unseen',$mailbox_status->unseen);
		$t->set_var('msgs_total',$phpgw->dcom->num_msg($mailbox));
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

	$phpgw->dcom->close($mailbox);

	$phpgw->common->phpgw_footer();
?>
