<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{
	echo "<p>\n";
	$imgfile = $GLOBALS['phpgw']->common->get_image_dir($appname) . '/' . $appname . '.gif';
	if (file_exists($imgfile))
	{
		$imgpath = $GLOBALS['phpgw']->common->get_image_path($appname) . '/' . $appname . '.gif';
	}
	else
	{
		$imgfile = $GLOBALS['phpgw']->common->get_image_dir($appname) . '/navbar.gif';
		if (file_exists($imgfile))
		{
			$imgpath = $GLOBALS['phpgw']->common->get_image_path($appname) . '/navbar.gif';
		}
		else
		{
			$imgpath = '';
		}
	}

	section_start(ucfirst($appname),$imgpath);

	section_item($GLOBALS['phpgw']->link('/felamimail/preferences_email.php'),lang('Mail Settings'));
	section_item($GLOBALS['phpgw']->link('/felamimail/preferences_highlight.php'),lang('Message Highlighting'));
	section_item($GLOBALS['phpgw']->link('/felamimail/preferences_index_order.php'),lang('Index Order'));
	section_item($GLOBALS['phpgw']->link('/felamimail/preferences_translate.php'),lang('Translation Preferences'));
	section_item($GLOBALS['phpgw']->link('/felamimail/preferences_display.php'),lang('Display Preferences'));
	section_item($GLOBALS['phpgw']->link('/felamimail/preferences_folder.php'),lang('Folder Preferences'));
	section_item($GLOBALS['phpgw']->link('/felamimail/folders.php'),lang('Manage Folders'));

	section_end(); 
}
?>
