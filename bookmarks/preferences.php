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
		'currentapp'               => 'bookmarks',
		'enabled_nextmatchs_class' => True
	);
	include('../header.inc.php');
	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');

	$GLOBALS['phpgw']->template->set_file(array(
		'common'     => 'common.tpl',
		'body'       => 'useropt.body.tpl'
	));
	app_header(&$GLOBALS['phpgw']->template);
//  set_standard("user preferences",&$GLOBALS['phpgw']->template);

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
