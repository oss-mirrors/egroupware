<?php

/*************************************************************************\
* Daily Comics (phpGroupWare application)                                 *
* http://www.phpgroupware.org                                             *
* This file is written by: Sam Wynn <neotexan@wynnsite.com>               *
*                          Rick Bakker <r.bakker@linvision.com>           *
* --------------------------------------------                            *
* This program is free software; you can redistribute it and/or modify it *
* under the terms of the GNU General Public License as published by the   *
* Free Software Foundation; either version 2 of the License, or (at your  *
* option) any later version.                                              *
\*************************************************************************/

/* $Id$ */

{
// Only Modify the $file and $title variables.....
	$title = 'Daily Comics';
	$file = Array(
		'Global Options'	=> $phpgw->link('/index.php','menuaction=comic.uiadmin.global_options'),
		'Global Comics'         => $phpgw->link('/index.php','menuaction=comic.uiadmin.global_comics'),
		'Reset Comic Data'	=> $phpgw->link('/index.php','menuaction=comic.uiadmin.reset_comic_data')
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}

?>
