<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail								*
  * http://www.phpgroupware.org							*
  * --------------------------------------------						*
  *  This program is free software; you can redistribute it and/or modify it 	*
  *  under the terms of the GNU General Public License as published by the	*
  *  Free Software Foundation; either version 2 of the License, or (at your  	*
  *  option) any later version.								*
  \**************************************************************************/

  /* $Id$ */

	$d1 = strtolower(substr(APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	// is this still needed?
	//$tmp_app_inc = $GLOBALS['phpgw_info']["server"]["app_inc"];
	//$GLOBALS['phpgw_info']["server"]["app_inc"] = $GLOBALS['phpgw_info']["server"]["server_root"]."/email/inc";

	if (($GLOBALS['phpgw_info']["user"]["preferences"]["email"]["mainscreen_showmail"])
	&& (isset($GLOBALS['phpgw_info']["user"]["apps"]["email"]))
	&& ($GLOBALS['phpgw_info']["user"]["apps"]["email"]))
	{
		// ----  Create the base email Msg Class    -----
		//$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
		if (is_object($GLOBALS['phpgw']->msg))
		{
			//echo 'email hook_notifywindow_simple: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
		}
		else
		{
			//echo 'email hook_notifywindow_simple: is_object test: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
			$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
		}
		$args_array = Array();
		$args_array['folder'] = 'INBOX';
		$args_array['do_login'] = True;
		$GLOBALS['phpgw']->msg->begin_request($args_array);
		if ((string)$GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream') != '')
		{
			/*  // this is the structure you will get
			  $inbox_data['is_imap'] boolean - pop3 server do not know what is "new" or not
			  $inbox_data['folder_checked'] string - the folder checked, as processed by the msg class
			  $inbox_data['alert_string'] string - what to show the user about this inbox check
			  $inbox_data['number_new'] integer - for IMAP is number "unseen"; for pop3 is number messages
			  $inbox_data['number_all'] integer - for IMAP and pop3 is total number messages in that inbox
			*/
			$inbox_data = Array();
			$inbox_data = $GLOBALS['phpgw']->msg->new_message_check();		
			if ($inbox_data['is_imap'])
			{
				if ($inbox_data['number_new'] > 0) 
				{
					echo 'action:newmail:'.$inbox_data["number_all"].chr(13);
				}
			}
			else
			{
				if ($inbox_data['number_all'] > 0) 
				{
					echo 'action:newmail'.$inbox_data["number_all"].chr(13);
				}
			}
		}
		else
		{
			echo lang('<b>Mail error:</b> Can not open connection to mail server');
		}
		// end the mailserver request
		$GLOBALS['phpgw']->msg->end_request();
	}

	// is this still necessary?
	//$GLOBALS['phpgw_info']["server"]["app_inc"] = $tmp_app_inc;

?>
