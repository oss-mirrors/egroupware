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

	$d1 = strtolower(substr($phpgw_info["server"]["app_inc"],0,3));
	if($d1 == "htt" || $d1 == "ftp" ) 
	{
		echo 'error:Failed attempt to break in via an old Security Hole!'.chr(13);
		$phpgw->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = $phpgw_info["server"]["app_inc"];
	$phpgw_info["server"]["app_inc"] = $phpgw_info["server"]["server_root"]."/email/inc";

	if ($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"] &&
		 (isset($phpgw_info["user"]["apps"]["email"]) && $phpgw_info["user"]["apps"]["email"]))
	{
		include($phpgw_info["server"]["app_inc"] . "/functions.inc.php");

		$mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}INBOX",SA_UNSEEN);
		$nummsg = intval($phpgw->msg->num_msg($mailbox));
		$str = '';
		
		if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") 
		{
			if ($mailbox_status->unseen == 1) 
			{
				$str .= lang("You have 1 new message!");
			}
			if ($mailbox_status->unseen > 1) 
			{
				$str .= lang("You have x new messages!",$mailbox_status->unseen);
			}
			if ($mailbox_status->unseen == 0) 
			{
				$str .= lang("You have no new messages");
			}
		}
		else
		{
			if ($nummsg > 0) 
			{
				$str .= lang("You have messages!");
			}
			elseif ($nummsg == 0) 
			{
				$str .= lang("You have no new messages");
			}
		}
		if ($str != '')
		{
			echo "\n".'<tr><td align="left"><!-- Mailbox info -->'."\n";
/*			echo '<script language="JavaScript">'.chr(13).chr(10);
			echo '<!-- Activate Cloaking Device'.chr(13).chr(10);
			echo '	funtion CheckEmail()'.chr(13).chr(10);
			echo '	{'.chr(13).chr(10);
			echo '		window.opener.document.location.href="'.$phpgw->link("../email/").'";'.chr(13).chr(10);
			echo '	}'.chr(13).chr(10);
			echo '//-->'.chr(13).chr(10);
			echo '</script>'.chr(13).chr(10); */
			echo '<font color="FFFFFF">EMail';
			echo ($str ? ' - <A href="JavaScript:CheckEmail();">' . $str . '</A>' : '') . '</font>';
			echo "\n".'<!-- Mailox info --></td></tr>'."\n";
		}
	}

	$phpgw_info["server"]["app_inc"] = $tmp_app_inc;
?>
