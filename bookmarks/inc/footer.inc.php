<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	// This is becuase this file is included inside of a function
	global $error_msg,$sess_error_msg,$warn_msg,$sess_warn_msg,$msg,$sess_msg;

	if ($error_msg)
	{
		$bk_print_error_msg = $error_msg;
	}
   
	// print any other error msgs that haven't
	// been printed yet - like from another page
	if ($sess_error_msg)
	{
		$bk_print_error_msg .= $sess_error_msg;
		$sess_error_msg ='';
	}
   
	// print any warn msgs from the current page
	if ($warn_msg)
	{
		$bk_print_warn_msg = $warn_msg;
	}
   
	// print any other warn msgs that haven't
	// been printed yet - like from another page
	if ($sess_warn_msg)
	{
		$bk_print_warn_msg .= $sess_warn_msg;
		$sess_warn_msg = '';
	}
   
	// print any info msgs from the current page
	if ($msg)
	{
		$bk_print_msg = $msg;
	}

/*	$session_message = unserialize($phpgw->common->appsession('message','bookmarks'));
	if ($session_message)
	{
		$bk_print_msg = $session_message;
	} */


	// print any other info msgs that haven't
	// been printed yet - like from another page
	if ($sess_msg)
	{
		$bk_print_msg .= $sess_msg;
		$sess_msg = '';
	}

	if ($bk_print_error_msg)
	{
		$bk_output_html = sprintf("<tr><td align=\"center\"><table cellpadding=2><tr><td><b>" . lang("error") . ":</b>%s</td></tr></table></td></tr>\n", $bk_print_error_msg);
	}
	if ($bk_print_warn_msg)
	{
		$bk_output_html .= sprintf("<tr><td align=\"center\"><table cellpadding=2><tr><td><b>" . lang("Warning") . ":</b>%s</td></tr></table></td></tr>\n", $bk_print_warn_msg);
	}
	if ($bk_print_msg)
	{
		$bk_output_html .= sprintf("<tr><td align=\"center\"><table cellpadding=2><tr><td>%s</td></tr></table></td></tr>\n", $bk_print_msg);
	}

	if ($bk_output_html)
	{
		$phpgw->template->set_var('messages',$bk_output_html);
	}

	$phpgw->template->pfp('body',array('body','common'));
?>