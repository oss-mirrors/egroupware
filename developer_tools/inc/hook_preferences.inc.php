<?php
	/**************************************************************************\
	* phpGroupWare                                                             *
	* http://www.phpgroupware.org                                              *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$file  = array(
		'Categories' => $GLOBALS['phpgw']->link('/preferences/categories.php','cats_app=developer_tools&global_cats=True'),
		'SF Project tracker preferences' => $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uisf_project_tracker.preferences')
	);

	display_section('developer_tools','Developer Tools',$file);
?>
