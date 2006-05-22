<?php
	/**************************************************************************\
	* eGroupWare - JiNN Preferences                                            *
	* http://www.egroupware.org                                                *
	* Written by Pim Snel <pim@egroupware.org>                                 *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; version 2 of the License.                     *
	\**************************************************************************/

	/* $Id$ */
	{
		$title = $appname;
		$file = Array(
			'Preferences' => $GLOBALS['phpgw']->link('/preferences/preferences.php','appname='.$appname)
		);
		display_section($appname,$title,$file);
	}
