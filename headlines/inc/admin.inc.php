<?php
  /**************************************************************************\
  * phpGroupWare - news headlines                                            *
  * http://www.phpgroupware.org                                              *
  * Written by Mark Peters <mpeters@satx.rr.com>                             *
  * Based on pheadlines 0.1 19991104 by Dan Steinman <dan@dansteinman.com>   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

{
	$img = '/' . $appname . '/images/' . $appname .'.gif';
	if (file_exists($GLOBALS['phpgw_info']['server']['server_root'].$img))
	{
		$img = $GLOBALS['phpgw_info']['server']['webserver_url'].$img;
	}
	else
	{
		$img = '/' . $appname . '/images/navbar.gif';
		if (file_exists($GLOBALS['phpgw_info']['server']['server_root'].$img))
		{
			$img = $GLOBALS['phpgw_info']['server']['webserver_url'].$img;
		}
		else
		{
			$img = '';
		}
	}
	section_start('Headlines',$img);

	echo '<a href="' . $GLOBALS['phpgw']->link('/headlines/admin.php') . '">' . lang('Edit headline sites') . '</a><br>';
	echo '<a href="' . $GLOBALS['phpgw']->link('/headlines/preferences.php','editDefault=1') . '">' . lang('Edit headlines shown by default') . '</a>';

	section_end(); 
}
?>
