<?php
  /**************************************************************************\
  * phpGroupWare - Calendar Holidays                                         *
  * http://www.phpgroupware.org                                              *
  * Written by Mark Peters <skeeter@phpgroupware.org>                        *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$lang = strtoupper($GLOBALS['phpgw_info']['user']['preferences']['common']['lang']); 
	$help_file = check_help_file($appname,$lang,'overview.php');
	if($help_file != '') 
	{ 
		$GLOBALS['treemenu'][] = '.<font face="'.$GLOBALS['phpgw_info']['theme']['font'].'">Overview</font>|'.$GLOBALS['phpgw']->link($help_file); 
	} 
?>
