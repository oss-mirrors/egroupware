<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$d1 = strtolower(substr($phpgw_info['server']['app_inc'],0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$phpgw->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = $phpgw->common->get_inc_dir('email');

	if ($phpgw_info['user']['preferences']['email']['mainscreen_showmail'] == True)
	{
		// ----  Create the base email Msg Class    -----
		$phpgw->msg = CreateObject("email.mail_msg");
		$args_array = Array();
		$args_array['folder'] = 'INBOX';
		$args_array['do_login'] = True;
		$phpgw->msg->begin_request($args_array);

		if (!$phpgw->msg->mailsvr_stream)
		{
			$error_msg = '<b>Mail error:</b> Can not open connection to mail server';
			echo "\r\n"
			.'<tr>'."\r\n"
				.'<td align="left">'."\r\n"
					.'<!-- start Mailbox info -->'."\r\n"
					.$error_msg."\r\n"
					.'<!-- ends Mailox info -->'."\r\n"
				.'</td>'."\r\n"
			.'</tr>'."\r\n";
			//$phpgw->common->phpgw_exit(True);
		}
		else
		{
			/*  class mail_msg "new_message_check()"
			  // this is the structure you will get
			  $inbox_data['is_imap'] boolean - pop3 server do not know what is "new" or not
		  	  $inbox_data['folder_checked'] string - the folder checked, as processed by the msg class
			  $inbox_data['alert_string'] string - what to show the user about this inbox check
			  $inbox_data['number_new'] integer - for IMAP is number "unseen"; for pop3 is number messages
			  $inbox_data['number_all'] integer - for IMAP and pop3 is total number messages in that inbox
			*/
			$inbox_data = Array();
			$inbox_data = $phpgw->msg->new_message_check();

			$title = '<font color="FFFFFF">'.lang('EMail').' '.$inbox_data['alert_string'].'</font>';

			$portalbox = CreateObject('phpgwapi.linkbox',Array($title,$phpgw_info['theme']['navbar_bg'],$phpgw_info['theme']['bg_color'],$phpgw_info['theme']['bg_color']));
			$portalbox->setvar('width',600);
			$portalbox->outerborderwidth = 0;
			$portalbox->header_background_image = $phpgw_info['server']['webserver_url'] . '/phpgwapi/templates/verdilak/images/bg_filler.gif';

			if($inbox_data['number_all'] >= 5)
			{
				$check_msgs = 5;
			}
			else
			{
				$check_msgs = $inbox_data['number_all'];
			}

			if ($inbox_data['number_all'] > 0)
			{
				$msg_nums_array = array();
				$msg_nums_array = $phpgw->msg->get_message_list();
			}
			for($i=0; $i<$check_msgs; $i++)
			{
				//$msg_headers = $phpgw->dcom->header($phpgw->msg->mailsvr_stream,$msg_nums_array[$i]);
				$msg_headers = $phpgw->msg->phpgw_header($msg_nums_array[$i]);
				$subject = $phpgw->msg->get_subject($msg_headers,'');
				if (strlen($subject) > 65)
				{
					$subject = substr($subject,0,65).' ...';
				}
				$portalbox->data[$i] = array($subject,$phpgw->link('/email/message.php','folder='.$phpgw->msg->prep_folder_out('').'&msgnum='.$msg_nums_array[$i]));
			}
			// ADD FOLDER LISTBOX TO HOME PAGE (Needs to be TEMPLATED)
			// Does This Mailbox Support Folders (i.e. more than just INBOX)?
			if ($phpgw->msg->get_mailsvr_supports_folders() == False)
			{
				$switchbox_tablerow = '';
			}
			else
			{
				// FUTURE: this will pick up the user option to show num unseen msgs in dropdown list
				//$listbox_show_unseen = True;
				$listbox_show_unseen = False;
				$switchbox_listbox = '<select name="folder" onChange="document.switchbox.submit()">'
						. '<option>' . lang('switch current folder to') . ':'
						. $phpgw->msg->all_folders_listbox('','','',$listbox_show_unseen)
						. '</select>';
				// make it another TR we can insert
				$switchbox_action = $phpgw->link('/email/index.php');
				$switchbox_tablerow = 
					'<tr>'."\r\n"
					.'<form name="switchbox" action="'.$switchbox_action.'" method="post">'."\r\n"
						.'<td align="left">'."\r\n"
							.'&nbsp;<strong>E-Mail Folders:</strong>&nbsp;'.$switchbox_listbox
						.'</td>'."\r\n"
					.'</form>'."\r\n"
					.'</tr>'."\r\n";
			}
			$phpgw->msg->end_request();
			// output the portalbox and (if applicable) the folders listbox below it
			echo '<!-- start Mailbox info -->'."\r\n"
			.'<tr>'."\r\n"
				.'<td align="left">'."\r\n"
					.$portalbox->draw()
				.'</td>'."\r\n"
			.'</tr>'."\r\n"
			.$switchbox_tablerow
			.'<!-- ends Mailox info -->'."\r\n";
		}
	}
?>
