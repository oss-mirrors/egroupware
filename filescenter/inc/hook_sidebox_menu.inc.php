<?php
  /**************************************************************************\
  * eGroupWare - Calendar's Sidebox-Menu for idots-template                  *
  * http://www.egroupware.org                                                *
  * Written by Pim Snel <pim@lingewoud.nl>                                   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{

 /*
	This hookfile is for generating an app-specific side menu used in the idots
	template set.

	$menu_title speaks for itself
	$file is the array with link to app functions

	display_sidebox can be called as much as you like
 */

	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	$file = Array(
	array('','text'=>lang('FilesCenter Preferences'),'link'=>$GLOBALS['phpgw']->link('/preferences/preferences.php','appname=filescenter'))
	);

	display_sidebox($appname,$menu_title,$file);

	if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
		$menu_title = lang('FilesCenter Administration');
		$file = Array(
			array('','text'=>lang('Custom File Properties'),'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=filescenter.ui_fm2.custom_manager')),
			array('','text'=>lang('Manage File Types'),'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=filescenter.ui_fm2.mime_manager')),
			array('','text'=>lang('File ID Management'),'link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=filescenter.ui_fm2.prefix_manager'))
		);
		display_sidebox($appname,$menu_title,$file);
	}

}
?>
