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

// ---- Folder Status Infomation   -----
	/*
	$mailbox_status = $phpgw->dcom->status($phpgw->msg->mailsvr_stream,
					$phpgw->msg->get_mailsvr_callstr().$phpgw->msg->folder,
					SA_ALL);
	$totalmessages = $mailbox_status->messages;
	*/
	$folder_info = array();
	$folder_info = $phpgw->msg->folder_status_info();
	$totalmessages = $folder_info['number_all'];

// ---- MOVE Messages from folder to folder   -----
	if ($phpgw->msg->args['what'] == "move")
	{
		// called by the "move selected messages to" listbox onChange action
		$tofolder = $phpgw->msg->prep_folder_in($phpgw->msg->args['tofolder']);
		// report number messages moved (will be made = 0 if error below)
		$tm = count($phpgw->msg->args['msglist']);
		$msgs = $phpgw->msg->args['msglist'] ? implode($phpgw->msg->args['msglist'], ",") : $phpgw->msg->args['msglist'];
		// mail_move accepts a single number (5); a comma seperated list of numbers (5,6,7,8); or a range with a colon (5:8)
		/*
		if (count($phpgw->msg->args['msglist']) > 1)
		{
			$msgs = implode($phpgw->msg->args['msglist'], ",");
		}
		else
		{
			$msgs = $phpgw->msg->args['msglist'];
		}
		*/
		//if (! $phpgw->dcom->mail_move($phpgw->msg->mailsvr_stream, $msgs, $tofolder))
		if (! $phpgw->msg->phpgw_mail_move($msgs, $tofolder))
		{
			// ERROR: report ZERO messages moved
			$tm = 0;
		}
		else
		{
			// expunge moved messages in from folder, they are marked as expungable after the move
			//$phpgw->dcom->expunge($phpgw->msg->mailsvr_stream);
			$phpgw->msg->phpgw_expunge();
		}
		// report folder messages were moved to
		$tf = $phpgw->msg->prep_folder_out($tofolder);
		$phpgw->msg->end_request();
		Header("Location: ".$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php',
						 'folder='.$phpgw->msg->prep_folder_out('')
						.'&tm='.$tm
						.'&tf='.$tf
						.'&sort='.$phpgw->msg->sort
						.'&order='.$phpgw->msg->order
						.'&start='.$phpgw->msg->start));
	}
	elseif ($phpgw->msg->args['what'] == "delall")
	{
		// this is called from the index pge after you check some boxes and click "delete" button
		for ($i = 0; $i < count($phpgw->msg->args['msglist']); $i++)
		{
			//$phpgw->dcom->delete($phpgw->msg->mailsvr_stream, $phpgw->msg->args['msglist'][$i],"",$phpgw->msg->folder);
			$phpgw->msg->phpgw_delete($phpgw->msg->args['msglist'][$i],"",$phpgw->msg->folder);
		}
		$totaldeleted = $i;
		//$phpgw->dcom->expunge($phpgw->msg->mailsvr_stream);
		$phpgw->msg->phpgw_expunge();
		// end the msg class session
		$phpgw->msg->end_request();
		Header("Location: ".$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php',
						 'folder='.$phpgw->msg->prep_folder_out('')
						.'&td='.$totaldeleted
						.'&sort='.$phpgw->msg->sort
						.'&order='.$phpgw->msg->order
						.'&start='.$phpgw->msg->start));
	}
	elseif ($phpgw->msg->args['what'] == "delete")
	{
		// called by clicking the "X" dutton while reading an individual message
		//$phpgw->dcom->delete($phpgw->msg->mailsvr_stream, $phpgw->msg->msgnum,"",$phpgw->msg->folder);
		$phpgw->msg->phpgw_delete($phpgw->msg->msgnum,"",$phpgw->msg->folder);
		if (($totalmessages != $phpgw->msg->msgnum)
		|| ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old"))
		{
			if ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old")
			{
				$nm = $phpgw->msg->msgnum - 1;
			}
			else
			{
				$nm = $phpgw->msg->msgnum;
			}
		}
		//$phpgw->dcom->expunge($phpgw->msg->mailsvr_stream);
		$phpgw->msg->phpgw_expunge();
		// end the msg class session
		$phpgw->msg->end_request();
		Header("Location: ".$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',
						 'folder='.$phpgw->msg->prep_folder_out('')
						.'&msgnum='.$nm
						.'&sort='.$phpgw->msg->sort
						.'&order='.$phpgw->msg->order
						.'&start='.$phpgw->msg->start));
	}
	else
	{
		echo "UNKNOWN ACTION";
		// end the msg class session
		$phpgw->msg->end_request();
	}

	$phpgw->common->phpgw_footer();
?>
