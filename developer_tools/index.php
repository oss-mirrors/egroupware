<?php
	/**************************************************************************\
	* phpGroupWare - Developer Tools                                           *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'developer_tools',
		'noheader'   => True,
		'nofooter'   => True
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->redirect_link('/index.php','menuaction=developer_tools.uilangfile.index');
