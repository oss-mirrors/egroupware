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

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'bookmarks',
		'nonavbar'   => True,
		'noheader' => True
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');
	$location_info    = $GLOBALS['phpgw']->bookmarks->read_session_data();

	if (is_array($location_info))
	{
		$extravars = 'bm_cat=' . $location_info['bm_cat'];
	}

	if (is_array($location_info) && $location_info['returnto'])
	{
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/' . $location_info['returnto'],$extravars));
	}
	else
	{
		if ($GLOBALS['phpgw_info']['user']['preferences']['bookmarks']['defaultview'] == 'tree')
		{
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/tree.php',$extravars));
		}
		else
		{
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/list.php',$extravars));
		}
	}
?>
