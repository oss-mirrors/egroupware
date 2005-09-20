<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * Written by Mark Peters <skeeter@phpgroupware.org>                        *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
	/* $Id$ */

	// Delete all records for a user
	if((int)$GLOBALS['hook_values']['account_id'] > 0)
	{
		if((int)$_POST['new_owner'] == 0)
		{
			ExecMethod('calendar.bocalendar.delete_calendar',(int)$GLOBALS['hook_values']['account_id']);
		}
		else
		{
			ExecMethod('calendar.bocalendar.change_owner',
				Array(
					'old_owner'	=> (int)$GLOBALS['hook_values']['account_id'],
					'new_owner'	=> (int)$_POST['new_owner']
				)
			);
		}
	}
?>
