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
		include($tmp_app_inc . '/functions.inc.php');
		if (! $mailbox)
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
			$server_str = $phpgw->msg->get_mailsvr_callstr();
			$mailbox_status = $phpgw->dcom->status($mailbox,$server_str .'INBOX',SA_UNSEEN);
			if ($mailbox_status->unseen == 1)
			{
				$num_new_str = ' - ' .lang('You have 1 new message!');
			}
			elseif ($mailbox_status->unseen > 1)
			{
				$num_new_str = ' - ' .lang('You have x new messages!',$mailbox_status->unseen);
			}
			elseif ($mailbox_status->unseen == 0)
			{
				$num_new_str = ' - ' .lang('You have no new messages');
			}
			else
			{
				$num_new_str = '';
			}
			$nummsg = $phpgw->dcom->num_msg($mailbox);

			$title = '<font color="FFFFFF">' . lang('EMail') . $num_new_str . '</font>';
			$portalbox = CreateObject('phpgwapi.linkbox',Array($title,$phpgw_info['theme']['navbar_bg'],$phpgw_info['theme']['bg_color'],$phpgw_info['theme']['bg_color']));
			$portalbox->setvar('width',600);
			$portalbox->outerborderwidth = 0;
			$portalbox->header_background_image = $phpgw_info['server']['webserver_url'] . '/phpgwapi/templates/verdilak/images/bg_filler.gif';
			if($nummsg >= 5)
			{
				$check_msgs = 5;
			}
			else
			{
				$check_msgs = $nummsg;
			}

			// order 1 = order by the time the mail server revieved the mail
			// NOT the (unreliable) timestamp from the senders MUA ( which would be order = 0 )
			$order_hook = 1;
			if ($phpgw_info['user']['preferences']['email']['default_sorting'] == 'new_old')
			{
				$sort_hook = 1;
			}
			else
			{
				$sort_hook = 0;
			}

			if ($nummsg > 0)
			{
				$msg_array_hook = array();
				$msg_array_hook = $phpgw->dcom->sort($mailbox, $order_hook, $sort_hook);
			}
			for($i=0;$i<$check_msgs;$i++,$j++)
			{
				$msg = $phpgw->dcom->header($mailbox,$msg_array_hook[$i]);
				$subject = $phpgw->msg->get_subject($msg,'');
				if (strlen($subject) > 65)
				{
					$subject = substr($subject,0,65).' ...';
				}
				$portalbox->data[$i] = array($subject,$phpgw->link('/email/message.php','folder='.urlencode($folder).'&msgnum='.$msg_array_hook[$i]));
			}
			// ADD FOLDER LISTBOX TO HOME PAGE (Needs to be TEMPLATED)
			// Does This Mailbox Support Folders (i.e. more than just INBOX)?
			if (($phpgw_info['user']['preferences']['email']['mail_server_type'] !='imap')
			&& ($phpgw_info['user']['preferences']['email']['mail_server_type'] != 'imaps'))
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
						. $phpgw->msg->all_folders_listbox($mailbox,'','',$listbox_show_unseen)
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
			$phpgw->dcom->close($mailbox);
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
