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
	$folder = urldecode($folder);

	$totalmessages = $phpgw->dcom->num_msg($mailbox);

	if ($what == "move")
	{
		$tofolder = ($tofolder == "INBOX" ? 
			"INBOX" : 
			$phpgw->dcom->construct_folder_str($tofolder));

		$msgs = $msglist ? implode($msglist, ",") : $msglist;
		if (! $phpgw->dcom->mail_move($mailbox, $msgs, $tofolder))
		{
			echo "<br>mail_move: summin went rong<br>";
		}
	}

	if ($what == "delall")
	{
		for ($i = 0; $i < count($msglist); $i++)
		{
			if ($folder == "Trash")
			{
				$phpgw->dcom->delete($mailbox, $msglist[$i],"",$folder);
			}
			else
			{
				$phpgw->dcom->delete($mailbox, $msglist[$i]);
			}
		}
		$totaldeleted = "&td=$i";
		$dontforward = False;
	}

	if ($what == "delete")
	{
		if ($folder == "Trash")
		{
			$phpgw->dcom->delete($mailbox, $msgnum,"",$folder);
		}
		else
		{
			$phpgw->dcom->delete($mailbox, $msgnum);
		}
		if ($totalmessages != $msgnum || $phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old")
		{
			if ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old")
			{
				$nm = $msgnum - 1;
			}
			else
			{
				$nm = $msgnum;
			}

			Header("Location: ".$phpgw->link("/email/message.php","folder=" . urlencode($folder)."&msgnum=".$nm));
			$dontforward = True;
		}
	}
	$phpgw->dcom->expunge($mailbox);
	$phpgw->dcom->close($mailbox);


	if (! $dontforward)
	{
		Header("Location: ".$phpgw->link("/email/index.php","folder=" . urlencode($folder) . $totaldeleted));
	}
	$phpgw->common->phpgw_footer();
?>
