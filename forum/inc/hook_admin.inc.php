<?php
	/**************************************************************************\
	* phpGroupWare                                                             *
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
//			'Forum Administration' => $GLOBALS['phpgw']->link('/forum/admin/index.php','appname='.$appname)
			'Forum Administration' => $GLOBALS['phpgw']->link('/index.php','menuaction=forum.uiadmin.index')
		);

//Do not modify below this line
		$GLOBALS['phpgw']->common->display_mainscreen($appname,$file);
	}
?>
