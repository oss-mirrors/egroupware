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

	$d1 = strtolower(substr($GLOBALS['phpgw_info']['server']['app_inc'],0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = $GLOBALS['phpgw']->common->get_inc_dir('email');

	// does this array item actually exist before we create the mail_msg, where is it created?
	if ($GLOBALS['phpgw_info']['user']['preferences']['email']['mainscreen_showmail'] == True)
	{
		// ----  Create the base email Msg Class    -----
		//$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
		if (is_object($GLOBALS['phpgw']->msg))
		{
			//echo 'email hook_home: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
		}
		else
		{
			//echo 'email hook_home: is_object test: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
			$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
		}

		$args_array = Array();
		$args_array['folder'] = 'INBOX';
		$args_array['do_login'] = True;
		$GLOBALS['phpgw']->msg->begin_request($args_array);
		if ((string)$GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream') == '')
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
				$msgball_list = array();
				$msgball_list = $GLOBALS['phpgw']->msg->get_msgball_list();
			}
			for($i=0; $i<$check_msgs; $i++)
			{
				$msg_headers = $GLOBALS['phpgw']->msg->phpgw_header($msgball_list[$i]);
				$subject = $GLOBALS['phpgw']->msg->get_subject($msg_headers,'');
				if (strlen($subject) > 65)
				{
					$subject = substr($subject,0,65).' ...';
				}
				$data[] = array(
					'text'	=> $subject,
					'link'	=> $GLOBALS['phpgw']->link('/email/message.php',$msgball_list[$i]['uri'])
				);
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
				// build the $feed_args array for the all_folders_listbox function
				// anything not specified will be replace with a default value if the function has one for that param
				$feed_args = Array(
					'mailsvr_stream'	=> '',
					'pre_select_folder'	=> '',
					'skip_folder'		=> '',
					'show_num_new'		=> $listbox_show_unseen,
					'widget_name'		=> 'folder',
					'on_change'		=> 'document.switchbox.submit()',
					'first_line_txt'	=> lang('switch current folder to')
				);
				// get you custom built HTML listbox (a.k.a. selectbox) widget
				$switchbox_listbox = $GLOBALS['phpgw']->msg->all_folders_listbox($feed_args);
				// make it another TR we can insert into the home page portal object
				// and surround it in FORM tage so the submit will work
				$switchbox_action = $GLOBALS['phpgw']->link(
								'/index.php',
								'menuaction=email.uiindex.index');
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
