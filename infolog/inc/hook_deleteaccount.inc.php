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
	$info =& CreateObject('infolog.soinfolog');

	$info->change_delete_owner(intval($_POST['account_id']),
		intval($_POST['new_owner']));

	unset($info);
?>
