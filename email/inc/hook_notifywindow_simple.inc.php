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

if ($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"] &&
     (isset($phpgw_info["user"]["apps"]["email"]) && $phpgw_info["user"]["apps"]["email"]))
{
	include($phpgw_info["server"]["app_inc"] . "/functions.inc.php");

	$mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}INBOX",SA_UNSEEN);

	$nummsg = intval($phpgw->msg->num_msg($mailbox));

	$str = '';
	//echo $mailbox_status->unseen;
	if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") 
	{
		if ($mailbox_status->unseen > 0) 
		{
			echo 'action:newmail:'.$nummsg.chr(13);
		}
	}
	else
	{
		if ($nummsg > 0) 
		{
			echo 'action:newmail'.chr(13);
		}
	}
}

$phpgw_info["server"]["app_inc"] = $tmp_app_inc;

?>
