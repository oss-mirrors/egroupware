<?php
	/**************************************************************************\
	* phpGroupWare - Stock Quotes                                              *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

{
// Only Modify the $file and $title variables.....
	$title = 'Stock Quotes';
	$file = Array(
		'Select displayed stocks'	=> $phpgw->link('/stocks/preferences.php')
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>

