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

	// urldecode folder especially needed to change "+" into spaces in folder names that have spaces
	$folder = urldecode($folder);
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


// ----  Get a List Of All Folders  AND Display them ----
	$folder_list = $phpgw->msg->get_folder_list($mailbox);

	for ($i=0; $i<count($folder_list);$i++)
	{
		$folder_long = $folder_list[$i]['folder_long'];
		$folder_short = $folder_list[$i]['folder_short'];
		// open this particular folder
		if (((count($folder_list)) == 1)
		&& ($folder_short == 'INBOX'))
		{
			// only difference here is that we do NOT REOPEN a stream to INBOX because
			// we already have an open stream to INBOX (it's the only folder in this case)
			// so DO NOTHING
		}
		else
		{
			// do we really have to do this?
			// TEST: elimnate reopen, see if it was really needed
			//$phpgw->dcom->reopen($mailbox, "$server_str"."$folder_long");
		}
		// get the stats ONLY for the number of new (unseen) messages
		//$mailbox_status = $phpgw->dcom->status($mailbox,$server_str .$folder_long,SA_UNSEEN);
		// $total_msgs = $phpgw->dcom->num_msg($mailbox)

		// SA_ALL gets the stats for the number of:  messages, recent, unseen, uidnext, uidvalidity
		$mailbox_status = $phpgw->dcom->status($mailbox,$server_str .$folder_long,SA_ALL);

		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$t->set_var('list_backcolor',$tr_color);
		$t->set_var('folder_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder=' .urlencode($folder_short)));
		$t->set_var('folder_name',$folder_short);
		//$t->set_var('folder_name',$folder_long);
		//$t->set_var('folder_name',$folder_list[$i]);
		//$t->set_var('folder_name',$phpgw->msg->htmlspecialchars_encode($folder_list[$i]));
		$t->set_var('msgs_unseen',$mailbox_status->unseen);
		//$t->set_var('msgs_total',$total_msgs);
		$t->set_var('msgs_total',$mailbox_status->messages);
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
	// FIXME  needs lang()
	$t->set_var('title_text','Folder Maintenance');
	$t->set_var('the_font',$phpgw_info['theme']['font']);
	$t->set_var('th_backcolor',$phpgw_info['theme']['th_bg']);
	
	$t->pparse('out','T_folder_out');

	$phpgw->dcom->close($mailbox);

	$phpgw->common->phpgw_footer();
?>
