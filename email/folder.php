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

	if (($action == 'create') || ($action == 'delete') || ($action == 'rename')
	|| ($action == 'create_expert') || ($action == 'delete_expert') || ($action == 'rename_expert'))
	{

		// basic sanity check
		if ((!isset($target_folder) || ($target_folder == '')))
		{
			// Result Message
			// FIXME needs lang
			$action_report = 'Please type a folder name in the text box';		
		}
		elseif ( (($action == 'rename') || ($action == 'rename_expert'))
		&& ((!isset($source_folder)) || ($source_folder == '')) )
		{
			// Result Message
			// FIXME needs lang
			$action_report = 'Please select a folder to rename';
		}
		else
		{
			// obtain propper folder names
			// if this is a delete, the folder name will (should) already exist
			// although the user had to type in the folder name
			// for these actions,  the "expert" tag means:
			// "do not add the name space for me, I'm an expert and I know what I'm doing"
			if (($action == 'create_expert') || ($action == 'delete_expert') || ($action == 'rename_expert'))
			{
				//$target_folder_long = $target_folder;
				// do nothing, the user is an expert, do not alter the target_folder name at all
			}
			else
			{
				// since the user is not an "expert", we properly prepare the folder name
				// see if the folder already exists in the folder lookup list
				$target_lookup = $phpgw->msg->folder_lookup('', $target_folder);
				if ($target_lookup != '')
				{
					// target_folder returned an official long name from the lookup
					$target_folder = $target_lookup;
				}
				else
				{
					// the lookup failed, so this is not an existing folder
					// we have to add the namespace for the user
					$target_folder = $phpgw->msg->get_folder_long($target_folder);
				}
			}
	
	

			// =====  NOTE:  maybe some "are you sure" code ????  =====
		
			if (($action == 'create') || ($action == 'create_expert'))
			{
				$success = $phpgw->dcom->createmailbox($phpgw->msg->mailsvr_stream, "$server_str"."$target_folder");
			}
			else if (($action == 'delete') || ($action == 'delete_expert'))
			{
				$success = $phpgw->dcom->deletemailbox($phpgw->msg->mailsvr_stream, "$server_str"."$target_folder");
			}
			else if (($action == 'rename') || ($action == 'rename_expert'))
			{
				// source_folder is taken directly from the listbox, so it *should* be official long name already
				// but it does need to be prep'd in because we prep out the foldernames put in that listbox
				$source_folder = $phpgw->msg->prep_folder_in($source_folder);
				$success = $phpgw->dcom->renamemailbox($phpgw->msg->mailsvr_stream, "$server_str"."$source_folder", "$server_str"."$target_folder");
			}

			// Result Message
			if (($action == 'rename') || ($action == 'rename_expert'))
			{
				$action_report = $action .' folder "' .$source_folder .'" to "' .$target_folder .'" <br>';
			}
			else
			{
				$action_report = $action .' folder "' .$target_folder .'" <br>';
			}
			// did it work or not
			if ($success)
			{
				// assemble some feedback to show
				$action_report = $action_report .'OK';
			}
			else
			{
				$imap_err = imap_last_error();
				if ($imap_err == '')
				{
					// NEEDS LANG
					$imap_err = 'unknown error';
				}
				// assemble some feedback to show the user about this error
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

// ----  Set Up Selectbox  ---


// ----  Set Up Form Variables  ---
	$t->set_var('form_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php'));
	$t->set_var('all_folders_listbox',$phpgw->msg->all_folders_listbox('','','',False));

	$t->set_var('select_name_rename','source_folder');

	// FIXME  needs lang
	$t->set_var('select_txt_rename',lang('choose to rename'));
	$t->set_var('form_create_txt','Create a folder');
	$t->set_var('form_delete_txt','Delete a folder');
	$t->set_var('form_rename_txt','Rename a folder');
	$t->set_var('form_create_expert_txt','Create (expert)');
	$t->set_var('form_delete_expert_txt','Delete (expert)');
	$t->set_var('form_rename_expert_txt','Rename (expert)');
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
