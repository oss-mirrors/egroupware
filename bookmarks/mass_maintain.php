<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp' => 'bookmarks',
		'noheader'   => True,
		'nofooter'   => True,
		'nonavbar'   => True
	);
	include('../header.inc.php');

	$bookmarks = new bmark;

	if ($delete_x || $delete_y)
	{
		while (list(,$id) = each($item_cb))
		{
			$bookmarks->delete($id);
		}
		$phpgw->common->appsession('message','bookmarks',count($item_cb) . 'bookmarks have been deleted');
	}

	Header('Location: ' . $phpgw->link('/bookmarks/list.php'));
?>