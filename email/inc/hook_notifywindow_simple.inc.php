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

	$d1 = strtolower(substr($phpgw_info["server"]["app_inc"],0,3));

	if($d1 == "htt" || $d1 == "ftp" ) 
	{
		echo 'error:Failed attempt to break in via an old Security Hole!'.chr(13);
		$phpgw->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = $phpgw_info["server"]["app_inc"];
	$phpgw_info["server"]["app_inc"] = $phpgw_info["server"]["server_root"]."/email/inc";

	if (($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"])
	&& (isset($phpgw_info["user"]["apps"]["email"]))
	&& ($phpgw_info["user"]["apps"]["email"]))
	{
		// ----  Create the base email Msg Class    -----
		$phpgw->msg = CreateObject("email.mail_msg");
		$args_array = Array();
		$args_array['folder'] = 'INBOX';
		$args_array['do_login'] = True;
		$phpgw->msg->begin_request($args_array);

		/*  // this is the structure you will get
		  $inbox_data['is_imap'] boolean - pop3 server do not know what is "new" or not
		  $inbox_data['folder_checked'] string - the folder checked, as processed by the msg class
		  $inbox_data['alert_string'] string - what to show the user about this inbox check
		  $inbox_data['number_new'] integer - for IMAP is number "unseen"; for pop3 is number messages
		  $inbox_data['number_all'] integer - for IMAP and pop3 is total number messages in that inbox
		*/
		$inbox_data = Array();
		$inbox_data = $phpgw->msg->new_message_check();

		// end the mailserver request object
		$phpgw->msg->end_request();

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
				echo 'action:newmail'.chr(13);
			}
		}

		/*
		if (($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") 
		|| ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imaps"))
		{
			if ($mailbox_status->unseen > 0) 
			{
				echo 'action:newmail:'.$mailbox_status->messages.chr(13);
			}
		}
		else
		{
			if ($mailbox_status->messages > 0) 
			{
				echo 'action:newmail'.chr(13);
			}
		}
		*/
		
	}

	$phpgw_info["server"]["app_inc"] = $tmp_app_inc;

?>
