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

	if (($phpgw->msg->args['action'] == 'create')
	|| ($phpgw->msg->args['action'] == 'delete')
	|| ($phpgw->msg->args['action'] == 'rename')
	|| ($phpgw->msg->args['action'] == 'create_expert')
	|| ($phpgw->msg->args['action'] == 'delete_expert')
	|| ($phpgw->msg->args['action'] == 'rename_expert'))
	{

		// basic sanity check
		if ((!isset($phpgw->msg->args['target_folder'])
		|| ($phpgw->msg->args['target_folder'] == '')))
		{
			// Result Message
			$action_report = lang('Please type a folder name in the text box');
		}
		elseif ( (($phpgw->msg->args['action'] == 'rename')
		  || ($phpgw->msg->args['action'] == 'rename_expert'))
		&& ((!isset($phpgw->msg->args['source_folder']))
		  || ($phpgw->msg->args['source_folder'] == '')) )
		{
			// Result Message
			$action_report = lang('Please select a folder to rename');
		}
		else
		{
			// get rid of the escape \ that magic_quotes HTTP POST will add
			// " becomes \" and  '  becomes  \'  and  \  becomes \\
			$phpgw->msg->args['target_folder'] = $phpgw->msg->stripslashes_gpc($phpgw->msg->args['target_folder']);
			// == is that necessary ? == are folder names allowed with '  "  \  in them ? ===
			// rfc2060 does NOT prohibit them

			// obtain propper folder names
			// if this is a delete, the folder name will (should) already exist
			// although the user had to type in the folder name
			// for these actions,  the "expert" tag means:
			// "do not add the name space for me, I'm an expert and I know what I'm doing"
			if (($phpgw->msg->args['action'] == 'create_expert')
			|| ($phpgw->msg->args['action'] == 'delete_expert')
			|| ($phpgw->msg->args['action'] == 'rename_expert'))
			{
				// other than stripslashes_gpc,  do nothing
				// the user is an expert, do not alter the phpgw->msg->args['target_folder'] name at all
			}
			else
			{
				// since the user is not an "expert", we properly prepare the folder name
				// see if the folder already exists in the folder lookup list
				// this would be the case if the user is deleting a folder
				$target_lookup = $phpgw->msg->folder_lookup('', $phpgw->msg->args['target_folder']);
				if ($target_lookup != '')
				{
					// phpgw->msg->args['target_folder'] returned an official long name from the lookup
					$phpgw->msg->args['target_folder'] = $target_lookup;
				}
				else
				{
					// the lookup failed, so this is not an existing folder
					// we have to add the namespace for the user
					$phpgw->msg->args['target_folder'] = $phpgw->msg->get_folder_long($phpgw->msg->args['target_folder']);
				}
			}
	
	

			// =====  NOTE:  maybe some "are you sure" code ????  =====
		
			if (($phpgw->msg->args['action'] == 'create')
			|| ($phpgw->msg->args['action'] == 'create_expert'))
			{
				$success = $phpgw->dcom->createmailbox($phpgw->msg->mailsvr_stream, $server_str.$phpgw->msg->args['target_folder']);
			}
			elseif (($phpgw->msg->args['action'] == 'delete')
			|| ($phpgw->msg->args['action'] == 'delete_expert'))
			{
				$success = $phpgw->dcom->deletemailbox($phpgw->msg->mailsvr_stream, $server_str.$phpgw->msg->args['target_folder']);
			}
			elseif (($phpgw->msg->args['action'] == 'rename')
			|| ($phpgw->msg->args['action'] == 'rename_expert'))
			{
				// phpgw->msg->args['source_folder'] is taken directly from the listbox, so it *should* be official long name already
				// but it does need to be prep'd in because we prep out the foldernames put in that listbox
				$phpgw->msg->args['source_folder'] = $phpgw->msg->prep_folder_in($phpgw->msg->args['source_folder']);
				$success = $phpgw->dcom->renamemailbox($phpgw->msg->mailsvr_stream, $server_str.$phpgw->msg->args['source_folder'], $server_str.$phpgw->msg->args['target_folder']);
			}

			// Result Message
			if (($phpgw->msg->args['action'] == 'rename')
			|| ($phpgw->msg->args['action'] == 'rename_expert'))
			{
				$action_report =
					$phpgw->msg->args['action'] .' '.lang('folder').' '.$phpgw->msg->args['source_folder']
					.' '.lang('to').' '.$phpgw->msg->args['target_folder'] .' <br>'
					.lang('result').' : ';
			}
			else
			{
				$action_report = $phpgw->msg->args['action'].' '.lang('folder').' '.$phpgw->msg->args['target_folder'].' <br>'
				.lang('result').' : ';
			}
			// did it work or not
			if ($success)
			{
				// assemble some feedback to show
				$action_report = $action_report .lang('OK');
			}
			else
			{
				$imap_err = imap_last_error();
				if ($imap_err == '')
				{
					$imap_err = lang('unknown error');
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

		if ((isset($phpgw->msg->args['show_long']))
		&& ($phpgw->msg->args['show_long'] != ''))
		{
			$t->set_var('folder_name',$folder_long);
		}
		else
		{
			$t->set_var('folder_name',$folder_short);
		}
		//$t->set_var('folder_name',$folder_list[$i]["folder_long"]);
		//$t->set_var('folder_name',$phpgw->msg->htmlspecialchars_encode($folder_long));

		$t->set_var('msgs_unseen',$mailbox_status->unseen);
		//$t->set_var('msgs_total',$total_msgs);
		$t->set_var('msgs_total',$mailbox_status->messages);
		$t->parse('V_folder_list','B_folder_list',True);
	}

// ----  Set Up Form Variables  ---
	$t->set_var('form_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php'));
	$t->set_var('all_folders_listbox',$phpgw->msg->all_folders_listbox('','','',False));
	$t->set_var('select_name_rename','source_folder');

	$t->set_var('select_txt_rename',lang('choose for rename'));
	$t->set_var('form_create_txt',lang('Create a folder'));
	$t->set_var('form_delete_txt',lang('Delete a folder'));
	$t->set_var('form_rename_txt',lang('Rename a folder'));
	$t->set_var('form_create_expert_txt',lang('Create (expert)'));
	$t->set_var('form_delete_expert_txt',lang('Delete (expert)'));
	$t->set_var('form_rename_expert_txt',lang('Rename (expert)'));
	$t->set_var('form_submit_txt',lang("submit"));

// ----  Set Up Other Variables  ---	
	$t->set_var('title_backcolor',$phpgw_info['theme']['em_folder']);
	$t->set_var('title_textcolor',$phpgw_info['theme']['em_folder_text']);
	$t->set_var('title_text',lang('Folder Maintenance'));
	$t->set_var('label_name_text',lang('Folder name'));
	$t->set_var('label_messages_text',lang('Messages'));

	$t->set_var('view_long_txt',lang('long names'));
	$t->set_var('view_long_lnk',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php?show_long=1'));
	$t->set_var('view_short_txt',lang('short names'));
	$t->set_var('view_short_lnk',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php'));

	$t->set_var('the_font',$phpgw_info['theme']['font']);
	$t->set_var('th_backcolor',$phpgw_info['theme']['th_bg']);

	$t->pparse('out','T_folder_out');

	$phpgw->msg->end_request();

	$phpgw->common->phpgw_footer();
?>
