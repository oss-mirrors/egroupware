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
	/*
	
	global $account_id;
	*/
	/* NOTE: This is untested */
	/* WIP: it should get all files owned by $account_id, not just in /home/account_id */
	/* Should also be capable of transfering files to another user */

/*
	$GLOBALS['egw']->vfs->working_id = $account_id;
	$ls_array = $GLOBALS['egw']->vfs->ls ($GLOBALS['egw']->vfs->fakebase . "/" . $account_id, array (RELATIVE_NONE));
	while (list ($num, $entry) = each ($ls_array))
	{
		$GLOBALS['egw']->vfs->rm ($entry["dir"] . "/" . $entry["name"], array (RELATIVE_NONE));
	}
*/
?>
