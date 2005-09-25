<?php
	/**************************************************************************\
	* eGroupWare - mydms                                                       *
	* http://www.egroupware.org                                                *
	* This application is ported from Mydms                                    *
	*        by Lian Liming <dawnlinux@realss.com>                             *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/*Write the Root Folder name into the database*/
	$oProc->query("INSERT INTO phpgw_mydms_Folders VALUES (1, 'Root-Folder', 0, 'no comment', 1, 0, 2, 0)");

?>
