<?php
	/**************************************************************************\
	* phpGroupWare - Project Prefs                                             *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	{
		echo "<p>\n";
		$imgfile = $phpgw->common->get_image_dir('projects') . '/' . $appname . '.gif';
		if (file_exists($imgfile)) 
		{
			$imgpath = $phpgw->common->get_image_path('projects') . '/' . $appname . '.gif';
		}
		else 
		{
			$imgfile = $phpgw->common->get_image_dir('projects') . '/navbar.gif';
			if (file_exists($imgfile)) 
			{
				$imgpath = $phpgw->common->get_image_path('projects') . '/navbar.gif';
			}
			else 
			{
				$imgpath = '';
			}
		}

		section_start('projects',$imgpath);

		section_item($phpgw->link('/projects/preferences.php'),lang('Preferences'));

		section_item($phpgw->link('/preferences/acl_preferences.php','acl_app=projects'),lang('Grant access'));

		section_item($phpgw->link('/preferences/categories.php','cats_app=projects&cats_level=True&global_cats=True'),lang('Edit categories'));

		section_end();
	}
?>
