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
		'noheader'   => True,
		'nofooter'   => True,
		'nonavbar'   => True
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');
	$location_info = $GLOBALS['phpgw']->bookmarks->read_session_data();

	if ($delete_x || $delete_y)
	{
		if (is_array($item_cb))
		{
			while (list(,$id) = each($item_cb))
			{
				$GLOBALS['phpgw']->bookmarks->delete($id);
			}
			$GLOBALS['phpgw']->session->appsession('message','bookmarks',count($item_cb) . ' bookmarks have been deleted');
		}
	}

	if ($mail_x || $mail_y)
	{
		$mass_bm_id = serialize($item_cb);
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/maillink.php','mass_bm_id=' . urlencode($mass_bm_id)));
	}

	$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/' . $location_info['returnto']));
?>
