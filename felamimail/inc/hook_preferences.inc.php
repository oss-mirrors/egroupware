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
	$file = array(
		'Preferences'			=> $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=felamimail'),
		'Manage Folders'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=felamimail.uipreferences.listFolder')	
	);

	$mailPreferences = ExecMethod('felamimail.bopreferences.getPreferences');
	if($mailPreferences['imapEnableSieve'] == true)
	{
		$sieveLinkData = array
		(
			'menuaction'	=> 'felamimail.uisieve.listScripts',
			'action'	=> 'updateFilter'
		);
		$file['Manage EMailfilter / Vacation']	= $GLOBALS['phpgw']->link('/index.php',$sieveLinkData);
	}
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
