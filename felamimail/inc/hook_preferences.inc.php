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
	$title = $appname;
	$file = array(
		'Mail Settings' => $GLOBALS['phpgw']->link('/'.$appname.'/preferences_email.php'),
		'Message Highlighting' => $GLOBALS['phpgw']->link('/'.$appname.'/preferences_highlight.php'),
		'Index Order' => $GLOBALS['phpgw']->link('/'.$appname.'/preferences_index_order.php'),
		'Translation Preferences' => $GLOBALS['phpgw']->link('/'.$appname.'/preferences_translate.php'),
		'Display Preferences' => $GLOBALS['phpgw']->link('/'.$appname.'/preferences_display.php'),
		'Folder Preferences' => $GLOBALS['phpgw']->link('/'.$appname.'/preferences_folder.php'),
		'Manage Folders' => $GLOBALS['phpgw']->link('/'.$appname.'/folders.php')
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
