<?php
	/**************************************************************************\
	* eGroupWare - Messenger                                                   *
	* http://www.egroupware.org                                                *
	* This application written by Joseph Engo <jengo@phpgroupware.org>         *
	* --------------------------------------------                             *
	* Funding for this program was provided by http://www.checkwithmom.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	global $hooks_string;

	$lastlogin = $GLOBALS['egw']->session->appsession('account_previous_login','phpgwapi');
	if($lastlogin)
	{
		$GLOBALS['egw']->db->query("select count(*) from phpgw_messenger_messages where message_owner='"
			. $GLOBALS['egw_info']['user']['account_id'] . "' and message_status='N' and message_date > $lastlogin",__LINE__,__FILE__);
		$GLOBALS['egw']->db->next_record();

		if ($GLOBALS['egw']->db->f(0))
		{
			$hooks_string['messenger'] = '<p><b><font size="-1">Messenger</font></b><ul><font size="-1"><li>'
				. lang('You have %1 new message' . ($GLOBALS['egw']->db->f(0)>1?'s':'') . ' in your inbox!',$GLOBALS['egw']->db->f(0))
				. '<br>[<a href="' . $GLOBALS['egw']->link('/index.php','menuaction=messenger.uimessenger.inbox')
				. '">View Messages</a> | <a href="' . $GLOBALS['egw']->link('/index.php','menuaction=messenger.uimessenger.compose')
				. '">Send a Message</a>]</font></ul>';
		}
		else
		{
			$hooks_string['messenger'] = '<p><b><font size="-1">Messenger</font></b><ul><font size="-1"><li> No new personal messages have been sent to you.<br>'
				. '[<a href="' . $GLOBALS['egw']->link('/index.php','menuaction=messenger.uimessenger.inbox')
				. '">View Messages</a> | <a href="' . $GLOBALS['egw']->link('/index.php','menuaction=messenger.uimessenger.compose')
				. '">Send a Message</a>]</font></ul>';
		}
	}
