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

	if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
		$menu_title = lang('Administration');
		$file = Array(
			'Global Configuration' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			'Edit this Site' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_this_jinn_site'),
			'Add Site' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.add_edit_phpgw_jinn_sites'),
			'Browse through sites' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.browse_phpgw_jinn_sites'),
			'Import JiNN Site' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.import_phpgw_jinn_site'),
			'Access Rights' => $GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.access_rights')
		);
		display_sidebox($appname,$menu_title,$file);
	}
}
?>
