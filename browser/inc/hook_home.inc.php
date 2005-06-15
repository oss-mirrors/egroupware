<?php
    /**************************************************************************\
    * eGroupWare - Skeleton Application                                        *
    * http://www.egroupware.org                                                *
    * -----------------------------------------------                          *
    *  This program is free software; you can redistribute it and/or modify it *
    *  under the terms of the GNU General Public License as published by the   *
    *  Free Software Foundation; either version 2 of the License, or (at your  *
    *  option) any later version.                                              *
    \**************************************************************************/

	/* $Id$ */

	$hp_display = (int)$GLOBALS['phpgw_info']['user']['preferences']['browser']['homepage_display'];
	if($hp_display > 0)
	{
		$obj = CreateObject('browser.ui');
		$obj->show_data_on_homepage();
	}

?>
