<?php
	/**************************************************************************\
	* phpGroupWare - Daily Comics Admin Hook File                              *
	* http://www.phpgroupware.org                                              *
	* This file written by Sam Wynn <neotexan@wynnsite.com>                    *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */
{
// Only Modify the $file and $title variables.....
	$title = 'Daily Comics';
	$file = Array(
		'Global Options'		=> $phpgw->link('/comic/admin_options.php'),
		'Global Comics'	=> $phpgw->link('/comic/admin_comics.php'),
		'Reset Comic Data'	=> $phpgw->link('/comic/admin_comics_reset.php')
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
