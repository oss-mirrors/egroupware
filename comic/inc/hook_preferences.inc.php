<?php
  /**************************************************************************\
  * phpGroupWare - Comic Prefs                                               *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */

{
	echo "<p>\n";

	$imgfile = $phpgw->common->get_image_dir("comic")."/" . $appname .".gif";
	if (file_exists($imgfile))
	{
		$imgpath = $phpgw->common->get_image_path("comic")."/" . $appname .".gif";
	}
	else
	{
		$imgfile = $phpgw->common->get_image_dir("comic")."/navbar.gif";
		if (file_exists($imgfile))
		{
			$imgpath = $phpgw->common->get_image_path("comic")."/navbar.gif";
		}
		else
		{
			$imgpath = "";
		}
	}

	section_start("Daily Comics",$imgpath);

	section_item($phpgw->link('/comic/preferences.php'),lang("Preferences"));

	section_end(); 
}
?>
