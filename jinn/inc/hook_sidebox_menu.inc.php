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

	$menu_title = lang('JiNN Editors Menu');
	$file = Array(
		'Browse current object' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.browse_objects'),
		'Add new entry' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.display_form')
	);
	display_sidebox($appname,$menu_title,$file);

	$menu_title = lang('JiNN Preferences');
	$file = Array(
		'General Preferences' => $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=jinn'),
		'Configure this Object List View'=> $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.config_objects')
	);

	display_sidebox($appname,$menu_title,$file);

	if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
		$menu_title = lang('Administration');
		$file = Array(
			'Global Configuration' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			'Add Site' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.add_edit_site'),
			'Browse through sites' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.browse_phpgw_jinn_sites'),
			'Import JiNN Site' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.import_phpgw_jinn_site'),
			'Access Rights' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.access_rights'),
			'_NewLine_', // give a newline
			'Edit this Site' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_this_jinn_site'),
			'Edit this Site Object' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_this_jinn_site_object')
		);
		display_sidebox($appname,$menu_title,$file);

		$menu_title = lang('Developer Links');
		$file = Array(
			'Site Media and Documents' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiumedia.index')
		);
		display_sidebox($appname,$menu_title,$file);

	}

}
?>
