<?php
  /**************************************************************************\
  * phpGroupWare - skel                                                      *
  * http://www.phpgroupware.org                                              *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	{
		echo "<p>\n";
		$imgfile = $phpgw->common->get_image_dir($appname) . '/' . $appname . '.gif';
		if (file_exists($imgfile))
		{
			$imgpath = $phpgw->common->get_image_path($appname) . '/' . $appname . '.gif';
		}
		else
		{
			$imgfile = $phpgw->common->get_image_dir($appname) . '/navbar.gif';
			if (file_exists($imgfile))
			{
				$imgpath = $phpgw->common->get_image_path($appname) . '/navbar.gif';
			}
			else
			{
				$imgpath = '';
			}
		}

		section_start('notes',$imgpath);

		section_item($phpgw->link('/notes/preferences.php'),
		lang('Preferences'));

		section_item($phpgw->link('/preferences/acl_preferences.php','acl_app=notes'),
		lang('Grant Access'));

		section_item($phpgw->link('/preferences/categories.php','cats_app=notes&cats_level=True&global_cats=True'),
		lang('Edit categories'));

		section_end();
	}
?>