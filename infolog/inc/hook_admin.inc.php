<?php
	/**************************************************************************\
	* phpGroupWare - Info Log administration                                   *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	{
		$file = Array
		(
			'Site configuration' => $GLOBALS['phpgw']->link('/index.php',array(
				'menuaction' => 'infolog.uiinfolog.admin' )),
			'Global Categories'  => $GLOBALS['phpgw']->link('/index.php',array(
				'menuaction' => 'admin.uicategories.index',
				'appname'    => $appname,
				'global_cats'=> True)),
			'Custom fields, typ and status' => $GLOBALS['phpgw']->link('/index.php',array(
				'menuaction' => 'infolog.uicustomfields.edit')),
			'CSV-Import'         => $GLOBALS['phpgw']->link('/infolog/csv_import.php')
		);

//Do not modify below this line
		if ($GLOBALS['phpgw']->common->public_functions['display_mainscreen'])
		{
			$GLOBALS['phpgw']->common->display_mainscreen($appname,$file);
		}
		else
		{
			display_section($appname,lang($appname),$file);	// for .14/6
		}
	}
?>
