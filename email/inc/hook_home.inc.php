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
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = $GLOBALS['phpgw']->common->get_inc_dir('email');

	if ($GLOBALS['phpgw_info']['user']['preferences']['email']['mainscreen_showmail'] == True)
	{
		// ----  Create the base email Msg Class    -----
		$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
		$args_array = Array();
		$args_array['folder'] = 'INBOX';
		$args_array['do_login'] = True;
		$GLOBALS['phpgw']->msg->begin_request($args_array);

		if (!$GLOBALS['phpgw']->msg->mailsvr_stream)
		{
			$title = '<font color="#FFFFFF">'.lang('EMail').'</font>';
			$extra_data = '<b>Mail error:</b> Can not open connection to mail server';
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
			$inbox_data = $GLOBALS['phpgw']->msg->new_message_check();

			$title = '<font color="#FFFFFF">'.lang('EMail').' '.$inbox_data['alert_string'].'</font>';

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
				$msg_nums_array = $GLOBALS['phpgw']->msg->get_message_list();
			}
			for($i=0; $i<$check_msgs; $i++)
			{
				//$msg_headers = $GLOBALS['phpgw']->dcom->header($GLOBALS['phpgw']->msg->mailsvr_stream,$msg_nums_array[$i]);
				$msg_headers = $GLOBALS['phpgw']->msg->phpgw_header($msg_nums_array[$i]);
				$subject = $GLOBALS['phpgw']->msg->get_subject($msg_headers,'');
				if (strlen($subject) > 65)
				{
					$subject = substr($subject,0,65).' ...';
				}
				$data[] = array('text'=>$subject,'link'=>$GLOBALS['phpgw']->link('/email/message.php','folder='.$GLOBALS['phpgw']->msg->prep_folder_out('').'&msgnum='.$msg_nums_array[$i]));
			}
			// ADD FOLDER LISTBOX TO HOME PAGE (Needs to be TEMPLATED)
			// Does This Mailbox Support Folders (i.e. more than just INBOX)?
			if ($GLOBALS['phpgw']->msg->get_mailsvr_supports_folders() == False)
			{
				$extra_data = '';
			}
			else
			{
				// FUTURE: this will pick up the user option to show num unseen msgs in dropdown list
				//$listbox_show_unseen = True;
				$listbox_show_unseen = False;
				$switchbox_listbox = '<select name="folder" onChange="document.switchbox.submit()">'
						. '<option>' . lang('switch current folder to') . ':'
						. $GLOBALS['phpgw']->msg->all_folders_listbox('','','',$listbox_show_unseen)
						. '</select>';
				// make it another TR we can insert
				$switchbox_action = $GLOBALS['phpgw']->link('/email/index.php');
				$extra_data = 
					'<form name="switchbox" action="'.$switchbox_action.'" method="post">'."\r\n"
						.'<td align="left">'."\r\n"
							.'&nbsp;<strong>E-Mail Folders:</strong>&nbsp;'.$switchbox_listbox
						.'</td>'."\r\n"
					.'</form>'."\r\n";
			}
			$GLOBALS['phpgw']->msg->end_request();
		}

		$portalbox = CreateObject('phpgwapi.listbox',
			Array(
				'title'	=> $title,
				'primary'	=> $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'secondary'	=> $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'tertiary'	=> $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'width'	=> '100%',
				'outerborderwidth'	=> '0',
				'header_background_image'	=> $GLOBALS['phpgw']->common->image('phpgwapi/templates/phpgw_website','bg_filler.gif')
			)
		);
		$app_id = $GLOBALS['phpgw']->applications->name2id('email');
		$GLOBALS['portal_order'][] = $app_id;
		$var = Array(
			'up'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id),
			'down'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id),
			'close'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id),
			'question'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id),
			'edit'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id)
		);

		while(list($key,$value) = each($var))
		{
			$portalbox->set_controls($key,$value);
		}

		if($data)
		{
			$portalbox->data = $data;
		}

		// output the portalbox and (if applicable) the folders listbox below it
		echo "\r\n".'<!-- start Mailbox info -->'."\r\n"
			.$portalbox->draw($extra_data)
			.'<!-- ends Mailox info -->'."\r\n";
	}
?>
