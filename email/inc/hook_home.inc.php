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
		echo "\n".'<tr><td align="left"><!-- Mailbox info -->'."\n";
		if (! $mailbox)
		{
			echo '<b>Mail error:</b> Can not open connection to mail server';
			//$phpgw->common->phpgw_exit(True);
		}
		else
		{
			$server_str = get_mailsvr_callstr();
			$mailbox_status = $phpgw->msg->status($mailbox,$server_str .'INBOX',SA_UNSEEN);
			$str = '';
			if ($mailbox_status->unseen == 1)
			{
//				echo '<tr><td><A href="' . $phpgw->link('email/index.php') . '"> '
//					. lang('You have 1 new message!') . '</A></td></tr>'."\n";
				$str .= lang('You have 1 new message!');
			}
			if ($mailbox_status->unseen > 1)
			{
//				echo '<tr><td><A href="' . $phpgw->link('email/index.php') . '"> '
//					. lang('You have x new messages!',$mailbox_status->unseen) . '</A></td></tr>'."\n";
				$str .= lang('You have x new messages!',$mailbox_status->unseen);
			}
			if ($mailbox_status->unseen == 0)
			{
				$str .= lang('You have no new messages');
			}
			$nummsg = $phpgw->msg->num_msg($mailbox);
//			$title = '<a href="'.$phpgw->link($phpgw_info['server']['webserver_url'].'/email/index.php').'">EMail'.($str ? ' - '.$str : '').'</a>';
			$title = '<font color="FFFFFF">EMail' . ($str ? ' - ' . $str : '') . '</font>';

			$portalbox = CreateObject('phpgwapi.linkbox',Array($title,$phpgw_info['theme']['navbar_bg'],$phpgw_info['theme']['bg_color'],$phpgw_info['theme']['bg_color']));
			$portalbox->setvar('width',600);
			$portalbox->outerborderwidth = 0;
			$portalbox->header_background_image = PHPGW_APP_TPL . '/images/bg_filler.gif';
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
				$msg_array_hook = $phpgw->msg->sort($mailbox, $order_hook, $sort_hook);
			}

			for($i=0;$i<$check_msgs;$i++,$j++)
			{
				$msg = $phpgw->msg->header($mailbox,$msg_array_hook[$i]);
				$subject = $phpgw->msg->get_subject($msg,'');
				if (strlen($subject) > 65)
				{
					$subject = substr($subject,0,65).' ...';
				}
				$portalbox->data[$i] = array($subject,$phpgw->link('/email/message.php','folder='.urlencode($folder).'&msgnum='.$msg_array_hook[$i]));
			}
			echo $portalbox->draw();
		}
		echo "\n".'<!-- Mailox info --></td></tr>'."\n";
	}
?>
