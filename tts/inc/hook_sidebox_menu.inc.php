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

	// $Id$
	// $Source$

	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	$file = Array(
		'New ticket'        => $GLOBALS['phpgw']->link('/tts/newticket.php'),
		'View all tickets' => $GLOBALS['phpgw']->link('/tts/index.php','filter=viewall'),
		'View only open tickets' => $GLOBALS['phpgw']->link('/tts/index.php')
	);
	display_sidebox($appname,$menu_title,$file);

	if ($GLOBALS['phpgw_info']['user']['apps']['preferences'])
	{
		$menu_title = lang('Preferences');
		$file = Array(
			'Preferences'		=> $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=tts'),
			'Edit Categories'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app='.$appname.'&cats_level=True&global_cats=True')
		);
		display_sidebox($appname,$menu_title,$file);
	}

	if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
		$menu_title = lang('Administration');
		$file = Array(
			'Admin options'     => $GLOBALS['phpgw']->link('/tts/admin.php'),
			'Global Categories' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=tts')
		);
		display_sidebox($appname,$menu_title,$file);
	}
