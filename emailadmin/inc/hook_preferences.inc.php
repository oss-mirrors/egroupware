<?php
	/*************************************************************************\
	* phpGroupWare - Notes Preferences						                  *
	* http://www.phpgroupware.org											  *
	* --------------------------------------------							  *
	* This program is free software; you can redistribute it and/or modify it *
	* under the terms of the GNU General Public License as published by the   *
	* Free Software Foundation; either version 2 of the License, or (at your  *
	* option) any later version.											  *
	\*************************************************************************/
	/* $Id$ */

	{
// Only Modify the $file and $title variables.....

		$file = Array
		(
			'Grant Access'    => $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app='.$appname),
			'Edit categories' => $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=qmailldap&cats_level=True&global_cats=True')
		);
//Do not modify below this line
		display_section($appname,$file);
	}
?>
