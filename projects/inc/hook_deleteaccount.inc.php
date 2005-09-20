<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
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
		$pro = CreateObject('projects.boprojects');

		if(intval($_POST['new_owner']) == 0)
		{
			$pro->delete_project((int)$GLOBALS['hook_values']['account_id'],0,'account');
		}
		else
		{
			$pro->change_owner((int)$GLOBALS['hook_values']['account_id'],(int)$_POST['new_owner']);
		}
	}
?>
