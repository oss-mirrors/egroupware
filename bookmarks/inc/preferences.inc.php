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

	$img = '/' . $appname . '/images/' . $appname . '.gif';
	if (file_exists($phpgw_info['server']['server_root'] . $img))
	{
		$img = $phpgw_info['server']['webserver_url'] . $img;
	}
	else
	{
		$img = '/' . $appname . '/images/navbar.gif';
		if (file_exists($phpgw_info['server']['server_root'] . $img))
		{
			$img = $phpgw_info['server']['webserver_url'] . $img;
		}
		else
		{
			$img = '';
		}
	}
	section_start(ucfirst($appname),$img);

	echo '<a href="' . $phpgw->link('/bookmarks/preferences.php') . ">' . lang('Bookmark preferences') . '</a>';

	section_end(); 
}
?>
