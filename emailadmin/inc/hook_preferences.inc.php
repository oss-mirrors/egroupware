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

	$title = $appname;
		$file = Array
		(
			'Preferences'	=> $phpgw->link('/index.php','menuaction=notes.uinotes.preferences'),
			'Grant Access'	=> $phpgw->link('/preferences/acl_preferences.php','acl_app=notes'),
			'Edit categories'	=> $phpgw->link('/preferences/categories.php','cats_app=notes&cats_level=True&global_cats=True')
		);

//Do not modify below this line

		display_section($appname,$title,$file);
	}
?>
