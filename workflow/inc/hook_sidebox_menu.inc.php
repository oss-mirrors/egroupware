<?php
    /**************************************************************************\
    * eGroupWare - Knowledge Base                                              *
    * http://www.egroupware.org                                                *
    * -----------------------------------------------                          *
    *  This program is free software; you can redistribute it and/or modify it *
    *  under the terms of the GNU General Public License as published by the   *
    *  Free Software Foundation; either version 2 of the License, or (at your  *
    *  option) any later version.                                              *
    \**************************************************************************/

	/* $Id$ */
{
	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	$file=Array(
		'Admin Processes'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_adminprocesses.form'),
		'Monitor Processes'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorprocesses.form'),
		'Monitor Activities'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitoractivities.form'),
		'Monitor Instances'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorinstances.form'),
		'Monitor Work Items'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorworkitems.form'),
		'User Processes'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_userprocesses.form'),
		'User Activities'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_useractivities.form'),
		'User Instances'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_userinstances.form')
	);
	display_sidebox($appname,$menu_title,$file);
}
?>
