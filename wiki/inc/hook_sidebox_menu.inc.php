<?php
  /**************************************************************************\
  * phpGroupWare - Calendar's Sidebox-Menu for idots-template                *
  * http://www.phpgroupware.org                                              *
  * Written by Pim Snel <pim@lingewoud.nl>                                   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{

	$menu_title = lang('Wiki Menu');
	$file = Array(
		'Recent Changes' => $GLOBALS['phpgw']->link('/wiki/index.php','page=RecentChanges'),
		'Preferences' => $GLOBALS['phpgw']->link('/wiki/index.php','action=prefs')
	);
	display_sidebox($appname,$menu_title,$file);

	if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
		$menu_title = lang('Wiki Administration');
		$file = Array(
			'Site Configuration' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			'Lock/Unlock Pages' => $GLOBALS['phpgw']->link('/wiki/index.php','action=admin&locking=1'),
			'Block/Unblock Hostes' => $GLOBALS['phpgw']->link('/wiki/index.php','action=admin&blocking=1')
		);
		display_sidebox($appname,$menu_title,$file);
	}
}
?>
