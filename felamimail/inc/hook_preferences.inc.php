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
// Only Modify the $file and $title variables.....
	$title = $appname;
	$sieveLinkData = array
	(
		'menuaction'	=> 'felamimail.uisieve.mainScreen',
		'action'	=> 'updateFilter'
	);
                                        
	$file = array(
		'Mail Settings '       	  => $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=felamimail'),
		'Mail Settings'           => $GLOBALS['phpgw']->link('/felamimail/preferences_email.php'),
		'Message Highlighting'    => $GLOBALS['phpgw']->link('/felamimail/preferences_highlight.php'),
		'Index Order'             => $GLOBALS['phpgw']->link('/felamimail/preferences_index_order.php'),
		'Translation Preferences' => $GLOBALS['phpgw']->link('/felamimail/preferences_translate.php'),
		'Display Preferences'     => $GLOBALS['phpgw']->link('/felamimail/preferences_display.php'),
		'Manage Sieve'     	  => $GLOBALS['phpgw']->link('/index.php',$sieveLinkData),
		'Folder Preferences'      => $GLOBALS['phpgw']->link('/felamimail/preferences_folder.php'),
		'Manage Folders '	  => $GLOBALS['phpgw']->link('/index.php','menuaction=felamimail.uipreferences.listFolder'),
		'Manage Folders'          => $GLOBALS['phpgw']->link('/felamimail/folders.php')	
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
