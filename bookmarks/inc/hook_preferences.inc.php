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

	$file = array(
		'Import Bookmarks' => $GLOBALS['phpgw']->link('/bookmarks/import.php'),
		'Grant access'     => $GLOBALS['phpgw']->link('/preferences/acl_preferences.php','acl_app=bookmarks'),
		'Categories'       => $GLOBALS['phpgw']->link('/preferences/categories.php','cats_app=bookmarks&global_cats=True')
	);
	display_section('Bookmarks','Bookmarks',$file);
?>
