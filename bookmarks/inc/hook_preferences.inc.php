<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */
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

	section_start(ucfirst($appname),$imgpath);


	echo '<a href="' . $phpgw->link('/bookmarks/preferences.php')
		. '">' . lang('Bookmark preferences') . '</a>';

	echo '<br><a href="' . $phpgw->link('/preferences/acl_preferences.php','acl_app=bookmarks')
		. '">' . lang('Grant Bookmark access') . '</a>';

	echo '<br><a href="' . $phpgw->link('/preferences/categories.php','cats_app=bookmarks')
		. '">' . lang('Bookmark categorys') . '</a>';


	section_end(); 
}
?>
