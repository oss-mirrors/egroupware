<?php
	/**************************************************************************\
	* phpGroupWare - E-Mail                                                    *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*	This program is free software; you can redistribute it and/or modify it*
	*	under the terms of the GNU General Public License as published by the  *
	*	Free Software Foundation; either version 2 of the License, or (at your *
	*	option) any later version.                                             *
	\**************************************************************************/

	/* $Id$ */

	$d1 = strtolower(substr($phpgw_info['server']['app_inc'],0,3));
	if($d1 == 'htt' || $d1 == 'ftp' ) 
	{
		echo 'error:Failed attempt to break in via an old Security Hole!'.chr(13);
		$phpgw->common->phpgw_exit();
	}
	unset($d1);

	$phpgw_info['server']['app_inc'] = PHPGW_SERVER_ROOT . SEP . 'email' . SEP . 'inc';

	// NOTE: notify for email not available if the welcome screen show mail option if off
	if (($phpgw_info['user']['preferences']['email']['mainscreen_showmail'])
	&& (isset($phpgw_info['user']['apps']['email'])
	&& $phpgw_info['user']['apps']['email']))
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

		if ($inbox_data['alert_string'] != '')
		{
			echo "\r\n" . '<tr><td align="left"><!-- Mailbox info X10 -->' . "\r\n";
/*			echo '<script language="JavaScript">'.chr(13).chr(10);
			echo '<!-- Activate Cloaking Device'.chr(13).chr(10);
			echo '	funtion CheckEmail()'.chr(13).chr(10);
			echo '	{'.chr(13).chr(10);
			echo '		window.opener.document.location.href="'.$phpgw->link("../email/").'";'.chr(13).chr(10);
			echo '	}'.chr(13).chr(10);
			echo '//-->'.chr(13).chr(10);
			echo '</script>'.chr(13).chr(10); */
			//echo '<font color="FFFFFF">EMail';
			//echo ($str ? ' - <A href="JavaScript:CheckEmail();">' . $str . '</A>' : '') . '</font>';
			echo lang("EMail").' - <a href="JavaScript:CheckEmail();">'.$inbox_data['alert_string'].'</a>';
			//echo '</font>';
			echo "\r\n".'<!-- Mailox info --></td></tr>'."\r\n";
		}
	}

	$phpgw_info['server']['app_inc'] = $tmp_app_inc;
?>
