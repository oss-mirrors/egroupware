<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info["flags"] = array(
		'currentapp' => 'email', 
		'enable_network_class' => True, 
		'noheader' => True,
		'nonavbar' => True
	);

	include("../header.inc.php");

	$totalmessages = $phpgw->dcom->num_msg($phpgw->msg->mailsvr_stream);

	if ($what == "move")
	{
		//$tofolder = ($tofolder == "INBOX" ? 
		//	"INBOX" : 
		//	$phpgw->dcom->construct_folder_str($tofolder));

		$tofolder = $phpgw->msg->prep_folder_in($tofolder);

		$msgs = $msglist ? implode($msglist, ",") : $msglist;

		if (! $phpgw->dcom->mail_move($phpgw->msg->mailsvr_stream, $msgs, $tofolder))
		{
			echo "<br>mail_move: summin went rong<br>";
		}
	}
	elseif ($what == "delall")
	{
		for ($i = 0; $i < count($msglist); $i++)
		{
			$phpgw->dcom->delete($phpgw->msg->mailsvr_stream, $msglist[$i],"",$phpgw->msg->folder);
		}
		$totaldeleted = "&td=$i";
		$dontforward = False;
	}
	elseif ($what == "delete")
	{
		$phpgw->dcom->delete($phpgw->msg->mailsvr_stream, $msgnum,"",$phpgw->msg->folder);

		if (($totalmessages != $msgnum)
		|| ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old"))
		{
			if ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old")
			{
				$nm = $msgnum - 1;
			}
			else
			{
				$nm = $msgnum;
			}
			//Header("Location: ".$phpgw->link("/email/message.php","folder=" . $phpgw->msg->prep_folder_out('')."&msgnum=".$nm));
			//$dontforward = True;
		}
	}
	$phpgw->dcom->expunge($phpgw->msg->mailsvr_stream);
	$phpgw->msg->end_request();

	if ($what == "delete")
	{
		Header("Location: ".$phpgw->link("/email/message.php","folder=" . $phpgw->msg->prep_folder_out('')."&msgnum=".$nm));
	}
	elseif (! $dontforward)
	{
		Header("Location: ".$phpgw->link("/email/index.php","folder=" . $phpgw->msg->prep_folder_out(''). $totaldeleted));
	}
	$phpgw->common->phpgw_footer();
?>
