<?php
	/**************************************************************************\
	* phpGroupWare - Messenger                                                 *
	* http://www.phpgroupware.org                                              *
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

	$phpgw->db->query("select count(*) from phpgw_messenger_messages where message_owner='"
			. $phpgw_info['user']['account_id'] . "' and message_status='N'",__LINE__,__FILE__);
	$phpgw->db->next_record();

	if ($phpgw->db->f(0))
	{
		echo '<center><a href="' . $phpgw->link('/messenger/main.php','menuaction=messenger.uimessage.inbox')
			. '">' . lang('You have %1 new message' . ($phpgw->db->f(0)>1?'s':''),$phpgw->db->f(0)) . '</a>'
			. '</center>';
	}
