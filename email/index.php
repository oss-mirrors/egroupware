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

	if(empty($folder)){ $folder="INBOX"; }

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

	@set_time_limit(0);
	
	$t = new Template($phpgw->common->get_tpl_dir('email'));
	$t->set_file(array(		
		'T_any_deleted' => 'index_any_deleted.tpl',
		'T_form_delmov_init' => 'index_form_delmov_init.tpl',
		'T_no_messages' => 'index_no_messages.tpl',
		'T_attach_clip' => 'index_attach_clip.tpl',
		'T_new_msg' => 'index_new_msg.tpl',
		'T_msg_list' => 'index_msg_list.tpl',
		'index_out' => 'index.tpl',
	));
	
// ---- lang var for checkbox javascript  -----
	$t->set_var('select_msg',lang('Please select a message first'));

// ---- report on number of messages deleted (if any)  -----
	if ($td)
	{
		if ($td == 1) 
		{
			$num_deleted = lang("1 message has been deleted",$td);
		} else {
			$num_deleted = lang("x messages have been deleted",$td);
		}
		// template only outputs if msgs were deleted, otherwise skipped
		$t->set_var('num_deleted',$num_deleted);
		$t->parse('V_any_deleted','T_any_deleted',True);
	}
	else
	{
		// nothing deleted, so template gets blank string
		$t->set_var('V_any_deleted',' ');
	}
	
// ----  Previous and Next arrows navigation  -----
	$nummsg = $phpgw->msg->num_msg($mailbox);
	if (! $start)
	{
		$start = 0;
	}
	
	$td_prev_arrows = $phpgw->nextmatchs->left('/'.$phpgw_info['flags']['currentapp'].'/index.php',
					$start,$nummsg,
					'&sort=' .$sort .'&order=' .$order .'&folder=' .urlencode($folder));

	$td_next_arrows = $phpgw->nextmatchs->right('/'.$phpgw_info['flags']['currentapp'].'/index.php',
					$start,$nummsg,
					'&sort=' .$sort .'&order=' .$order .'&folder=' .urlencode($folder));

	$t->set_var('arrows_backcolor',$phpgw_info['theme']['bg_color']);
	$t->set_var('prev_arrows',$td_prev_arrows);
	$t->set_var('next_arrows',$td_next_arrows);

// ---- Message Folder Stats   -----
	if ($sort == "ASC") {
		$oursort = 0;
	} else {
		$oursort = 1;
	}

	if (!isset($order))
	{
		// Only do this on first visit to the app, where order should depend on prefs date order
		$order = 0;
		if ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old") 
		{
			$oursort = 1;
		} else {
			$oursort = 0;
		}
	}

	$mailbox_info = $phpgw->msg->mailboxmsginfo($mailbox);

	if ($folder != "INBOX")
	{
		$t_folder_s = $phpgw->msg->construct_folder_str($folder);
	} else {
		$t_folder_s = "INBOX";
	}
	
	if ($phpgw_info['user']['preferences']['email']['mail_server_type']=='imaps')
	{
 		/* IMAP over SSL */
		$mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . "/ssl/novalidate-cert:993}$t_folder_s", SA_UNSEEN);
	} else {
		/* No SSL, normal connection */
		$mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":". $phpgw_info["user"]["preferences"]["email"]["mail_port"] ."}$t_folder_s",SA_UNSEEN);
	}

	if ($nummsg > 0) 
	{
		$msg_array = array();
		// Note: sorting on email is on address, not displayed name per php imap_sort
		//echo "<br>SORT GOT: column '$order', '$oursort'.";
		$msg_array = $phpgw->msg->sort($mailbox, $order, $oursort);
		$stats_new = $mailbox_status->unseen;
		$ksize = round(10*($mailbox_info->Size/1024))/10;
	} else {
		$stats_new = '-';
		$ksize = '-';
	}

// ---- SwitchTo Folder Listbox   -----
	if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" 
	  || $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imaps" 
	  || $phpgw_info["flags"]["newsmode"])
	{
		$switchbox_listbox = '<select name="folder" onChange="document.switchbox.submit()">'
				. '<option>' . lang('switch current folder to') . ':'
				//. list_folders($mailbox,$folder,False)
				. list_folders($mailbox,'',False)
				. '</select>';
	} else {
		$switchbox_listbox = '&nbsp';
	}

// ---- Folder Button  -----
	if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" 
	  || $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imaps" )
	{
		$folder_maint_button = '<input type="button" value="' . lang("folder") . '" onClick="'
				. 'window.location=\'' . $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php',
				'folder=' .urlencode($folder)) . '\'">';
	} else {
		$folder_maint_button = '&nbsp';
	}

	$t->set_var('stats_backcolor',$phpgw_info['theme']['em_folder']);
	$t->set_var('stats_font',$phpgw_info['theme']['font']);
	$t->set_var('stats_color',$phpgw_info['theme']['em_folder_text']);
	$t->set_var('stats_folder',$folder);
	$t->set_var('stats_saved',$nummsg);
	$t->set_var('stats_new',$stats_new);
	$t->set_var('stats_size',$ksize);	
	$t->set_var('switchbox_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php'));
	$t->set_var('switchbox_listbox',$switchbox_listbox);
	$t->set_var('folder_maint_button',$folder_maint_button);

// ----  Messages List Table Headers  -----
	  /*
	     Sorting defs:
	     SORTDATE:  0
	     SORTFROM:  2
	     SORTSUBJECT: 3
	     SORTSIZE:  6
	  */
	if ($newsmode == "on")
	{
		$sizesort = lang("lines");
	} else {
		$sizesort = lang("size");
	}
	
	$t->set_var('hdr_backcolor',$phpgw_info["theme"]["th_bg"]);
	$t->set_var('hdr_font',$phpgw_info['theme']['font']);
	$t->set_var('hdr_subject',$phpgw->nextmatchs->show_sort_order($sort,"3",$order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("subject"),"&folder=".urlencode($folder)) );
	$t->set_var('hdr_from',$phpgw->nextmatchs->show_sort_order($sort,"2",$order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("from"),"&folder=".urlencode($folder)) );
	$t->set_var('hdr_date',$phpgw->nextmatchs->show_sort_order($sort,"0",$order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("date"),"&folder=".urlencode($folder)) );
	$t->set_var('hdr_size',$phpgw->nextmatchs->show_sort_order($sort,"6",$order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$sizesort) );

// ----  Form delmov Intialization  Setup  -----
	// ----  place in first checkbox cell of the messages list table, ONE TIME ONLY   -----
	$t->set_var('delmov_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/action.php'));
	$t->set_var('current_folder',$folder);
	$t->parse('V_form_delmov_init','T_form_delmov_init');
	$mlist_delmov_init = $t->get_var('V_form_delmov_init');	

// ----  New Message Indicator   -----
	$t->set_var('mlist_newmsg_char','*');
	$t->set_var('mlist_newmsg_color','#ff0000');
	$t->set_var('mlist_newmsg_txt',lang("New message"));

// ----  Init Vars Used In Messages List  -----
	$t->set_var('mlist_font',$phpgw_info['theme']['font']);
	$t->set_var('images_dir',$phpgw_info['server']['images_dir']);
	// prepare attachment paperclip image
	$t->parse('V_attach_clip','T_attach_clip');
	$mlist_attach = $t->get_var('V_attach_clip');
	// prepare new message character
	$t->parse('V_new_msg','T_new_msg');
	$mlist_new_msg = $t->get_var('V_new_msg');
	// initialize
	$t->set_var('V_msg_list',' ');

// ----  Zero Messages To List  -----
	if ($nummsg == 0)
	{
		if (!$mailbox)
		{
			$report_no_msgs = lang("Could not open this mailbox");
		}
		else
		{
			$report_no_msgs = lang("this folder is empty");
		}
		// no messages to display, msgs list is just one row reporting this
		$t->set_var('report_no_msgs',$report_no_msgs);
		$t->set_var('mlist_delmov_init',$mlist_delmov_init);
		$t->set_var('mlist_backcolor',$phpgw_info["theme"]["row_on"]);
		// big Mr. Message List is just one row in this case
		$t->parse('V_msg_list','T_no_messages');
        }
// ----  Fill The Messages List  -----
	else
	{
		if ($nummsg < $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
		{
			$totaltodisplay = $nummsg;
		}
		else if (($nummsg - $start) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
		{
			$totaltodisplay = $start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
		}
		else
		{
			$totaltodisplay = $nummsg;
		}

		for ($i=$start; $i < $totaltodisplay; $i++)
		{
			// place the delmov form header tags ONLY ONCE, blank string all subsequent loops
			$do_init_form = ($i == $start);

			// ROW BACK COLOR
			$bg = (($i + 1)/2 == floor(($i + 1)/2)) ? $phpgw_info["theme"]["row_off"] : $phpgw_info["theme"]["row_on"];

			$struct = $phpgw->msg->fetchstructure($mailbox, $msg_array[$i]);

			// SHOW ATTACHMENT CLIP ?
			$show_attach = False; // fallback value
			if (count($struct->parts) == 0)
			{
				// no attachment, no paperclip image
				$show_attach = False;
			}
			else
			// show paperclip image indicating attachment(s)
			{
				for ($j = 0; $j< (count($struct->parts) - 1); $j++)
				{
					if (!$struct->parts[$j])
					{
						$part = $struct;
					}
					else
					{
						$part = $struct->parts[$j];
					}

					$att_name = get_att_name($part);
					if ($att_name != "Unknown")
					{
						$show_attach = True;
					}
				}
			}

			$msg = $phpgw->msg->header($mailbox, $msg_array[$i]);
			
			// MESSAGE REFERENCE NUMBER
			$mlist_msg_num = $msg_array[$i];

			// SUBJECT
			$subject = !$msg->Subject ? "[".lang("no subject")."]" : $msg->Subject;
			$subject = decode_header_string($subject);
			$subject_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php','folder='.urlencode($folder).'&msgnum='.$mlist_msg_num);

			// SIZE
			if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"])
			{
				$size = $msg->Size;
			}
			else
			{
				$ksize = round(10*($msg->Size/1024))/10;
				$size = $msg->Size > 1024 ? "$ksize k" : $msg->Size;
			}

			// SEEN OR UNSEEN/NEW
			if (($msg->Unseen == "U") || ($msg->Recent == "N"))
			{
				$show_newmsg = True;
			}
			else
			{
				$show_newmsg = False;
			}

			// FROM and REPLY TO  LINK
			if ($msg->reply_to[0])
			{
				$reply   = $msg->reply_to[0];
			}
			else
			{
				$reply   = $msg->from[0];
			}

			$replyto = $reply->mailbox . "@" . $reply->host;

			$from = $msg->from[0];
			// what does this do
			$personal = !$from->personal ? "$from->mailbox@$from->host" : $from->personal;
			if ($personal == "@")
			{
				$personal = $replyto;
			}
			if ($phpgw_info['user']['preferences']['email']['show_addresses'] == 'from' && ($personal != "$from->mailbox@$from->host"))
			{
				$display_address->from = "($from->mailbox@$from->host)";
			}
			elseif ($phpgw_info["user"]["preferences"]["email"]["show_addresses"] == "replyto" && ($personal != $replyto))
			{
				$display_address->from = "($replyto)";
			}
			else
			{
				$display_address->from = "";
			}

			$from_link = $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='
					.urlencode($folder) .'&to=' .urlencode($replyto));
			$from_name = decode_header_string($personal);
			//echo "$display_address->from";

			// DATE
			$msg_date = $phpgw->common->show_date($msg->udate);

		// set up vars for the parsing
		if ($do_init_form)
		{
			$t->set_var('mlist_delmov_init',$mlist_delmov_init);
		}
		else
		{
			$t->set_var('mlist_delmov_init', '');
		}
		if ($show_newmsg)
		{
			$t->set_var('mlist_new_msg',$mlist_new_msg);
		}
		else
		{
			$t->set_var('mlist_new_msg','');
		}
		if ($show_attach)
		{
			$t->set_var('mlist_attach',$mlist_attach);
		}
		else
		{
			$t->set_var('mlist_attach','');
		}
		$t->set_var('mlist_msg_num',$mlist_msg_num);
		$t->set_var('mlist_backcolor',$bg);
		$t->set_var('mlist_subject',$subject);
		$t->set_var('mlist_subject_link',$subject_link);		
		$t->set_var('mlist_from',$from_name);
		$t->set_var('mlist_reply_link',$from_link);
		$t->set_var('mlist_date',$msg_date);
		$t->set_var('mlist_size',$size);
		$t->parse('V_msg_list','T_msg_list',True);
		// end iterating through the messages to display
		}
	}

// ---- Delete/Move Folder Listbox  for Msg Table Footer -----
	if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap"
	  || $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imaps")
	{
		$delmov_listbox = '<select name="tofolder" onChange="do_action(\'move\')">'
			. '<option>' . lang("move selected messages into") . ':'
			. list_folders($mailbox,'',False)
			. '</select>';
            
	}
	else
	{
		$delmov_listbox = '&nbsp;';
	}
	
// ----  Messages List Table Footer  -----
	$t->set_var('app_images',$phpgw_info['server']['app_images']);
	$t->set_var('ftr_backcolor',$phpgw_info['theme']['th_bg']);
	$t->set_var('ftr_font',$phpgw_info['theme']['font']);
	$t->set_var('ftr_compose_txt',lang("compose"));
	$t->set_var('ftr_compose_link',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',"folder=".urlencode($folder)));
	$t->set_var('delmov_button',lang("delete"));
	$t->set_var('delmov_listbox',$delmov_listbox);
	
// ----  Output the Template   -----
	$t->pparse('out','index_out');

	$phpgw->msg->close($mailbox);

	$phpgw->common->phpgw_footer();
?>
