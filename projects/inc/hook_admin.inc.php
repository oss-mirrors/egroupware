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
		$file = Array
		(
			'Site Configuration'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			'managing committee'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiconfig.list_admins&action=pmanager'),
			'project administrators'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiconfig.list_admins&action=pad'),
			'sales department'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiconfig.list_admins&action=psale'),
			'Global Categories'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=' . $appname)
		);
//Do not modify below this line
		display_section($appname,$appname,$file);
	}
?>
