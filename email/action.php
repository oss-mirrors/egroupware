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

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'email', 
		'enable_network_class' => True, 
		'noheader' => True,
		'nonavbar' => True
	);

	include('../header.inc.php');

// ---- Folder Status Infomation   -----
	/*
	$mailbox_status = $GLOBALS['phpgw']->dcom->status($GLOBALS['phpgw']->msg->mailsvr_stream,
					$GLOBALS['phpgw']->msg->get_mailsvr_callstr().$GLOBALS['phpgw']->msg->folder,
					SA_ALL);
	$totalmessages = $mailbox_status->messages;
	*/
	/*
	echo 'HTTP_POST_VARS dump:<br>';
	var_dump($GLOBALS['HTTP_POST_VARS']);
	echo '<br><br>HTTP_GET_VARS dump:<br>';
	var_dump($GLOBALS['HTTP_GET_VARS']);
	echo '<br><br>';
	*/
	
	$folder_info = array();
	$folder_info = $GLOBALS['phpgw']->msg->folder_status_info();
	$totalmessages = $folder_info['number_all'];

// ---- MOVE Messages from folder to folder   -----
	if ($GLOBALS['phpgw']->msg->args['what'] == "move")
	{
		// called by the "move selected messages to" listbox onChange action
		$tofolder = $GLOBALS['phpgw']->msg->prep_folder_in($GLOBALS['phpgw']->msg->args['tofolder']);
		// report number messages moved (will be made = 0 if error below)
		$tm = count($GLOBALS['phpgw']->msg->args['msglist']);
		$msgs = $GLOBALS['phpgw']->msg->args['msglist'] ? implode($GLOBALS['phpgw']->msg->args['msglist'], ",") : $GLOBALS['phpgw']->msg->args['msglist'];
		// mail_move accepts a single number (5); a comma seperated list of numbers (5,6,7,8); or a range with a colon (5:8)
		/*
		if (count($GLOBALS['phpgw']->msg->args['msglist']) > 1)
		{
			$msgs = implode($GLOBALS['phpgw']->msg->args['msglist'], ",");
		}
		else
		{
			$msgs = $GLOBALS['phpgw']->msg->args['msglist'];
		}
		*/
		//if (! $GLOBALS['phpgw']->dcom->mail_move($GLOBALS['phpgw']->msg->mailsvr_stream, $msgs, $tofolder))
		if (! $GLOBALS['phpgw']->msg->phpgw_mail_move($msgs, $tofolder))
		{
			// ERROR: report ZERO messages moved
			$tm = 0;
			//echo 'Server reports error: '.$GLOBALS['phpgw']->msg->dcom->server_last_error();
		}
		else
		{
			// expunge moved messages in from folder, they are marked as expungable after the move
			//$GLOBALS['phpgw']->dcom->expunge($GLOBALS['phpgw']->msg->mailsvr_stream);
			$GLOBALS['phpgw']->msg->phpgw_expunge();
		}
		// report folder messages were moved to
		$tf = $GLOBALS['phpgw']->msg->prep_folder_out($tofolder);
		$GLOBALS['phpgw']->msg->end_request();
		Header('Location: '.$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
						 'folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
						.'&tm='.$tm
						.'&tf='.$tf
						.'&sort='.$GLOBALS['phpgw']->msg->sort
						.'&order='.$GLOBALS['phpgw']->msg->order
						.'&start='.$GLOBALS['phpgw']->msg->start));
	}
	elseif ($GLOBALS['phpgw']->msg->args['what'] == 'delall')
	{
		// this is called from the index pge after you check some boxes and click "delete" button
		for ($i = 0; $i < count($GLOBALS['phpgw']->msg->args['msglist']); $i++)
		{
			//$GLOBALS['phpgw']->dcom->delete($GLOBALS['phpgw']->msg->mailsvr_stream, $GLOBALS['phpgw']->msg->args['msglist'][$i],'',$GLOBALS['phpgw']->msg->folder);
			$GLOBALS['phpgw']->msg->phpgw_delete($GLOBALS['phpgw']->msg->args['msglist'][$i],'',$GLOBALS['phpgw']->msg->folder);
		}
		$totaldeleted = $i;
		//$GLOBALS['phpgw']->dcom->expunge($GLOBALS['phpgw']->msg->mailsvr_stream);
		$GLOBALS['phpgw']->msg->phpgw_expunge();
		// end the msg class session
		$GLOBALS['phpgw']->msg->end_request();
		Header('Location: '.$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php',
						 'folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
						.'&td='.$totaldeleted
						.'&sort='.$GLOBALS['phpgw']->msg->sort
						.'&order='.$GLOBALS['phpgw']->msg->order
						.'&start='.$GLOBALS['phpgw']->msg->start));
	}
	elseif ($GLOBALS['phpgw']->msg->args['what'] == "delete")
	{
		// called by clicking the "X" dutton while reading an individual message
		//$GLOBALS['phpgw']->dcom->delete($GLOBALS['phpgw']->msg->mailsvr_stream, $GLOBALS['phpgw']->msg->msgnum,'',$GLOBALS['phpgw']->msg->folder);
		$GLOBALS['phpgw']->msg->phpgw_delete($GLOBALS['phpgw']->msg->msgnum,'',$GLOBALS['phpgw']->msg->folder);
		if (($totalmessages != $GLOBALS['phpgw']->msg->msgnum)
		|| ($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting'] == 'new_old'))
		{
			if ($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting'] == 'new_old')
			{
				$nm = $GLOBALS['phpgw']->msg->msgnum - 1;
			}
			else
			{
				$nm = $GLOBALS['phpgw']->msg->msgnum;
			}
		}
		//$GLOBALS['phpgw']->dcom->expunge($GLOBALS['phpgw']->msg->mailsvr_stream);
		$GLOBALS['phpgw']->msg->phpgw_expunge();
		// end the msg class session
		$GLOBALS['phpgw']->msg->end_request();
		Header('Location: '.$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/message.php',
						 'folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
						.'&msgnum='.$nm
						.'&sort='.$GLOBALS['phpgw']->msg->sort
						.'&order='.$GLOBALS['phpgw']->msg->order
						.'&start='.$GLOBALS['phpgw']->msg->start));
	}
	else
	{
		echo 'UNKNOWN ACTION';
		// end the msg class session
		$GLOBALS['phpgw']->msg->end_request();
	}

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
