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
	
	Header("Cache-Control: no-cache");
	Header("Pragma: no-cache");
	Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
  
	$phpgw_info["flags"] = array(
		'currentapp' => 'email', 
		'enable_network_class' => True, 
		'enable_nextmatchs_class' => True);

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


// ----  Create or Delete A Folder  ----
	if ((isset($target_folder))
	&& ($target_folder != ''))
	{
		//$target_folder = $phpgw->msg->prep_folder_in($target_folder);
		// if this is a delete, the folder name will already exist
		$target_folder_long = $phpgw->msg->folder_lookup('', $target_folder);
		if ($target_folder_long != '')
		{
			$target_folder = $target_folder_long;
		}
		else
		{
			// we have to add the namespace for the user
			$target_folder = $phpgw->msg->get_folder_long($target_folder);
		}
	}

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
		
			if ($action == 'create')
			{
				//$phpgw->dcom->createmailbox($mailbox, "$server_str"."$folder_long");
				 $phpgw->dcom->createmailbox($phpgw->msg->mailsvr_stream, "$server_str"."$target_folder");
			}
			else if ($action == 'delete')
			{
				//$phpgw->dcom->deletemailbox($mailbox, "$server_str"."$folder_long");
				 $phpgw->dcom->deletemailbox($phpgw->msg->mailsvr_stream, "$server_str"."$target_folder");
			}

			// Result Message
			$action_report = $action .' folder "' .$target_folder .'": ';
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
	$folder_list = $phpgw->msg->get_folder_list('');

	for ($i=0; $i<count($folder_list);$i++)
	{
		$folder_long = $folder_list[$i]['folder_long'];
		$folder_short = $folder_list[$i]['folder_short'];

		// SA_ALL gets the stats for the number of:  messages, recent, unseen, uidnext, uidvalidity
		 $mailbox_status = $phpgw->dcom->status($phpgw->msg->mailsvr_stream,"$server_str"."$folder_long",SA_ALL);
		
		//debug
		//$real_long_name = $phpgw->msg->folder_lookup('',$folder_list[$i]['folder_short']);
		//if ($real_long_name != '')
		//{
		//	echo 'folder exists, official long name: '.$real_long_name.'<br>';
		//}

		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$t->set_var('list_backcolor',$tr_color);
		$t->set_var('folder_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php','folder=' .$phpgw->msg->prep_folder_out($folder_long)));
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

	$phpgw->msg->end_request();

	$phpgw->common->phpgw_footer();
?>
