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

	if($GLOBALS['egw_info']['flags']['currentapp'] != 'messenger' &&
		$GLOBALS['egw_info']['flags']['currentapp'] != 'welcome')
	{
		$GLOBALS['egw']->db->query("select count(*) from phpgw_messenger_messages where message_owner='"
			. $GLOBALS['egw_info']['user']['account_id'] . "' and message_status='N'",__LINE__,__FILE__);
		$GLOBALS['egw']->db->next_record();

		if($GLOBALS['egw']->db->f(0))
		{
			echo '<center><a href="' . $GLOBALS['egw']->link('/index.php','menuaction=messenger.uimessenger.inbox')
				. '">' . lang('You have %1 new message' . ($GLOBALS['egw']->db->f(0)>1?'s':''),$GLOBALS['egw']->db->f(0)) . '</a>'
				. '</center>';
		}
	}
