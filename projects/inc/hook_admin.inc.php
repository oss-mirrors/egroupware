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
			'Site Configuration'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			'Administration'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_admins&action=pad'),
			'Accountancy'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_admins&action=pbo'),
			'Global Categories'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=' . $appname)
		);
//Do not modify below this line
		display_section($appname,$appname,$file);
	}
?>
