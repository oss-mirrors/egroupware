<?php
	/**************************************************************************\
	* phpGroupWare - projects administration                                   *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	{
// Only Modify the $file and $title variables.....
		$title = $appname;
		$file = Array
		(
			'Project administration' => $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_admins&action=pad'),
			'Project bookkeeping' => $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_admins&action=pbo')
		);
//Do not modify below this line
		display_section($appname,$title,$file);
	}
?>
